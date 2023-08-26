<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\Module;
use App\Common\PaymentMethod;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Sale;
use App\Models\SaleData;
use App\Models\Transaction;
use App\Models\User;
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
            'payment_method' => 'required',
            'products' => 'required|array',
            'payment_category_id' => 'required_unless:payment_method,cash',
            'products.*.shift_sale_id' => 'required',
            'products.*.product_id' => 'required',
            'products.*.income_category_id' => 'required',
            'products.*.stock_category_id' => 'required',
            'products.*.expense_category_id' => 'required',
            'products.*.quantity' => 'required',
            'products.*.price' => 'required',
            'products.*.subtotal' => 'required',
        ],[
            'products.*.shift_sale_id.required' => 'Shift sale is not started. Please start shift sale first.',
            'products.*.product_id.required' => 'The product field is required.',
            'products.*.income_category_id.required' => 'Product is not a income category. Please update product first.',
            'products.*.stock_category_id.required' => 'Product is not a stock category. Please update product first.',
            'products.*.expense_category_id.required' => 'Product is not a expense category. Please update product first.',
            'products.*.price.required' => 'The price field is required.',
            'products.*.subtotal.required' => 'The subtotal field is required.',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $requestData = $request->all();
        if ($requestData['payment_method'] == PaymentMethod::CARD) {
            return response()->json(['status' => 500, 'errors' => ['payment_method' => ['The payment category field is required.']]]);
        }
        $sessionUser = SessionUser::getUser();
        if (!$sessionUser instanceof User) {
            return response()->json(['status' => 500, 'message' => 'Cannot find session user.']);
        }
        $driverTipsCategory = null;
        $payment_category_id = $requestData['payment_category_id'] ?? '';
        $cash_in_hand_category_id = null;
        $total_amount = array_sum(array_column($requestData['products'], 'subtotal'));
        if ($requestData['payment_method'] == PaymentMethod::CASH  || $requestData['payment_method'] == PaymentMethod::COMPANY) {
            $category = Category::where('id', $sessionUser['category_id'])->first();
            if (!$category instanceof Category) {
                return response()->json(['status' => 500, 'message' => 'You are not a cashier user.']);
            }
            if ($requestData['payment_method'] == PaymentMethod::CASH) {
                $payment_category_id = $category['id'];
            }
            $cash_in_hand_category_id = $category['id'];
            if (!empty($requestData['driver_tip'])) {
                $driverTipsCategory = Category::where('client_company_id', $sessionUser['client_company_id'])->where('module', Module::DRIVER_TIPS)->where('module_id', $payment_category_id)->first();
                if (!$driverTipsCategory instanceof Category) {
                    return response()->json(['status' => 500, 'message' => 'Driver tips category is not created. Please update credit company.']);
                }
                $total_amount = $total_amount + $requestData['driver_tip'];
            }

        }
        if (empty($payment_category_id)) {
            return response()->json(['status' => 500, 'errors' => ['payment_category_id' => ['The payment category field is required.']]]);
        }

        $sale = new Sale();
        $sale->date = Carbon::now('UTC');
        $sale->invoice_number = Sale::getInvoiceNumber();
        $sale->total_amount = $total_amount;
        $sale->driver_tip = $requestData['driver_tip'] ?? 0;
        $sale->user_id = $requestData['session_user']['id'];
        $sale->customer_id = $requestData['payment_method'] == PaymentMethod::COMPANY ? $payment_category_id : null;
        $sale->payment_method = $requestData['payment_method'] ?? null;
        $sale->payment_category_id = $payment_category_id;
        $sale->client_company_id = $requestData['session_user']['client_company_id'];
        if ($sale->save()) {
            foreach ($requestData['products'] as $product) {
                $transactionData['linked_id'] = $payment_category_id;
                $buyingPrice = 0;
                $productModel = Product::where('id', $product['product_id'])->first();
                if (!empty($productModel['buying_price'])) {
                    $buyingPrice = $productModel['buying_price'] * $product['quantity'];
                }
                $saleData = new SaleData();
                $saleData->sale_id = $sale->id;
                $saleData->product_id = $product['product_id'];
                $saleData->quantity = $product['quantity'];
                $saleData->price = $product['price'];
                $saleData->subtotal = $product['subtotal'];
                $saleData->shift_sale_id = $product['shift_sale_id'];
                $saleData->save();
                $transactionData['transaction'] = [
                    ['date' => date('Y-m-d'), 'account_id' => $product['income_category_id'], 'debit_amount' => $product['subtotal'], 'credit_amount' => 0, 'module' => Module::POS_SALE, 'module_id' => $sale->id],
                ];
                TransactionController::saveTransaction($transactionData);
                $transactionData['linked_id'] = $product['expense_category_id'];
                $transactionData['transaction'] = [
                    ['date' => date('Y-m-d'), 'account_id' => $product['stock_category_id'], 'debit_amount' => $buyingPrice, 'credit_amount' => 0, 'module' => Module::POS_SALE, 'module_id' => $sale->id],
                ];
                TransactionController::saveTransaction($transactionData);
            }
            if (!empty($requestData['driver_tip']) && !empty($driverTipsCategory)) {
                $transactionData['linked_id'] = $cash_in_hand_category_id;
                $transactionData['transaction'] = [
                    ['date' => date('Y-m-d'), 'account_id' => $driverTipsCategory['id'], 'debit_amount' => 0, 'credit_amount' => $requestData['driver_tip'], 'module' => Module::POS_SALE, 'module_id' => $sale->id],
                ];
                TransactionController::saveTransaction($transactionData);
            }
            return response()->json(['status' => 200, 'message' => 'Successfully saved sale.', 'data' => $sale->id]);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot saved sale.']);
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
        $order_by = $inputData['order_by'] ?? 'sale.id';
        $order_mode = $inputData['order_mode'] ?? 'DESC';
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
        $result['payment_method'] = ucfirst($result['payment_method']);
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
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
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
        if (!$sale instanceof Sale) {
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
