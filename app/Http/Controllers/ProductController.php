<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\Module;
use App\Models\Category;
use App\Models\Dispenser;
use App\Models\NozzleReading;
use App\Models\Product;
use App\Models\SaleData;
use App\Models\ShiftSale;
use App\Models\ShiftSummary;
use App\Models\Stock;
use App\Models\Tank;
use App\Models\TankLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function save(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'name' => 'required',
            'selling_price' => 'required',
            'type_id' => 'required',
            'unit' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $product = new Product();
        $product->name = $inputData['name'];
        $product->selling_price = $inputData['selling_price'];
        $product->type_id = $inputData['type_id'];
        $product->buying_price = $inputData['buying_price'] ?? 0;
        $product->unit = $inputData['unit'];
        $product->opening_stock = $inputData['opening_stock'] ?? null;
        $product->client_company_id = $inputData['session_user']['client_company_id'];
        if ($product->save()) {
            $incomeCategory = Category::where('category', AccountCategory::INCOME)->where('client_company_id', $inputData['session_user']['client_company_id'])->first();
            $costOfGoodSoldCategory = Category::where('category', AccountCategory::COST_OF_GOOD_SOLD)->where('client_company_id', $inputData['session_user']['client_company_id'])->first();
            $stockCategory = Category::where('category', AccountCategory::STOCK_IN_HAND)->where('client_company_id', $inputData['session_user']['client_company_id'])->first();
            $categoryData = [
                [
                    'category' => $inputData['name'],
                    'parent_category' => $incomeCategory['id'],
                    'type' => $incomeCategory['type'],
                    'module' => 'product',
                    'module_id' => $product->id,
                    'client_company_id' => $inputData['session_user']['client_company_id']
                ],
                [
                    'category' => $inputData['name'],
                    'parent_category' => $costOfGoodSoldCategory['id'],
                    'type' => $costOfGoodSoldCategory['type'],
                    'module' => 'product',
                    'module_id' => $product->id,
                    'client_company_id' => $inputData['session_user']['client_company_id']
                ],
                [
                    'category' => $inputData['name'],
                    'parent_category' => $stockCategory['id'],
                    'type' => $stockCategory['type'],
                    'module' => 'product',
                    'module_id' => $product->id,
                    'client_company_id' => $inputData['session_user']['client_company_id']
                ]
            ];
            foreach ($categoryData as $data) {
                $category = new Category($data);
                if ($category->save()) {
                    $category->updateCategory();
                }
            }
            return response()->json(['status' => 200, 'message' => 'Successfully save product.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot save product.']);
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
    public function single(Request $request)
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
    public function update(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
            'name' => 'required',
            'type_id' => 'required',
            'buying_price' => 'required',
            'unit' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $product = Product::find($inputData['id']);
        if ($product == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find product.']);
        }
        $product->name = $inputData['name'];
        $product->selling_price = $inputData['selling_price'];
        $product->type_id = $inputData['type_id'];
        $product->buying_price = $inputData['buying_price'] ?? 0;
        $product->unit = $inputData['unit'];
        $product->opening_stock = $inputData['opening_stock'] ?? null;
        if ($product->save()) {
            $incomeCategory = Category::where('category', AccountCategory::INCOME)->where('client_company_id', $inputData['session_user']['client_company_id'])->first();
            $costOfGoodSoldCategory = Category::where('category', AccountCategory::COST_OF_GOOD_SOLD)->where('client_company_id', $inputData['session_user']['client_company_id'])->first();
            $stockCategory = Category::where('category', AccountCategory::STOCK_IN_HAND)->where('client_company_id', $inputData['session_user']['client_company_id'])->first();

            $incomeCategoryModel = Category::where('module', Module::PRODUCT)->where('parent_category', $incomeCategory->id)->where('module_id', $inputData['id'])->first();
            if ($incomeCategoryModel == null) {
                $incomeCategoryModel = new Category();
                $incomeCategoryModel->category = $inputData['name'];
                $incomeCategoryModel->parent_category = $incomeCategory['id'];
                $incomeCategoryModel->type = $incomeCategory['type'];
                $incomeCategoryModel->module = Module::PRODUCT;
                $incomeCategoryModel->module_id = $product->id;
                $incomeCategoryModel->client_company_id = $inputData['session_user']['client_company_id'];
            } else {
                $incomeCategoryModel->category = $inputData['name'];
            }
            if ($incomeCategoryModel->save()) {
                $incomeCategoryModel->updateCategory();
            }

            $costOfGoodSoldCategoryModel = Category::where('module', Module::PRODUCT)->where('parent_category', $costOfGoodSoldCategory->id)->where('module_id', $inputData['id'])->first();
            if ($costOfGoodSoldCategoryModel == null) {
                $costOfGoodSoldCategory = Category::where('category', AccountCategory::COST_OF_GOOD_SOLD)->where('client_company_id', $inputData['session_user']['client_company_id'])->first();
                $costOfGoodSoldCategoryModel = new Category();
                $costOfGoodSoldCategoryModel->category = $inputData['name'];
                $costOfGoodSoldCategoryModel->parent_category = $costOfGoodSoldCategory['id'];
                $costOfGoodSoldCategoryModel->type = $costOfGoodSoldCategory['type'];
                $costOfGoodSoldCategoryModel->module = Module::PRODUCT;
                $costOfGoodSoldCategoryModel->module_id = $product->id;
                $costOfGoodSoldCategoryModel->client_company_id = $inputData['session_user']['client_company_id'];
            } else {
                $costOfGoodSoldCategoryModel->category = $inputData['name'];
            }
            if ($costOfGoodSoldCategoryModel->save()) {
                $costOfGoodSoldCategoryModel->updateCategory();
            }

            $stockCategoryModel = Category::where('module', Module::PRODUCT)->where('parent_category', $stockCategory->id)->where('module_id', $inputData['id'])->first();
            if ($stockCategoryModel == null) {
                $stockCategory = Category::where('category', AccountCategory::STOCK_IN_HAND)->where('client_company_id', $inputData['session_user']['client_company_id'])->first();
                $stockCategoryModel = new Category();
                $stockCategoryModel->category = $inputData['name'];
                $stockCategoryModel->parent_category = $stockCategory['id'];
                $stockCategoryModel->type = $stockCategory['type'];
                $stockCategoryModel->module = Module::PRODUCT;
                $stockCategoryModel->module_id = $product->id;
                $stockCategoryModel->client_company_id = $inputData['session_user']['client_company_id'];
            } else {
                $stockCategoryModel->category = $inputData['name'];
            }
            if ($stockCategoryModel->save()) {
                $stockCategoryModel->updateCategory();
            }
            return response()->json(['status' => 200, 'message' => 'Successfully updated product.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot updated product.']);
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
        $product = Product::find($inputData['id']);
        if ($product == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find product.']);
        }
        Product::where('id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully delete product.']);
    }
    public function getDispenser(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'product_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $product = Product::where('id', $inputData['product_id'])->where('client_company_id', $inputData['session_user']['client_company_id'])->first();
        if ($product == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find product.']);
        }
        $stock = Stock::select('*')
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->where('date', date('Y-m-d'))
            ->where('module', 'product')->where('module_id', $inputData['product_id'])
            ->orderBy('id', 'DESC')
            ->first();
        $tank_refill = 0;
        $end_reading = 0;
        $start_reading = $product->opening_stock ?? 0;
        if ($stock != null) {
            if ($stock['in_stock'] > 0) {
                $start_reading = $stock['opening_stock'];
            } else {
                $start_reading = $stock['closing_stock'];
            }
            $tank_refill = $stock['in_stock'];
        } else {
            $previousStock = Stock::select('*')
                ->where('client_company_id', $inputData['session_user']['client_company_id'])
                ->where('module', 'product')->where('module_id', $inputData['product_id'])
                ->orderBy('id', 'DESC')
                ->first();
            if ($previousStock != null) {
                $start_reading = $previousStock->closing_stock;
            }
        }
        $tank = Tank::where('product_id', $inputData['product_id'])->select('id')->where('client_company_id', $inputData['session_user']['client_company_id'])->first();
        if ($tank != null) {
            $tankReading = TankLog::select('tank_log.volume')
                ->where('type', 'shift sell')
                ->where('tank_id', $tank->id)
                ->orderBy('tank_log.id', 'DESC')
                ->first();
            if ($tankReading != null) {
                $end_reading = $tankReading['volume'];
            }
        }
        $consumption = $start_reading + $tank_refill - $end_reading;
        $amount = $consumption * $product['selling_price'];
        $result = [
            'date' => date('Y-m-d'),
            'product_id' => $inputData['product_id'],
            'start_reading' => $start_reading,
            'tank_refill' => $tank_refill,
            'end_reading' => $end_reading,
            'consumption' => $consumption,
            'amount' => $amount,
            'selling_price' => $product->selling_price
        ];
        $dispensers = Dispenser::select('id', 'dispenser_name')
            ->where('product_id', $inputData['product_id'])
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->with(['nozzle' => function($q) {
                $q->select('nozzles.id', 'nozzles.dispenser_id', 'nozzles.name');
            }])
            ->get()
            ->toArray();
        foreach ($dispensers as &$dispenser) {
            foreach ($dispenser['nozzle'] as &$nozzle) {
                $reading = NozzleReading::select('*')->where('client_company_id', $inputData['session_user']['client_company_id'])->where('nozzle_id', $nozzle['id'])->orderBy('id', 'DESC')->where('type', 'shift sell')->limit(2)->get()->toArray();
                $nozzle['end_reading'] = isset($reading[0]) ? $reading[0]['reading'] : 0;
                $nozzle['start_reading'] = isset($reading[1]) ? $reading[1]['reading'] : 0;
                $nozzle['consumption'] =  $nozzle['end_reading']  - $nozzle['start_reading'];
                $nozzle['amount'] = $nozzle['consumption'] * $product['selling_price'];
            }
        }
        $result['dispensers'] = $dispensers;
        $shiftSale = ShiftSale::where('client_company_id', $inputData['session_user']['client_company_id'])
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
    public function getTank(Request $request)
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
