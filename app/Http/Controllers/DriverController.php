<?php

namespace App\Http\Controllers;

use App\Common\Module;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Driver;
use App\Models\Transaction;
use App\Repository\CategoryRepository;
use App\Repository\DriverRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $data = [
            'name' => $requestData['name'],
            'module_id' => $requestData['company_id']
        ];
        $sessionUser = SessionUser::getUser();
        $driverSaleCategory = Category::where('module', Module::DRIVER_SALE)->where('module_id', $requestData['company_id'])->where('client_company_id', $sessionUser['client_company_id'])->first();
        if (!$driverSaleCategory instanceof Category) {
            return response()->json(['status' => 500, 'error' => 'Cannot find driver sale category.']);
        }

        $unEarnRevenueCategory = Category::where('module', Module::UN_EARNED_REVENUE)->where('module_id', $requestData['company_id'])->where('client_company_id', $sessionUser['client_company_id'])->first();
        if (!$unEarnRevenueCategory instanceof Category) {
            return response()->json(['status' => 500, 'error' => 'Cannot find driver sale category.']);
        }

        $driverSaleDriver = CategoryRepository::saveCategory($data, $driverSaleCategory['id'], Module::DRIVER_SALE_DRIVER);
        if (!$driverSaleDriver instanceof Category) {
            return response()->json(['status' => 500, 'message' => 'Cannot save [driver]']);
        }
        $unEarnRevenueDriver = CategoryRepository::saveCategory($data, $unEarnRevenueCategory['id'], Module::UN_EARNED_REVENUE_DRIVER);
        if (!$unEarnRevenueDriver instanceof Category) {
            return response()->json(['status' => 500, 'message' => 'Cannot save [driver]']);
        }
        $driverData = [
            'name' => $requestData['name'],
            'company_id' => $request['company_id'],
            'email' => $requestData['email'],
            'phone_number' => $requestData['phone_number'],
            'driver_expense_id' => $driverSaleDriver['id'],
            'driver_liability_id' => $unEarnRevenueDriver['id'],
        ];
        $newDriver = DriverRepository::save($driverData, $sessionUser);
        if (!$newDriver instanceof Driver) {
            return response()->json(['status' => 500, 'message' => 'Cannot save [driver]']);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully saved driver.']);
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
        $result = Driver::select('driver.id', 'driver.name as driver_name', 'driver.email', 'driver.phone_number', 'categories.name as company_name')
            ->leftJoin('categories', 'categories.id', '=', 'driver.company_id')
            ->where('driver.client_company_id', $sessionUser['client_company_id']);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('driver.name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('driver.email', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('driver.phone_number', 'LIKE', '%'.$keyword.'%');
            });
        }
        if (!empty($company_id)) {
            $result->where(function($q) use ($company_id) {
                $q->where('driver.company_id', $company_id);
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
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = Driver::find($requestData['id']);
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
        $driver = Driver::find($requestData['id']);
        if (!$driver instanceof Driver) {
            return response()->json(['status' => 500, 'errors' => 'Cannot find [driver]']);
        }
        $expenseCategory = Category::find($driver['driver_expense_id']);
        if (!$expenseCategory instanceof Category) {
            return response()->json(['status' => 500, 'errors' => 'Cannot find [driver sale category]']);
        }
        $liabilityCategory = Category::find($driver['driver_liability_id']);
        if (!$liabilityCategory instanceof Category) {
            return response()->json(['status' => 500, 'errors' => 'Cannot find [driver un revenue category]']);
        }
        $driver->name = $requestData['name'];
        $driver->company_id = $requestData['company_id'];
        $driver->phone_number = $requestData['phone_number'];
        $driver->email = $requestData['email'];
        if (!$driver->save()) {
            return response()->json(['status' => 500, 'error' => 'Cannot update driver.']);
        }
        $data = [
            'name' => $requestData['name'],
            'module_id' => $requestData['company_id']
        ];
        $updateCategory = CategoryRepository::updateCategory($expenseCategory, $data);
        if (!$updateCategory instanceof Category) {
            return response()->json(['status' => 500, 'error' => 'Cannot update driver.']);
        }
        $updateCategory = CategoryRepository::updateCategory($liabilityCategory,  $data);
        if (!$updateCategory instanceof Category) {
            return response()->json(['status' => 500, 'error' => 'Cannot update driver.']);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully updated driver.']);
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
        $driver = Driver::find($requestData['id']);
        if (!$driver instanceof Driver) {
            return response()->json(['status' => 500, 'errors' => 'Cannot find [driver].']);
        }
        $transaction = Transaction::where('account_id', $driver['driver_expense_id'])->orWhere('linked_id', $driver['driver_expense_id'])->first();
        if ($transaction instanceof Transaction) {
            return response()->json(['status' => 500, 'errors' => 'Cannot delete [driver].']);
        }
        $transaction = Transaction::where('account_id', $driver['driver_liability_id'])->orWhere('linked_id', $driver['driver_liability_id'])->first();
        if ($transaction instanceof Transaction) {
            return response()->json(['status' => 500, 'errors' => 'Cannot delete [driver].']);
        }
        Driver::where('id', $requestData['id'])->delete();
        Category::whereIn('id', [$driver['driver_expense_id'], $driver['driver_liability_id']])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted driver.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAmount(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validation = Validator::make($requestData, [
            'driver_id' => 'required|integer',
        ]);
        if ($validation->fails()) {
            return response()->json(['status' => 500, 'errors' => $validation->errors()]);
        }
        $driver = Driver::find($requestData['driver_id']);
        if (!$driver instanceof Driver) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [driver].']);
        }
        $driverLiability = Category::find($driver['driver_liability_id']);
        if (!$driverLiability instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [driver liability].']);
        }
        $result = DriverRepository::getDriverAmount($driverLiability['id']);
        return response()->json(['status' => 200, 'data' => $result]);
    }
}
