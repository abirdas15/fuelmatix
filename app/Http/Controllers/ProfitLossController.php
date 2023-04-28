<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProfitLossController extends Controller
{
    public function get(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'start_date' => 'required',
            'end_date' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $total_revenue = self::getTotalRevenue($inputData['start_date'], $inputData['end_date']);
        $cost_of_good_sold = self::getTotalCostOfGoodSold($inputData['start_date'], $inputData['end_date']);
        $operating_expenses = self::getOperatingExpense($inputData['start_date'], $inputData['end_date']);
        $total_operating_expenses = self::getTotalOperatingExpense($operating_expenses);
        $gross_profit = $total_revenue - $cost_of_good_sold;
        $operating_profit = $gross_profit - $total_operating_expenses;
        $interest_expense = self::getInterestExpense($inputData['start_date'], $inputData['end_date']);
        $income_before_text = $operating_profit - $interest_expense;
        $tax = self::getTax($inputData['start_date'], $inputData['end_date']);
        $net_income = $income_before_text - $tax;
        $result = [
            'total_revenue' => $total_revenue,
            'cost_of_good_sold' => $cost_of_good_sold,
            'gross_profit' => $gross_profit,
            'operating_expenses' => $operating_expenses,
            'total_operating_expenses' => $total_operating_expenses,
            'operating_profit' => $operating_profit,
            'interest_expense' => $interest_expense,
            'income_before_tax' => $income_before_text,
            'tax' => $tax,
            'net_income' => $net_income
        ];
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public static function getTax($start_date, $end_date)
    {
        $result = Transaction::select(DB::raw('SUM(debit_amount - credit_amount) as balance'))
            ->whereBetween('date', [$start_date, $end_date])
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->where('account_category', 4)
            ->groupBy('categories.type')
            ->first();
        if ($result != null) {
            return $result['balance'];
        }
        return 0;
    }
    public static function getInterestExpense($start_date, $end_date)
    {
        $result = Transaction::select(DB::raw('SUM(debit_amount - credit_amount) as balance'))
            ->whereBetween('date', [$start_date, $end_date])
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->where('account_category', 3)
            ->groupBy('categories.type')
            ->first();
        if ($result != null) {
            return $result['balance'];
        }
        return 0;
    }
    public static function getTotalOperatingExpense($expenses)
    {
        $total = 0;
        foreach ($expenses as $expense) {
            $total = $total + $expense['balance'];
        }
        return $total;
    }
    public static function getOperatingExpense($start_date, $end_date)
    {
        $result = Transaction::select('categories.category', DB::raw('SUM(credit_amount - debit_amount) as balance'))
            ->whereBetween('date', [$start_date, $end_date])
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->where('account_category', 2)
            ->groupBy('account_id')
            ->get()
            ->toArray();
        return $result;
    }
    public static function getTotalCostOfGoodSold($start_date, $end_date)
    {
        $result = Transaction::select(DB::raw('SUM(debit_amount - credit_amount) as balance'))
            ->whereBetween('date', [$start_date, $end_date])
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->where('account_category', 1)
            ->groupBy('categories.type')
            ->first();
        if ($result != null) {
            return $result['balance'];
        }
        return 0;
    }
    public static function getTotalRevenue($start_date, $end_date)
    {
        $result = Transaction::select(DB::raw('SUM(debit_amount - credit_amount) as balance'))
            ->whereBetween('date', [$start_date, $end_date])
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->where('type', '=', 'income')
            ->where('account_category', '!=', 1)
            ->groupBy('categories.type')
            ->first();
        if ($result != null) {
            return $result['balance'];
        }
        return 0;
    }
}
