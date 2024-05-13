<?php

namespace App\Http\Controllers;

use App\Common\FuelMatixStatus;
use App\Common\Module;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\PayOrder;
use App\Models\PayOrderData;
use App\Models\Tank;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PayOrderController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'bank_id' => 'nullable|integer',
            'vendor_id' => 'required|integer',
            'number' => 'required|string',
            'products' => 'required|array',
            'products.*.product_id' => 'required|integer',
            'products.*.quantity' => 'required|numeric',
            'products.*.unit_price' => 'required|numeric',
            'products.*.total' => 'required|numeric',
            'products.*.expense_category_id' => 'required|numeric',
        ],[
            'products.*.product_id.required' => 'The product field is required',
            'products.*.quantity.required' => 'The quantity field is required',
            'products.*.unit_price.required' => 'The unit price field is required',
            'products.*.total.required' => 'The total field is required',
            'products.*.expense_category_id.required' => 'Cannot find cost of good sold product.',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $vendorCategory = Category::find($request['vendor_id']);
        if (!$vendorCategory instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [vendor] category']);
        }
        if (!empty($request['bank_id'])) {
            $bankCategory = Category::find($request['bank_id']);
            if (!$bankCategory instanceof Category) {
                return response()->json(['status' => 400, 'message' => 'Cannot find [bank] category']);
            }
        }
        $sessionUser = SessionUser::getUser();
        $amount = array_sum(array_column($inputData['products'], 'total'));
        $transaction = Transaction::select(DB::raw('SUM(debit_amount) as debit_amount'), DB::raw('SUM(credit_amount) as credit_amount'))
            ->where('linked_id', $request['bank_id'])
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first();
        if (!empty($request['bank_id'])) {
            $bankAmount = $transaction['debit_amount'] ?? 0 - $transaction['credit_amount'] ?? 0;
            if ($bankAmount < $amount) {
                return response()->json(['status' => 500, 'errors' => ['bank_id' => ['Not enough balance in your bank.']]]);
            }
        }
        $account_id = $inputData['bank_id'];
        if (empty($inputData['bank_id'])) {
            $account_id = $inputData['vendor_id'];
        }
        $payOrder = new PayOrder();
        $payOrder->bank_id = $inputData['bank_id'] ?? null;
        $payOrder->vendor_id = $inputData['vendor_id'];
        $payOrder->amount = $amount;
        $payOrder->number = $inputData['number'];
        $payOrder->client_company_id =$sessionUser['client_company_id'];
        if (!$payOrder->save()) {
            return response()->json(['status' => 400, 'message' => 'Cannot saved [pay order].']);
        }
        $payOrderData = [];
        foreach ($inputData['products'] as $product) {
            $payOrderData[] = [
                'pay_order_id' => $payOrder['id'],
                'product_id' => $product['product_id'],
                'unit_price' => $product['unit_price'],
                'quantity' => $product['quantity'],
                'total' => $product['total']
            ];
            $transactionData['linked_id'] =  $product['stock_category_id'];
            $transactionData['transaction'] = [
                ['date' => date('Y-m-d'), 'account_id' => $request['vendor_id'], 'debit_amount' => $product['total'], 'credit_amount' => 0, 'module' => Module::PAY_ORDER, 'module_id' => $payOrder->id],
            ];
            TransactionController::saveTransaction($transactionData);
            if (!empty($inputData['bank_id'])) {
                $transactionData = [];
                $transactionData['linked_id'] = $request['vendor_id'];
                $transactionData['transaction'] = [
                    ['date' => date('Y-m-d'), 'account_id' => $request['bank_id'], 'debit_amount' => $product['total'], 'credit_amount' => 0, 'module' => Module::PAY_ORDER, 'module_id' => $payOrder->id],
                ];
                TransactionController::saveTransaction($transactionData);
            }
        }
        PayOrderData::insert($payOrderData);
        return response()->json(['status' => 200, 'message' => 'Successfully saved pay order.']);
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
        $order_by = $inputData['order_by'] ?? 'pay_order.id';
        $order_mode = $inputData['order_mode'] ?? 'DESC';
        $result = PayOrder::select('pay_order.id', 'pay_order.amount', 'pay_order.number', 'bank.name as bank_name', 'vendor.name as vendor_name')
            ->leftJoin('categories as bank', 'bank.id', '=', 'pay_order.bank_id')
            ->leftJoin('categories as vendor', 'vendor.id', '=', 'pay_order.vendor_id')
            ->where('pay_order.client_company_id', $inputData['session_user']['client_company_id']);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('bank.name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('vendor.name', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
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
            'id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = PayOrder::find($inputData['id']);
        $result['products'] = PayOrderData::select('pay_order_data.id', 'pay_order_data.product_id', 'pay_order_data.unit_price', 'pay_order_data.quantity', 'pay_order_data.total', 'products.name as product_name')
            ->leftJoin('products', 'products.id', '=', 'pay_order_data.product_id')
            ->where('pay_order_id', $inputData['id'])
            ->get()
            ->toArray();
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function update(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required|integer',
            'bank_id' => 'required|integer',
            'vendor_id' => 'required|integer',
            'amount' => 'required|numeric',
            'number' => 'required|string',
            'quantity' => 'required|numeric'
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
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        PayOrder::where('id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted pay order.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function latest(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $result = PayOrder::select('pay_order.id', DB::raw('CONCAT("PO-", pay_order.number) as number'))
            ->leftJoin('pay_order_data', 'pay_order_data.pay_order_id', '=', 'pay_order.id')
            ->where('pay_order.client_company_id', $inputData['session_user']['client_company_id'])
            ->where('pay_order_data.status', FuelMatixStatus::PENDING)
            ->orderBy('pay_order.id', 'DESC')
            ->groupBy('pay_order.id')
            ->limit(5)
            ->get()
            ->toArray();
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getQuantity(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'tank_id' => 'required|integer',
            'pay_order_id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $payOrder = PayOrder::find($requestData['pay_order_id']);
        if (!$payOrder instanceof PayOrder) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [pay order]']);
        }
        $tank = Tank::find($requestData['tank_id']);
        if (!$tank instanceof Tank) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [tank]']);
        }
        if (empty($tank['product_id'])) {
            return response()->json(['status' => 400, 'message' => 'No product assign for tank ['.$tank['tank_name'].']']);
        }
        $payOrderData = PayOrderData::select('quantity', 'unit_price', 'total')
            ->where('product_id', $tank['product_id'])
            ->where('pay_order_id', $requestData['pay_order_id'])
            ->where('status', FuelMatixStatus::PENDING)
            ->first();
        return response()->json(['status' => 200, 'data' => $payOrderData]);
    }
}
