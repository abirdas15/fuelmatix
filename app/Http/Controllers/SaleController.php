<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\FuelMatixDateTimeFormat;
use App\Common\FuelMatixStatus;
use App\Common\Module;
use App\Common\PaymentMethod;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Driver;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleData;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Voucher;
use App\Repository\DriverRepository;
use App\Repository\SaleRepository;
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
        $requestData = $request->all();
        if (!empty($requestData['advance_pay'])) {
            $validator = SaleRepository::validateAdvancePayment($requestData);
        } else {
            $validator = SaleRepository::validateSale($requestData);
        }
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $driverId = null;
        $voucher = null;
        $driverLiabilityId = null;
        $total_amount = array_sum(array_column($requestData['products'], 'subtotal'));
        $payment_category_id = $requestData['company_id'] ?? '';
        if ($requestData['payment_method'] == PaymentMethod::COMPANY) {
            $company = Category::find($requestData['company_id']);
            if (!$company instanceof Category) {
                return response()->json(['status' => 400, 'message' => 'Cannot find [company].']);
            }
            $driver = Driver::find($requestData['driver_sale']['driver_id']);
            if (!$driver instanceof Driver) {
                return response()->json(['status' => 400, 'message' => 'Cannot find [driver].']);
            }
            if (empty($requestData['advance_sale']) && !empty($requestData['voucher_number'])) {
                $voucher = Voucher::where('company_id', $requestData['company_id'])
                    ->where('status', 'pending')
                    ->where('validity', '>=', date('Y-m-d'))
                    ->where('voucher_number', $requestData['voucher_number'])
                    ->first();
                if (!$voucher instanceof Voucher) {
                    return response()->json(['status' => 500, 'errors' => ['voucher_number' => ['The voucher number is not valid.']]]);
                }
            }
            if (empty($requestData['voucher_number'])) {
                $payment_category_id = $driver['un_authorized_bill_id'];
            }
            if (!empty($requestData['advance_pay']) || !empty($requestData['advance_sale'])) {
                $driverLiability = Category::find($driver['driver_liability_id']);
                if (!$driverLiability instanceof Category) {
                    return response()->json(['status' => 400, 'message' => 'Cannot find [driver un revenue category].']);
                }
                $driverLiabilityId = $driverLiability['id'];
                if (!empty($requestData['advance_pay'])) {
                    $transactionData['linked_id'] = $requestData['company_id'];
                    $transactionData['transaction'] = [
                        ['date' => date('Y-m-d'), 'account_id' => $driverLiabilityId, 'debit_amount' => $requestData['advance_amount'], 'credit_amount' => 0, 'module' => Module::ADVANCE_PAYMENT],
                    ];
                    $response = TransactionController::saveTransaction($transactionData);
                    if (!$response) {
                        return response()->json(['status' => 400, 'message' => 'Cannot saved advance payment.']);
                    }
                    $voucher->status = 'done';
                    $voucher->save();
                    return response()->json(['status' => 200, 'message' => 'Successfully saved advance payment.']);
                }
                if (!empty($requestData['advance_sale'])) {
                    $driverAmount = DriverRepository::getDriverAmount($driverLiabilityId);
                    if ($total_amount > $driverAmount) {
                        return response()->json(['status' => 400, 'message' => 'Not enough driver amount.']);
                    }
                }
            }
            if (!empty($requestData['is_driver_sale'])) {
                $driverExpense = Category::find($driver['driver_expense_id']);
                if (!$driverExpense instanceof Category) {
                    return response()->json(['status' => 400, 'message' => 'Cannot find [driver expense category].']);
                }
                $driverId = $driverExpense['id'];
            }
        }
        if (!empty($requestData['voucher_number'])) {
            $sessionUser = SessionUser::getUser();
            if (!$sessionUser instanceof User) {
                return response()->json(['status' => 500, 'message' => 'Cannot find session [user].']);
            }
            $category = Category::where('id', $sessionUser['category_id'])->first();
            if (!$category instanceof Category) {
                return response()->json(['status' => 500, 'message' => 'You are not a cashier user.']);
            }
            if ($requestData['payment_method'] == PaymentMethod::CASH) {
                $payment_category_id = $category['id'];
            }
            $cash_in_hand_category_id = $category['id'];
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
        if (!$sale->save()) {
            return response()->json(['status' => 400, 'message' => 'Cannot save sale.']);
        }

        foreach ($requestData['products'] as $product) {
            $productModel = Product::where('id', $product['product_id'])->first();
            $buyingPrice = 0;
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
            $transactionData = [];
            $transactionData['linked_id'] = $payment_category_id;
            $transactionData['transaction'] = [
                ['date' => date('Y-m-d'), 'account_id' => $product['income_category_id'], 'debit_amount' => $product['subtotal'], 'credit_amount' => 0, 'module' => Module::POS_SALE, 'module_id' => $sale->id],
            ];
            TransactionController::saveTransaction($transactionData);

            $transactionData = [];
            $transactionData['linked_id'] = $product['expense_category_id'];
            $transactionData['transaction'] = [
                ['date' => date('Y-m-d'), 'account_id' => $product['stock_category_id'], 'debit_amount' => $buyingPrice, 'credit_amount' => 0, 'module' => Module::POS_SALE, 'module_id' => $sale->id],
            ];
            TransactionController::saveTransaction($transactionData);
        }
        if (!empty($requestData['is_driver_sale'])) {
            $transactionData = [];
            $transactionData['linked_id'] = $cash_in_hand_category_id;
            $transactionData['transaction'] = [
                ['date' => date('Y-m-d'), 'account_id' => $driverId, 'debit_amount' => 0, 'credit_amount' => $requestData['driver_sale']['price'], 'module' => Module::POS_SALE, 'module_id' => $sale->id],
            ];
            TransactionController::saveTransaction($transactionData);

            $transactionData = [];
            $transactionData['linked_id'] = $requestData['products'][0]['stock_category_id'];
            $transactionData['transaction'] = [
                ['date' => date('Y-m-d'), 'account_id' => $requestData['products'][0]['expense_category_id'], 'debit_amount' => $requestData['driver_sale']['buying_price'], 'credit_amount' => 0, 'module' => Module::POS_SALE, 'module_id' => $sale->id],
            ];
            TransactionController::saveTransaction($transactionData);
        }
        if (!empty($requestData['advance_sale'])) {
            $transactionData['linked_id'] = $requestData['company_id'];
            $transactionData['transaction'] = [
                ['date' => date('Y-m-d'), 'account_id' => $driverLiabilityId, 'debit_amount' => 0, 'credit_amount' => $total_amount, 'module' => Module::POS_SALE, 'module_id' => $sale->id],
            ];
            TransactionController::saveTransaction($transactionData);
        }
        if ($voucher instanceof Voucher) {
            $voucher->status = 'done';
            $voucher->save();
        }
        return response()->json(['status' => 200, 'message' => 'Successfully saved sale.', 'data' => $sale['id']]);
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
        $accountReceivable = Category::where('client_company_id', $sessionUser['client_company_id'])->where('slug', strtolower( AccountCategory::ACCOUNT_RECEIVABLE))->first();
        $limit = $requestData['limit'] ?? 10;
        $orderBy = $requestData['order_by'] ?? 'transactions.id';
        $orderMode = $requestData['order_mode'] ?? 'DESC';
        $keyword = $requestData['keyword'] ?? '';
        $result = Transaction::select('transactions.id', 'invoice_item.invoice_id',  DB::raw("SUM(transactions.debit_amount) as amount"), 'transactions.date', 'transactions.description', 'categories.name', 'transactions.module', 'transactions.module_id', 'transactions.linked_id as category_id')
            ->leftJoin('categories', 'categories.id', '=', 'transactions.linked_id')
            ->leftJoin('invoice_item', 'invoice_item.transaction_id', 'transactions.id')
            ->where('categories.parent_category', $accountReceivable->id)
            ->where('transactions.debit_amount', '>', 0)
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->groupBy(DB::raw('invoice_item.invoice_id  , CASE WHEN invoice_item.invoice_id IS NULL THEN transactions.module_id ELSE 0 END'));
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('categories.name', 'LIKE', '%'.$keyword.'%');
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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function unauthorizedBill(Request $request): JsonResponse
    {
        $sessionUser = SessionUser::getUser();
        $limit = $request['limit'] ?? 10;
        $keyword = $request['keyword'] ?? '';
        $driverId = Driver::select('un_authorized_bill_id')
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->get()
            ->pluck('un_authorized_bill_id')
            ->toArray();
        $result = Transaction::select('transactions.id','transactions.created_at', 'transactions.linked_id as driver_id', 'transactions.debit_amount as amount', 'c1.name as driver_name', 'c2.name as company_name', 'users.name as user_name')
            ->leftJoin('categories as c1', 'c1.id', '=', 'transactions.linked_id')
            ->leftJoin('categories as c2', 'c2.id', '=', 'c1.parent_category')
            ->leftJoin('users', 'users.id', '=', 'transactions.user_id')
            ->whereIn('transactions.linked_id', $driverId)
            ->where('transactions.client_company_id', $sessionUser['client_company_id']);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('c1.name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('c2.name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('users.name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('transactions.amount', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy('id', 'DESC')
            ->paginate($limit);
        foreach ($result as &$data) {
            $data['created_at'] = Helpers::formatDate($data['created_at'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME);
            $data['amount'] = number_format($data['amount'], 2);
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function unauthorizedBillTransfer(Request $request)
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required',
            'voucher_number' => 'required',
            'driver_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $driver = Category::where('id', $requestData['driver_id'])->first();
        if (!$driver instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [driver].']);
        }
        $voucher = Voucher::where('voucher_number', $requestData['voucher_number'])
            ->where('company_id', $driver['module_id'])
            ->where('status', FuelMatixStatus::PENDING)
            ->first();
        if (!$voucher instanceof Voucher) {
            return response()->json(['status' => 500, 'errors' => ['voucher_number' => ['The voucher number is not valid.']]]);
        }
        $transaction = Transaction::find($requestData['id']);
        if (!$transaction instanceof Transaction) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [transaction].']);
        }
        $transaction->linked_id = $driver['module_id'];
        $transaction->save();
        $transaction = Transaction::where('id', $transaction['parent_id'])->first();
        $transaction->account_id = $driver['module_id'];
        $transaction->save();
        $voucher->status = FuelMatixStatus::COMPLETE;
        $voucher->save();
        return response()->json(['status' => 200, 'message' => 'Successfully saved.']);
    }
}
