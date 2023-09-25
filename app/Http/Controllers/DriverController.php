<?php

namespace App\Http\Controllers;

use App\Common\Module;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DriverController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validation = Validator::make($requestData, [
            'company_id' => 'required|integer',
            'name' => 'required|string',
            'phone_number' => 'required|string',
            'email' => 'email'
        ]);
        if ($validation->fails()) {
            return response()->json(['status' => 500, 'errors' => $validation->errors()]);
        }
        $company = Category::find($request['company_id']);
        if (!$company instanceof Category) {
            return response()->json(['status' => 500, 'errors' => 'Cannot find [company]']);
        }
        $category = Category::where('module', Module::DRIVER_TIPS)->where('module_id', $requestData['company_id'])->first();
        if (!$category instanceof Category) {
            $driverSaleData = [
                'name' => $company['category'],
                'category_id' => $request['company_id']
            ];
            $category = CreditCompanyController::saveCompanyDriverCategory($driverSaleData);
        }
        $sessionUser = SessionUser::getUser();
        $others = [
            'email' => $requestData['email'] ?? null,
            'phone_number' => $requestData['phone_number'] ?? null,
        ];
        $categoryModel = new Category();
        $categoryModel->category = $requestData['name'];
        $categoryModel->parent_category = $category->id;
        $categoryModel->type = $category->type;
        $categoryModel->others = json_encode($others);
        $categoryModel->module = Module::DRIVER;
        $categoryModel->module_id = $request['company_id'];
        $categoryModel->client_company_id = $sessionUser['client_company_id'];
        if ($categoryModel->save()) {
            $categoryModel->updateCategory();
            return response()->json(['status' => 200, 'message' => 'Successfully saved driver.']);
        }
        return response()->json(['status' => 500, 'errors' => 'Cannot saved driver.']);
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
        $company_id = $inputData['company_id'] ?? '';
        $sessionUser = SessionUser::getUser();
        $result = Category::select('categories.id', 'categories.category as driver_name', 'categories.others', 'company.category as company_name')
            ->leftJoin('categories as company', 'company.id', '=' , 'categories.module_id')
            ->where('categories.client_company_id', $sessionUser['client_company_id'])
            ->where('categories.module', Module::DRIVER);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('categories.category', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('company.category', 'LIKE', '%'.$keyword.'%');
            });
        }
        if (!empty($company_id)) {
            $result->where(function($q) use ($company_id) {
                $q->where('categories.module_id', $company_id);
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
        foreach ($result as &$data) {
            $others = json_decode($data['others']);
            $data['email'] = $others != null ? $others->email : null;
            $data['phone_number'] = $others != null ? $others->phone_number : null;
            unset($data['others']);
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function single(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = Category::select('id', 'category as name', 'others', 'module_id as company_id')->find($requestData['id']);
        $others = json_decode($result['others']);
        $result['email'] = $others != null ? $others->email : null;
        $result['phone_number'] = $others != null ? $others->phone_number : null;
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
        $validation = Validator::make($requestData, [
            'id' => 'required|integer',
            'company_id' => 'required|integer',
            'name' => 'required|string',
            'phone_number' => 'required|string',
            'email' => 'email'
        ]);
        if ($validation->fails()) {
            return response()->json(['status' => 500, 'errors' => $validation->errors()]);
        }
        $company = Category::find($request['company_id']);
        if (!$company instanceof Category) {
            return response()->json(['status' => 500, 'errors' => 'Cannot find [company]']);
        }
        $category = Category::where('module', Module::DRIVER_TIPS)->where('module_id', $requestData['company_id'])->first();
        if (!$category instanceof Category) {
            $driverSaleData = [
                'name' => $company['category'],
                'category_id' => $request['company_id']
            ];
            $category = CreditCompanyController::saveCompanyDriverCategory($driverSaleData);
        }
        $others = [
            'email' => $requestData['email'] ?? null,
            'phone_number' => $requestData['phone_number'] ?? null,
        ];
        $categoryModel = Category::find($requestData['id']);
        if (!$categoryModel instanceof Category) {
            return response()->json(['status' => 500, 'errors' => 'Cannot find driver.']);
        }
        $categoryModel->category = $requestData['name'];
        $categoryModel->parent_category = $category->id;
        $categoryModel->type = $category->type;
        $categoryModel->others = json_encode($others);
        $categoryModel->module = Module::DRIVER;
        $categoryModel->module_id = $request['company_id'];
        if ($categoryModel->save()) {
            $categoryModel->updateCategory();
            return response()->json(['status' => 200, 'message' => 'Successfully updated driver.']);
        }
        return response()->json(['status' => 500, 'errors' => 'Cannot updated driver.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validation = Validator::make($requestData, [
            'id' => 'required|integer',
        ]);
        if ($validation->fails()) {
            return response()->json(['status' => 500, 'errors' => $validation->errors()]);
        }
        $category = Category::find($requestData['id']);
        if (!$category instanceof Category) {
            return response()->json(['status' => 500, 'errors' => 'Cannot find [driver].']);
        }
        $transaction = Transaction::where('account_id', $requestData['id'])->orWhere('linked_id', $requestData['id'])->first();
        if ($transaction instanceof Transaction) {
            return response()->json(['status' => 500, 'errors' => 'Cannot delete [driver].']);
        }
        Category::where('id', $requestData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted driver.']);
    }
}
