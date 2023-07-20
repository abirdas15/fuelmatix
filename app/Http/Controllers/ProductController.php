<?php

namespace App\Http\Controllers;

use App\Models\Dispenser;
use App\Models\NozzleReading;
use App\Models\Product;
use App\Models\ShiftSale;
use App\Models\ShiftSummary;
use App\Models\Tank;
use App\Models\TankLog;
use Illuminate\Http\Request;
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
            return response()->json(['status' => 200, 'message' => 'Successfully save product.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot save product.']);
    }
    public function list(Request $request)
    {
        $inputData = $request->all();
        $limit = isset($inputData['limit']) ? $inputData['limit'] : 10;
        $keyword = isset($inputData['keyword']) ? $inputData['keyword'] : '';
        $order_by = isset($inputData['order_by']) ? $inputData['order_by'] : 'id';
        $order_mode = isset($inputData['order_mode']) ? $inputData['order_mode'] : 'DESC';
        $result = Product::select('products.*', 'product_types.name as product_type')
            ->leftJoin('product_types', 'product_types.id', '=', 'products.type_id')
            ->where('client_company_id', $inputData['session_user']['client_company_id']);
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
            return response()->json(['status' => 200, 'message' => 'Successfully update product.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot update product.']);
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
        $shitSale = ShiftSale::select('id', 'end_reading', 'start_reading', 'consumption')
            ->where('product_id', $inputData['product_id'])
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->orderBy('id', 'DESC')
            ->first();
        $end_reading = 0;
        $start_reading = $product->opening_stock != null ? $product->opening_stock : 0;
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
        if ($shitSale != null) {
            $start_reading = $shitSale['end_reading'];
        }
        $consumption = $start_reading - $end_reading;
        $amount = $consumption * $product['selling_price'];
        $result = [
            'date' => date('Y-m-d'),
            'product_id' => $inputData['product_id'],
            'start_reading' => $start_reading,
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
                $reading = NozzleReading::select('reading')->where('client_company_id', $inputData['session_user']['client_company_id'])->where('nozzle_id', $nozzle['id'])->where('type', 'shift sell')->limit(2)->get()->toArray();
                $nozzle['start_reading'] = isset($reading[0]) ? $reading[0]['reading'] : 0;
                $nozzle['end_reading'] = isset($reading[1]) ? $reading[1]['reading'] : 0;
                $nozzle['consumption'] =  $nozzle['start_reading']  - $nozzle['end_reading'];
                $nozzle['amount'] = $nozzle['consumption'] * $product['selling_price'];
            }
        }
        $result['dispensers'] = $dispensers;

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
