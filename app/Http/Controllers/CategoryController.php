<?php

namespace App\Http\Controllers;

use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function list(Request $request)
    {
        $inputData = $request->all();
        $sessionUser = SessionUser::getUser();
        $transaction = Transaction::select(DB::raw('SUM(debit_amount) as debit_amount'), DB::raw('SUM(credit_amount) as credit_amount'), 'category_ids')
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->groupBy('account_id')
            ->get()
            ->toArray();
        $categories = Category::select('id', 'category', 'balance', 'parent_category', 'description', 'category_ids', 'type')
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->with(['children' => function($q) {
                $q->select('id', 'category', 'parent_category', 'balance', 'description', 'category_ids', 'type');
            }])
            ->whereNull('parent_category')
            ->get()
            ->toArray();
        $result = self::updateCategoryBalance($categories, $transaction);
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public static function updateCategoryBalance($categories, $transactions)
    {
        foreach ($categories as &$category) {
            foreach ($transactions as $transaction) {
                $categoryIds = json_decode($transaction['category_ids']);
                if (!empty($category['id']) && !empty($categoryIds)) {
                    if (in_array($category['id'], $categoryIds)) {
                        if ($category['type'] == 'expenses') {
                            $balance = $transaction['credit_amount'] - $transaction['debit_amount'];
                        } else if ($category['type'] == 'income') {
                            $balance = $transaction['debit_amount'] - $transaction['credit_amount'];
                        }  else if ($category['type'] == 'assets') {
                            $balance = $transaction['credit_amount'] - $transaction['debit_amount'];
                        } else if ($category['type'] == 'liabilities') {
                            $balance = $transaction['debit_amount'] - $transaction['credit_amount'];
                        } else if ($category['type'] == 'equity') {
                            $balance = $transaction['debit_amount'] - $transaction['credit_amount'];
                        }
                        $category['balance'] =  $category['balance'] + $balance;
                    }
                }
            }
            if (count($category['children'] )> 0) {
                $category['children'] = self::updateCategoryBalance($category['children'], $transactions);
            }
        }
        return $categories;
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function parent(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $result = Category::select('id', 'category_hericy', 'type')
            ->where('client_company_id', $inputData['session_user']['client_company_id']);
        if (!empty($inputData['type'])) {
            $result->where(function($q) use ($inputData) {
                $q->where('type', $inputData['type']);
            });
        }
        $result = $result->get()
            ->toArray();
        foreach ($result as &$data) {
            $category = json_decode($data['category_hericy']);
            $data['category'] = implode(' --> ', $category);
            unset($data['category_hericy']);
        }
        usort($result, function ($item1, $item2) {
            return $item1['category'] <=> $item2['category'];
        });
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function save(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'category' => 'required',
            'type' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        if ($inputData['type'] == 'expenses') {
            if ($inputData['account_category'] == '') {
                return response()->json(['status' => 500, 'errors' => ['account_category' => ['Account category field is required.']]]);
            }
        }
        $category = new Category();
        $category->category = $inputData['category'];
        $category->code = $inputData['code'] ?? null;
        $category->parent_category = !empty($inputData['parent_category']) ? $inputData['parent_category'] : null;
        $category->type = $inputData['type'];
        $category->description = $inputData['description'] ?? null;
        $category->account_category = $inputData['account_category'] ?? 0;
        $category->client_company_id = $inputData['session_user']['client_company_id'];
        if ($category->save()) {
            $category->updateCategory();
            return response()->json(['status' => 200, 'msg' => 'Successfully save category']);
        }
        return response()->json(['status' => 200, 'msg' => 'Can not save category']);
    }
    public function single(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = Category::select('id', 'category', 'code', 'parent_category', 'type', 'description', 'account_category')
            ->where('id', $inputData['id'])
            ->first();
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function update(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
            'category' => 'required',
            'type' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        if ($inputData['type'] == 'expenses') {
            if ($inputData['account_category'] == '') {
                return response()->json(['status' => 500, 'errors' => ['account_category' => ['Account category field is required.']]]);
            }
        }
        $category = Category::find($inputData['id']);
        $category->category = $inputData['category'];
        $category->code = $inputData['code'] ?? null;
        $category->parent_category = !empty($inputData['parent_category']) ? $inputData['parent_category'] : null;
        $category->type = $inputData['type'];
        $category->description = $inputData['description'] ?? null;
        $category->account_category = $inputData['account_category'] ?? 0;
        if ($category->save()) {
            $category->updateCategory();
            return response()->json(['status' => 200, 'msg' => 'Successfully update category']);
        }
        return response()->json(['status' => 200, 'msg' => 'Can not update category']);
    }
}
