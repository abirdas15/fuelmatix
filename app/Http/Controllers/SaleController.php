<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\Module;
use App\Common\PaymentMethod;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Sale;
use App\Models\SaleData;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'voucher_number' => 'required_if:payment_method,company|integer',
            'products.*.shift_sale_id' => 'required',
            'products.*.product_id' => 'required',
            'products.*.income_category_id' => 'required',
            'products.*.stock_category_id' => 'required',
            'products.*.expense_category_id' => 'required',
            'products.*.quantity' => 'required',
            'products.*.price' => 'required',
            'products.*.subtotal' => 'required',
        ],[
            'voucher_number.required_if' => 'The voucher number filed is required',
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
        $voucher = null;
        if ($requestData['payment_method'] == PaymentMethod::COMPANY) {
            $voucher = Voucher::where('company_id', $requestData['payment_category_id'])
                ->where('status', 'pending')
                ->where('validity', '>=', date('Y-m-d'))
                ->where('voucher_number', $requestData['voucher_number'])
                ->first();
            if (!$voucher instanceof Voucher) {
                return response()->json(['status' => 500, 'errors' => ['voucher_number' => ['The voucher number is not valid.']]]);
            }
        }
        $sessionUser = SessionUser::getUser();
        if (!$sessionUser instanceof User) {
            return response()->json(['status' => 500, 'message' => 'Cannot find session user.']);
        }
        $driverTipsCategory = null;
        $payment_category_id = $requestData['payment_category_id'] ?? '';
        $cash_in_hand_category_id = null;
        $total_amount = array_sum(array_column($requestData['products'], 'subtotal'));
        $driverTips = $requestData['driver_tip'] ?? 0;
        if ($requestData['payment_method'] == PaymentMethod::CASH  || $requestData['payment_method'] == PaymentMethod::COMPANY) {
            $category = Category::where('id', $sessionUser['category_id'])->first();
            if (!$category instanceof Category) {
                return response()->json(['status' => 500, 'message' => 'You are not a cashier user.']);
            }
            if ($requestData['payment_method'] == PaymentMethod::CASH) {
                $payment_category_id = $category['id'];
            }
            $cash_in_hand_category_id = $category['id'];
            if (!empty($requestData['driver_tip']) || !empty($requestData['driver_sale']['price'])) {
                $driverTipsCategory = Category::where('client_company_id', $sessionUser['client_company_id'])->where('module', Module::DRIVER_TIPS)->where('module_id', $payment_category_id)->first();
                if (!$driverTipsCategory instanceof Category) {
                    return response()->json(['status' => 500, 'message' => 'Driver tips category is not created. Please update credit company.']);
                }
            }

        }
        if (empty($payment_category_id)) {
            return response()->json(['status' => 500, 'errors' => ['payment_category_id' => ['The payment category field is required.']]]);
        }
        $total_amount = $total_amount + $driverTips;

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
            $totalPrice = array_sum(array_column($requestData['products'], 'subtotal'));
            $totalQuantity = array_sum(array_column($requestData['products'], 'quantity'));
            $avgUnitPrice = $totalPrice / $totalQuantity;
            $extraQuantity = $driverTips != 0 ? ($driverTips / $avgUnitPrice) / $totalQuantity  : 0;
            foreach ($requestData['products'] as $product) {
                $quantity = $product['quantity'];
                $subtotal = $product['subtotal'];
                if (!empty($driverTips)) {
                    $quantity = ($extraQuantity * $quantity) + $quantity;
                    $subtotal = $quantity * $product['price'];
                }
                $transactionData = [];
                $transactionData['linked_id'] = $payment_category_id;
                $buyingPrice = 0;
                $productModel = Product::where('id', $product['product_id'])->first();
                if (!empty($productModel['buying_price'])) {
                    $buyingPrice = $productModel['buying_price'] * $product['quantity'];
                }
                $saleData = new SaleData();
                $saleData->sale_id = $sale->id;
                $saleData->product_id = $product['product_id'];
                $saleData->quantity = $quantity;
                $saleData->price = $product['price'];
                $saleData->subtotal = $subtotal;
                $saleData->shift_sale_id = $product['shift_sale_id'];
                $saleData->save();
                $transactionData['transaction'] = [
                    ['date' => date('Y-m-d'), 'account_id' => $product['income_category_id'], 'debit_amount' => $subtotal, 'credit_amount' => 0, 'module' => Module::POS_SALE, 'module_id' => $sale->id],
                ];
                TransactionController::saveTransaction($transactionData);

                $transactionData = [];
                $transactionData['linked_id'] = $product['expense_category_id'];
                $transactionData['transaction'] = [
                    ['date' => date('Y-m-d'), 'account_id' => $product['stock_category_id'], 'debit_amount' => $buyingPrice, 'credit_amount' => 0, 'module' => Module::POS_SALE, 'module_id' => $sale->id],
                ];
                TransactionController::saveTransaction($transactionData);
            }
            if (!empty($requestData['driver_tip']) && !empty($driverTipsCategory)) {
                $transactionData = [];
                $transactionData['linked_id'] = $cash_in_hand_category_id;
                $transactionData['transaction'] = [
                    ['date' => date('Y-m-d'), 'account_id' => $driverTipsCategory['id'], 'debit_amount' => 0, 'credit_amount' => $requestData['driver_tip'], 'module' => Module::POS_SALE, 'module_id' => $sale->id],
                ];
                TransactionController::saveTransaction($transactionData);
            }
            if (!empty($requestData['driver_sale']['price']) && !empty($driverTipsCategory)) {
                $transactionData = [];
                $transactionData['linked_id'] = $cash_in_hand_category_id;
                $transactionData['transaction'] = [
                    ['date' => date('Y-m-d'), 'account_id' => $driverTipsCategory['id'], 'debit_amount' => 0, 'credit_amount' => $requestData['driver_sale']['price'], 'module' => Module::POS_SALE, 'module_id' => $sale->id],
                ];
                TransactionController::saveTransaction($transactionData);

                $transactionData = [];
                $transactionData['linked_id'] = $requestData['products'][0]['stock_category_id'];
                $transactionData['transaction'] = [
                    ['date' => date('Y-m-d'), 'account_id' => $requestData['products'][0]['expense_category_id'], 'debit_amount' => $requestData['driver_sale']['buying_price'], 'credit_amount' => 0, 'module' => Module::POS_SALE, 'module_id' => $sale->id],
                ];
                TransactionController::saveTransaction($transactionData);
            }
            if ($voucher instanceof Voucher) {
                $voucher->status = 'done';
                $voucher->save();
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
        $products = SaleData::select('sale_data.*', 'products.name as product_name', 'product_types.name as type_name')
            ->leftJoin('products', 'products.id', '=', 'sale_data.product_id')
            ->leftJoin('product_types', 'products.type_id', '=', 'product_types.id')
            ->where('sale_data.sale_id', $inputData['id'])
            ->get()
            ->toArray();
        foreach ($products as &$product) {
            $product['price'] = number_format($product['price'], 2);
            $product['quantity'] = number_format($product['quantity'], 2);
            $product['subtotal'] = number_format($product['subtotal'], 2);
        }
        $result['products'] = $products;
        $result['total_amount'] = number_format($result['total_amount'], 2);
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
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCompanySale(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $sessionUser = SessionUser::getUser();
        $accountReceivable = Category::where('client_company_id', $sessionUser['client_company_id'])->where('category', AccountCategory::ACCOUNT_RECEIVABLE)->first();
        $limit = $requestData['limit'] ?? 10;
        $orderBy = $requestData['order_by'] ?? 'transactions.id';
        $orderMode = $requestData['order_mode'] ?? 'DESC';
        $keyword = $requestData['keyword'] ?? '';
        $result = Transaction::select('transactions.id', 'invoice_item.invoice_id',  DB::raw("SUM(transactions.debit_amount) as amount"), 'transactions.date', 'transactions.description', 'categories.category as name', 'transactions.module', 'transactions.module_id', 'transactions.linked_id as category_id')
            ->leftJoin('categories', 'categories.id', '=', 'transactions.linked_id')
            ->leftJoin('invoice_item', 'invoice_item.transaction_id', 'transactions.id')
            ->where('categories.parent_category', $accountReceivable->id)
            ->where('transactions.debit_amount', '>', 0)
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->groupBy(DB::raw('invoice_item.invoice_id  , CASE WHEN invoice_item.invoice_id IS NULL THEN transactions.module_id ELSE 0 END'));
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('categories.category', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($orderBy, $orderMode)
            ->paginate($limit);
        foreach ($result as &$data) {
            $data['amount'] = number_format($data['amount'], 2);
            $data['date'] = date('d/m/Y', strtotime($data['date']));
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
}
