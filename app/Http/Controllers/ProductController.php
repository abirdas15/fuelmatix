<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\Module;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Dispenser;
use App\Models\FuelAdjustment;
use App\Models\FuelAdjustmentData;
use App\Models\NozzleReading;
use App\Models\Product;
use App\Models\SaleData;
use App\Models\ShiftSale;
use App\Models\ShiftSummary;
use App\Models\Stock;
use App\Models\Tank;
use App\Models\TankLog;
use App\Models\Transaction;
use App\Repository\CategoryRepository;
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
            'type_id' => 'required|integer',
            'unit' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
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
        $product->unit = $inputData['unit'];
        $product->opening_stock = $inputData['opening_stock'] ?? null;
        $product->client_company_id = $inputData['session_user']['client_company_id'];
        if (!$product->save()) {
            return response()->json(['status' => 400, 'message' => 'Cannot save [product].']);
        }
        $categoryData = [
            'name' => $inputData['name'],
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
        $result = Product::select('products.*', 'product_types.name as product_type')
            ->leftJoin('product_types', 'product_types.id', '=', 'products.type_id')
            ->where('client_company_id', $inputData['session_user']['client_company_id']);
        if (!empty($inputData['type_id'])) {
            $result->where(function($q) use ($inputData) {
                $q->where('products.type_id', $inputData['type_id']);
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
        $shiftSale = ShiftSale::select('product_id', 'id')->where('client_company_id', $inputData['session_user']['client_company_id'])->orderBy('id', 'DESC')->where('status', 'start')->get()->keyBy('product_id')->toArray();
        $productId = [];
        foreach ($result as &$data) {
            $productId[] = $data['id'];
            $data['shift_sale_id'] = isset($shiftSale[$data['id']]) ? $shiftSale[$data['id']]['id']: '';
        }
        $incomeCategory = Category::select('id', 'module_id')->where('client_company_id', $inputData['session_user']['client_company_id'])->whereIn('module_id', $productId)->where('type', 'income')->where('module', Module::PRODUCT)->get()->keyBy('module_id')->toArray();
        $stockCategory = Category::select('id', 'module_id')->where('client_company_id', $inputData['session_user']['client_company_id'])->whereIn('module_id', $productId)->where('type', 'assets')->where('module', Module::PRODUCT)->get()->keyBy('module_id')->toArray();
        $expenseCategory = Category::select('id', 'module_id')->where('client_company_id', $inputData['session_user']['client_company_id'])->whereIn('module_id', $productId)->where('type', 'expenses')->where('module', Module::PRODUCT)->get()->keyBy('module_id')->toArray();
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
            'unit' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
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
        $product->name = $inputData['name'];
        $product->selling_price = $inputData['selling_price'];
        $product->type_id = $inputData['type_id'];
        $product->buying_price = $inputData['buying_price'] ?? 0;
        $product->driver_selling_price = $inputData['driver_selling_price'] ?? 0;
        $product->unit = $inputData['unit'];
        $product->opening_stock = $inputData['opening_stock'] ?? null;
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
            'product_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $sessionUser = SessionUser::getUser();
        $product = Product::where('id', $inputData['product_id'])->where('client_company_id', $sessionUser['client_company_id'])->first();
        if (!$product instanceof Product) {
            return response()->json(['status' => 500, 'error' => 'Cannot find product.']);
        }
        $tank = Tank::where('product_id', $product['id'])->first();
        if (!$tank instanceof Tank) {
            return response()->json(['status' => 500, 'error' => 'Cannot find tank.']);
        }
        $shiftSale = ShiftSale::select('id', 'end_reading')->where('client_company_id', $sessionUser['client_company_id'])->where('status', 'end')->where('product_id', $request['product_id'])->orderBy('id', 'DESC')->first();
        $start_reading = $tank['opening_stock'] ?? 0;
        $shiftSaleId = 0;
        if ($shiftSale instanceof ShiftSale) {
            $start_reading = $shiftSale['end_reading'];
            $shiftSaleId = $shiftSale['id'];
        }
        $end_reading = 0;
        $stock = Stock::select('*')
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->where('date', date('Y-m-d'))
            ->where('module', 'product')->where('module_id', $inputData['product_id'])
            ->orderBy('id', 'DESC')
            ->first();
        $tank_refill = 0;
        if ($stock instanceof Stock) {
            $tank_refill = $stock['in_stock'];
        }
        $shiftSale = ShiftSale::select('id', 'end_reading')->where('client_company_id', $sessionUser['client_company_id'])->where('status', 'start')->where('product_id', $request['product_id'])->orderBy('id', 'DESC')->first();
        $fuelAdjustment = FuelAdjustment::select('id', 'loss_quantity')->where('shift_sale_id', $shiftSale['id'] ?? 0)->first();
        $adjustment = 0;
        $nozzleAdjustment = [];
        if ($fuelAdjustment instanceof FuelAdjustment) {
            $fuelAdjustmentData = FuelAdjustmentData::where('fuel_adjustment_id', $fuelAdjustment['id'])->get()->toArray();
            foreach ($fuelAdjustmentData as $adjustmentData) {
                if ($adjustmentData['tank_id'] == $tank['id']) {
                    $adjustment = $adjustmentData['quantity'];
                }
                if (!empty($adjustmentData['nozzle_id'])) {
                    $nozzleAdjustment[$adjustmentData['nozzle_id']] = $adjustmentData;
                }
            }
        }
        $consumption = $start_reading + $tank_refill + $adjustment - $end_reading;
        $amount = $consumption * $product['selling_price'];
        $result = [
            'date' => date('Y-m-d'),
            'product_id' => $inputData['product_id'],
            'start_reading' => $start_reading,
            'tank_refill' => $tank_refill,
            'adjustment' => $adjustment,
            'end_reading' => $end_reading,
            'consumption' => $consumption,
            'amount' => $amount,
            'selling_price' => $product->selling_price
        ];
        $dispensers = Dispenser::select('id', 'dispenser_name')
            ->where('product_id', $inputData['product_id'])
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->with(['nozzle' => function($q) {
                $q->select('nozzles.id', 'nozzles.dispenser_id', 'nozzles.name', 'nozzles.opening_stock');
            }])
            ->get()
            ->toArray();
        $shiftSaleData = ShiftSummary::select('nozzle_id', 'end_reading')->where('shift_sale_id', $shiftSaleId)->get()->keyBy('nozzle_id')->toArray();
        foreach ($dispensers as &$dispenser) {
            foreach ($dispenser['nozzle'] as &$nozzle) {
                $nozzle['start_reading'] = isset($shiftSaleData[$nozzle['id']]) ? $shiftSaleData[$nozzle['id']]['end_reading'] : $nozzle['opening_stock'];
                $nozzle['end_reading'] = 0;
                $nozzle['adjustment'] = isset($nozzleAdjustment[$nozzle['id']]) ? $nozzleAdjustment[$nozzle['id']]['quantity'] : 0;
                $nozzle['consumption'] =  $nozzle['end_reading']  - $nozzle['start_reading'] -  $nozzle['adjustment'];
                $nozzle['amount'] = $nozzle['consumption'] * $product['selling_price'];
            }
        }
        $result['dispensers'] = $dispensers;
        $shiftSale = ShiftSale::where('client_company_id', $sessionUser['client_company_id'])
            ->where('product_id', $inputData['product_id'])
            ->orderBy('id', 'DESC')
            ->first();
        $result['status'] = 'start';
        $result['pos_sale'] = [];
        if ($shiftSale instanceof ShiftSale) {
            if ($shiftSale['status'] == 'end') {
                $result['status'] = 'start';
            } else {
                $result['status'] = 'end';
            }
            $posSale = SaleData::select('sale_data.sale_id', 'sale_data.id', DB::raw('SUM(sale_data.subtotal) as amount'), 'sale.payment_category_id as category_id')
                ->leftJoin('sale', 'sale.id', '=', 'sale_data.sale_id')
                ->where('shift_sale_id', $shiftSale->id)
                ->groupBy('sale.payment_category_id')
                ->get()
                ->toArray();
            $result['pos_sale'] = $posSale;
        }
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
