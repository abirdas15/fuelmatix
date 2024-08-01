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
     * @return array[]
     */
    public static function getShiftSale(): array
    {
        $sessionUser = SessionUser::getUser();
        $startDate = Carbon::now(SessionUser::TIMEZONE)->subDay(15);
        $endDate = Carbon::now(SessionUser::TIMEZONE);
        $shiftSale = ShiftTotal::select(DB::raw('SUM(shift_sale.consumption) as quantity'), DB::raw('SUM(shift_sale.amount) as amount'), 'start_date', DB::raw('DATE(start_date) as date'))
            ->leftJoin('shift_sale', 'shift_total.id', '=', 'shift_sale.shift_id')
            ->whereBetween('shift_total.start_date',[$startDate, $endDate])
            ->where('shift_total.client_company_id', $sessionUser['client_company_id'])
            ->where('shift_total.status', FuelMatixStatus::END)
            ->groupBy(DB::raw('DATE(start_date)'))
            ->get()
            ->keyBy('date')
            ->toArray();
        $month = [];
        $amount = [];
        $quantity = [];
        while ($startDate->lessThanOrEqualTo($endDate)) {
            $month[] = $startDate->format('d M');
            $date = $startDate->format('Y-m-d');
            $amount[] = isset($shiftSale[$date]) ? $shiftSale[$date]['amount'] : 0;
            $quantity[] = isset($shiftSale[$date]) ? $shiftSale[$date]['quantity'] : 0;
            $startDate->addDay();
        }
        return [
            'month' => $month,
            'amount' => $amount,
            'quantity' => $quantity
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
