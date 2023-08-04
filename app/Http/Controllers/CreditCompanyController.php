<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CreditCompanyController extends Controller
{
    public function save(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $accountReceivable = Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('category', AccountCategory::ACCOUNT_RECEIVABLE)->first();
        if ($accountReceivable == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find account receivable group.']);
        }
        $others = [
            'email' => $inputData['email'] ?? null,
            'phone' => $inputData['phone'] ?? null,
            'address' => $inputData['address'] ?? null,
            'contact_person' => $inputData['contact_person'] ?? null,
        ];
        $category = new Category();
        $category->category = $inputData['name'];
        $category->parent_category = $accountReceivable->id;
        $category->type = $accountReceivable->type;
        $category->credit_limit = $inputData['credit_limit'] ?? null;
        $category->others = json_encode($others);
        $category->client_company_id = $inputData['session_user']['client_company_id'];
        if ($category->save()) {
            $category->updateCategory();
            return response()->json(['status' => 200, 'message' => 'Successfully saved credit company.']);
        }
        return response()->json(['status' => 500, 'errors' => 'Cannot saved credit company.']);
    }
    public function list(Request $request)
    {
        $inputData = $request->all();
        $limit = isset($inputData['limit']) ? $inputData['limit'] : 10;
        $keyword = isset($inputData['keyword']) ? $inputData['keyword'] : '';
        $order_by = isset($inputData['order_by']) ? $inputData['order_by'] : 'id';
        $order_mode = isset($inputData['order_mode']) ? $inputData['order_mode'] : 'DESC';
        $accountReceivable = Category::select('id')->where('client_company_id', $inputData['session_user']['client_company_id'])->where('category', AccountCategory::ACCOUNT_RECEIVABLE)->first();
        $result = Category::select('id', 'category as name', 'credit_limit', 'others')
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->where('parent_category', $accountReceivable->id);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('category', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
        foreach ($result as &$data) {
            $others = json_decode($data['others']);
            $data['email'] = $others != null ? $others->email : null;
            $data['phone'] = $others != null ? $others->phone : null;
            $data['contact_person'] = $others != null ? $others->contact_person : null;
            $data['address'] = $others != null ? $others->address : null;
            unset($data['others']);
        }
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
        $result = Category::select('id', 'category as name', 'others', 'credit_limit')->find($inputData['id']);
        $others = json_decode($result['others']);
        $result['email'] = $others != null ? $others->email : null;
        $result['phone'] = $others != null ? $others->phone : null;
        $result['contact_person'] = $others != null ? $others->contact_person : null;
        $result['address'] = $others != null ? $others->address : null;
        unset($result['others']);
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function update(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $accountReceivable = Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('category', AccountCategory::ACCOUNT_RECEIVABLE)->first();
        if ($accountReceivable == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find account receivable group.']);
        }
        $category = Category::find($inputData['id']);
        if ($category == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find bank.']);
        }
        $others = [
            'email' => $inputData['email'] ?? null,
            'phone' => $inputData['phone'] ?? null,
            'address' => $inputData['address'] ?? null,
            'contact_person' => $inputData['contact_person'] ?? null,
        ];
        $category->category = $inputData['name'];
        $category->parent_category = $accountReceivable->id;
        $category->type = $accountReceivable->type;
        $category->credit_limit = $inputData['credit_limit'] ?? null;
        $category->others = json_encode($others);
        if ($category->save()) {
            $category->updateCategory();
            return response()->json(['status' => 200, 'message' => 'Successfully updated credit company.']);
        }
        return response()->json(['status' => 500, 'errors' => 'Cannot updated credit company.']);
    }
}
