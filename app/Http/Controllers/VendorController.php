<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Helpers\SessionUser;
use App\Models\Category;
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
            'name' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $accountPayable = Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('category', AccountCategory::ACCOUNT_PAYABLE)->first();
        if (!$accountPayable instanceof Category) {
            return response()->json(['status' => 500, 'error' => 'Cannot find vendor group.']);
        }
        $sessionUser = SessionUser::getUser();
        $categoryData = [
            'category' => $inputData['name'],
            'parent_category' => $accountPayable->id,
            'type' => $accountPayable->type,
            'client_company_id'=> $sessionUser['id']
        ];
        $newCategory = CategoryRepository::save($categoryData);
        if ($newCategory instanceof Category) {
            return response()->json(['status' => 200, 'message' => 'Successfully saved vendor.']);
        }
        return response()->json(['status' => 500, 'message' => 'Cannot save vendor.']);
    }
    public function list(Request $request)
    {
        $inputData = $request->all();
        $limit = isset($inputData['limit']) ? $inputData['limit'] : 10;
        $keyword = isset($inputData['keyword']) ? $inputData['keyword'] : '';
        $order_by = isset($inputData['order_by']) ? $inputData['order_by'] : 'id';
        $order_mode = isset($inputData['order_mode']) ? $inputData['order_mode'] : 'DESC';
        $bank = Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('category', AccountCategory::ACCOUNT_PAYABLE)->first();
        $result = Category::select('id', 'category as name')
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->where('parent_category', $bank->id);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('category', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
        return response()->json(['status' => 200, 'data' => $result]);
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
        $result = Category::select('id', 'category as name')->find($inputData['id']);
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function update(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
            'name' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $accountPayable = Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('category', AccountCategory::ACCOUNT_PAYABLE)->first();
        if ($accountPayable == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find vendor group.']);
        }
        $category = Category::find($inputData['id']);
        if ($category == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find bank.']);
        }
        $category->category = $inputData['name'];
        if ($category->save()) {
            $category->updateCategory();
            return response()->json(['status' => 200, 'message' => 'Successfully updated vendor.']);
        }
        return response()->json(['status' => 500, 'errors' => 'Cannot updated vendor.']);
    }
    public function delete(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        Category::where('id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted vendor.']);
    }
}
