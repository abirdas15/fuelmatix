<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class IncomeStatementController extends Controller
{
    public static function get(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'start_date' => 'required',
            'end_date' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $revenue = self::getRevenue($inputData['start_date'], $inputData['end_date']);
        $total_revenue = self::getTotal($revenue);
        $expenses = self::getExpenses($inputData['start_date'], $inputData['end_date']);
        $total_expense = self::getTotal($expenses);
        $net_profit = $total_revenue - $total_expense;
        $result = [
            'revenue' => $revenue,
            'total_revenue' => $total_revenue,
            'expense' => $expenses,
            'total_expense' => $total_expense,
            'net_profit' => $net_profit
        ];
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public static function getExpenses($start_date, $end_date)
    {
        $result = Transaction::select('categories.name', DB::raw('SUM(credit_amount - debit_amount) as balance'))
            ->whereBetween('date', [$start_date, $end_date])
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->where('type', '=', 'expenses')
            ->groupBy('account_id')
            ->get()
            ->toArray();
        return $result;
    }
    public static function getTotal($data)
    {
        $total = 0;
        foreach ($data as $row) {
            $total = $total + $row['balance'];
        }
        return $total;
    }
    public static function getRevenue($start_date, $end_date)
    {
        $result = Transaction::select('categories.name', DB::raw('SUM(debit_amount - credit_amount) as balance'))
            ->whereBetween('date', [$start_date, $end_date])
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->where('type', '=', 'income')
            ->groupBy('account_id')
            ->get()
            ->toArray();
        return $result;
    }
}
