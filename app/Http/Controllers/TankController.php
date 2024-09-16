<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\FuelMatixCategoryType;
use App\Common\FuelMatixDateTimeFormat;
use App\Common\FuelMatixStatus;
use App\Common\Module;
use App\Console\Commands\FuelMatixCategory;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Imports\BstiChartImport;
use App\Models\BstiChart;
use App\Models\Category;
use App\Models\Dispenser;
use App\Models\NozzleReading;
use App\Models\PayOrder;
use App\Models\PayOrderData;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\ShiftSale;
use App\Models\ShiftTotal;
use App\Models\Tank;
use App\Models\TankLog;
use App\Models\TankRefill;
use App\Models\TankRefillHistory;
use App\Models\TankRefillTotal;
use App\Models\Transaction;
use App\Models\User;
use App\Repository\CategoryRepository;
use App\Repository\NozzleRepository;
use App\Repository\TankRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class TankController extends Controller
{
    /**
     * Save a new tank entry and optionally import data from a file.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        // Retrieve all input data from the request
        $inputData = $request->all();

        // Validate the input data
        $validator = Validator::make($inputData, [
            'product_id' => 'required',  // Product ID is required
            'tank_name' => 'required',   // Tank name is required
            'file' => 'required|file',   // File is required and must be a file
            'tank_mac' => 'nullable|string' // Tank MAC is optional and must be a string
        ]);

        // Return validation errors if validation fails
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }

        // Get the session user
        $sessionUser = SessionUser::getUser();
        if (!$sessionUser instanceof User) {
            return response()->json([
                'status' => 400,
                'message' => 'Session user cannot be found.'
            ]);
        }

        // Retrieve the asset category based on the product ID and other parameters
        $assetCategory = Category::where('module', Module::PRODUCT)
            ->where('module_id', $inputData['product_id'])
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->where('type', FuelMatixCategoryType::ASSET)
            ->first();

        // If no category is found, return an error response
        if (!$assetCategory instanceof Category) {
            return response()->json([
                'status' => 200,
                'message' => 'Cannot find category.'
            ]);
        }

        // Create a new Tank object
        $tank = new Tank();
        $tank->product_id = $inputData['product_id'];
        $tank->tank_name = $inputData['tank_name'];
        $tank->opening_stock = $inputData['opening_stock'] ?? 0; // Set opening stock, default to 0 if not provided
        $tank->client_company_id = $sessionUser['client_company_id'];
        $tank->tank_mac = $inputData['tank_mac'] ?? ''; // Set tank MAC, default to an empty string if not provided

        // Save the Tank object to the database
        if (!$tank->save()) {
            return response()->json(['status' => 400, 'message' => 'Cannot save tank.']);
        }

        // Import data from the uploaded file if provided
        if ($request->file('file')) {
            Excel::import(new BstiChartImport($tank['id']), $request->file('file'));
        }

        // Retrieve the most recent BstiChart for the tank
        $bstiChart = BstiChart::select('volume', 'height')
            ->where('tank_id', $tank['id'])
            ->whereNotNull('volume')
            ->whereNotNull('height')
            ->orderBy('id', 'DESC')
            ->first();

        // Update tank capacity and height based on BstiChart data
        $tank->capacity = $bstiChart['volume'] ?? 0; // Set capacity, default to 0 if not available
        $tank->height = $bstiChart['height'] ?? 0; // Set height, default to 0 if not available

        // Save the updated Tank object to the database
        if (!$tank->save()) {
            return response()->json([
                'status' => 300,
                'message' => 'Cannot save tank.'
            ]);
        }

        // Add opening stock to the tank using the asset category
        $tank->addOpeningStock($assetCategory);

        // Return a success response
        return response()->json(['status' => 200, 'message' => 'Successfully saved tank.']);
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
        $productId = $request->input('product_id', '');
        $sessionUser = SessionUser::getUser();
        $result = Tank::select('tank.id' ,'tank.tank_name', 'tank.height', 'tank.capacity', 'products.name as product_name', 'product_types.name as product_type_name', 'tank.opening_stock')
            ->leftJoin('products', 'products.id', 'tank.product_id')
            ->leftJoin('product_types', 'product_types.id', 'products.type_id')
            ->where('tank.client_company_id', $inputData['session_user']['client_company_id'])
            ->where('tank.client_company_id', $sessionUser['client_company_id']);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('tank.tank_name', 'LIKE', '%'.$keyword.'%');
            });
        }
        if (!empty($productId)) {
            $result->where(function($q) use ($productId) {
               $q->where('tank.product_id', $productId);
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
        $tankId = [];
        foreach ($result as $data) {
            $tankId[] = $data['id'];
        }
        $tankIds = TankLog::select(DB::raw('MAX(id) as id'))
            ->whereIn('tank_id', $tankId)
            ->groupBy('tank_id')
            ->pluck('id')
            ->toArray();
        $tankLog = TankLog::select('id', 'tank_id', 'height', 'water_height', 'volume')
            ->whereIn('id', $tankIds)
            ->get()
            ->keyBy('tank_id')
            ->toArray();
        foreach ($result as &$data) {
            $data['last_reading'] = $tankLog[$data['id']] ?? null;
            $data['fuel_percent'] = 0;
            $data['water_percent'] = 0;
            if ($data['last_reading'] != null) {
                if ($data['capacity'] > 0 && $data['last_reading']['height'] > 0) {
                    $data['fuel_percent'] = number_format(($data['last_reading']['height'] / $data['height']) * 100, 2);
                }
                if ($data['capacity'] > 0 && $data['last_reading']['water_height'] > 0) {
                    $data['water_percent'] = number_format(($data['last_reading']['water_height'] / $data['height']) * 100, 2);
                }
            }
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
        $result = Tank::select('id', 'tank_name', 'height', 'capacity', 'product_id', 'opening_stock', 'tank_mac')->find($inputData['id']);
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
            'id' => 'required',
            'tank_name' => 'required',
            'product_id' => 'required',
            'tank_mac' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $tank = Tank::find($inputData['id']);
        if (!$tank instanceof Tank) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [tank].']);
        }
        $sessionUser = SessionUser::getUser();
        $assetCategory = Category::where('module', Module::PRODUCT)
            ->where('module_id', $inputData['product_id'])
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->where('type', FuelMatixCategoryType::ASSET)
            ->first();
        if (!$assetCategory instanceof Category) {
            return response()->json([
                'status' => 200,
                'message' => 'Cannot find category.'
            ]);
        }
        $tank->product_id = $inputData['product_id'];
        $tank->tank_name = $inputData['tank_name'];
        $tank->opening_stock = $inputData['opening_stock'] ?? 0;
        $tank->tank_mac = $inputData['tank_mac'] ?? '';
        if (!$tank->save()) {
            return response()->json(['status' => 400, 'message' => 'Cannot updated [tank].']);
        }
        if ($request->file('file')) {
            BstiChart::where('tank_id', $tank['id'])->delete();
            Excel::import(new BstiChartImport($tank['id']), $request->file('file'));
        }
        $bstiChart = BstiChart::select('volume', 'height')
            ->where('tank_id', $inputData['id'])
            ->whereNotNull('volume')
            ->whereNotNull('height')
            ->orderBy('id', 'DESC')
            ->first();
        $tank->capacity =  $bstiChart['volume'] ?? 0;
        $tank->height =  $bstiChart['height'] ?? 0;
        if (!$tank->save()) {
            return response()->json([
                'status' => 300,
                'message' => 'Cannot update tank.'
            ]);
        }
        $tank->addOpeningStock($assetCategory);
        return response()->json(['status' => 200, 'message' => 'Successfully updated tank.']);
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
        Tank::where('id', $inputData['id'])->delete();
        BstiChart::where('tank_id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted tank.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function readingSave(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'tank_id' => 'required',
            'date' => 'required',
            'height' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $bstiChart = BstiChart::where('tank_id', $inputData['tank_id'])
            ->where('height', '=', floor($inputData['height']))
            ->first();
        $tankReading = TankRepository::readingSave([
            'tank_id' => $inputData['tank_id'],
            'date' => $inputData['date'],
            'height' => $inputData['height'],
            'volume' => $bstiChart['volume'] ?? 0,
            'type' => $inputData['type'],
        ]);
        if (!$tankReading instanceof TankLog) {
            return response()->json($tankReading);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully saved tank reading.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function readingList(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $limit = $inputData['limit'] ?? 10;
        $keyword = $inputData['keyword'] ?? '';
        $order_by = $inputData['order_by'] ?? 'tank_log.id';
        $order_mode = $inputData['order_mode'] ?? 'DESC';
        $result = TankLog::select('tank_log.id', 'tank_log.date', 'tank_log.height', 'tank_log.water_height', 'tank_log.volume', 'tank.tank_name')
            ->leftJoin('tank', 'tank.id', '=', 'tank_log.tank_id')
            ->where('tank_log.client_company_id', $inputData['session_user']['client_company_id']);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('tank.tank_name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('tank_log.height', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('tank_log.water_height', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('tank_log.volume', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
        foreach ($result as $data) {
            $data['date'] = Helpers::formatDate($data['date'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME);
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function readingSingle(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = TankLog::select('id', 'height', 'water_height', 'tank_id', 'date', 'type')->find($inputData['id']);
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function readingUpdate(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
            'tank_id' => 'required',
            'date' => 'required',
            'height' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $reading = TankLog::find($inputData['id']);
        if (!$reading instanceof TankLog) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [tank reading].']);
        }
        $bstiChart = BstiChart::where('tank_id', $inputData['tank_id'])
            ->where('height', '=', floor($inputData['height']))
            ->first();
        $reading->tank_id = $inputData['tank_id'];
        $reading->date = $inputData['date'].' '.date('H:i:s');
        $reading->height = $inputData['height'];
        $reading->water_height = $inputData['water_height'] ?? null;
        $reading->volume = $bstiChart != null ? $bstiChart->volume : 0;
        if ($reading->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully updated tank reading.']);
        }
        return response()->json(['status' => 400, 'message' => 'Cannot updated [tank reading].']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function readingDelete(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        TankLog::where('id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted tank reading.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function latestReading(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'tank_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $tank = Tank::where('id', $inputData['tank_id'])->first();
        if (!$tank instanceof Tank) {
            return response()->json(['status' => 500, 'message' => 'Cannot find tank.']);
        }
        $result = TankLog::select('id', 'volume', 'height')
            ->where('type', 'tank refill')
            ->where('tank_id', $inputData['tank_id'])
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->orderBy('id',  'DESC')
           ->first();
        $start_reading = 0;
        $end_reading = 0;
        $start_reading_mm = 0;
        $end_reading_mm = 0;
        if ($result instanceof TankLog) {
            $start_reading = $result['volume'];
            $start_reading_mm = $result['height'];
        }
        $dip_sale = $end_reading - $start_reading;
        $dip_sale = $dip_sale > 0 ? $dip_sale : 0;
        return response()->json([
            'status' => 200,
            'data' => [
                'start_reading_mm' => $start_reading_mm,
                'end_reading_mm' => $end_reading_mm,
                'start_reading' => $start_reading,
                'end_reading' => $end_reading,
                'dip_sale' => $dip_sale,
                'tank_height' => $tank['height']
            ]
        ]);
    }
    public function getNozzle(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'tank_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $dispensers = Dispenser::select('id', 'dispenser_name')
            ->where('tank_id', $inputData['tank_id'])
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->with(['nozzle' => function($q) {
                $q->select('nozzles.id', 'nozzles.dispenser_id', 'nozzles.name');
            }])
            ->get()
            ->toArray();
        foreach ($dispensers as &$dispenser) {
            foreach ($dispenser['nozzle'] as &$nozzle) {
                $reading = NozzleReading::select('reading')->where('client_company_id', $inputData['session_user']['client_company_id'])->where('nozzle_id', $nozzle['id'])->where('type', 'tank refill')->orderBy('id', 'DESC')->limit(2)->get()->toArray();
                $nozzle['end_reading'] = isset($reading[0]) ? $reading[0]['reading'] : 0;
                $nozzle['start_reading'] = isset($reading[1]) ? $reading[1]['reading'] : 0;
                $nozzle['sale'] = 0;
            }
        }
        return response()->json([
            'status' => 200,
            'data' => $dispensers
        ]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function refillSave(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'pay_order_id' => 'required|integer',
            'tanks' => 'required|array',
            'tanks.*.id' => 'required',
            'tanks.*.dip_sale' => 'required|numeric|min:0',
            'tanks.*.start_reading_mm' => 'nullable|numeric|min:0',
            'tanks.*.end_reading_mm' => 'nullable|numeric|min:0',
            'tanks.*.dispensers' => 'array',
            'tanks.*.dispensers.*.nozzle' => 'array',
            'tanks.*.dispensers.*.nozzle.*.start_reading' => 'nullable|numeric|min:0',
            'tanks.*.dispensers.*.nozzle.*.end_reading' => 'nullable|numeric|min:0',
            'tanks.*.dispensers.*.nozzle.*.sale' => 'nullable|numeric|min:0',
            'total_refill_volume' => 'required|numeric|min:0',
        ],[
            'tanks.*.dip_sale.min' => 'The volume cannot be negative.',
            'total_refill_volume.min' => 'The total refill volume cannot be negative.',
            'tanks.*.start_reading_mm.min' => 'The start reading cannot be negative.',
            'tanks.*.end_reading_mm.min' => 'The end reading cannot be negative.',
            'tanks.*.dispensers.*.nozzle.*.start_reading.min' => 'The start reading cannot be negative.',
            'tanks.*.dispensers.*.nozzle.*.end_reading.min' => 'The end reading cannot be negative.',
            'tanks.*.dispensers.*.nozzle.*.sale.min' => 'The sale cannot be negative.',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $sessionUser = SessionUser::getUser();
        if (!$sessionUser instanceof User) {
            return response()->json([
                'status' => 500,
                'message' => 'Session user cannot be found.'
            ]);
        }
        $shiftSale = ShiftTotal::where('client_company_id', $sessionUser['client_company_id'])
            ->where('product_id', $request->input('product_id'))
            ->where('status', 'start')
            ->first();
        if (!$shiftSale instanceof ShiftTotal) {
            return response()->json(['status' => 400, 'message' => 'Please start shift sale first.']);
        }
        $payOrder = PayOrderData::select('pay_order_data.*', 'pay_order.vendor_id')
            ->where('product_id', $request->input('product_id'))
            ->leftJoin('pay_order', 'pay_order.id', '=', 'pay_order_data.pay_order_id')
            ->where('pay_order_id', $inputData['pay_order_id'])
            ->where('status', FuelMatixStatus::PENDING)->first();
        if (!$payOrder instanceof PayOrderData) {
            return response()->json([
                'status' => 500,
                'errors' => ['pay_order_id' => ['The pay order cannot be found.']]
            ]);
        }
        $product = Product::find($request->input('product_id'));
        if (!$product instanceof Product) {
            return response()->json(['status' => 400, 'message' => 'Can not find [product].']);
        }
        $category = Category::where('slug', strtolower(AccountCategory::COST_OF_GOOD_SOLD))->where('client_company_id', $inputData['session_user']['client_company_id'])->first();
        $costOfGoodSoldCategory = Category::where('parent_category', $category['id'])
            ->where('module', 'product')
            ->where('module_id', $request->input('product_id'))
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->first();
        if (!$costOfGoodSoldCategory instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [cost of good sold] category.']);
        }
        $request->merge(['shift_id' => $shiftSale->id]);
        $request->merge(['product_name' => $product->name]);
        $request->merge(['vendor_id' => $payOrder->vendor_id]);

        $response = TankRepository::saveTankRefill($request->all(), $product);
        if (!$response instanceof TankRefillTotal) {
            return response()->json($response);
        }
        $payOrder->status = FuelMatixStatus::COMPLETE;
        $payOrder->save();
        return response()->json([
            'status' => 200,
            'message' => 'Successfully saved tank refill.'
        ]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     * */
    public function refillList(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $limit = $inputData['limit'] ?? 10;
        $keyword = $inputData['keyword'] ?? '';
        $order_by = $inputData['order_by'] ?? 'tank_refill.id';
        $order_mode = $inputData['order_mode'] ?? 'DESC';
        $result = TankRefillTotal::select('tank_refill_total.*', 'products.name as product_name', 'pay_order.amount')
            ->leftJoin('products', 'products.id', 'tank_refill_total.product_id')
            ->leftJoin('pay_order', 'pay_order.id', 'tank_refill_total.pay_order_id')
            ->where('tank_refill_total.client_company_id', $inputData['session_user']['client_company_id']);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('products.product_name', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
        foreach ($result as &$data) {
            $data['date'] = date('d/m/Y', strtotime($data['date'])). ' '.date('h:i A', strtotime('time'));
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function refillSingle(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = TankRefill::find($inputData['id']);
        $refillHistory = TankRefillHistory::where('tank_refill_id', $inputData['id'])->get()->keyBy('nozzle_id');
        $dispensers = Dispenser::select('id', 'dispenser_name')
            ->where('tank_id', $result['tank_id'])
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->with(['nozzle' => function($q) {
                $q->select('nozzles.id', 'nozzles.dispenser_id', 'nozzles.name');
            }])
            ->get()
            ->toArray();
        foreach ($dispensers as &$dispenser) {
            foreach ($dispenser['nozzle'] as &$nozzle) {
                $nozzle['start_reading'] = isset($refillHistory[$nozzle['id']]) ? $refillHistory[$nozzle['id']]['start_reading'] : 0;
                $nozzle['end_reading'] = isset($refillHistory[$nozzle['id']]) ? $refillHistory[$nozzle['id']]['end_reading'] : 0;
                $nozzle['sale'] = isset($refillHistory[$nozzle['id']]) ? $refillHistory[$nozzle['id']]['sale'] : 0;
            }
        }
        return response()->json(['status' => 200, 'data' => $result, 'dispensers' => $dispensers]);
    }
    public function refillUpdate(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
            'date' => 'required',
            'tank_id' => 'required',
            'pay_order_id' => 'required',
            'quantity' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $payOrder = PayOrder::find($inputData['pay_order_id']);
        if ($payOrder == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find pay order.']);
        }
        $tank = Tank::find($inputData['tank_id']);
        if ($tank == null) {
            return response()->json(['status' => 500, 'error' => 'Can not find tank.']);
        }
        if ($tank['product_id'] == null) {
            return response()->json(['status' => 500, 'error' => 'Tank has no product. Please assign product.']);
        }
        $tankRefill = TankRefill::find($inputData['id']);
        if ($tankRefill == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find tank refill.']);
        }
        $tankRefill->date = $inputData['date'];
        $tankRefill->tank_id = $inputData['tank_id'];
        $tankRefill->pay_order_id = $inputData['pay_order_id'];
        $tankRefill->quantity = $inputData['quantity'];
        $tankRefill->start_reading = $inputData['start_reading'];
        $tankRefill->end_reading = $inputData['end_reading'] ?? 0;
        $tankRefill->dip_sale = $inputData['dip_sale'] ?? 0;
        $tankRefill->total_refill_volume = $inputData['total_refill_volume'] ?? 0;
        $tankRefill->net_profit = $inputData['net_profit'] ?? 0;
        if ($tankRefill->save()) {
            $productPrice = ProductPrice::where('module', 'tank refill')->where('module_id', $inputData['id'])->first();
            if ($productPrice != null) {
                $productPrice->quantity = $payOrder['quantity'];
                $productPrice->price = $payOrder['amount'];
                $productPrice->unit_price = $payOrder['amount'] / $payOrder['quantity'];
                $productPrice->save();
            }
            if (isset($inputData['dispensers'])) {
                TankRefillHistory::where('tank_refill_id', $inputData['id'])->delete();
                foreach ($inputData['dispensers'] as $dispenser) {
                    foreach ($dispenser['nozzle'] as $nozzle) {
                        $tankRefillHistory = new TankRefillHistory();
                        $tankRefillHistory->tank_refill_id = $inputData['id'];
                        $tankRefillHistory->nozzle_id = $nozzle['id'];
                        $tankRefillHistory->start_reading = $nozzle['start_reading'];
                        $tankRefillHistory->end_reading = $nozzle['end_reading'];
                        $tankRefillHistory->sale = $nozzle['sale'];
                        $tankRefillHistory->save();
                    }
                }
            }
            return response()->json(['status' => 200, 'message' => 'Successfully updated tank refill.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot updated tank refill.']);
    }
    public function refillDelete(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        TankRefillTotal::where('id', $inputData['id'])->delete();
        TankRefill::where('refill_id', $inputData['id'])->delete();
        TankRefillHistory::where('tank_refill_id', $inputData['id'])->delete();
        ProductPrice::where('module', 'tank refill')->where('module_id', $inputData['id'])->delete();
        Transaction::where('module', Module::TANK_REFILL)->where('module_id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted tank refill.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getTankByProduct(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'product_id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $product = Product::find($requestData['product_id']);
        if (!$product instanceof Product) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [product].']);
        }
        $tank = Tank::select('id', 'tank_name')
            ->where('product_id', $requestData['product_id'])
            ->first();
        if (!$tank instanceof Tank) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [product].']);
        }
        return response()->json(['status' => 200, 'data' => $tank]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getVolume(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'tank_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $tank = Tank::where('id', $request->input('tank_id'))->first();
        if (!$tank instanceof Tank) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [tank].'
            ]);
        }
        $data = null;
        if ($request->has('height')) {
            $data = $tank->findVolume($request->input('height'));
        } else  if ($request->has('volume')) {
            $data = Tank::findHeight($tank->id, $request->input('volume'));
        }
        return response()->json([
            'status' => 200,
            'data' => $data
        ]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getBstiChart(Request $request)
    {
        $tank_id = $request['tank_id'] ?? 0;
        if (!empty($request['product_id'])) {
            $tank = Tank::where('product_id', $request['product_id'])->first();
            if ($tank instanceof Tank) {
                $tank_id = $tank['id'];
            }
        }
        $bstiChart = BstiChart::select('volume', 'height')->where('tank_id', $tank_id)
            ->get()
            ->toArray();
        return response()->json([
            'status' => 200,
            'data' => $bstiChart
        ]);
    }
    /**
     * Retrieves tanks with the latest readings and their associated dispensers and nozzles.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function tankWithLatestReading(Request $request): JsonResponse
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer'
        ]);

        // If validation fails, return a JSON response with status 500 and error messages
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Get the session user
        $sessionUser = SessionUser::getUser();

        // Query to get tanks with the latest log
        $tanks = Tank::select('tank.id', 'tank.tank_name', 'tank.opening_stock', 'tank_log.volume', 'tank_log.height')
            ->leftJoin('tank_log', function($join) {
                $join->on('tank.id', '=', 'tank_log.tank_id')
                    ->whereRaw('tank_log.id = (SELECT MAX(t2.id) FROM tank_log t2 WHERE t2.tank_id = tank.id)');
            })
            ->where('tank.product_id', $request->input('product_id'))
            ->where('tank.client_company_id', $sessionUser['client_company_id'])
            ->get()
            ->toArray();

        // Query to get dispensers with their associated nozzles and latest nozzle readings
        $dispensers = Dispenser::select('id', 'dispenser_name', 'tank_id')
            ->where('product_id', $request->input('product_id'))
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->with(['nozzle' => function ($query) {
                $query->select('nozzles.id', 'nozzles.dispenser_id', 'nozzles.name', 'nozzles.opening_stock', 'nozzles.pf', 'nozzles.max_value', 'nozzle_readings.reading')
                    ->leftJoin('nozzle_readings', function($join) {
                        $join->on('nozzles.id', '=', 'nozzle_readings.nozzle_id')
                            ->whereRaw('nozzle_readings.id = (SELECT MAX(nr2.id) FROM nozzle_readings nr2 WHERE nr2.nozzle_id = nozzles.id)');
                    });
            }])
            ->get()
            ->toArray();

        // Initialize an array to store dispensers grouped by tank_id
        $dispenserArray = [];

        // Process dispensers and their nozzles
        foreach ($dispensers as &$dispenser) {
            foreach ($dispenser['nozzle'] as &$nozzle) {
                // Set initial values for nozzle readings
                $nozzle['start_reading'] = $nozzle['opening_stock'] ?? 0;
                if (!empty($nozzle['reading'])) {
                    $nozzle['start_reading'] = $nozzle['reading'];
                }
                $nozzle['end_reading'] = $nozzle['start_reading'];
                $nozzle['sale'] = 0;
                unset($nozzle['reading']);
                unset($nozzle['opening_stock']);
            }
            // Group dispensers by their tank_id
            $dispenserArray[$dispenser['tank_id']][] = $dispenser;
        }

        // Process tanks and associate them with their dispensers
        foreach ($tanks as &$tank) {
            // Set initial values for tank readings
            $tank['start_reading'] = $tank['opening_stock'] ?? 0;
            if (!empty($tank['volume'])) {
                $tank['start_reading'] = $tank['volume'];
            }
            $tank['start_reading_mm'] = Tank::findHeight($tank['id'], $tank['start_reading']);
            $tank['end_reading'] = $tank['start_reading'];
            $tank['end_reading_mm'] = $tank['start_reading_mm'];
            $tank['dip_sale'] = 0;
            $tank['dip_sale_mm'] = 0;
            unset($tank['opening_stock']);
            // Attach the dispensers to the tank
            $tank['dispensers'] = $dispenserArray[$tank['id']] ?? [];
        }

        // Return the processed data in a JSON response with status 200
        return response()->json([
            'status' => 200,
            'data' => $tanks
        ]);
    }

}
