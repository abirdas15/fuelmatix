<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Transaction;
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
        $cost_of_good_sold = self::getTotalCostOfGoodSold($inputData['start_date'], $inputData['end_date']);
        $operating_expenses = self::getOperatingExpense($inputData['start_date'], $inputData['end_date']);
        $total_operating_expenses = self::getTotalOperatingExpense($operating_expenses);
        $gross_profit = $total_revenue - $cost_of_good_sold;
        $operating_profit = $gross_profit - $total_operating_expenses;
        $interest_expense = self::getCategoryExpense($inputData['start_date'], $inputData['end_date'],  AccountCategory::TAX);
        $evaporative_expense = self::getCategoryExpense($inputData['start_date'], $inputData['end_date'],  AccountCategory::EVAPORATIVE);
        $driver_sale = self::getCategoryExpense($inputData['start_date'], $inputData['end_date'],  AccountCategory::DRIVER_SALE);
        $income_before_text = $operating_profit - $interest_expense - $evaporative_expense - $driver_sale;
        $tax = self::getCategoryExpense($inputData['start_date'], $inputData['end_date'], AccountCategory::INTEREST_EXPENSE);
        $net_income = $income_before_text - $tax;
        $result = [
            'total_revenue' => $total_revenue,
            'cost_of_good_sold' => $cost_of_good_sold,
            'gross_profit' => $gross_profit,
            'operating_expenses' => $operating_expenses,
            'total_operating_expenses' => $total_operating_expenses,
            'operating_profit' => $operating_profit,
            'interest_expense' => $interest_expense,
            'evaporative_expense' => $evaporative_expense,
            'driver_sale' => $driver_sale,
            'income_before_tax' => $income_before_text,
            'tax' => $tax,
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
        $expenseCategory = Category::select('id')->where('client_company_id', $sessionUser['client_company_id'])->where('category', $category)->first();
        $result = Transaction::select(DB::raw('SUM(credit_amount - debit_amount) as balance'))
            ->whereBetween('date', [$start_date, $end_date])
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->whereJsonContains('category_ids', $expenseCategory->id)
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
        $operationExpenseCategory = Category::select('id')->where('client_company_id', $sessionUser['client_company_id'])->where('category', AccountCategory::OPERATING_EXPENSE)->first();
        return Transaction::select('categories.category', DB::raw('SUM(credit_amount - debit_amount) as balance'))
            ->whereBetween('date', [$start_date, $end_date])
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
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
        $result = Transaction::select(DB::raw('SUM(credit_amount - debit_amount) as balance'))
            ->whereBetween('date', [$start_date, $end_date])
            ->leftJoin('categories as c1', 'c1.id', '=', 'transactions.account_id')
            ->leftJoin('categories as c2', 'c2.id', '=', 'c1.parent_category')
            ->where('c2.category', AccountCategory::COST_OF_GOOD_SOLD)
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
        $result = Transaction::select(DB::raw('SUM(debit_amount - credit_amount) as balance'))
            ->whereBetween('date', [$start_date, $end_date])
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->where('type', '=', 'income')
            ->groupBy('categories.type')
            ->first();
        if ($result instanceof Transaction) {
            return $result['balance'];
        }
        return 0;
    }
}
