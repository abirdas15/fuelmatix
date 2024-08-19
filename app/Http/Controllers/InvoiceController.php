<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\FuelMatixDateTimeFormat;
use App\Common\Module;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\ClientCompany;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Sale;
use App\Models\SaleData;
use App\Models\ShiftSale;
use App\Models\ShiftTotal;
use App\Models\Transaction;
use App\Repository\CategoryRepository;
use App\Repository\InvoiceRepository;
use App\Repository\TransactionRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function generate(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'ids' => 'required|array',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $transaction = Transaction::select('id','module', 'account_id' ,'module_id', 'description', 'linked_id as category_id', 'debit_amount as amount')
            ->whereIn('id', $requestData['ids'])
            ->get()
            ->toArray();
        $transactionArray = [];
        foreach ($transaction as $data) {
            $transactionArray[$data['account_id']][] = $data;
        }
        $sessionUser = SessionUser::getUser();
        foreach ($transactionArray as $key =>  $data) {
            $invoiceItem = [];
            foreach ($data as $row) {
                if ($row['module'] == Module::POS_SALE) {
                    $posSale = SaleData::select('sale_data.product_id', 'sale_data.quantity', 'sale_data.price', 'sale_data.subtotal', 'sale.date')
                        ->leftJoin('sale', 'sale.id', 'sale_data.sale_id')
                        ->where('sale_data.sale_id', $row['module_id'])
                        ->get()
                        ->toArray();
                    foreach ($posSale as &$sale) {
                        $sale['transaction_id'] = $row['id'];
                        $sale['description'] = $row['description'] ?? null;
                        $invoiceItem[] = $sale;
                    }
                } else if ($row['module'] == Module::SHIFT_SALE) {
                    $shiftSale = ShiftTotal::select('shift_sale.product_id', 'products.selling_price as price', 'shift_sale.date')
                        ->leftJoin('shift_sale', 'shift_total.id', 'shift_sale.shift_id')
                        ->leftJoin('products', 'products.id', '=', 'shift_total.product_id')
                        ->where('shift_total.id', $row['module_id'])
                        ->first();
                    if ($shiftSale instanceof ShiftTotal) {
                        $shiftSale['quantity'] = $row['amount'] / $shiftSale['price'];
                        $shiftSale['subtotal'] = $row['amount'];
                        $shiftSale['transaction_id'] = $row['id'];
                        $shiftSale = $shiftSale->toArray();
                        $invoiceItem[] = $shiftSale;
                    }
                }
            }
            $invoice = new Invoice();
            $invoice->invoice_number = Invoice::getInvoiceNumber();
            $invoice->date = Carbon::now(SessionUser::TIMEZONE);
            $invoice->category_id = $key;
            $invoice->amount = array_sum(array_column($transaction, 'amount'));
            $invoice->status = 'due';
            $invoice->due_date = Carbon::now()->add('30D');
            $invoice->client_company_id = $sessionUser['client_company_id'];
            if ($invoice->save()) {
                foreach ($invoiceItem as $item) {
                    $invoiceItemObj = new InvoiceItem();
                    $invoiceItemObj->invoice_id = $invoice->id;
                    $invoiceItemObj->transaction_id = $item['transaction_id'];
                    $invoiceItemObj->car_number = $item['description'] ?? null;
                    $invoiceItemObj->product_id = $item['product_id'];
                    $invoiceItemObj->quantity = $item['quantity'];
                    $invoiceItemObj->price = $item['price'];
                    $invoiceItemObj->subtotal = $item['subtotal'];
                    $invoiceItemObj->date = Carbon::parse($item['date'])->format('Y-m-d');
                    $invoiceItemObj->save();
                }
            }
        }
        return response()->json(['status' => 200, 'message' => 'Successfully generated invoice.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $sessionUser = SessionUser::getUser();
        $limit = $requestData['limit'] ?? 10;
        $orderBy = $requestData['order_by'] ?? 'invoices.id';
        $orderMode = $requestData['order_mode'] ?? 'DESC';
        $keyword = $requestData['keyword'] ?? '';
        $result = Invoice::select('invoices.*', 'categories.name', DB::raw('(invoices.amount - invoices.paid_amount) as due_amount'))
            ->leftJoin('categories', 'categories.id', '=', 'invoices.category_id')
            ->where('invoices.client_company_id', $sessionUser['client_company_id']);
        $result = $result->orderBy($orderBy, $orderMode)
            ->paginate($limit);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('categories.category', 'LIKE', '%'.$keyword.'%');
            });
        }
        foreach ($result as &$data) {
            if (Carbon::parse($data['due_date'])->lessThan(Carbon::now())) {
                $data['status'] = 'over due';
            }
            $data['date'] = date('d/m/Y', strtotime($data['date']));
            $data['due_date'] = date('d/m/Y', strtotime($data['due_date']));
            $data['format_amount'] = !empty($data['amount']) ? number_format($data['amount'], 2) : '';
            $data['format_paid_amount'] = !empty($data['paid_amount']) ? number_format($data['paid_amount'], 2) : '';
            $data['format_due_amount'] = !empty($data['due_amount']) ? number_format($data['due_amount'], 2) : '';
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * Processes a payment for an invoice.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function payment(Request $request): JsonResponse
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'payment_id' => 'required|integer'
        ]);

        // If validation fails, return error response with validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Find the invoice by ID
        $invoice = Invoice::find($request->input('id'));
        if (!$invoice instanceof Invoice) {
            // If invoice not found, return error response
            return response()->json([
                'status' => 500,
                'error' => 'Cannot find invoice.'
            ]);
        }

        // Update the invoice with the new payment amount
        $invoice->paid_amount += $request->input('amount');
        $invoice->status = $invoice->amount == $invoice->paid_amount ? 'paid' : 'partial paid';

        // Save the updated invoice details
        if (!$invoice->save()) {
            return response()->json([
                'status' => 500,
                'error' => 'Cannot save payment.'
            ]);
        }

        $transactionData = [
            ['date' => date('Y-m-d'), 'account_id' => $request->input('payment_id'), 'debit_amount' => $request->input('amount'), 'credit_amount' => 0, 'module' => Module::INVOICE_PAYMENT, 'module_id' => $invoice['id']],
            ['date' => date('Y-m-d'), 'account_id' => $invoice['category_id'], 'debit_amount' => 0, 'credit_amount' => $request->input('amount'), 'module' => Module::INVOICE_PAYMENT, 'module_id' => $invoice['id']],
        ];
        TransactionRepository::saveTransaction($transactionData);

        // Return success response
        return response()->json([
            'status' => 200,
            'message' => 'Successfully saved payment.'
        ]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $invoice = Invoice::find($requestData['id']);
        if (!$invoice instanceof Invoice) {
            return response()->json(['status' => 500, 'error' => 'Cannot find invoice.']);
        }
        Invoice::where('id', $requestData['id'])->delete();
        Invoice::where('invoice_id', $requestData['id'])->delete();
        Transaction::where('module', Module::INVOICE)->where('module_id', $requestData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted invoice.']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function single(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $invoice = Invoice::find($requestData['id']);
        if (!$invoice instanceof Invoice) {
            return response()->json(['status' => 500, 'error' => 'Cannot find invoice.']);
        }
        $invoice = self::getSingleInvoice($invoice);
        return response()->json(['status' => 200, 'data' => $invoice]);
    }

    /**
     * @param Invoice $invoice
     * @return Invoice
     */
    public static function getSingleInvoice(Invoice $invoice): Invoice
    {
        $sessionUser = SessionUser::getUser();
        $company = null;
        if (!empty($sessionUser['client_company_id'])) {
            $company = ClientCompany::find($sessionUser['client_company_id']);
        }
        $category = Category::select('others', 'name')->find($invoice['category_id']);
        $others = $category != null ?? json_decode($category['others']);
        $category['email'] = $others->email ?? '';
        $category['phone'] = $others->phone ?? '';
        $category['address'] = $others->address ?? '';
        $invoice['customer_company'] = $category;
        $invoice['company'] = $company;
        $invoice['amount'] = number_format($invoice['amount'], 2);
        $invoiceItem = InvoiceItem::select('invoice_item.id', 'invoice_item.date', 'car.car_number', 'transactions.voucher_no', 'invoice_item.quantity', 'invoice_item.price', 'invoice_item.subtotal', 'products.name as product_name')
            ->leftJoin('transactions', 'transactions.id', 'invoice_item.transaction_id')
            ->leftJoin('car', 'car.id', 'transactions.car_id')
            ->leftJoin('products', 'products.id', 'invoice_item.product_id')
            ->where('invoice_item.invoice_id', $invoice['id'])
            ->get()
            ->toArray();
        foreach ($invoiceItem as &$item) {
            $item['price'] = number_format($item['price'], 2);
            $item['subtotal'] = number_format($item['subtotal'], 2);
            $item['quantity'] = number_format($item['quantity'], 2);
            $item['date'] = !empty($item['date']) ? Helpers::formatDate($item['date'], FuelMatixDateTimeFormat::STANDARD_DATE) : '';
        }
        $invoice['invoice_item'] = $invoiceItem;
        return $invoice;
    }
    public function downloadPdf(Request $request)
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $invoice = Invoice::find($requestData['id']);
        if (!$invoice instanceof Invoice) {
            return response()->json(['status' => 500, 'error' => 'Cannot find invoice.']);
        }
        $invoice = self::getSingleInvoice($invoice);
        $pdf = Pdf::loadView('pdf.invoice', ['data' => $invoice]);
        return $pdf->output();
    }

    /**
     * Processes a global payment for multiple invoices.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function globalPayment(Request $request): JsonResponse
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0', // 'amount' is required, must be numeric and non-negative
            'company_id' => 'required|integer', // 'company_id' is required and must be an integer
            'payment_id' => 'required|integer' // 'payment_id' is required and must be an integer
        ]);

        // If validation fails, return error response with validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Find the company category by ID
        $companyCategory = Category::where('id', $request->input('company_id'))->first();
        if (!$companyCategory instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [company].'
            ]);
        }

        // Find the payment category by ID
        $paymentCategory = Category::where('id', $request->input('payment_id'))->first();
        if (!$paymentCategory instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [payment].'
            ]);
        }

        // Get the currently logged-in user
        $sessionUser = SessionUser::getUser();

        // Fetch all unpaid invoices for the user's company
        $invoices = Invoice::where('client_company_id', $sessionUser['client_company_id'])
            ->where('status', '!=', 'paid')
            ->get();

        $amount = $request->input('amount');
        $totalPaidAmount = 0;

        // Process each invoice
        if (count($invoices) > 0) {
            // Process each invoice
            foreach ($invoices as $invoice) {
                if ($amount <= 0) {
                    break; // Stop processing if the payment amount is exhausted
                }

                $dueAmount = $invoice['amount'] - $invoice['paid_amount'];
                if ($dueAmount <= $amount) {
                    // Pay the full amount of the invoice
                    $paymentAmount = $dueAmount;
                    $amount -= $paymentAmount;
                    $status = 'paid';
                } else {
                    // Pay the remaining amount to this invoice
                    $paymentAmount = $amount;
                    $amount = 0;
                    $status = 'partial paid';
                }

                $totalPaidAmount += $paymentAmount;
                $invoice->paid_amount =  $invoice->paid_amount + $paymentAmount;
                $invoice->status = $status;
                $invoice->save();
            }
        }

        // If any amount was paid, record the transaction
        if ($totalPaidAmount > 0) {
            $transactionData = [
                ['date' => date('Y-m-d'), 'account_id' => $request->input('payment_id'), 'debit_amount' => $totalPaidAmount, 'credit_amount' => 0, 'module' => Module::INVOICE_PAYMENT, 'module_id' => $invoice['id']],
                ['date' => date('Y-m-d'), 'account_id' => $request->input('company_id'), 'debit_amount' => 0, 'credit_amount' => $totalPaidAmount, 'module' => Module::INVOICE_PAYMENT, 'module_id' => $invoice['id']],
            ];
            TransactionRepository::saveTransaction($transactionData);
        }

        // If there is still remaining amount after processing invoices, handle as advance payment
        if ($amount > 0) {
            $advancePaymentData = [
                'category' => $companyCategory['category'],
                'amount' => $amount,
                'company_id' => $request->input('company_id'),
                'payment_id' => $request->input('payment_id')
            ];
            InvoiceRepository::advancePayment($advancePaymentData);
        }

        // Return success response
        return response()->json([
            'status' => 200,
            'message' => 'Successfully saved invoice payment.'
        ]);
    }

    /**
     * Handles the retrieval of invoice payment transactions for the currently authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function invoicePayment(Request $request): JsonResponse
    {
        // Retrieve all input data from the request.
        $requestData = $request->all();

        // Set the search keyword if provided, otherwise use an empty string.
        $keyword = $requestData['keyword'] ?? '';

        // Set the pagination limit, defaulting to 10 if not provided.
        $limit = $requestData['limit'] ?? 10;

        // Get the current session user details.
        $sessionUser = SessionUser::getUser();

        // Build the query to retrieve transaction details related to invoice payments.
        $result = Transaction::select(
            'transactions.created_at',
            'transactions.debit_amount as amount',
            'invoices.invoice_number',
            'c1.name as payment_method',
            'c2.name as company_name'
        )
            ->leftJoin('invoices', 'invoices.id', 'transactions.module_id')
            ->leftJoin('categories as c1', 'c1.id', '=', 'transactions.linked_id')
            ->leftJoin('categories as c2', 'c2.id', '=', 'transactions.account_id')
            ->where('transactions.client_company_id', $sessionUser['client_company_id']) // Filter by client company ID
            ->where('transactions.module', Module::INVOICE_PAYMENT) // Filter by module type (invoice payment)
            ->where('debit_amount', '>', 0); // Only include transactions with a positive debit amount

        // Apply search filters if a keyword is provided.
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('invoices.invoice_number', 'LIKE', '%'.$keyword.'%'); // Search by invoice number
                $q->orWhere('c1.name', 'LIKE', '%'.$keyword.'%'); // Search by payment method name
                $q->orWhere('c2.name', 'LIKE', '%'.$keyword.'%'); // Search by company name
            });
        }

        // Order the results by transaction ID in descending order and apply pagination.
        $result = $result->orderBy('transactions.id', 'DESC')
            ->paginate($limit);

        // Format the created_at date and amount for each transaction in the result.
        foreach ($result as &$data) {
            $data['created_at'] = Helpers::formatDate($data['created_at'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME); // Format the date
            $data['amount'] = number_format($data['amount'], 2); // Format the amount to two decimal places
        }

        // Return the response with the status code and the paginated result data.
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }

}
