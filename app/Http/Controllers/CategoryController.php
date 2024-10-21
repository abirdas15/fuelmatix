<?php

namespace App\Http\Controllers;

use App\Common\FuelMatixCategoryType;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $sessionUser = SessionUser::getUser();
        $transaction = Transaction::select(DB::raw('SUM(debit_amount) as debit_amount'), DB::raw('SUM(credit_amount) as credit_amount'), 'category_ids')
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->groupBy('account_id')
            ->get()
            ->toArray();
        $categories = Category::select('id', 'name', 'balance', 'parent_category', 'description', 'category_ids', 'type')
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->with(['children' => function($q) {
                $q->select('id', 'name', 'parent_category', 'balance', 'description', 'category_ids', 'type');
            }])
            ->whereNull('parent_category')
            ->get()
            ->toArray();
        $result = self::updateCategoryBalance($categories, $transaction);
        return response()->json(['status' => 200, 'data' => $result]);
    }

    /**
     * @param array $categories
     * @param array $transactions
     * @return array
     */
    public static function updateCategoryBalance(array $categories, array $transactions): array
    {
        $sessionUser = SessionUser::getUser();
        foreach ($categories as &$category) {
            foreach ($transactions as $transaction) {
                $categoryIds = json_decode($transaction['category_ids']);
                if (!empty($category['id']) && !empty($categoryIds)) {
                    if (in_array($category['id'], $categoryIds)) {
                        if ($category['type'] == 'expenses') {
                            $balance = $transaction['debit_amount'] - $transaction['credit_amount'];
                        } else if ($category['type'] == 'income') {
                            $balance = $transaction['credit_amount'] - $transaction['debit_amount'];
                        }  else if ($category['type'] == 'assets') {
                            $balance = $transaction['debit_amount'] - $transaction['credit_amount'];
                        } else if ($category['type'] == 'liabilities') {
                            $balance = $transaction['credit_amount'] - $transaction['debit_amount'];
                        } else if ($category['type'] == 'equity') {
                            $balance = $transaction['credit_amount'] - $transaction['debit_amount'];
                        }
                        $category['balance'] =  $category['balance'] + $balance;
                        $category['balance_format'] = number_format($category['balance'], $sessionUser['currency_precision']);
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
        if (!empty($inputData['equity'])) {
            $result->orWhere(function($q) use ($inputData) {
                $q->where('type', FuelMatixCategoryType::EQUITY)
                    ->where('client_company_id', $inputData['session_user']['client_company_id']);
            });
        }
        $result = $result->get()
            ->toArray();
        foreach ($result as &$data) {
            $category = json_decode($data['category_hericy']);
            $data['name'] = implode(' --> ', $category);
            unset($data['category_hericy']);
        }
        usort($result, function ($item1, $item2) {
            return $item1['name'] <=> $item2['name'];
        });
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'name' => 'required|string',
            'type' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $category = new Category();
        $category->name = $inputData['name'];
        $category->code = $inputData['code'] ?? null;
        $category->parent_category = !empty($inputData['parent_category']) ? $inputData['parent_category'] : null;
        $category->type = $inputData['type'];
        $category->description = $inputData['description'] ?? null;
        $category->client_company_id = $inputData['session_user']['client_company_id'];
        if (!$category->save()) {
            return response()->json(['status' => 400, 'message' => 'Cannot save [category]']);
        }
        $category->updateCategory();
        return response()->json(['status' => 200, 'message' => 'Successfully save category']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function single(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = Category::select('id', 'name', 'code', 'parent_category', 'type', 'description')
            ->where('id', $inputData['id'])
            ->first();
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required|integer',
            'name' => 'required|string',
            'type' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $category = Category::find($inputData['id']);
        if ($category instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [category].']);
        }
        $category->name = $inputData['name'];
        $category->code = $inputData['code'] ?? null;
        $category->parent_category = !empty($inputData['parent_category']) ? $inputData['parent_category'] : null;
        $category->type = $inputData['type'];
        $category->description = $inputData['description'] ?? null;
        if (!$category->save()) {
            return response()->json(['status' => 200, 'message' => 'Can not update category']);
        }
        $category->updateCategory();
        return response()->json(['status' => 200, 'message' => 'Successfully updated category']);
    }
}
