<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\ShiftSale;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function get(Request $request)
    {
        $result = [
            'sale' => self::getShiftSale(),
            'invoice' => self::getInvoiceAmount(),
            'payable' => self::getPayableAmount(),
        ];
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public static function getShiftSale()
    {
        $sessionUser = SessionUser::getUser();
        $startDate = date('Y-01-01');
        $endDate = date('Y-12-31');
        $shiftSale = ShiftSale::select(DB::raw('SUM(consumption) as quantity'), DB::raw('SUM(amount) as amount'), DB::raw('MONTH(date) as month'))
            ->whereBetween('date',[$startDate, $endDate])
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->groupBy('month')
            ->get()
            ->keyBy('month')
            ->toArray();
        $month = [];
        $amount = [];
        $quantity = [];
        for($i = 1; $i <= 12; $i++) {
            $month[] = date('F', strtotime(date('Y-'.$i.'-01')));
            $amount[] = isset($shiftSale[$i]) ? $shiftSale[$i]['amount'] : 0;
            $quantity[] = isset($shiftSale[$i]) ? $shiftSale[$i]['quantity'] : 0;
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
        $result = Invoice::select(DB::raw('SUM(invoices.amount - invoices.paid_amount) as amount'), 'categories.category as name')
            ->leftJoin('categories', 'categories.id', '=', 'invoices.category_id')
            ->where('invoices.client_company_id', $sessionUser['client_company_id'])
            ->having('amount','>', 0)
            ->groupBy('invoices.category_id')
            ->get()
            ->toArray();
        return $result;
    }
    public static function getPayableAmount()
    {
        $sessionUser = SessionUser::getUser();
        $payableCategory = Category::where('client_company_id', $sessionUser['client_company_id'])->where('category', AccountCategory::ACCOUNT_PAYABLE)->first();
        $result = Transaction::select('categories.category as name', DB::raw('SUM(debit_amount - credit_amount) as amount'))
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->where('categories.parent_category', $payableCategory->id)
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->having('amount', '>', 0)
            ->groupBy('account_id')
            ->get()
            ->toArray();
        return $result;
    }
}
