<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function list(Request $request)
    {
        $inputData = $request->all();
        $transaction = Transaction::select(DB::raw('SUM(debit_amount) as total_debit_amount'), DB::raw('SUM(credit_amount) as total_credit_amount'), 'account_id')
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id')
            ->toArray();
        $categories = Category::select('id', 'category', 'balance', 'parent_category', 'description', 'category_ids', 'type')
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->with(['children' => function($q) {
                $q->select('id', 'category', 'parent_category', 'balance', 'description', 'category_ids', 'type');
            }])
            ->whereNull('parent_category')
            ->get()
            ->toArray();
        $result = self::categoryTree($categories, $transaction);
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public static function categoryTree($categories, $transaction)
    {
        foreach ($categories as &$category) {
            if (isset($transaction[$category['id']])) {
                self::updateCategoryBalance($category, $transaction[$category['id']]['amount']);
            }
        }
        return $categories;
    }
    public function updateCategoryBalance($category, $amount)
    {

    }
    public function parent(Request $request)
    {
        $inputData = $request->all();
        $result = Category::select('id', 'category_hericy', 'type')
            ->where('client_company_id', $inputData['session_user']['client_company_id']);
        if (isset($inputData['type']) && !empty($inputData['type'])) {
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
            if (!empty($inputData['parent_category'])) {
                $parentCategory = Category::select('category_hericy')->where('id', $inputData['parent_category'])->first();
                $category_hericy = json_decode($parentCategory['category_hericy']);
                array_push($category_hericy, $category->category);
                $category_hericy = json_encode($category_hericy);
            } else {
                $category_hericy = json_encode([$category->category]);
            }
            $category->category_hericy = $category_hericy;
            $category->save();
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
            if (!empty($inputData['parent_category'])) {
                $parentCategory = Category::select('category_hericy')->where('id', $inputData['parent_category'])->first();
                $category_hericy = json_decode($parentCategory['category_hericy']);
                array_push($category_hericy, $category->category);
                $category_hericy = json_encode($category_hericy);
            } else {
                $category_hericy = json_encode([$category->category]);
            }
            $category->category_hericy = $category_hericy;
            $category->save();
            return response()->json(['status' => 200, 'msg' => 'Successfully update category']);
        }
        return response()->json(['status' => 200, 'msg' => 'Can not update category']);
    }
}
