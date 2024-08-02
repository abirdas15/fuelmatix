<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\FuelMatixStatus;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\ShiftSale;
use App\Models\ShiftTotal;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function get(Request $request): JsonResponse
    {
        $result = [
            'sale' => self::getShiftSale(),
            'invoice' => self::getInvoiceAmount(),
            'payable' => self::getPayableAmount(),
        ];
        return response()->json(['status' => 200, 'data' => $result]);
    }

    /**
     * Retrieves the shift sales data for the last 15 days.
     *
     * @return array[] An array containing the dates, amounts, and quantities for each day.
     */
    public static function getShiftSale(): array
    {
        // Get the current session user
        $sessionUser = SessionUser::getUser();

        // Define the date range: from 15 days ago to today, in the user's timezone
        $startDate = Carbon::now(SessionUser::TIMEZONE)->subDay(15);
        $endDate = Carbon::now(SessionUser::TIMEZONE);

        // Fetch shift sales data within the date range for the user's company, grouped by date
        $shiftSale = ShiftTotal::select(
            DB::raw('SUM(shift_sale.consumption) as quantity'), // Sum of consumption (quantity)
            DB::raw('SUM(shift_sale.amount) as amount'), // Sum of amount
            'start_date', // Start date of the shift
            DB::raw('DATE(start_date) as date') // Date portion of the start date
        )
            ->leftJoin('shift_sale', 'shift_total.id', '=', 'shift_sale.shift_id')
            ->whereBetween('shift_total.start_date', [$startDate, $endDate]) // Filter by date range
            ->where('shift_total.client_company_id', $sessionUser['client_company_id']) // Filter by company ID
            ->where('shift_total.status', FuelMatixStatus::END) // Filter by status
            ->groupBy(DB::raw('DATE(start_date)')) // Group by date
            ->get()
            ->keyBy('date') // Use the date as the key for easy access
            ->toArray();

        // Initialize arrays for storing the result
        $month = [];
        $amount = [];
        $quantity = [];

        // Iterate through each day in the date range
        while ($startDate->lessThanOrEqualTo($endDate)) {
            $month[] = $startDate->format('d M'); // Format date as 'day month'
            $date = $startDate->format('Y-m-d'); // Format date as 'year-month-day'

            // Get the amount and quantity for the current date, or 0 if not available
            $amount[] = isset($shiftSale[$date]) ? $shiftSale[$date]['amount'] : 0;
            $quantity[] = isset($shiftSale[$date]) ? $shiftSale[$date]['quantity'] : 0;

            // Move to the next day
            $startDate->addDay();
        }

        // Return the results
        return [
            'month' => $month, // Array of dates
            'amount' => $amount, // Array of amounts
            'quantity' => $quantity // Array of quantities
        ];
    }

    public static function getInvoiceAmount()
    {
        $sessionUser = SessionUser::getUser();
        $receivableCategory = Category::where('client_company_id', $sessionUser['client_company_id'])->where('slug', strtolower(AccountCategory::ACCOUNT_RECEIVABLE))->first();
        $queryResult = Transaction::select('categories.name', DB::raw('SUM(credit_amount - debit_amount) as amount'))
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->where('categories.parent_category', $receivableCategory->id)
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->having('amount', '!=', 0)
            ->groupBy('account_id')
            ->get()
            ->toArray();
        $data = [];
        $label = [];
        foreach ($queryResult as $row) {
            $data[] = $row['amount'];
            $label[] = $row['name'];
        }
        return [
            'label' => $label,
            'data' => $data
        ];
    }
    public static function getPayableAmount(): array
    {
        $sessionUser = SessionUser::getUser();
        $payableCategory = Category::where('client_company_id', $sessionUser['client_company_id'])->where('slug', strtolower(AccountCategory::ACCOUNT_PAYABLE))->first();
        $queryResult = Transaction::select('categories.name', DB::raw('SUM(debit_amount - credit_amount) as amount'))
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->where('categories.parent_category', $payableCategory->id)
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->having('amount', '!=', 0)
            ->groupBy('account_id')
            ->get()
            ->toArray();
        $data = [];
        $label = [];
        foreach ($queryResult as $row) {
            $data[] = $row['amount'];
            $label[] = $row['name'];
        }
        return [
            'label' => $label,
            'data' => $data
        ];
    }
}
