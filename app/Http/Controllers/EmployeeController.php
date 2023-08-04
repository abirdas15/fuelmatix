<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    public function save(Request $request)
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'name' => 'required',
            'position' => 'required',
            'salary' => 'required',
            'rfid' => 'required|unique:categories,rfid'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $salaryExpense = Category::where('client_company_id', $requestData['session_user']['client_company_id'])->where('category', AccountCategory::SALARY_EXPENSE)->first();
        if ($salaryExpense == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find account salary expense group.']);
        }
        $others = [
            'position' => $requestData['position'] ?? null,
            'salary' => $requestData['salary'] ?? null,
        ];
        $category_hericy = json_decode($salaryExpense['category_hericy']);
        array_push($category_hericy, $requestData['name']);
        $category_hericy = json_encode($category_hericy);
        $category = new Category();
        $category->category = $requestData['name'];
        $category->parent_category = $salaryExpense->id;
        $category->type = $salaryExpense->type;
        $category->category_hericy = $category_hericy;
        $category->rfid = $requestData['rfid'] ?? null;
        $category->others = json_encode($others);
        $category->client_company_id = $requestData['session_user']['client_company_id'];
        if ($category->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully saved employee.']);
        }
        return response()->json(['status' => 500, 'errors' => 'Cannot saved employee.']);
    }
    public function list(Request $request)
    {
        $inputData = $request->all();
        $limit = isset($inputData['limit']) ? $inputData['limit'] : 10;
        $keyword = isset($inputData['keyword']) ? $inputData['keyword'] : '';
        $order_by = isset($inputData['order_by']) ? $inputData['order_by'] : 'id';
        $order_mode = isset($inputData['order_mode']) ? $inputData['order_mode'] : 'DESC';
        $salaryExpense = Category::select('id')->where('client_company_id', $inputData['session_user']['client_company_id'])->where('category', AccountCategory::SALARY_EXPENSE)->first();
        $result = Category::select('id', 'category as name', 'rfid', 'others')
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->where('parent_category', $salaryExpense->id);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('category', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
        foreach ($result as &$data) {
            $others = json_decode($data['others']);
            $data['position'] = $others != null ? $others->position : null;
            $data['salary'] = $others != null ? $others->salary : null;
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
        $result = Category::select('id', 'category as name', 'others', 'rfid')->find($inputData['id']);
        $others = json_decode($result['others']);
        $result['position'] = $others != null ? $others->position : null;
        $result['salary'] = $others != null ? $others->salary : null;
        unset($result['others']);
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function update(Request $request)
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required',
            'name' => 'required',
            'position' => 'required',
            'salary' => 'required',
            'rfid' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $category = Category::where('id', '!=', $requestData['id'])->where('client_company_id', $requestData['session_user']['client_company_id'])->where('rfid', $requestData['rfid'])->first();
        if ($category instanceof Category) {
            return response()->json(['status' => 500, 'errors' => ['rfid' => ['The RFID is already have been taken.']]]);
        }
        $category = Category::find($requestData['id']);
        if (!$category instanceof Category) {
            return response()->json(['status' => 500, 'error' => 'Cannot find employee.']);
        }
        $salaryExpense = Category::where('client_company_id', $requestData['session_user']['client_company_id'])->where('category', AccountCategory::SALARY_EXPENSE)->first();
        if ($salaryExpense == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find account salary expense group.']);
        }
        $others = [
            'position' => $requestData['position'] ?? null,
            'salary' => $requestData['salary'] ?? null,
        ];
        $category_hericy = json_decode($salaryExpense['category_hericy']);
        array_push($category_hericy, $requestData['name']);
        $category_hericy = json_encode($category_hericy);
        $category->category = $requestData['name'];
        $category->parent_category = $salaryExpense->id;
        $category->category_hericy = $category_hericy;
        $category->rfid = $requestData['rfid'] ?? null;
        $category->others = json_encode($others);
        if ($category->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully updated employee.']);
        }
        return response()->json(['status' => 500, 'errors' => 'Cannot updated employee.']);
    }
}
