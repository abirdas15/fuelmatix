<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Models\BstiChart;
use App\Models\Category;
use App\Models\Dispenser;
use App\Models\NozzleReading;
use App\Models\PayOrder;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Stock;
use App\Models\Tank;
use App\Models\TankLog;
use App\Models\TankRefill;
use App\Models\TankRefillHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TankController extends Controller
{
    public function save(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'product_id' => 'required',
            'tank_name' => 'required',
            'capacity' => 'required',
            'height' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $tank = new Tank();
        $tank->product_id = $inputData['product_id'];
        $tank->tank_name = $inputData['tank_name'];
        $tank->capacity = $inputData['capacity'];
        $tank->height = $inputData['height'];
        $tank->client_company_id = $inputData['session_user']['client_company_id'];
        if ($tank->save()) {
            if ($request->file('file')) {
                $file = $request->file('file');
                $spreadsheet = IOFactory::load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $row_limit = $sheet->getHighestDataRow();
                $row_range = range(2, $row_limit);
                $bstiChartData = [];
                foreach ($row_range as $row) {
                    $bstiChartData[] = [
                        'height' => $sheet->getCell('A' . $row)->getValue(),
                        'volume' => $sheet->getCell('B' . $row)->getValue(),
                        'tank_id' => $tank->id,
                    ];
                }
                if (count($bstiChartData) > 0) {
                    BstiChart::insert($bstiChartData);
                }
            }
            return response()->json(['status' => 200, 'message' => 'Successfully saved tank.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot saved tank.']);
    }
    public function list(Request $request)
    {
        $inputData = $request->all();
        $limit = isset($inputData['limit']) ? $inputData['limit'] : 10;
        $keyword = isset($inputData['keyword']) ? $inputData['keyword'] : '';
        $order_by = isset($inputData['order_by']) ? $inputData['order_by'] : 'id';
        $order_mode = isset($inputData['order_mode']) ? $inputData['order_mode'] : 'DESC';
        $count = Tank::count();
        $result = Tank::select('tank.id' ,'tank.tank_name', 'tank.height', 'tank.capacity', 'products.name as product_name')
            ->leftJoin('products', 'products.id', 'tank.product_id')
            ->where('tank.client_company_id', $inputData['session_user']['client_company_id'])
            ->with('last_reading', function($query) use ($count) {
                return $query->select('id', 'tank_id', 'height', 'water_height')->orderBy('id', 'DESC')->take($count);
            });
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('tank.tank_name', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
        foreach ($result as &$data) {
            $data['fuel_percent'] = 0;
            $data['water_percent'] = 0;
            if ($data['last_reading'] != null) {
                if ($data['capacity'] > 0 && $data['last_reading']['height'] > 0) {
                    $data['fuel_percent'] = number_format(($data['last_reading']['height'] / $data['capacity']) * 100, 2);
                }
                if ($data['capacity'] > 0 && $data['last_reading']['water_height'] > 0) {
                    $data['water_percent'] = number_format(($data['last_reading']['water_height'] / $data['capacity']) * 100, 2);
                }
            }
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function single(Request $request)
    {
        $inputData = $request->all();
        $result = Tank::select('id', 'tank_name', 'height', 'capacity', 'product_id')->find($inputData['id']);
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function update(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
            'tank_name' => 'required',
            'capacity' => 'required',
            'height' => 'required',
            'product_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $tank = Tank::find($inputData['id']);
        if ($tank == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find tank.']);
        }
        $tank->product_id = $inputData['product_id'];
        $tank->tank_name = $inputData['tank_name'];
        $tank->capacity = $inputData['capacity'];
        $tank->height = $inputData['height'];
        if ($tank->save()) {
            if ($request->file('file')) {
                BstiChart::where('tank_id', $inputData['id'])->delete();
                $file = $request->file('file');
                $spreadsheet = IOFactory::load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $row_limit = $sheet->getHighestDataRow();
                $row_range = range(2, $row_limit);
                $bstiChartData = [];
                foreach ($row_range as $row) {
                    $bstiChartData[] = [
                        'height' => $sheet->getCell('A' . $row)->getValue(),
                        'volume' => $sheet->getCell('B' . $row)->getValue(),
                        'tank_id' => $tank->id,
                    ];
                }
                if (count($bstiChartData) > 0) {
                    BstiChart::insert($bstiChartData);
                }
            }
            return response()->json(['status' => 200, 'message' => 'Successfully updated tank.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot updated tank.']);
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
        Tank::where('id', $inputData['id'])->delete();
        BstiChart::where('tank_id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted tank.']);
    }
    public function readingSave(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'tank_id' => 'required',
            'date' => 'required',
            'height' => 'required',
            'water_height' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $bstiChart = BstiChart::where('tank_id', $inputData['tank_id'])
            ->where('height', '=', floor($inputData['height']))
            ->first();
        $reading = new TankLog();
        $reading->tank_id = $inputData['tank_id'];
        $reading->date = $inputData['date'];
        $reading->height = $inputData['height'];
        $reading->water_height = $inputData['water_height'];
        $reading->type = $inputData['type'];
        $reading->volume = $bstiChart != null ? $bstiChart->volume : 0;
        $reading->client_company_id = $inputData['session_user']['client_company_id'];
        if ($reading->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully saved tank reading.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot saved tank reading.']);
    }
    public function readingList(Request $request)
    {
        $inputData = $request->all();
        $limit = isset($inputData['limit']) ? $inputData['limit'] : 10;
        $keyword = isset($inputData['keyword']) ? $inputData['keyword'] : '';
        $order_by = isset($inputData['order_by']) ? $inputData['order_by'] : 'tank_log.id';
        $order_mode = isset($inputData['order_mode']) ? $inputData['order_mode'] : 'DESC';
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
            $data['date'] = date('d/m/Y', strtotime($data['date']));
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function readingSingle(Request $request)
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
    public function readingUpdate(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
            'tank_id' => 'required',
            'date' => 'required',
            'height' => 'required',
            'water_height' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $reading = TankLog::find($inputData['id']);
        if ($reading == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find tank reading.']);
        }
        $bstiChart = BstiChart::where('tank_id', $inputData['tank_id'])
            ->where('height', '=', floor($inputData['height']))
            ->first();
        $reading->tank_id = $inputData['tank_id'];
        $reading->date = $inputData['date'];
        $reading->height = $inputData['height'];
        $reading->water_height = $inputData['water_height'];
        $reading->volume = $bstiChart != null ? $bstiChart->volume : 0;
        if ($reading->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully updated tank reading.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot updated tank reading.']);
    }
    public function readingDelete(Request $request)
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
    public function latestReading(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'tank_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $tank = Tank::find($inputData['tank_id']);
        if ($tank == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find tank.']);
        }
        if ($tank['product_id'] == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find product for this tank.']);
        }
        $product = Product::find($tank['product_id']);
        $stock = Stock::select('*')
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->where('date', date('Y-m-d'))
            ->where('module', 'product')->where('module_id', $product['id'])
            ->first();
        $start_reading = $product->opening_stock;
        if ($stock != null) {
            $start_reading = $stock['closing_stock'];
        } else {
            $previousStock = Stock::select('*')
                ->where('client_company_id', $inputData['session_user']['client_company_id'])
                ->where('module', 'product')->where('module_id', $product['id'])
                ->orderBy('id', 'DESC')
                ->first();
            if ($previousStock != null) {
                $start_reading = $previousStock->closing_stock;
            }
        }

        $result = TankLog::select('id', 'height')
            ->where('type', 'tank refill')
            ->where('tank_id', $inputData['tank_id'])
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->orderBy('id',  'DESC')
            ->limit(2)
            ->get()
            ->toArray();
        $end_height = isset($result[0]) ? $result[0]['height'] : 0;
        $bstiChart = BstiChart::where('tank_id', $inputData['tank_id'])
            ->where('height', '=', floor($end_height))
            ->first();
        $end_reading = $bstiChart != null ? $bstiChart['volume'] : 0;

        return response()->json([
            'status' => 200,
            'data' => [
                'start_reading' => $start_reading,
                'end_reading' => $end_reading,
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
                $reading = NozzleReading::select('reading')->where('client_company_id', $inputData['session_user']['client_company_id'])->where('nozzle_id', $nozzle['id'])->where('type', 'tank refill')->limit(2)->get()->toArray();
                $nozzle['start_reading'] = isset($reading[0]) ? $reading[0]['reading'] : 0;
                $nozzle['end_reading'] = isset($reading[1]) ? $reading[1]['reading'] : 0;
                $nozzle['sale'] = 0;
            }
        }
        return $dispensers;
    }
    public function refillSave(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
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
        $category = Category::where('category', AccountCategory::STOCK_IN_HAND)->where('client_company_id', $inputData['session_user']['client_company_id'])->first();
        $stockCategory = Category::where('parent_category', $category['id'])
            ->where('module', 'product')
            ->where('module_id', $tank['product_id'])
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->first();
        if ($stockCategory == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find accounts stock category.']);
        }
        $lossCategory = Category::where('category', AccountCategory::EVAPORATIVE)
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->first();
        if ($lossCategory == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find accounts loss category.']);
        }
        $tankRefill = new TankRefill();
        $tankRefill->date = $inputData['date'];
        $tankRefill->tank_id = $inputData['tank_id'];
        $tankRefill->pay_order_id = $inputData['pay_order_id'];
        $tankRefill->quantity = $inputData['quantity'];
        $tankRefill->start_reading = $inputData['start_reading'];
        $tankRefill->end_reading = $inputData['end_reading'] ?? 0;
        $tankRefill->dip_sale = $inputData['dip_sale'] ?? 0;
        $tankRefill->total_refill_volume = $inputData['total_refill_volume'] ?? 0;
        $tankRefill->net_profit = $inputData['net_profit'] ?? 0;
        $tankRefill->client_company_id = $inputData['session_user']['client_company_id'];
        if ($tankRefill->save()) {
            $unitPrice = $payOrder['amount'] / $payOrder['quantity'];
            $totalRefillAmount = $inputData['total_refill_volume'] * $unitPrice;
            $transactionData['linked_id'] = $payOrder['vendor_id'];
            $lossAmount = $payOrder['amount'] - $totalRefillAmount;
            $transactionData['transaction'] = [
                ['date' => $inputData['date'], 'account_id' => $stockCategory['id'], 'debit_amount' => 0, 'credit_amount' => $totalRefillAmount, 'module' => 'tank refill', 'module_id' => $tankRefill->id],
                ['date' => $inputData['date'], 'account_id' => $lossCategory['id'], 'debit_amount' => 0, 'credit_amount' => $lossAmount, 'module' => 'tank refill', 'module_id' => $tankRefill->id],
            ];
            TransactionController::saveTransaction($transactionData);
            $stockData = [
                'client_company_id' => $inputData['session_user']['client_company_id'],
                'product_id' => $tank['product_id'],
                'date' => $inputData['date'],
                'in_stock' => $inputData['total_refill_volume'],
                'opening_stock' => $inputData['start_reading']
            ];
            TransactionController::saveInStock($stockData);

            $productPrice = new ProductPrice();
            $productPrice->date = $inputData['date'];
            $productPrice->product_id = $tank['product_id'];
            $productPrice->quantity = $payOrder['quantity'];
            $productPrice->stock_quantity = $payOrder['quantity'];
            $productPrice->price = $payOrder['amount'];
            $productPrice->unit_price = $payOrder['amount'] / $payOrder['quantity'];
            $productPrice->module = 'tank refill';
            $productPrice->module_id = $tankRefill->id;
            $productPrice->client_company_id = $inputData['session_user']['client_company_id'];
            $productPrice->save();
            if (isset($inputData['dispensers'])) {
                foreach ($inputData['dispensers'] as $dispenser) {
                    foreach ($dispenser['nozzle'] as $nozzle) {

                        $tankRefillHistory = new TankRefillHistory();
                        $tankRefillHistory->tank_refill_id = $tankRefill->id;
                        $tankRefillHistory->nozzle_id = $nozzle['id'];
                        $tankRefillHistory->start_reading = $nozzle['start_reading'];
                        $tankRefillHistory->end_reading = $nozzle['end_reading'];
                        $tankRefillHistory->sale = $nozzle['sale'];
                        $tankRefillHistory->save();
                    }
                }
            }
            return response()->json(['status' => 200, 'message' => 'Successfully saved tank refill.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot saved tank refill.']);
    }
    public function refillList(Request $request)
    {
        $inputData = $request->all();
        $limit = isset($inputData['limit']) ? $inputData['limit'] : 10;
        $keyword = isset($inputData['keyword']) ? $inputData['keyword'] : '';
        $order_by = isset($inputData['order_by']) ? $inputData['order_by'] : 'tank_refill.id';
        $order_mode = isset($inputData['order_mode']) ? $inputData['order_mode'] : 'DESC';
        $result = TankRefill::select('tank_refill.*', 'tank.tank_name', 'pay_order.amount')
            ->leftJoin('tank', 'tank.id', 'tank_refill.tank_id')
            ->leftJoin('pay_order', 'pay_order.id', 'tank_refill.pay_order_id')
            ->where('tank_refill.client_company_id', $inputData['session_user']['client_company_id']);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('tank.tank_name', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
        foreach ($result as &$data) {
            $data['date'] = date('d/m/Y', strtotime($data['date']));
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
        TankRefill::where('id', $inputData['id'])->delete();
        TankRefillHistory::where('tank_refill_id', $inputData['id'])->delete();
        ProductPrice::where('module', 'tank refill')->where('module_id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted tank refill.']);
    }
}
