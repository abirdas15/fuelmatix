<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BalanceSheetController extends Controller
{
    public function get(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'date' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $assets = self::getAssets($inputData['date']);
        $liabilities = self::getLiabilities($inputData['date']);
        $equity = self::getEquity($inputData['date']);
        $result = [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'retain_earning' => self::getRetainEarning($inputData['date']),
            'total_asset' => self::getTotalAmount($assets),
            'total_liabilities' => self::getTotalAmount($liabilities),
            'total_equity' => self::getTotalAmount($equity),
        ];
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public static function getTotalAmount($transactions, $total = 0)
    {
        foreach ($transactions as $data) {
            $total = $total + $data['balance'];
            if (isset($data['children']) && count($data['children']) > 0) {
                $total = self::getTotalAmount($data['children'], $total);
            }
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
    public static function getEquity($date)
    {
        $categories = Category::select('id', 'category', 'balance', 'parent_category', 'description')
            ->where('type', 'equity')
            ->get()
            ->toArray();
        $transactions =  self::getTransactionAmount($date, 'assets');
        $categories =  self::addCategoryAmount($categories, $transactions);
        return self::buildTree($categories);
    }
    public static function getAssets($date)
    {
        $categories = Category::select('id', 'category', 'balance', 'parent_category', 'description')
            ->where('type', 'assets')
            ->get()
            ->toArray();
        $transactions =  self::getTransactionAmount($date, 'assets');
        $categories =  self::addCategoryAmount($categories, $transactions);
        return self::buildTree($categories);
    }
    public static function getLiabilities($date)
    {
        $categories = Category::select('id', 'category', 'balance', 'parent_category', 'description')
            ->where('type', 'liabilities')
            ->get()
            ->toArray();
        $transactions =  self::getTransactionAmount($date, 'liabilities');
        $categories =  self::addCategoryAmount($categories, $transactions);
        return self::buildTree($categories);
    }
    public static function buildTree($elements, $parentId = 0) {
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
    public function addCategoryAmount($categories, $transactions)
    {
        foreach ($categories as &$category) {
            $category['balance'] = isset($transactions[$category['id']]) ? $transactions[$category['id']]  : 0;
        }
        return $categories;
    }
    public static function getTransactionAmount($date, $type)
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
