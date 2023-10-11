<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Models\Category;
use App\Repository\CategoryRepository;
use App\Repository\EmployeeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
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
        $salaryExpense = Category::where('client_company_id', $requestData['session_user']['client_company_id'])->where('slug', strtolower(AccountCategory::SALARY_EXPENSE))->first();
        if (!$salaryExpense instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [salary expense] category.']);
        }
        $others = [
            'position' => $requestData['position'] ?? null,
            'salary' => $requestData['salary'] ?? null,
        ];
        $categoryData = [
            'name' => $requestData['name'],
            'rfid' => $requestData['rfid'] ?? null,
            'others' => json_encode($others)
        ];
        $newCategory = CategoryRepository::saveCategory($categoryData, $salaryExpense['id']);
        if (!$newCategory instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot saved employee.']);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully saved employee.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $result = EmployeeRepository::list($requestData);
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
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = Category::select('id', 'name', 'others', 'rfid')->find($inputData['id']);
        $others = json_decode($result['others']);
        $result['position'] = $others != null ? $others->position : null;
        $result['salary'] = $others != null ? $others->salary : null;
        unset($result['others']);
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
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
            return response()->json(['status' => 400, 'message' => 'Cannot find [employee].']);
        }
        $others = [
            'position' => $requestData['position'] ?? null,
            'salary' => $requestData['salary'] ?? null,
        ];
        $categoryData = [
            'name' => $requestData['name'],
            'rfid' => $requestData['rfid'] ?? null,
            'others' => json_encode($others)
        ];
        $updateCategory = CategoryRepository::updateCategory($category, $categoryData);
        if (!$updateCategory instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot updated [employee].']);
        }
        $category->updateCategory();
        return response()->json(['status' => 200, 'message' => 'Successfully updated employee.']);
    }
}
