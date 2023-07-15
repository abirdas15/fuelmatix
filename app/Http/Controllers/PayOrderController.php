<?php

namespace App\Http\Controllers;

use App\Models\PayOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PayOrderController extends Controller
{
    public function save(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'bank_id' => 'required',
            'vendor_id' => 'required',
            'amount' => 'required',
            'number' => 'required',
            'quantity' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $payOrder = new PayOrder();
        $payOrder->bank_id = $inputData['bank_id'];
        $payOrder->vendor_id = $inputData['vendor_id'];
        $payOrder->amount = $inputData['amount'];
        $payOrder->number = $inputData['number'];
        $payOrder->quantity = $inputData['quantity'];
        $payOrder->client_company_id = $inputData['session_user']['client_company_id'];
        if ($payOrder->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully saved pay order.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot saved pay order.']);
    }
    public function list(Request $request)
    {
        $inputData = $request->all();
        $limit = isset($inputData['limit']) ? $inputData['limit'] : 10;
        $keyword = isset($inputData['keyword']) ? $inputData['keyword'] : '';
        $order_by = isset($inputData['order_by']) ? $inputData['order_by'] : 'pay_order.id';
        $order_mode = isset($inputData['order_mode']) ? $inputData['order_mode'] : 'DESC';
        $result = PayOrder::select('pay_order.id', 'pay_order.amount', 'pay_order.quantity', 'pay_order.number', 'bank.category as bank_name', 'vendor.category as vendor_name')
            ->leftJoin('categories as bank', 'bank.id', '=', 'pay_order.bank_id')
            ->leftJoin('categories as vendor', 'vendor.id', '=', 'pay_order.vendor_id')
            ->where('pay_order.client_company_id', $inputData['session_user']['client_company_id']);
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
        $result = PayOrder::find($inputData['id']);
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function update(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
            'bank_id' => 'required',
            'vendor_id' => 'required',
            'amount' => 'required',
            'number' => 'required',
            'quantity' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $payOrder = PayOrder::find($inputData['id']);
        if ($payOrder == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find pay order.']);
        }
        $payOrder->bank_id = $inputData['bank_id'];
        $payOrder->vendor_id = $inputData['vendor_id'];
        $payOrder->amount = $inputData['amount'];
        $payOrder->number = $inputData['number'];
        $payOrder->quantity = $inputData['quantity'];
        if ($payOrder->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully updated pay order.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot updated pay order.']);
    }
    public function delete(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        PayOrder::where('id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted pay order.']);
    }
    public function latest(Request $request)
    {
        $inputData = $request->all();
        $result = PayOrder::select('id', DB::raw('CONCAT("PO-", number) as number'))
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->get()
            ->toArray();
        return response()->json(['status' => 200, 'data' => $result]);
    }
}
