<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\Module;
use App\Helpers\SessionUser;
use App\Models\Car;
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
use App\Models\TankRefillTotal;
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
     * Saves a new product based on the provided input.
     *
     * @param Request $request The HTTP request object containing the product details to save.
     * @return JsonResponse The JSON response indicating the result of the save operation.
     */
    public function save(Request $request): JsonResponse
    {
        // Validate the request input to ensure all required fields are provided and correctly formatted
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'selling_price' => 'required|numeric',
            'buying_price' => 'required|numeric',
            'type_id' => 'required|integer',
        ]);

        // If validation fails, return a JSON response with status 500 and validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Find the product type by ID
        $productType = ProductType::where('id', $request->input('type_id'))->first();

        // If the product type is not found, return an error response
        if (!$productType instanceof ProductType) {
            return response()->json([
                'status' => 300,
                'message' => 'Cannot find product type'
            ]);
        }

        // If the product type is a tank, set opening stock to null
        if ($productType['tank'] == 1) {
            $request->merge(['opening_stock' => null]);
        }

        // Retrieve the session user information
        $sessionUser = SessionUser::getUser();

        // Find or return errors for required categories
        $directIncomeCategory = Category::where('slug', strtolower(AccountCategory::DIRECT_INCOME))
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first();
        if (!$directIncomeCategory instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [income] category.'
            ]);
        }

        $costOfGoodSoldCategory = Category::where('slug', strtolower(AccountCategory::COST_OF_GOOD_SOLD))
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first();
        if (!$costOfGoodSoldCategory instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [cost of goods sold] category'
            ]);
        }

        $stockCategory = Category::where('slug', strtolower(AccountCategory::STOCK_IN_HAND))
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first();
        if (!$stockCategory instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [stock in hand] category'
            ]);
        }

        // Create and populate a new Product instance
        $product = new Product();
        $product->name = $request->input('name');
        $product->selling_price = $request->input('selling_price');
        $product->type_id = $request->input('type_id');
        $product->buying_price = $request->input('buying_price') ?? 0;
        $product->driver_selling_price = $request->input('driver_selling_price') ?? 0;
        $product->opening_stock = $request->input('opening_stock') ?? null;
        $product->current_stock = $request->input('opening_stock') ?? null;
        $product->client_company_id = $sessionUser['client_company_id'];
        $product->vendor_id = $request->input('vendor_id') ?? null;

        // Save the product and return an error response if saving fails
        if (!$product->save()) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot save product.'
            ]);
        }

        $openingStock = $request->input('opening_stock') * $product['buying_price'];
        // Prepare category data for the product
        $categoryData = [
            'name' => $request->input('name'),
            'opening_balance' => $openingStock,
            'module_id' => $product->id
        ];

        // Save categories related to the product
        $directIncomeCategory = CategoryRepository::saveCategory($categoryData, $directIncomeCategory['id'], Module::PRODUCT);
        if (!$directIncomeCategory instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot save [income] category.'
            ]);
        }

        $costOfGoodSoldCategory = CategoryRepository::saveCategory($categoryData, $costOfGoodSoldCategory['id'], Module::PRODUCT);
        if (!$costOfGoodSoldCategory instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot save [cost of goods sold] category.'
            ]);
        }

        $stockCategory = CategoryRepository::saveCategory($categoryData, $stockCategory['id'], Module::PRODUCT);
        if (!$stockCategory instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot save [stock] category.'
            ]);
        }

        // Add opening balance to the stock category if applicable
        $stockCategory->addOpeningBalance();

        // Return a success response
        return response()->json([
            'status' => 200,
            'message' => 'Successfully saved product.'
        ]);
    }

    /**
     * Retrieves a paginated list of products with various filters and sorting options.
     *
     * @param Request $request The HTTP request object containing filter and sorting parameters.
     * @return JsonResponse The JSON response containing the list of products and their associated category information.
     */
    public function list(Request $request): JsonResponse
    {
        $sessionUser = SessionUser::getUser();
        // Retrieve input data and set default values for pagination and sorting
        $inputData = $request->all();
        $limit = $request->input('limit', 10);
        $keyword = $request->input('keyword', '');
        $order_by = $request->input('order_by', 'id');
        $order_mode = $request->input('order_mode', 'DESC');
        $shift_sale = $request->input('shift_sale', '');

        // Build the query to retrieve products along with their types
        $result = Product::select('products.*', 'product_types.name as product_type', 'product_types.shift_sale', 'product_types.unit')
            ->leftJoin('product_types', 'product_types.id', '=', 'products.type_id')
            ->where('client_company_id', $sessionUser['client_company_id']);

        // Apply filters based on input data
        if (!empty($inputData['type_id'])) {
            $result->where('products.type_id', $inputData['type_id']);
        }
        if (!empty($shift_sale)) {
            $result->where('product_types.shift_sale', $shift_sale);
        }
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('products.name', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('products.selling_price', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('products.buying_price', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('product_types.name', 'LIKE', '%'.$keyword.'%');
            });
        }

        // Order the results and apply pagination
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);

        // Collect product IDs for later use
        $productId = [];
        foreach ($result as &$data) {
            $productId[] = $data['id'];
        }

        // Retrieve category information for income, stock, and expenses
        $incomeCategory = Category::select('id', 'module_id')
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->whereIn('module_id', $productId)
            ->where('type', 'income')
            ->where('module', Module::PRODUCT)
            ->get()->keyBy('module_id')->toArray();

        $stockCategory = Category::select('id', 'module_id')
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->whereIn('module_id', $productId)
            ->where('type', 'assets')
            ->where('module', Module::PRODUCT)
            ->get()->keyBy('module_id')->toArray();

        $costOfGoodSoldCategory = Category::select('id')
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->where('slug', strtolower(AccountCategory::COST_OF_GOOD_SOLD))
            ->first();

        $expenseCategory = Category::select('id', 'module_id')
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->where('parent_category', $costOfGoodSoldCategory['id'])
            ->get()->keyBy('module_id')->toArray();

        // Add category IDs to the product data
        foreach ($result as &$data) {
            $data['income_category_id'] = isset($incomeCategory[$data['id']]) ? $incomeCategory[$data['id']]['id'] : '';
            $data['stock_category_id'] = isset($stockCategory[$data['id']]) ? $stockCategory[$data['id']]['id'] : '';
            $data['expense_category_id'] = isset($expenseCategory[$data['id']]) ? $expenseCategory[$data['id']]['id'] : '';
        }

        // Return the paginated product data with category information
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }

    /**
     * Retrieves a single product by its ID.
     *
     * @param Request $request The HTTP request object containing the product ID.
     * @return JsonResponse The JSON response containing the product details.
     */
    public function single(Request $request): JsonResponse
    {
        // Validate the request input to ensure 'id' is provided and is an integer
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer'
        ]);

        // Return validation errors if the request is invalid
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Find the product by ID
        $result = Product::find($request->input('id'));

        // Return a JSON response with the product details
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }

    /**
     * Updates an existing product based on the provided data.
     *
     * @param Request $request The HTTP request object containing product data.
     * @return JsonResponse The JSON response indicating the success or failure of the update.
     */
    public function update(Request $request): JsonResponse
    {
        // Validate the request data to ensure all required fields are present and correct
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'name' => 'required|string',
            'type_id' => 'required|integer',
            'buying_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
        ]);

        // If validation fails, return the errors
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }

        // Fetch the product type based on the provided type_id
        $productType = ProductType::where('id', $request->input('type_id'))->first();
        if (!$productType instanceof ProductType) {
            return response()->json([
                'status' => 300,
                'message' => 'Cannot find product type'
            ]);
        }

        // If the product type has a 'tank' attribute, set the opening stock to null
        if ($productType['tank'] == 1) {
            $request->merge(['opening_stock' => null]);
        }

        // Retrieve the session user
        $sessionUser = SessionUser::getUser();

        // Fetch categories for income, cost of goods sold, and stock in hand
        $directIncomeCategory = Category::where('slug', strtolower(AccountCategory::DIRECT_INCOME))
            ->where('client_company_id', $sessionUser['client_company_id'])->first();
        if (!$directIncomeCategory instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [income] category.'
            ]);
        }

        $costOfGoodSoldCategory = Category::where('slug', strtolower(AccountCategory::COST_OF_GOOD_SOLD))
            ->where('client_company_id', $sessionUser['client_company_id'])->first();
        if (!$costOfGoodSoldCategory instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [cost of good sold] category'
            ]);
        }

        $stockCategory = Category::where('slug', strtolower(AccountCategory::STOCK_IN_HAND))
            ->where('client_company_id', $sessionUser['client_company_id'])->first();
        if (!$stockCategory instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [stock in hand] category'
            ]);
        }

        // Fetch the product to be updated based on its ID
        $product = Product::find($request->input('id'));
        if (!$product instanceof Product) {
            return response()->json([
                'status' => 400,
                'error' => 'Cannot find [product].'
            ]);
        }

        // Calculate the new current stock
        $opening_stock = $product['opening_stock'] ?? 0;
        $current_stock = $product->current_stock + ($request->input('opening_stock') - $opening_stock);

        // Update product attributes
        $product->name = $request->input('name');
        $product->selling_price = $request->input('selling_price');
        $product->type_id = $request->input('type_id');
        $product->buying_price = $request->input('buying_price') ?? 0;
        $product->driver_selling_price = $request->input('driver_selling_price') ?? 0;
        $product->opening_stock = $request->input('opening_stock') ?? null;
        $product->current_stock = $current_stock;
        $product->vendor_id = $request->input('vendor_id') ?? null;

        // Save the updated product and handle any save failures
        if (!$product->save()) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot update [product].'
            ]);
        }

        $openingStock = $request->input('opening_stock') * $product['buying_price'];
        // Prepare category data for the product
        $categoryData = [
            'name' => $request->input('name'),
            'opening_balance' => $openingStock,
            'module_id' => $product->id
        ];

        // Update or save the associated income category
        $incomeCategoryModel = Category::where('module', Module::PRODUCT)
            ->where('parent_category', $directIncomeCategory->id)
            ->where('module_id', $request->input('id'))->first();
        if (!$incomeCategoryModel instanceof Category) {
            CategoryRepository::saveCategory($categoryData, $directIncomeCategory['id'], Module::PRODUCT);
        } else {
            CategoryRepository::updateCategory($incomeCategoryModel, $categoryData);
        }

        // Update or save the associated cost of goods sold category
        $costOfGoodSoldCategoryModel = Category::where('module', Module::PRODUCT)
            ->where('parent_category', $costOfGoodSoldCategory->id)
            ->where('module_id', $request->input('id'))->first();
        if (!$costOfGoodSoldCategoryModel instanceof Category) {
            CategoryRepository::saveCategory($categoryData, $costOfGoodSoldCategory['id'], Module::PRODUCT);
        } else {
            CategoryRepository::updateCategory($costOfGoodSoldCategoryModel, $categoryData);
        }

        // Update or save the associated stock category
        $stockCategoryModel = Category::where('module', Module::PRODUCT)
            ->where('parent_category', $stockCategory->id)
            ->where('module_id', $request->input('id'))->first();
        if (!$stockCategoryModel instanceof Category) {
            CategoryRepository::saveCategory($categoryData, $stockCategory['id'], Module::PRODUCT);
        } else {
            CategoryRepository::updateCategory($stockCategoryModel, $categoryData);
        }

        // Update the opening balance for the stock category
        $stockCategoryModel->addOpeningBalance();

        // Return a success message after successfully updating the product
        return response()->json([
            'status' => 200,
            'message' => 'Successfully updated product.'
        ]);
    }
    /**
     * Deletes a product and its associated categories.
     *
     * @param Request $request The HTTP request object containing the product ID.
     * @return JsonResponse The JSON response indicating the success or failure of the deletion.
     */
    public function delete(Request $request): JsonResponse
    {
        // Validate the request data to ensure the 'id' field is present and is an integer
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer'
        ]);

        // If validation fails, return the errors
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Find the product based on the provided ID
        $product = Product::find($request->input('id'));

        // Check if the product was found, if not, return an error message
        if (!$product instanceof Product) { // Corrected the condition to check if the product doesn't exist
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [product].'
            ]);
        }

        // Retrieve and delete all associated categories
        $categories = Category::where('module', Module::PRODUCT)
            ->where('module_id', $request->input('id'))
            ->get();

        foreach ($categories as $category) {
            $category->deleteCategory(); // Assuming deleteCategory() is a method that handles category deletion
        }

        // Delete the product from the database
        $product->delete(); // Corrected to call the delete() method on the product model instance

        // Return a success message after successfully deleting the product
        return response()->json([
            'status' => 200,
            'message' => 'Successfully deleted product.'
        ]);
    }

    /**
     * Retrieve dispenser and tank information based on the request input.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getDispenser(Request $request): JsonResponse
    {
        $inputData = $request->all();

        // Validate required input
        $validator = Validator::make($inputData, [
            'product_id' => 'required',
            'date' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }

        // Determine the date for querying
        $date = Carbon::parse($inputData['date']. date(' H:i:s'))->endOfDay();

        $sessionUser = SessionUser::getUser();

        // Retrieve the product and validate its existence
        $product = Product::where('id', $inputData['product_id'])
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first();

        if (!$product instanceof Product) {
            return response()->json(['status' => 500, 'error' => 'Cannot find product.']);
        }

        // Retrieve the product type
        $productType = ProductType::where('id', $product['type_id'])->first();

        if (!$productType instanceof ProductType) {
            return response()->json(['status' => 500, 'error' => 'Cannot find product type.']);
        }


        if ($request->has('status') && $request->input('status') == 'previous') {
            $date = Carbon::parse($request->input('date'), SessionUser::TIMEZONE)->endOfDay();
        }

        // Retrieve the relevant shift sale based on the product and date
        $shiftSaleId = $inputData['shift_id'] ?? 0;
        $shiftSale = ShiftTotal::where('product_id', $request->input('product_id'))
            ->where('start_date', '<=', $date)
            ->where('status', 'start')
            ->first();

        if ($shiftSale instanceof ShiftTotal) {
            $shiftSaleId = $shiftSale->id;
            $date = Carbon::parse($shiftSale->start_date, SessionUser::TIMEZONE);
        }
        // Query tanks with the most recent shift sale details
        $tanks = Tank::select(
            'tank.id',
            'tank.tank_name',
            'shift_sale.start_reading',
            'shift_sale.end_reading',
            'tank.opening_stock',
            'shift_sale.id as shift_sale_id'
        )
            ->leftJoin('shift_sale', function ($join) use ($date) {
                $join->on('tank.id', '=', 'shift_sale.tank_id')
                    ->whereRaw('shift_sale.shift_id = (
            SELECT ss.shift_id
            FROM shift_sale ss
            JOIN shift_total st ON ss.shift_id = st.id
            WHERE ss.tank_id = tank.id
            AND st.start_date = (
                SELECT MAX(st2.start_date)
                FROM shift_total st2
                JOIN shift_sale ss2 ON ss2.shift_id = st2.id
                WHERE ss2.tank_id = tank.id
                AND st2.start_date <= ?
                ORDER BY st2.start_date DESC
                LIMIT 1
            )
            ORDER BY st.start_date DESC
            LIMIT 1
        )', [$date]);
            })
            ->leftJoin('shift_total', 'shift_sale.shift_id', '=', 'shift_total.id')
            ->where('tank.product_id', $request['product_id'])
            ->get()
            ->toArray();

        // Retrieve dispensers with their nozzles and the latest shift summaries
        $dispensers = Dispenser::select('id', 'dispenser_name', 'tank_id')
            ->where('product_id', $inputData['product_id'])
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->with(['nozzle' => function ($query) use ($date) {
                $query->select('id', 'dispenser_id', 'name', 'opening_stock', 'pf', 'max_value', 'mac')
                    ->with(['latestShiftSummary' => function ($subQuery) use ($date) {
                        $subQuery->select('shift_summary.id', 'shift_summary.nozzle_id', 'shift_summary.start_reading', 'shift_summary.end_reading')
                            ->join('shift_sale', 'shift_summary.shift_sale_id', '=', 'shift_sale.id')
                            ->join('shift_total', 'shift_sale.shift_id', '=', 'shift_total.id')
                            ->where('shift_total.start_date', '<=', $date)
                            ->whereIn('shift_summary.id', function ($query) {
                                $query->select(DB::raw('MAX(shift_summary.id)'))
                                    ->from('shift_summary')
                                    ->groupBy('nozzle_id');
                            });
                    }]);
            }])
            ->get()
            ->toArray();


        // Retrieve fuel adjustments based on status and date
        $fuelAdjustment = FuelAdjustment::select('id', 'loss_quantity');
        if ($request->input('status') == 'previous') {
            $fuelAdjustment->where(function($query) use ($date) {
                $query->where(DB::raw('DATE(date)'), '=', date('Y-m-d', strtotime($date)));
            });
        } else {
            $fuelAdjustment->where(function($query) use ($shiftSaleId) {
                $query->where('shift_sale_id', '=', $shiftSaleId);
            });
        }
        $fuelAdjustment = $fuelAdjustment->get()->toArray();
        $fuelAdjustmentId = array_column($fuelAdjustment, 'id');
        $fuelAdjustmentData = FuelAdjustmentData::whereIn('fuel_adjustment_id', $fuelAdjustmentId)->get()->toArray();

        // Process dispenser data
        $dispenserArray = [];
        foreach ($dispensers as &$dispenser) {
            foreach ($dispenser['nozzle'] as &$nozzle) {
                $adjustment = 0;
                if (!empty($fuelAdjustment)) {
                    foreach ($fuelAdjustmentData as $adjustmentData) {
                        if (!empty($adjustmentData['nozzle_id']) && $adjustmentData['nozzle_id'] == $nozzle['id']) {
                            $adjustment = $adjustmentData['quantity'];
                        }
                    }
                }

                $nozzle['start_reading'] =  $nozzle['opening_stock'] ?? 0;
                if (!empty($nozzle['latest_shift_summary'])) {
                    $nozzle['start_reading'] = !empty($nozzle['latest_shift_summary'][0]['end_reading']) ? $nozzle['latest_shift_summary'][0]['end_reading'] : $nozzle['latest_shift_summary'][0]['start_reading'];
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

        // Retrieve and process tank refill data
        $tankRefill = TankRefillTotal::select('tank_refill_total.*', 'tank_refill.tank_id', DB::raw('SUM(dip_sale) as dip_sale'))
            ->leftJoin('tank_refill', 'tank_refill.refill_id', '=', 'tank_refill_total.id')
            ->where('tank_refill_total.shift_id', '=', $shiftSaleId)
            ->groupBy('tank_id')
            ->get()
            ->keyBy('tank_id')
            ->toArray();

        // Process tank data
        foreach ($tanks as &$tank) {
            $adjustment = 0;
            if (!empty($fuelAdjustment)) {
                foreach ($fuelAdjustmentData as $adjustmentData) {
                    if (!empty($adjustmentData['tank_id']) && $adjustmentData['tank_id'] == $tank['id']) {
                        $adjustment += $adjustmentData['quantity'];
                    }
                }
            }

            $tank['noDIPShow'] = 1;
            if (empty($shiftSaleId)) {
                $tank['start_reading'] = $tank['end_reading'] ?? ($tank['opening_stock'] ?? 0);
            }
            $tank['start_reading_mm'] = Tank::findHeight($tank['id'], $tank['start_reading']);
            $tank['end_reading'] = 0;
            $tank['end_reading_mm'] = 0;
            $tank['tank_refill'] = isset($tankRefill[$tank['id']]) ? $tankRefill[$tank['id']]['dip_sale'] : 0;
            $tank['tank_refill_mm'] = 0;
            $tank['adjustment'] = $adjustment;
            $tank['adjustment_mm'] = 0;
            $tank['consumption'] = $tank['start_reading'] + $tank['tank_refill'] - $tank['end_reading'] + $adjustment;
            $tank['consumption_mm'] = $tank['start_reading_mm'] + $tank['tank_refill_mm'] - $tank['end_reading_mm'];

            $tank['dispensers'] = $dispenserArray[$tank['id']] ?? [];
            unset($tank['opening_stock']);
        }

        // Calculate total consumption and amount
        $consumption = array_sum(array_column($tanks, 'consumption'));
        $amount = $consumption * $product['selling_price'];

        // Prepare the final result array
        $result = [
            'date' => Carbon::parse($date, SessionUser::TIMEZONE)
                ->setTimeFromTimeString(Carbon::now(SessionUser::TIMEZONE)->format('H:i:s'))
                ->format('Y-m-d H:i:s'),
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
                ->where('sale.client_company_id', $sessionUser['client_company_id'])
                ->whereNotNull('sale.id')
                ->groupBy('sale.payment_category_id')
                ->get()
                ->toArray();
            $result['pos_sale'] = $posSale;
            $result['total_pos_sale_liter'] = array_sum(array_column($posSale, 'quantity'));
        }

        $result['shift_id'] = $shiftSaleId;

        // Set status based on request input
        if ($request->input('status')) {
            $result['status'] = $request->input('status');
        }

        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * Retrieves a list of tanks associated with a specific product.
     *
     * @param Request $request The HTTP request object containing the product ID.
     * @return JsonResponse The JSON response containing the list of tanks or error messages.
     */
    public function getTank(Request $request): JsonResponse
    {
        // Validate the request to ensure 'product_id' is provided and is a string
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer'
        ]);

        // If validation fails, return the validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Retrieve tanks based on the provided product ID
        $result = Tank::select('id', 'tank_name')
            ->where('product_id', $request->input('product_id'))
            ->get()
            ->toArray();

        // If no tanks are found, return a relevant message
        if (empty($result)) {
            return response()->json([
                'status' => 404,
                'message' => 'No tanks found for the provided product ID.'
            ]);
        }

        // Return the list of tanks with a success status
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }

}
