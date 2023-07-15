<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VendorController extends Controller
{

    public function save(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'name' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $accountPayable = Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('slug', 'account-payable')->first();
        if ($accountPayable == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find vendor group.']);
        }
        $category_hericy = json_decode($accountPayable['category_hericy']);
        array_push($category_hericy, $inputData['name']);
        $category_hericy = json_encode($category_hericy);
        $category = new Category();
        $category->category = $inputData['name'];
        $category->slug = Str::slug($inputData['name'], '-');
        $category->parent_category = $accountPayable->id;
        $category->type = $accountPayable->type;
        $category->category_hericy = $category_hericy;
        $category->client_company_id = $inputData['session_user']['client_company_id'];
        if ($category->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully saved vendor.']);
        }
        return response()->json(['status' => 500, 'errors' => 'Cannot save vendor.']);
    }
    public function list(Request $request)
    {
        $inputData = $request->all();
        $limit = isset($inputData['limit']) ? $inputData['limit'] : 10;
        $keyword = isset($inputData['keyword']) ? $inputData['keyword'] : '';
        $order_by = isset($inputData['order_by']) ? $inputData['order_by'] : 'id';
        $order_mode = isset($inputData['order_mode']) ? $inputData['order_mode'] : 'DESC';
        $bank = Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('slug', 'account-payable')->first();
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
        $accountPayable = Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('slug', 'account-payable')->first();
        if ($accountPayable == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find vendor group.']);
        }
        $category_hericy = json_decode($accountPayable['category_hericy']);
        array_push($category_hericy, $inputData['name']);
        $category_hericy = json_encode($category_hericy);
        $category = Category::find($inputData['id']);
        if ($category == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find bank.']);
        }
        $category->category = $inputData['name'];
        $category->slug = Str::slug($inputData['name'], '-');
        $category->category_hericy = $category_hericy;
        if ($category->save()) {
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
