<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\Module;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Dispenser;
use App\Models\FuelAdjustment;
use App\Models\FuelAdjustmentData;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\SaleData;
use App\Models\ShiftSale;
use App\Models\ShiftTotal;
use App\Models\Tank;
use App\Models\TankRefill;
use App\Models\Transaction;
use App\Repository\CategoryRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'name' => 'required|string',
            'selling_price' => 'required|numeric',
            'buying_price' => 'required|numeric',
            'type_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $productType = ProductType::where('id', $inputData['type_id'])->first();
        if (!$productType instanceof ProductType) {
            return response()->json([
                'status' => 300,
                'message' => 'Cannot find product type'
            ]);
        }
        if ($productType['tank'] == 1) {
            $inputData['opening_stock'] = 0;
        }

        $directIncomeCategory = Category::where('slug', strtolower(AccountCategory::DIRECT_INCOME))->where('client_company_id', $inputData['session_user']['client_company_id'])->first();
        if (!$directIncomeCategory instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [income] category.']);
        }
        $costOfGoodSoldCategory = Category::where('slug', strtolower(AccountCategory::COST_OF_GOOD_SOLD))->where('client_company_id', $inputData['session_user']['client_company_id'])->first();
        if (!$costOfGoodSoldCategory instanceof  Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [cost of good sold] category']);
        }
        $stockCategory = Category::where('slug', strtolower(AccountCategory::STOCK_IN_HAND))->where('client_company_id', $inputData['session_user']['client_company_id'])->first();
        if (!$stockCategory instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [stock in hand] category']);
        }

        $product = new Product();
        $product->name = $inputData['name'];
        $product->selling_price = $inputData['selling_price'];
        $product->type_id = $inputData['type_id'];
        $product->buying_price = $inputData['buying_price'] ?? 0;
        $product->driver_selling_price = $inputData['driver_selling_price'] ?? 0;
        $product->opening_stock = $inputData['opening_stock'] ?? null;
        $product->current_stock = $inputData['opening_stock'] ?? null;
        $product->client_company_id = $inputData['session_user']['client_company_id'];
        $product->vendor_id = $inputData['vendor_id'] ?? null;
        if (!$product->save()) {
            return response()->json(['status' => 400, 'message' => 'Cannot save [product].']);
        }
        $categoryData = [
            'name' => $inputData['name'],
            'opening_balance' => $inputData['opening_stock'] ?? null,
            'module_id' => $product->id
        ];
        $directIncomeCategory = CategoryRepository::saveCategory($categoryData, $directIncomeCategory['id'], Module::PRODUCT);
        if (!$directIncomeCategory instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot save [income] category.']);
        }
        $costOfGoodSoldCategory = CategoryRepository::saveCategory($categoryData, $costOfGoodSoldCategory['id'], Module::PRODUCT);
        if (!$costOfGoodSoldCategory instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot save [cost of good sold] category.']);
        }
        $stockCategory = CategoryRepository::saveCategory($categoryData, $stockCategory['id'], Module::PRODUCT);
        if (!$stockCategory instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot save [stock] category.']);
        }
        $deleteResponse = $stockCategory->deleteOpeningBalance();
        if ($deleteResponse) {
            if (!empty($request['opening_stock'])) {
                $retainEarning = Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('slug', strtolower(AccountCategory::RETAIN_EARNING))->first();
                if ($retainEarning instanceof Category) {
                    $transactionData['linked_id'] = $stockCategory['id'];
                    $transactionData['transaction'] = [
                        ['date' => "1970-01-01",  'account_id' => $retainEarning['id'], 'debit_amount' => $request['opening_stock'], 'credit_amount' => 0, 'opening_balance' => 1],
                    ];
                    TransactionController::saveTransaction($transactionData);
                }
            }
        }
        return response()->json(['status' => 200, 'message' => 'Successfully save product.']);
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
        $shift_sale = $inputData['shift_sale'] ?? '';
        $result = Product::select('products.*', 'product_types.name as product_type', 'product_types.shift_sale', 'product_types.unit')
            ->leftJoin('product_types', 'product_types.id', '=', 'products.type_id')
            ->where('client_company_id', $inputData['session_user']['client_company_id']);
        if (!empty($inputData['type_id'])) {
            $result->where(function($q) use ($inputData) {
                $q->where('products.type_id', $inputData['type_id']);
            });
        }
        if (!empty($shift_sale)) {
            $result->where(function($q) use ($shift_sale) {
                $q->where('product_types.shift_sale', $shift_sale);
            });
        }
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('products.name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('products.selling_price', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('products.buying_price', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('product_types.name', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
        $productId = [];
        foreach ($result as &$data) {
            $productId[] = $data['id'];
        }
        $incomeCategory = Category::select('id', 'module_id')->where('client_company_id', $inputData['session_user']['client_company_id'])->whereIn('module_id', $productId)->where('type', 'income')->where('module', Module::PRODUCT)->get()->keyBy('module_id')->toArray();
        $stockCategory = Category::select('id', 'module_id')->where('client_company_id', $inputData['session_user']['client_company_id'])->whereIn('module_id', $productId)->where('type', 'assets')->where('module', Module::PRODUCT)->get()->keyBy('module_id')->toArray();

        $costOfGoodSoldCategory = Category::select('id')->where('client_company_id', $inputData['session_user']['client_company_id'])->where('slug', strtolower(AccountCategory::COST_OF_GOOD_SOLD))->first();
        $expenseCategory = Category::select('id', 'module_id')->where('client_company_id', $inputData['session_user']['client_company_id'])->where('parent_category', $costOfGoodSoldCategory['id'])->get()->keyBy('module_id')->toArray();
        foreach ($result as &$data) {
            $data['income_category_id'] = isset($incomeCategory[$data['id']]) ? $incomeCategory[$data['id']]['id']: '';
            $data['stock_category_id'] = isset($stockCategory[$data['id']]) ? $stockCategory[$data['id']]['id']: '';
            $data['expense_category_id'] = isset($expenseCategory[$data['id']]) ? $expenseCategory[$data['id']]['id']: '';
        }
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
        $result = Product::find($inputData['id']);
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
            'id' => 'required|integer',
            'name' => 'required|string',
            'type_id' => 'required|integer',
            'buying_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $productType = ProductType::where('id', $inputData['type_id'])->first();
        if (!$productType instanceof ProductType) {
            return response()->json([
                'status' => 300,
                'message' => 'Cannot find product type'
            ]);
        }
        if ($productType['tank'] == 1) {
            $inputData['opening_stock'] = 0;
        }

        $directIncomeCategory = Category::where('slug', strtolower(AccountCategory::DIRECT_INCOME))->where('client_company_id', $inputData['session_user']['client_company_id'])->first();
        if (!$directIncomeCategory instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [income] category.']);
        }
        $costOfGoodSoldCategory = Category::where('slug', strtolower(AccountCategory::COST_OF_GOOD_SOLD))->where('client_company_id', $inputData['session_user']['client_company_id'])->first();
        if (!$costOfGoodSoldCategory instanceof  Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [cost of good sold] category']);
        }
        $stockCategory = Category::where('slug', strtolower(AccountCategory::STOCK_IN_HAND))->where('client_company_id', $inputData['session_user']['client_company_id'])->first();
        if (!$stockCategory instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [stock in hand] category']);
        }

        $product = Product::find($inputData['id']);
        if (!$product instanceof Product) {
            return response()->json(['status' => 400, 'error' => 'Cannot find [product].']);
        }

        $opening_stock = $product['opening_stock'] ?? 0;
        $current_stock = $product->current_stock  + ($inputData['opening_stock'] - $opening_stock);
        $product->name = $inputData['name'];
        $product->selling_price = $inputData['selling_price'];
        $product->type_id = $inputData['type_id'];
        $product->buying_price = $inputData['buying_price'] ?? 0;
        $product->driver_selling_price = $inputData['driver_selling_price'] ?? 0;
        $product->opening_stock = $inputData['opening_stock'] ?? null;
        $product->current_stock = $current_stock;
        $product->vendor_id = $inputData['vendor_id'] ?? null;
        if (!$product->save()) {
            return response()->json(['status' => 400, 'message' => 'Cannot updated [product].']);
        }
        $categoryData = [
            'name' => $inputData['name'],
            'module_id' => $product->id
        ];
        $incomeCategoryModel = Category::where('module', Module::PRODUCT)->where('parent_category', $directIncomeCategory->id)->where('module_id', $inputData['id'])->first();
        if (!$incomeCategoryModel instanceof Category) {
            CategoryRepository::saveCategory($categoryData, $directIncomeCategory['id'], Module::PRODUCT);
        } else {
            CategoryRepository::updateCategory($incomeCategoryModel, $categoryData);
        }

        $costOfGoodSoldCategoryModel = Category::where('module', Module::PRODUCT)->where('parent_category', $costOfGoodSoldCategory->id)->where('module_id', $inputData['id'])->first();
        if (!$costOfGoodSoldCategoryModel instanceof Category) {
            CategoryRepository::saveCategory($categoryData, $costOfGoodSoldCategory['id'], Module::PRODUCT);
        } else {
            CategoryRepository::updateCategory($costOfGoodSoldCategoryModel, $categoryData);
        }

        $stockCategoryModel = Category::where('module', Module::PRODUCT)->where('parent_category', $stockCategory->id)->where('module_id', $inputData['id'])->first();
        if (!$stockCategoryModel instanceof Category) {
            CategoryRepository::saveCategory($categoryData, $stockCategory['id'], Module::PRODUCT);
        } else {
            CategoryRepository::updateCategory($stockCategoryModel, $categoryData);
        }
        $deleteResponse = $stockCategoryModel->deleteOpeningBalance();
        if ($deleteResponse) {
            if (!empty($request['opening_stock'])) {
                $retainEarning = Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('slug', strtolower(AccountCategory::RETAIN_EARNING))->first();
                if ($retainEarning instanceof Category) {
                    $transactionData['linked_id'] = $stockCategoryModel['id'];
                    $transactionData['transaction'] = [
                        ['date' => "1970-01-01",  'account_id' => $retainEarning['id'], 'debit_amount' => $request['opening_stock'], 'credit_amount' => 0, 'opening_balance' => 1],
                    ];
                    TransactionController::saveTransaction($transactionData);
                }
            }
        }
        return response()->json(['status' => 200, 'message' => 'Successfully updated product.']);
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
        $product = Product::find($inputData['id']);
        if ($product instanceof Product) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [product].']);
        }
        $categoryId = Category::where('module', Module::PRODUCT)->where('module_id', $inputData['id'])->get()->pluck('id')->toArray();
        $transaction = Transaction::whereIn('account_id', $categoryId)->orWhereIn('linked_id', $categoryId)->first();
        if ($transaction instanceof Transaction) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [product].']);
        }
        Product::where('id', $inputData['id'])->delete();
        Category::where('module', Module::PRODUCT)->where('module_id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully delete product.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDispenser(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'product_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $sessionUser = SessionUser::getUser();
        $product = Product::where('id', $inputData['product_id'])
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first();
        if (!$product instanceof Product) {
            return response()->json(['status' => 500, 'error' => 'Cannot find product.']);
        }
        $productType = ProductType::where('id', $product['type_id'])->first();
        if (!$productType instanceof ProductType) {
            return response()->json(['status' => 500, 'error' => 'Cannot find product type.']);
        }

        $shiftSaleId = $inputData['shift_id'] ?? 0;
        $shiftSale = ShiftTotal::where('product_id', $request->input('product_id'))
            ->where('start_date', '<=', Carbon::now(SessionUser::TIMEZONE))
            ->where('status', 'start')
            ->first();
        if ($shiftSale instanceof ShiftTotal) {
            $shiftSaleId = $shiftSale->id;
        }
        $tanks = Tank::select('tank.id', 'tank.tank_name', 'shift_sale.start_reading', 'shift_sale.end_reading', 'tank.opening_stock')
            ->leftJoin('shift_sale', function ($join) {
                $join->on('tank.id', '=', 'shift_sale.tank_id')
                    ->whereRaw('shift_sale.id = (SELECT MAX(id) FROM shift_sale WHERE tank_id = tank.id)');
            })
            ->where('tank.product_id', $request['product_id'])
            ->get()
            ->toArray();
        $dispensers = Dispenser::select('id', 'dispenser_name', 'tank_id')
            ->where('product_id', $inputData['product_id'])
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->with(['nozzle' => function ($query) {
                $query->select('id', 'dispenser_id', 'name', 'opening_stock', 'pf', 'max_value')
                    ->with(['latestShiftSummary' => function ($subQuery) {
                        $subQuery->select('shift_summary.id', 'shift_summary.nozzle_id', 'shift_summary.start_reading', 'shift_summary.end_reading');
                    }]);
            }])
            ->get()
            ->toArray();
        $fuelAdjustment = FuelAdjustment::select('id', 'loss_quantity')
            ->where('shift_sale_id', $shiftSaleId)
            ->get()
            ->toArray();
        $fuelAdjustmentId = array_column($fuelAdjustment, 'id');
        $fuelAdjustmentData = FuelAdjustmentData::whereIn('fuel_adjustment_id', $fuelAdjustmentId)->get()->toArray();
        $dispenserArray = [];
        foreach ($dispensers as &$dispenser) {
            foreach ($dispenser['nozzle'] as &$nozzle) {
                $adjustment = 0;
                if (!empty($fuelAdjustment)) {
                    foreach ($fuelAdjustmentData as $adjustmentData) {
                        if (!empty($adjustmentData['nozzle_id'])) {
                            if ($adjustmentData['nozzle_id'] == $nozzle['id']) {
                                $adjustment = $adjustmentData['quantity'];
                            }
                        }
                    }
                }

                $nozzle['start_reading'] =  $nozzle['opening_stock'] ?? 0;
                if (!empty($nozzle['latest_shift_summary'])) {
                    $nozzle['start_reading'] = !empty($nozzle['latest_shift_summary']['end_reading']) ? $nozzle['latest_shift_summary']['end_reading'] : $nozzle['latest_shift_summary']['start_reading'];
                }
                $nozzle['end_reading'] = 0;
                $nozzle['adjustment'] = $adjustment;
                $nozzle['consumption'] =  $nozzle['end_reading']  - $nozzle['start_reading'] -  $adjustment;
                $nozzle['amount'] = 0;
                $nozzle['consumption'] = max($nozzle['consumption'], 0);
                unset($nozzle['latest_shift_summary']);

            }
            $dispenserArray[$dispenser['tank_id']][] = $dispenser;
        }
        $tankRefill = TankRefill::where('shift_sale_id', $shiftSaleId)
            ->get()
            ->keyBy('tank_id')
            ->toArray();
//        $adjustment = 0;
//        $nozzleAdjustment = [];
//        if (!empty($fuelAdjustment)) {
//            $fuelAdjustmentId = array_column($fuelAdjustment, 'id');
//            $fuelAdjustmentData = FuelAdjustmentData::whereIn('fuel_adjustment_id', $fuelAdjustmentId)->get()->toArray();
//            foreach ($fuelAdjustmentData as $adjustmentData) {
//                if (!empty($adjustmentData['tank_id'])) {
//                    if ($adjustmentData['tank_id'] == $tank['id']) {
//                        $adjustment += $adjustmentData['quantity'];
//                    }
//                }
//                if (!empty($adjustmentData['nozzle_id'])) {
//                    $nozzleAdjustment[$adjustmentData['nozzle_id']][] = $adjustmentData;
//                }
//            }
//        }
        foreach ($tanks as &$tank) {
            $adjustment = 0;
            if (!empty($fuelAdjustment)) {
                foreach ($fuelAdjustmentData as $adjustmentData) {
                    if (!empty($adjustmentData['tank_id'])) {
                        if ($adjustmentData['tank_id'] == $tank['id']) {
                            $adjustment += $adjustmentData['quantity'];
                        }
                    }
                }
            }

            $tank['noDIPShow'] = 1;
            if (empty($shiftSaleId)) {
                if (!empty($tank['end_reading'])) {
                    $tank['start_reading'] = $tank['end_reading'];
                } else {
                    $tank['start_reading'] = $tank['opening_stock'] ?? 0;
                }
            }
            $tank['start_reading_mm'] = Tank::findHeight($tank['id'], $tank['start_reading']);
            $tank['end_reading'] = 0;
            $tank['end_reading_mm'] = 0;
            $tank['tank_refill'] = isset($tankRefill[$tank['id']]) ? $tankRefill[$tank['id']]['total_refill_volume'] : 0;
            $tank['tank_refill_mm'] = 0;
            $tank['adjustment'] = $adjustment;
            $tank['adjustment_mm'] = 0;
            $tank['consumption'] = $tank['start_reading'] + $tank['tank_refill'] - $tank['end_reading'] + $adjustment;
            $tank['consumption_mm'] = $tank['start_reading_mm'] + $tank['tank_refill_mm'] - $tank['end_reading_mm'];

            $tank['dispensers'] = $dispenserArray[$tank['id']] ?? [];
            unset($tank['opening_stock']);
        }
        $consumption = array_sum(array_column($tanks, 'consumption'));
        $amount = $consumption * $product['selling_price'];

        $result = [
            'date' => Carbon::now(SessionUser::TIMEZONE)->format('Y-m-d H:i:s'),
            'product_id' => $inputData['product_id'],
            'tanks' => $tanks,
            'amount' => $amount,
            'selling_price' => $product->selling_price,
            'unit' => $productType['unit'],
            'tank' => $productType['tank'],
            'net_profit' => 0
        ];
        $result['status'] = 'start';
        $result['pos_sale'] = [];
        $result['total_pos_sale_liter'] = 0;
        if ($shiftSale instanceof ShiftTotal) {
            if ($shiftSale['status'] == 'end') {
                $result['status'] = 'start';
            } else {
                $result['date'] = Carbon::parse($shiftSale['date'], SessionUser::TIMEZONE)->format('Y-m-d H:i:s');
                $result['status'] = 'end';
            }
            $posSale = SaleData::select('sale_data.sale_id', 'sale_data.id', DB::raw('SUM(sale_data.quantity) as quantity'), DB::raw('SUM(sale_data.subtotal) as amount'), 'sale.payment_category_id as category_id')
                ->leftJoin('sale', 'sale.id', '=', 'sale_data.sale_id')
                ->where('shift_sale_id', $shiftSale->id)
                ->groupBy('sale.payment_category_id')
                ->get()
                ->toArray();
            $result['pos_sale'] = $posSale;
            $result['total_pos_sale_liter'] = array_sum(array_column($posSale, 'quantity'));
        }
        $result['shift_id'] = $shiftSaleId;
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getTank(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'product_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = Tank::select('id', 'tank_name')
            ->where('product_id', $inputData['product_id'])
            ->get()
            ->toArray();
        return response()->json(['status' => 200, 'data' => $result]);
    }
}
