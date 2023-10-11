<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Models\Category;
use App\Models\Transaction;
use App\Repository\CategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'name' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $accountPayable = Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('slug', strtolower(AccountCategory::ACCOUNT_PAYABLE))->first();
        if (!$accountPayable instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [account payable] category.']);
        }
        $categoryData = [
            'name' => $inputData['name'],
        ];
        $newCategory = CategoryRepository::saveCategory($categoryData, $accountPayable['id']);
        if (!$newCategory instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot saved [vendor].']);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully saved vendor.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $limit = $inputData['limit'] ?? 10;
        $keyword = $inputData['keyword'] ?? '';
        $order_by = $inputData['order_by'] ?? 'id';
        $order_mode = $inputData['order_mode'] ?? 'DESC';
        $accountPayable = Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('slug', strtolower(AccountCategory::ACCOUNT_PAYABLE))->first();
        if (!$accountPayable instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [account payable] category.']);
        }
        $result = Category::select('id', 'name')
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->where('parent_category', $accountPayable['id']);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('name', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
        return response()->json(['status' => 200, 'data' => $result]);
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
        $result = Category::select('id', 'name')->find($inputData['id']);
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
            'id' => 'required',
            'name' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $category = Category::find($inputData['id']);
        if (!$category instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find bank.']);
        }
        $category->name = $inputData['name'];
        $categoryData = [
            'name' => $inputData['name'],
        ];
        $updateCategory = CategoryRepository::updateCategory($category, $categoryData);
        if (!$updateCategory instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot updated [vendor].']);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully updated vendor.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $transaction = Transaction::where('account_id', $inputData['id'])->orWhere('linked_id', $inputData['id'])->first();
        if ($transaction instanceof Transaction) {
            return response()->json(['status' => 400, 'message' => 'Cannot deleted [vendor].']);
        }
        Category::where('id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted vendor.']);
    }
}
