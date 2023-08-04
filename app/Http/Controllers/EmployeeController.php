<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Models\Category;
use App\Repository\EmployeeRepository;
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
        $category = new Category();
        $category->category = $requestData['name'];
        $category->parent_category = $salaryExpense->id;
        $category->type = $salaryExpense->type;
        $category->rfid = $requestData['rfid'] ?? null;
        $category->others = json_encode($others);
        $category->client_company_id = $requestData['session_user']['client_company_id'];
        if ($category->save()) {
            $category->updateCategory();
            return response()->json(['status' => 200, 'message' => 'Successfully saved employee.']);
        }
        return response()->json(['status' => 500, 'errors' => 'Cannot saved employee.']);
    }
    public function list(Request $request)
    {
        $requestData = $request->all();
        $result = EmployeeRepository::list($requestData);
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
        $category->category = $requestData['name'];
        $category->parent_category = $salaryExpense->id;
        $category->rfid = $requestData['rfid'] ?? null;
        $category->others = json_encode($others);
        if ($category->save()) {
            $category->updateCategory();
            return response()->json(['status' => 200, 'message' => 'Successfully updated employee.']);
        }
        return response()->json(['status' => 500, 'errors' => 'Cannot updated employee.']);
    }
}
