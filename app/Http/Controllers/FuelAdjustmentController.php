<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\FuelMatixDateTimeFormat;
use App\Common\Module;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\FuelAdjustment;
use App\Models\FuelAdjustmentData;
use App\Models\Product;
use App\Models\Transaction;
use App\Repository\CategoryRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FuelAdjustmentController extends Controller
{
    /**
     * @param Request $request
     * */
    public function save(Request $request)
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'product_id' => 'required|integer',
            'purpose' => 'required|string',
            'loss_quantity' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $product = Product::find($requestData['product_id']);
        if (!$product instanceof Product) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [product]']);
        }
        $categoryData = [
            'name' => $product['name'],
            'module_id' => $product->id
        ];
        $sessionUser = SessionUser::getUser();
        $categoryId = null;
        if ($requestData['loss_quantity'] > 0) {
            $indirectExpenseCategory = Category::where('slug', strtolower(AccountCategory::IN_DIRECT_EXPENSE))->where('client_company_id', $sessionUser['client_company_id'])->first();
            if (!$indirectExpenseCategory instanceof Category) {
                return response()->json(['status' => 400, 'message' => 'Cannot find [indirect expense] category.']);
            }
            $expenseCategory = Category::where('module', Module::PRODUCT)->where('parent_category', $indirectExpenseCategory->id)->where('module_id', $product['id'])->first();
            if (!$expenseCategory instanceof Category) {
                $expenseCategory = CategoryRepository::saveCategory($categoryData, $indirectExpenseCategory['id'], Module::PRODUCT);
            }
            $categoryId = $expenseCategory['id'];
        } else if ($requestData['loss_quantity'] < 0) {
            $indirectIncomeCategory = Category::where('slug', strtolower(AccountCategory::IN_DIRECT_INCOME))->where('client_company_id', $sessionUser['client_company_id'])->first();
            if (!$indirectIncomeCategory instanceof Category) {
                return response()->json(['status' => 400, 'message' => 'Cannot find [indirect income].']);
            }
            $incomeCategory = Category::where('module', Module::PRODUCT)->where('parent_category', $indirectIncomeCategory->id)->where('module_id', $product['id'])->first();
            if (!$incomeCategory instanceof Category) {
                $incomeCategory = CategoryRepository::saveCategory($categoryData, $indirectIncomeCategory['id'], Module::PRODUCT);
            }
            $categoryId = $incomeCategory['id'];
        }
        $stockCategory = Category::where('slug', strtolower(AccountCategory::STOCK_IN_HAND))->where('client_company_id', $sessionUser['client_company_id'])->first();
        if (!$stockCategory instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [stock] category.'], 422);
        }
        $stockProduct = Category::where('parent_category', $stockCategory['id'])->where('module', Module::PRODUCT)->where('module_id', $product['id'])->first();
        if (!$stockProduct instanceof Category) {
            $stockProduct = CategoryRepository::saveCategory($categoryData, $stockCategory['id'], Module::PRODUCT);
        }

        $fuelAdjustmentModel = new FuelAdjustment();
        $fuelAdjustmentModel->date = Carbon::now('UTC');
        $fuelAdjustmentModel->product_id = $requestData['product_id'];
        $fuelAdjustmentModel->purpose = $requestData['purpose'];
        $fuelAdjustmentModel->loss_quantity = $requestData['loss_quantity'];
        $fuelAdjustmentModel->loss_amount = $requestData['loss_quantity'] * $product['buying_price'];
        $fuelAdjustmentModel->client_company_id = $sessionUser['client_company_id'];
        $fuelAdjustmentModel->user_id = $sessionUser['id'];
        if (!$fuelAdjustmentModel->save()) {
            return response()->json(['message' => 'Cannot save fuel adjustment.']);
        }
        $fuelAdjustmentData = [];
        if (!empty($requestData['nozzles'])) {
            foreach ($requestData['nozzles'] as $nozzle) {
                $fuelAdjustmentData[] = [
                    'fuel_adjustment_id' => $fuelAdjustmentModel['id'],
                    'tank_id' => null,
                    'nozzle_id' => $nozzle['id'],
                    'quantity' => $nozzle['quantity'],
                    'unit_price' => $product['buying_price'],
                    'total' => $product['buying_price'] * $nozzle['quantity']
                ];
            }
        }
        if (!empty($requestData['tank'])) {
            $fuelAdjustmentData[] = [
                'fuel_adjustment_id' => $fuelAdjustmentModel['id'],
                'tank_id' => $requestData['tank']['id'],
                'nozzle_id' => null,
                'quantity' => $requestData['tank']['quantity'],
                'unit_price' => $product['buying_price'],
                'total' => $product['buying_price'] * $requestData['tank']['quantity']
            ];
        }
        FuelAdjustmentData::insert($fuelAdjustmentData);
        $transactionData['transaction'] = [
            ['date' => date('Y-m-d'), 'account_id' => $stockProduct['id'], 'debit_amount' => $fuelAdjustmentModel['loss_amount'] > 0 ? $fuelAdjustmentModel['loss_amount'] : 0, 'credit_amount' => $fuelAdjustmentModel['loss_amount'] < 0 ? abs($fuelAdjustmentModel['loss_amount']) : 0, 'module' => Module::FUEL_ADJUSTMENT, 'module_id' => $fuelAdjustmentModel['id']]
        ];
        $transactionData['linked_id'] = $categoryId;
        TransactionController::saveTransaction($transactionData);
        return response()->json(['status' => 200, 'message' => 'Successfully saved fuel adjustment.']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $limit = $requestData['limit'] ?? 10;
        $sessionUser = SessionUser::getUser();
        $keyword = $requestData['keyword'] ?? '';
        $result = FuelAdjustment::select('fuel_adjustment.id', 'fuel_adjustment.date', 'fuel_adjustment.purpose', 'fuel_adjustment.loss_quantity', 'fuel_adjustment.loss_amount', 'products.name', 'users.name as user_name')
            ->leftJoin('products', 'products.id', '=', 'fuel_adjustment.product_id')
            ->leftJoin('users', 'users.id', '=', 'fuel_adjustment.user_id')
            ->where('fuel_adjustment.client_company_id', $sessionUser['client_company_id'])
            ->orderBy('id', 'DESC')
            ->paginate($limit);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('products.name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('purpose', 'LIKE', '%'.$keyword.'%');
            });
        }
        foreach ($result as &$data) {
            $data['date'] = Helpers::formatDate($data['date'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME);
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
            'id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = FuelAdjustment::select('id', 'product_id', 'purpose', 'loss_quantity')
            ->where('id', $requestData['id'])
            ->first();
        $fuelAdjustmentData = FuelAdjustmentData::select('fuel_adjustment_data.*', 'nozzles.name as nozzle_name', 'tank.tank_name')
            ->leftJoin('nozzles', 'nozzles.id', '=', 'fuel_adjustment_data.nozzle_id')
            ->leftJoin('tank', 'tank.id', '=', 'fuel_adjustment_data.tank_id')
            ->where('fuel_adjustment_id', $requestData['id'])
            ->get()
            ->toArray();
        $nozzles = [];
        foreach ($fuelAdjustmentData as $data) {
            if (!empty($data['nozzle_id'])) {
                $nozzles[] = [
                    'id' => $data['nozzle_id'],
                    'name' => $data['nozzle_name'],
                    'quantity' => $data['quantity']
                ];
            }
            if (!empty($data['tank_id'])) {
                $result['tank'] = [
                    'id' => $data['tank_id'],
                    'name' => $data['tank_name'],
                    'quantity' => $data['quantity']
                ];
            }
        }
        $result['nozzles'] = $nozzles;
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
            'id' => 'required|integer',
            'product_id' => 'required|integer',
            'purpose' => 'required|string',
            'loss_quantity' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }

        $fuelAdjustmentModel = FuelAdjustment::find($requestData['id']);
        if (!$fuelAdjustmentModel instanceof FuelAdjustment) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [fuel adjustment]']);
        }

        $product = Product::find($requestData['product_id']);
        if (!$product instanceof Product) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [product]']);
        }
        $categoryData = [
            'name' => $product['name'],
            'module_id' => $product->id
        ];
        $sessionUser = SessionUser::getUser();
        $categoryId = null;
        if ($requestData['loss_quantity'] > 0) {
            $indirectExpenseCategory = Category::where('slug', strtolower(AccountCategory::IN_DIRECT_EXPENSE))->where('client_company_id', $sessionUser['client_company_id'])->first();
            if (!$indirectExpenseCategory instanceof Category) {
                return response()->json(['status' => 400, 'message' => 'Cannot find [indirect expense] category.']);
            }
            $expenseCategory = Category::where('module', Module::PRODUCT)->where('parent_category', $indirectExpenseCategory->id)->where('module_id', $product['id'])->first();
            if (!$expenseCategory instanceof Category) {
                $expenseCategory = CategoryRepository::saveCategory($categoryData, $indirectExpenseCategory['id'], Module::PRODUCT);
            }
            $categoryId = $expenseCategory['id'];
        } else if ($requestData['loss_quantity'] < 0) {
            $indirectIncomeCategory = Category::where('slug', strtolower(AccountCategory::IN_DIRECT_INCOME))->where('client_company_id', $sessionUser['client_company_id'])->first();
            if (!$indirectIncomeCategory instanceof Category) {
                return response()->json(['status' => 400, 'message' => 'Cannot find [indirect income].']);
            }
            $incomeCategory = Category::where('module', Module::PRODUCT)->where('parent_category', $indirectIncomeCategory->id)->where('module_id', $product['id'])->first();
            if (!$incomeCategory instanceof Category) {
                $incomeCategory = CategoryRepository::saveCategory($categoryData, $indirectIncomeCategory['id'], Module::PRODUCT);
            }
            $categoryId = $incomeCategory['id'];
        }
        $stockCategory = Category::where('slug', strtolower(AccountCategory::STOCK_IN_HAND))->where('client_company_id', $sessionUser['client_company_id'])->first();
        if (!$stockCategory instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [stock] category.'], 422);
        }
        $stockProduct = Category::where('parent_category', $stockCategory['id'])->where('module', Module::PRODUCT)->where('module_id', $product['id'])->first();
        if (!$stockProduct instanceof Category) {
            $stockProduct = CategoryRepository::saveCategory($categoryData, $stockCategory['id'], Module::PRODUCT);
        }
        $fuelAdjustmentModel->purpose = $requestData['purpose'];
        $fuelAdjustmentModel->loss_quantity = $requestData['loss_quantity'];
        $fuelAdjustmentModel->loss_amount = $requestData['loss_quantity'] * $product['buying_price'];
        if (!$fuelAdjustmentModel->save()) {
            return response()->json(['message' => 'Cannot updated fuel adjustment.']);
        }
        FuelAdjustmentData::where('fuel_adjustment_id', $requestData['id'])->delete();
        $fuelAdjustmentData = [];
        if (!empty($requestData['nozzles'])) {
            foreach ($requestData['nozzles'] as $nozzle) {
                $fuelAdjustmentData[] = [
                    'fuel_adjustment_id' => $fuelAdjustmentModel['id'],
                    'tank_id' => null,
                    'nozzle_id' => $nozzle['id'],
                    'quantity' => $nozzle['quantity'],
                    'unit_price' => $product['buying_price'],
                    'total' => $product['buying_price'] * $nozzle['quantity']
                ];
            }
        }
        if (!empty($requestData['tank'])) {
            $fuelAdjustmentData[] = [
                'fuel_adjustment_id' => $fuelAdjustmentModel['id'],
                'tank_id' => $requestData['tank']['id'],
                'nozzle_id' => null,
                'quantity' => $requestData['tank']['quantity'],
                'unit_price' => $product['buying_price'],
                'total' => $product['buying_price'] * $requestData['tank']['quantity']
            ];
        }
        Transaction::where('module', Module::FUEL_ADJUSTMENT)->where('module_id', $requestData['id'])->delete();
        FuelAdjustmentData::insert($fuelAdjustmentData);
        $transactionData['transaction'] = [
            ['date' => date('Y-m-d'), 'account_id' => $stockProduct['id'], 'debit_amount' => $fuelAdjustmentModel['loss_amount'] > 0 ? $fuelAdjustmentModel['loss_amount'] : 0, 'credit_amount' => $fuelAdjustmentModel['loss_amount'] < 0 ? abs($fuelAdjustmentModel['loss_amount']) : 0, 'module' => Module::FUEL_ADJUSTMENT, 'module_id' => $fuelAdjustmentModel['id']]
        ];
        $transactionData['linked_id'] = $categoryId;
        TransactionController::saveTransaction($transactionData);
        return response()->json(['status' => 200, 'message' => 'Successfully updated fuel adjustment.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        FuelAdjustment::where('id', $requestData['id'])->delete();
        FuelAdjustmentData::where('fuel_adjustment_id', $requestData['id'])->delete();
        Transaction::where('module', Module::FUEL_ADJUSTMENT)->where('module_id', $requestData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted fuel adjustment.']);
    }
}
