<?php

namespace App\Http\Controllers;

use App\Helpers\SessionUser;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class IncomeStatementController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * */
    public static function get(Request $request): JsonResponse
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
    /**
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public static function getExpenses(string $start_date, string $end_date): array
    {
        $sessionUser = SessionUser::getUser();
        return Transaction::select('categories.name', DB::raw('SUM(credit_amount - debit_amount) as balance'))
            ->whereBetween('date', [$start_date, $end_date])
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->where('type', '=', 'expenses')
            ->groupBy('account_id')
            ->get()
            ->toArray();
    }
    /**
     * @param array $data
     * @return float
     */
    public static function getTotal(array $data): float
    {
        $total = 0;
        foreach ($data as $row) {
            $total = $total + $row['balance'];
        }
        return $total;
    }

    /**
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public static function getRevenue(string $start_date, string $end_date): array
    {
        $sessionUser = SessionUser::getUser();
        return Transaction::select('categories.name', DB::raw('SUM(debit_amount - credit_amount) as balance'))
            ->whereBetween('date', [$start_date, $end_date])
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->where('type', '=', 'income')
            ->groupBy('account_id')
            ->get()
            ->toArray();
    }
}
