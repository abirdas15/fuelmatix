<?php

namespace App\Http\Controllers;

use App\Models\Dispenser;
use App\Models\Product;
use App\Models\ShiftSale;
use App\Models\ShiftSummary;
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
            'buying_price' => 'required',
            'unit' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $product = new Product();
        $product->name = $inputData['name'];
        $product->selling_price = $inputData['selling_price'];
        $product->type_id = $inputData['type_id'];
        $product->buying_price = $inputData['buying_price'];
        $product->unit = $inputData['unit'];
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
            ->leftJoin('product_types', 'product_types.id', '=', 'products.type_id');
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
            'selling_price' => 'required',
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
        $product->buying_price = $inputData['buying_price'];
        $product->unit = $inputData['unit'];
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
        $shitSale = ShiftSale::select('id', 'start_reading', 'end_reading', 'consumption', 'amount')
            ->where('product_id', $inputData['product_id'])
            ->where('status', 'start')
            ->orderBy('id', 'DESC')
            ->first();
        if ($shitSale == null) {
            $shitSale = [
                'id' => '',
                'start_reading' => 0,
                'end_reading' => 0,
                'consumption' => null,
                'amount' => 0,
                'status' => 'start'
            ];
        } else {
            $shitSale['status'] = 'end';
        }
        $shitSaleSummary = ShiftSummary::select('id', 'nozzle_id', 'start_reading', 'end_reading', 'consumption', 'amount')
            ->where('shift_sale_id', $shitSale['id'])
            ->get()
            ->keyBy('nozzle_id');
        $dispensers = Dispenser::select('id', 'dispenser_name')
            ->where('product_id', $inputData['product_id'])
            ->with(['nozzle' => function($q) {
                $q->select('nozzles.id', 'nozzles.dispenser_id', 'nozzles.name');
            }])
            ->get()
            ->toArray();
        foreach ($dispensers as &$dispenser) {
            foreach ($dispenser['nozzle'] as &$nozzle) {
                $nozzle['start_reading'] = isset($shitSaleSummary[$nozzle['id']]) ? $shitSaleSummary[$nozzle['id']]['start_reading'] : 0;
                $nozzle['end_reading'] = isset($shitSaleSummary[$nozzle['id']]) ? $shitSaleSummary[$nozzle['id']]['end_reading'] : 0;
                $nozzle['consumption'] = isset($shitSaleSummary[$nozzle['id']]) ? $shitSaleSummary[$nozzle['id']]['consumption'] : null;
                $nozzle['amount'] = isset($shitSaleSummary[$nozzle['id']]) ? $shitSaleSummary[$nozzle['id']]['amount'] : 0;
            }
        }
        return response()->json(['status' => 200, 'summary' => $dispensers, 'shift_sale' => $shitSale]);
    }
}
