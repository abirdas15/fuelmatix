<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Transaction;
use App\Repository\ReportRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProfitLossController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function get(Request $request): JsonResponse
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

        $expenses = ReportRepository::getAllExpense([
            'start_date' => $inputData['start_date'],
            'end_date' => $inputData['end_date'],
        ]);
        $totalExpense = array_sum(array_column($expenses, '_amount'));
        $net_income = $total_revenue - $totalExpense;
        $result = [
            'total_revenue' => $total_revenue,
            'expenses' => $expenses,
            'total_expense' => number_format($totalExpense, 2),
            'net_income' => $net_income
        ];
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param string $start_date
     * @param string $end_date
     * @param string $category
     * @return mixed
     */
    public static function getCategoryExpense(string $start_date, string $end_date, string $category)
    {
        $sessionUser = SessionUser::getUser();
        $expenseCategory = Category::select('id')->where('client_company_id', $sessionUser['client_company_id'])->where('slug', strtolower($category))->first();
        $result = Transaction::select(DB::raw('SUM(credit_amount - debit_amount) as balance'))
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->whereBetween('date', [$start_date, $end_date])
            ->whereJsonContains('category_ids', $expenseCategory->id)
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->first();
        if ($result instanceof Transaction) {
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

    /**
     * @param string $start_date
     * @param string $end_date
     * @return mixed
     */
    public static function getOperatingExpense(string $start_date, string $end_date)
    {
        $sessionUser = SessionUser::getUser();
        $operationExpenseCategory = Category::select('id')->where('client_company_id', $sessionUser['client_company_id'])->where('slug', strtolower(AccountCategory::OPERATING_EXPENSE))->first();
        return Transaction::select('categories.name', DB::raw('SUM(credit_amount - debit_amount) as balance'))
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->whereBetween('date', [$start_date, $end_date])
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->whereJsonContains('category_ids', $operationExpenseCategory->id)
            ->groupBy('account_id')
            ->get()
            ->toArray();
    }
    /**
     * @param string $start_date
     * @param string $end_date
     * @return int|mixed
     */
    public static function getTotalCostOfGoodSold(string $start_date, string $end_date)
    {
        $sessionUser = SessionUser::getUser();
        $result = Transaction::select(DB::raw('SUM(credit_amount - debit_amount) as balance'))
            ->leftJoin('categories as c1', 'c1.id', '=', 'transactions.account_id')
            ->leftJoin('categories as c2', 'c2.id', '=', 'c1.parent_category')
            ->whereBetween('date', [$start_date, $end_date])
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->where('c2.slug', strtolower(AccountCategory::COST_OF_GOOD_SOLD))
            ->groupBy('c1.type')
            ->first();
        if ($result instanceof Transaction) {
            return $result['balance'];
        }
        return 0;
    }

    /**
     * @param string $start_date
     * @param string $end_date
     * @return int|mixed
     */
    public static function getTotalRevenue(string $start_date, string $end_date)
    {
        $sessionUser = SessionUser::getUser();
        $result = Transaction::select(DB::raw('SUM(debit_amount - credit_amount) as balance'))
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->whereBetween('date', [$start_date, $end_date])
            ->where('type', '=', 'income')
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->groupBy('categories.type')
            ->first();
        if ($result instanceof Transaction) {
            return $result['balance'];
        }
        return 0;
    }
}
