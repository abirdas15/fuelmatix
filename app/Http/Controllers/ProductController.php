<?php

namespace App\Http\Controllers;

use App\Models\Product;
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
                $q->orWhere('product_type.name', 'LIKE', '%'.$keyword.'%');
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
}
