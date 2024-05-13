<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\FuelMatixDateTimeFormat;
use App\Common\Module;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\BstiChart;
use App\Models\Category;
use App\Models\Dispenser;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\ShiftSale;
use App\Models\ShiftSaleTransaction;
use App\Models\ShiftSummary;
use App\Models\Tank;
use App\Models\TankLog;
use App\Repository\NozzleRepository;
use App\Repository\TankRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShiftSaleController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'date' => 'required',
            'tank' => 'required',
            'status' => 'required',
            'product_id' => 'required',
            'start_reading' => $inputData['status'] == 'end' ? 'required' : 'nullable',
            'end_reading' => $inputData['status'] == 'end' ? 'required' : 'nullable',
            'consumption' => $inputData['status'] == 'end' ? 'required' : 'nullable',
            'amount' => $inputData['status'] == 'end' ? 'required' : 'nullable',
            'dispensers' => $inputData['status'] == 'end' ? 'required|array' : 'nullable',
            'categories.*.category_id' => $inputData['status'] == 'end' ? 'required' : 'nullable',
            'categories.*.amount' => $inputData['status'] == 'end' ? 'required' : 'nullable',
        ],[
            'categories.*.category_id.required' => 'The category field is required.',
            'categories.*.amount.required' => 'The category field is required.'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $sessionUser = SessionUser::getUser();
        if ($inputData['status'] == 'start') {
            $shiftSale = new ShiftSale();
            $shiftSale->date = $inputData['date'];
            $shiftSale->start_time = Carbon::now('UTC')->format(FuelMatixDateTimeFormat::ONLY_TIME);
            $shiftSale->product_id = $inputData['product_id'];
            $shiftSale->status = 'start';
            $shiftSale->user_id = $sessionUser['id'];
            $shiftSale->client_company_id = $inputData['session_user']['client_company_id'];
            if (!$shiftSale->save()) {
                return response()->json(['status' => 400, 'message' => 'Cannot start shift sale.']);
            }
            return response()->json(['status' => 200, 'message' => 'Successfully started shift sale.']);
        }
        $tank = null;
        if (!empty($inputData['tank']) && $inputData['tank'] == 1) {
            $tank = Tank::where('product_id', $inputData['product_id'])->first();
            if (!$tank instanceof Tank) {
                return response()->json(['status' => 400, 'message' => 'Cannot find tank.']);
            }
            $tankLog = TankLog::select('id', 'tank_id', 'height', 'water_height', 'volume')
                ->where('tank_id', $tank['id'])
                ->orderBy('id', 'DESC')
                ->first();
            if ($tankLog instanceof TankLog && $tankLog['volume'] < $request['consumption']) {
                return response()->json(['status' => 400, 'message' => 'Your tank has not enough fuel. Please refill your tank.']);
            }
        }
        $category = Category::where('slug', strtolower(AccountCategory::DIRECT_INCOME))->where('client_company_id', $inputData['session_user']['client_company_id'])->first();
        $incomeCategory = Category::where('parent_category', $category['id'])
            ->where('module', Module::PRODUCT)
            ->where('module_id', $inputData['product_id'])
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->first();
        if (!$incomeCategory instanceof Category) {
            return response()->json(['status' => 500, 'error' => 'Cannot fin account income category.']);
        }
        $category = Category::where('slug', strtolower(AccountCategory::STOCK_IN_HAND))->where('client_company_id', $inputData['session_user']['client_company_id'])->first();
        $stockCategory = Category::where('parent_category', $category['id'])
            ->where('module', 'product')
            ->where('module_id', $inputData['product_id'])
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->first();
        if (!$stockCategory instanceof Category) {
            return response()->json(['status' => 500, 'error' => 'Cannot fin account stock category.']);
        }
        $category = Category::where('slug', strtolower(AccountCategory::COST_OF_GOOD_SOLD))->where('client_company_id', $inputData['session_user']['client_company_id'])->first();
        $costOfGoodSoldCategory = Category::where('parent_category', $category['id'])
            ->where('module', 'product')
            ->where('module_id', $inputData['product_id'])
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->first();
        if (!$costOfGoodSoldCategory instanceof Category) {
            return response()->json(['status' => 500, 'error' => 'Cannot fin account stock of good sold category.']);
        }
        $shiftSale = ShiftSale::where('client_company_id', $inputData['session_user']['client_company_id'])
            ->where('product_id', $inputData['product_id'])->where('status', 'start')
            ->where('date', '<=', $inputData['date'])
            ->first();
        if (!$shiftSale instanceof ShiftSale) {
            return response()->json(['status' => 400, 'message' => 'Cannot find shift sale.']);
        }
        $shiftSale->end_time = Carbon::now('UTC')->format(FuelMatixDateTimeFormat::ONLY_TIME);
        $shiftSale->start_reading =  $inputData['tank'] == 1 ? $inputData['start_reading'] : null;
        $shiftSale->tank_refill = $inputData['tank_refill'];
        $shiftSale->end_reading = $inputData['tank'] == 1 ? $inputData['end_reading'] : null;
        $shiftSale->adjustment = $inputData['adjustment'];
        $shiftSale->consumption = $inputData['consumption'];
        $shiftSale->amount = $inputData['amount'];
        $shiftSale->net_profit = $inputData['net_profit'] ?? null;
        $shiftSale->status = 'end';
        if (!$shiftSale->save()) {
            return response()->json(['status' => 500, 'error' => 'Cannot ended shift sale.']);
        }
        if (!empty($inputData['tank']) && $inputData['tank'] == 1) {
            $bstiChart = BstiChart::where('tank_id', $tank['id'])
                ->where('volume', '=', floor($inputData['end_reading']))
                ->first();
            TankRepository::readingSave([
                'tank_id' => $tank['id'],
                'date' => $inputData['date'],
                'volume' => $inputData['end_reading'],
                'height' => $bstiChart->height ?? 0,
                'type' => 'shift sell',
            ]);
        }
        $stockData = [
            'client_company_id' => $inputData['session_user']['client_company_id'],
            'date' => $inputData['date'],
            'out_stock' => $inputData['consumption'],
            'in_stock' => 0,
            'product_id' => $inputData['product_id'],
            'opening_stock' => $inputData['start_reading'] + $inputData['tank_refill']
        ];
        TransactionController::saveStock($stockData);
        foreach ($inputData['dispensers'] as $dispenser) {
            foreach ($dispenser['nozzle'] as $nozzle) {
                $shiftSaleSummary = new ShiftSummary();
                $shiftSaleSummary->shift_sale_id = $shiftSale->id;
                $shiftSaleSummary->dispenser_id = $dispenser['id'];
                $shiftSaleSummary->nozzle_id = $nozzle['id'];
                $shiftSaleSummary->start_reading = $nozzle['start_reading'];
                $shiftSaleSummary->end_reading = $nozzle['end_reading'] != 0 ? $nozzle['end_reading'] : $nozzle['start_reading'];
                $shiftSaleSummary->adjustment = $nozzle['adjustment'];
                $shiftSaleSummary->consumption = $nozzle['consumption'];
                $shiftSaleSummary->amount = $nozzle['amount'];
                $shiftSaleSummary->save();
                $readingData = [
                    'date' => $inputData['date'],
                    'nozzle_id' => $nozzle['id'],
                    'reading' => $nozzle['end_reading'],
                    'type' => 'shift sell',
                ];
                NozzleRepository::readingSave($readingData);
            }
        }
        $buyingPrice = 0;
        $product = Product::where('id', $inputData['product_id'])->first();
        $totalNozzleConsumption = $inputData['amount'] / $product['selling_price'];
        if (!empty($product['buying_price'])) {
            $buyingPrice = $product['buying_price'] * $totalNozzleConsumption;
        }
        $shiftSaleTransaction = [];
        foreach ($inputData['categories'] as $category) {
            $transactionData['linked_id'] = $incomeCategory['id'];
            $transactionData['transaction'] = [
                ['date' => $inputData['date'], 'account_id' => $category['category_id'], 'debit_amount' => 0, 'credit_amount' => $category['amount'], 'module' => 'shift sale', 'module_id' => $shiftSale->id]
            ];
            TransactionController::saveTransaction($transactionData);
            $shiftSaleTransaction[] = [
                'shift_sale_id' => $shiftSale->id,
                'category_id' => $category['category_id'],
                'amount' => $category['amount']
            ];
        }
        $linkedId = $stockCategory['id'];
        if (!empty($product['vendor_id'])) {
            $productType = ProductType::find($product['type_id']);
            if ($productType instanceof ProductType && $productType['vendor'] == 1) {
                $linkedId = $product['vendor_id'];
            }
        }
        $transactionData = [];
        $transactionData['linked_id'] = $linkedId;
        $transactionData['transaction'] = [
            ['date' => $inputData['date'], 'account_id' => $costOfGoodSoldCategory['id'], 'debit_amount' => 0, 'credit_amount' => $buyingPrice, 'module' => 'shift sale', 'module_id' => $shiftSale->id]
        ];
        TransactionController::saveTransaction($transactionData);

        $lossAmount = abs($inputData['net_profit']) * $buyingPrice;
        if ($inputData['net_profit'] < 0) {
            // Loss amount transaction after tank refill
            $lossCategory = Category::where('slug', strtolower(AccountCategory::EVAPORATIVE))
                ->where('client_company_id', $inputData['session_user']['client_company_id'])
                ->first();
            if ($lossCategory instanceof Category) {
                $description = 'Shift ID: '.$shiftSale['id'].', Product: '.$product['name'].', Loss: '.abs($inputData['net_profit']);
                $transactionData['linked_id'] = $lossCategory['id'];
                $transactionData['transaction'] = [
                    ['date' => $inputData['date'], 'description' => $description, 'account_id' => $stockCategory['id'], 'debit_amount' => abs($lossAmount), 'credit_amount' => 0],
                ];
                TransactionController::saveTransaction($transactionData);
            }
        } else if ($inputData['net_profit'] > 0) {
            // Profit amount transaction after tank refill
            $description = 'Shift ID: '.$shiftSale['id'].', Product: '.$product['name'].', Windfall: '.abs($inputData['net_profit']);
            $transactionData['linked_id'] = $stockCategory['id'];
            $transactionData['transaction'] = [
                ['date' => $inputData['date'], 'description' => $description, 'account_id' => $incomeCategory['id'], 'debit_amount' => abs($lossAmount), 'credit_amount' => 0],
            ];
            TransactionController::saveTransaction($transactionData);
        }

        ShiftSaleTransaction::insert($shiftSaleTransaction);
        return response()->json(['status' => 200, 'message' => 'Successfully ended shift sale.', 'shift_sale_id' => $shiftSale->id]);
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
        $order_by = $inputData['order_by'] ?? 'shift_sale.id';
        $order_mode = $inputData['order_mode'] ?? 'DESC';
        $result = ShiftSale::select('shift_sale.*', 'products.name as product_name', 'users.name as user_name', 'product_types.tank')
            ->leftJoin('products', 'products.id', 'shift_sale.product_id')
            ->leftJoin('product_types', 'product_types.id', 'products.type_id')
            ->leftJoin('users', 'users.id','=', 'shift_sale.user_id')
            ->where('shift_sale.client_company_id', $inputData['session_user']['client_company_id']);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('products.product_name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('users.name', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
        foreach ($result as &$data) {
            if ($data['tank'] == 0) {
                $data['start_reading'] = '';
                $data['end_reading'] = '';
            }
            $data['date'] = Helpers::formatDate($data['date'].' '.$data['start_time'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME);
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
        $result = ShiftSale::select('shift_sale.*', 'products.name as product_name', 'product_types.tank', 'product_types.unit')
            ->leftJoin('products', 'products.id', '=', 'shift_sale.product_id')
            ->leftJoin('product_types', 'product_types.id', '=', 'products.type_id')
            ->where('shift_sale.id', $request['id'])
            ->first();
        $shiftSummary = ShiftSummary::where('shift_sale_id', $inputData['id'])->get()->keyBy('nozzle_id');
        $dispensers = Dispenser::select('id', 'dispenser_name')
            ->where('product_id', $result['product_id'])
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->with(['nozzle' => function($q) {
                $q->select('nozzles.id', 'nozzles.dispenser_id', 'nozzles.name');
            }])
            ->get()
            ->toArray();
        foreach ($dispensers as &$dispenser) {
            foreach ($dispenser['nozzle'] as &$nozzle) {
                $nozzle['start_reading'] = isset($shiftSummary[$nozzle['id']]) ? $shiftSummary[$nozzle['id']]['start_reading'] : 0;
                $nozzle['end_reading'] = isset($shiftSummary[$nozzle['id']]) ? $shiftSummary[$nozzle['id']]['end_reading'] : 0;
                $nozzle['consumption'] = isset($shiftSummary[$nozzle['id']]) ? $shiftSummary[$nozzle['id']]['consumption'] : 0;
                $nozzle['amount'] = isset($shiftSummary[$nozzle['id']]) ? $shiftSummary[$nozzle['id']]['amount'] : 0;
            }
        }
        $result['dispensers'] = $dispensers;
        $result['categories'] = ShiftSaleTransaction::select('shift_sale_transaction.category_id', 'shift_sale_transaction.amount', 'categories.name')
            ->leftJoin('categories', 'categories.id', '=', 'shift_sale_transaction.category_id')
            ->where('shift_sale_id', $inputData['id'])
            ->get()
            ->toArray();
        $result['date_format'] = Helpers::formatDate($result['date']. ' '.$result['start_time'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME);
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
            'date' => 'required',
            'product_id' => 'required',
            'start_reading' => 'required',
            'end_reading' => 'required',
            'consumption' => 'required',
            'amount' => 'required',
            'dispensers' => 'required|array',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $shiftSale = ShiftSale::find($inputData['id']);
        if (!$shiftSale instanceof ShiftSale) {
            return response()->json(['status' => 400, 'message' => 'Cannot find shift sale.']);
        }
        $shiftSale->date = $inputData['date'];
        $shiftSale->product_id = $inputData['product_id'];
        $shiftSale->start_reading = $inputData['start_reading'];
        $shiftSale->end_reading = $inputData['end_reading'];
        $shiftSale->consumption = $inputData['consumption'];
        $shiftSale->amount = $inputData['amount'];
        if ($shiftSale->save()) {
            ShiftSummary::where('shift_sale_id', $inputData['id'])->delete();
            foreach ($inputData['dispensers'] as $dispenser) {
                foreach ($dispenser['nozzle'] as $nozzle) {
                    $shiftSaleSummary = new ShiftSummary();
                    $shiftSaleSummary->shift_sale_id = $inputData['id'];
                    $shiftSaleSummary->nozzle_id = $nozzle['id'];
                    $shiftSaleSummary->start_reading = $nozzle['start_reading'];
                    $shiftSaleSummary->end_reading = $nozzle['end_reading'];
                    $shiftSaleSummary->consumption = $nozzle['consumption'];
                    $shiftSaleSummary->amount = $nozzle['amount'];
                    $shiftSaleSummary->save();
                }
            }

            return response()->json(['status' => 200, 'message' => 'Successfully updated shift sale.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot updated shift sale.']);
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
        ShiftSale::where('id', $inputData['id'])->delete();
        ShiftSummary::where('shift_sale_id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted shift sale.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCategory(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $categoryId = Category::select('id')
            ->whereIn('slug', [strtolower(AccountCategory::CASH_IM_HAND), strtolower(AccountCategory::ACCOUNT_RECEIVABLE),  strtolower(AccountCategory::POS_MACHINE)])
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->pluck('id')
            ->toArray();
        $result = Category::select('id', 'name')
            ->with('product_price')
            ->whereIn('parent_category', $categoryId)
            ->orderBy('parent_category', 'ASC')
            ->get()
            ->toArray();
        foreach ($result as &$data) {
            $data['selected'] = false;
            if ($data['name'] == AccountCategory::CASH) {
                $data['selected'] = true;
            }
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getShiftByDate(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'date' => 'required|date'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = ShiftSale::select('shift_sale.id', 'shift_sale.start_time', 'shift_sale.end_time', 'products.name as product_name')
            ->leftJoin('products', 'products.id', '=', 'shift_sale.product_id')
            ->where('date', $requestData['date'])
            ->get()
            ->toArray();
        foreach ($result as &$data) {
            if (!empty($data['start_time']) && !empty($data['end_time'])) {
                $data['name'] = $data['product_name']. ' ('.Helpers::formatDate($data['start_time'], FuelMatixDateTimeFormat::STANDARD_TIME).' - '.Helpers::formatDate($data['end_time'], FuelMatixDateTimeFormat::STANDARD_TIME).')';
            } else {
                $data['name'] = $data['product_name']. ' ('.Helpers::formatDate($data['start_time'], FuelMatixDateTimeFormat::STANDARD_TIME).' - Running)';
            }
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
}
