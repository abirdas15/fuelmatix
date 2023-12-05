<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\ShiftSale;
use App\Models\Transaction;
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
        $startDate = date("Y-m-d", strtotime("- 15 day"));
        $endDate = date('Y-m-d');
        $shiftSale = ShiftSale::select(DB::raw('SUM(consumption) as quantity'), DB::raw('SUM(amount) as amount'), 'date')
            ->whereBetween('date',[$startDate, $endDate])
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->groupBy('date')
            ->get()
            ->keyBy('date')
            ->toArray();
        $month = [];
        $amount = [];
        $quantity = [];
        for($i = strtotime($startDate); $i <= strtotime($endDate); $i = $i + 86400) {
            $month[] = date('d M', $i);
            $date = date('Y-m-d', $i);
            $amount[] = isset($shiftSale[$date]) ? $shiftSale[$date]['amount'] : 0;
            $quantity[] = isset($shiftSale[$date]) ? $shiftSale[$date]['quantity'] : 0;
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
        $queryResult = Invoice::select(DB::raw('SUM(invoices.amount - invoices.paid_amount) as amount'), 'categories.name')
            ->leftJoin('categories', 'categories.id', '=', 'invoices.category_id')
            ->where('invoices.client_company_id', $sessionUser['client_company_id'])
            ->having('amount','>', 0)
            ->groupBy('invoices.category_id')
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
            ->having('amount', '>', 0)
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
