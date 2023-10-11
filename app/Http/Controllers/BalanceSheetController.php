<?php

namespace App\Http\Controllers;

use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BalanceSheetController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function get(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'date' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $sessionUser = SessionUser::getUser();
        $transactions = Transaction::select(DB::raw('SUM(debit_amount) as debit_amount'), DB::raw('SUM(credit_amount) as credit_amount'), 'category_ids')
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->where('date', '<=', $inputData['date'])
            ->groupBy('account_id')
            ->get()
            ->toArray();
        $assets = self::getAssets($transactions);
        $liabilities = self::getLiabilities($transactions);
        $equity = self::getEquity($transactions);
        $retain_earning = self::getRetainEarning($inputData['date']);
        $total_equity = $retain_earning + self::getTotalAmount($equity);
        $total_liabilities = self::getTotalAmount($liabilities);
        $result = [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'retain_earning' => $retain_earning,
            'total_asset' => self::getTotalAmount($assets),
            'total_liabilities' => $total_liabilities,
            'total_equity' => $total_equity,
            'total_equity_and_liabilities' => $total_equity + $total_liabilities,
        ];
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public static function getTotalAmount($transactions, $total = 0)
    {
        foreach ($transactions as $data) {
            $total = $total + $data['balance'];
        }
        return $total;
    }
    public static function getRetainEarning($date)
    {
        $income = self::getTransactionAmount($date, 'income');
        $income = array_sum($income);
        $expense = self::getTransactionAmount($date, 'expenses');
        $expense = array_sum($expense);
        return $income - $expense;
    }
    public static function getEquity($transactions)
    {
        $sessionUser = SessionUser::getUser();
        $categories = Category::select('id', 'name', 'balance', 'parent_category', 'description', 'category_ids', 'type')
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->with(['children' => function($q) {
                $q->select('id', 'name', 'parent_category', 'balance', 'description', 'category_ids', 'type');
            }])
            ->where('type', 'equity')
            ->whereNull('parent_category')
            ->get()
            ->toArray();
        return CategoryController::updateCategoryBalance($categories, $transactions);
    }
    public static function getAssets($transactions)
    {
        $sessionUser = SessionUser::getUser();
        $categories = Category::select('id', 'name', 'balance', 'parent_category', 'description', 'category_ids', 'type')
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->with(['children' => function($q) {
                $q->select('id', 'name', 'parent_category', 'balance', 'description', 'category_ids', 'type');
            }])
            ->where('type', 'assets')
            ->whereNull('parent_category')
            ->get()
            ->toArray();
        return CategoryController::updateCategoryBalance($categories, $transactions);
    }
    public static function getLiabilities($transactions)
    {
        $sessionUser = SessionUser::getUser();
        $categories = Category::select('id', 'name', 'balance', 'parent_category', 'description', 'category_ids', 'type')
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->with(['children' => function($q) {
                $q->select('id', 'name', 'parent_category', 'balance', 'description', 'category_ids', 'type');
            }])
            ->where('type', 'liabilities')
            ->whereNull('parent_category')
            ->get()
            ->toArray();
        return CategoryController::updateCategoryBalance($categories, $transactions);
    }

    /**
     * @param array $elements
     * @param int $parentId
     * @return array
     */
    public static function buildTree(array $elements, int $parentId = 0): array
    {
        $branch = [];
        foreach ($elements as $element) {
            if ($element['parent_category'] == $parentId) {
                $children = self::buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }

    /**
     * @param array $categories
     * @param array $transactions
     * @return array
     */
    public static function addCategoryAmount(array $categories, array $transactions): array
    {
        foreach ($categories as &$category) {
            $category['balance'] = $transactions[$category['id']] ?? 0;
        }
        return $categories;
    }

    /**
     * @param string $date
     * @param string $type
     * @return array
     */
    public static function getTransactionAmount(string $date, string $type): array
    {
        if ($type == 'assets') {
            $select = DB::raw('SUM(credit_amount - debit_amount) as balance');
        } else if ($type == 'liabilities') {
            $select = DB::raw('SUM(debit_amount - credit_amount) as balance');
        } else if ($type == 'equity') {
            $select = DB::raw('SUM(debit_amount - credit_amount) as balance');
        } else if ($type == 'income') {
            $select = DB::raw('SUM(debit_amount - credit_amount) as balance');
        } else if ($type == 'expenses') {
            $select = DB::raw('SUM(credit_amount - debit_amount) as balance');
        }
        $result = Transaction::select('account_id', $select)
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->where('date', '<=', $date)
            ->where('categories.type', $type)
            ->groupBy('account_id')
            ->get()
            ->toArray();
        $rv = [];
        foreach ($result as $data) {
            $rv[$data['account_id']] = $data['balance'];
        }
        return $rv;
    }

}
