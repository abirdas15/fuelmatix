<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\Module;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\Sale;
use App\Models\SaleData;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SaleController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'products' => 'required|array',
            'products.*.product_id' => 'required',
            'products.*.quantity' => 'required',
            'products.*.price' => 'required',
            'products.*.subtotal' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $total_amount = array_sum(array_column($inputData['products'], 'subtotal'));
        $sale = new Sale();
        $sale->date = Carbon::now('UTC');
        $sale->invoice_number = Sale::getInvoiceNumber();
        $sale->total_amount = $total_amount;
        $sale->user_id = $inputData['session_user']['id'];
        $sale->customer_id = $inputData['customer_id'] ?? null;
        $sale->payment_method = $inputData['payment_method'] ?? null;
        $sale->client_company_id = $inputData['session_user']['client_company_id'];
        if ($sale->save()) {
            foreach ($inputData['products'] as $product) {
                $saleData = new SaleData();
                $saleData->sale_id = $sale->id;
                $saleData->product_id = $product['product_id'];
                $saleData->quantity = $product['quantity'];
                $saleData->price = $product['price'];
                $saleData->subtotal = $product['subtotal'];
                $saleData->save();
            }
            return response()->json(['status' => 200, 'message' => 'Successfully saved sale.', 'data' => $sale->id]);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot saved sale.']);
    }
    public function list(Request $request)
    {
        $inputData = $request->all();
        $limit = isset($inputData['limit']) ? $inputData['limit'] : 10;
        $keyword = isset($inputData['keyword']) ? $inputData['keyword'] : '';
        $order_by = isset($inputData['order_by']) ? $inputData['order_by'] : 'sale.id';
        $order_mode = isset($inputData['order_mode']) ? $inputData['order_mode'] : 'DESC';
        $result = Sale::select('sale.id', 'sale.invoice_number', 'sale.date', 'sale.total_amount', 'sale.payment_method', 'users.name as user_name')
            ->leftJoin('users', 'users.id', '=', 'sale.user_id')
            ->where('sale.client_company_id', $inputData['session_user']['client_company_id']);
        if (!empty($keyword)) {
            $result->where(function ($q) use ($keyword) {
                $q->where('invoice_number', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
        foreach ($result as &$data) {
            $data['date'] = date('d/m/Y', strtotime($data['date']));
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
        $result = Sale::find($inputData['id']);
        $result['date'] = Helpers::formatDate($result['date'], 'd/m/Y h:iA');
        $result['customer_name'] = 'Walk in Customer';
        if (!empty($result['customer_id'])) {
            $category = Category::where('id', $result['customer_id'])->first();
            if ($category instanceof Category) {
                $result['customer_name'] = $category->category;
            }
        }
        $result['products'] = SaleData::select('sale_data.*', 'products.name as product_name', 'product_types.name as type_name')
            ->leftJoin('products', 'products.id', '=', 'sale_data.product_id')
            ->leftJoin('product_types', 'products.type_id', '=', 'product_types.id')
            ->where('sale_data.sale_id', $inputData['id'])
            ->get()->toArray();
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function update(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
            'products' => 'required|array',
            'products.*.product_id' => 'required',
            'products.*.quantity' => 'required',
            'products.*.price' => 'required',
            'products.*.subtotal' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $sale = Sale::find($inputData['id']);
        if ($sale == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find sale.']);
        }
        $total_amount = array_sum(array_column($inputData['products'], 'subtotal'));
        $sale->total_amount = $total_amount;
        $sale->customer_id = $inputData['customer_id'] ?? null;
        $sale->payment_method = $inputData['payment_method'] ?? null;
        if ($sale->save()) {
            SaleData::where('sale_id', $inputData['id'])->delete();
            foreach ($inputData['products'] as $product) {
                $saleData = new SaleData();
                $saleData->sale_id = $sale->id;
                $saleData->product_id = $product['product_id'];
                $saleData->quantity = $product['quantity'];
                $saleData->price = $product['price'];
                $saleData->subtotal = $product['subtotal'];
                $saleData->save();
            }
            return response()->json(['status' => 200, 'message' => 'Successfully updated sale.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot updated sale.']);
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
        Sale::where('id', $inputData['id'])->delete();
        SaleData::where('sale_id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted sale.']);
    }
    public function getCompanySale(Request $request)
    {
        $requestData = $request->all();
        $sessionUser = SessionUser::getUser();
        $accountReceivable = Category::where('client_company_id', $sessionUser['client_company_id'])->where('category', AccountCategory::ACCOUNT_RECEIVABLE)->first();
        $limit = $requestData['limit'] ?? 10;
        $orderBy = $requestData['order_by'] ?? 'transactions.id';
        $orderMode = $requestData['order_mode'] ?? 'DESC';
        $keyword = $requestData['keyword'] ?? '';
        $result = Transaction::select('transactions.id', 'transactions.debit_amount as amount', 'transactions.date', 'transactions.description', 'categories.category as name')
            ->leftJoin('categories', 'categories.id', '=', 'transactions.linked_id')
            ->where('categories.parent_category', $accountReceivable->id)
            ->where('transactions.debit_amount', '>', 0);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('categories.category', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($orderBy, $orderMode)
            ->paginate($limit);
        $transactionId = [];
        foreach ($result as &$data) {
            $transactionId[] = $data['id'];
            $data['date'] = date('d/m/Y', strtotime($data['date']));
        }
        $invoice = Invoice::select('transaction_id', 'id')->whereIn('transaction_id', $transactionId)->get()->keyBy('transaction_id')->toArray();
        foreach ($result as &$data) {
            $data['is_invoice'] = isset($invoice[$data['id']]) ? true : false;
            $data['invoice_id'] = isset($invoice[$data['id']]) ? $invoice[$data['id']]['id'] : '';
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
}
