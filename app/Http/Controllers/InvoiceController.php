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
use App\Models\Transaction;
use App\Repository\CategoryRepository;
use App\Repository\InvoiceRepository;
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
        $transaction = Transaction::select('id','module', 'module_id', 'description', 'linked_id as category_id', 'debit_amount as amount')
            ->whereIn('id', $requestData['ids'])
            ->get()
            ->toArray();
        $transactionArray = [];
        foreach ($transaction as $data) {
            $transactionArray[$data['category_id']][] = $data;
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
                    $shiftSale = ShiftSale::select('shift_sale.product_id', 'products.selling_price as price', 'shift_sale.date')
                        ->leftJoin('products', 'products.id', '=', 'shift_sale.product_id')
                        ->where('shift_sale.id', $row['module_id'])
                        ->first();
                    if ($shiftSale instanceof ShiftSale) {
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
            $invoice->date = Carbon::now();
            $invoice->category_id = $key;
            $invoice->amount = array_sum(array_column($invoiceItem, 'subtotal'));
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
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function payment(Request $request)
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required',
            'amount' => 'required',
            'payment_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $invoice = Invoice::find($requestData['id']);
        if (!$invoice instanceof Invoice) {
            return response()->json(['status' => 500, 'error' => 'Cannot find invoice.']);
        }
        $invoice->paid_amount = $invoice->paid_amount + $requestData['amount'];
        $invoice->status = $invoice->amount == $invoice->paid_amount ? 'paid' : 'partial paid';
        if ($invoice->save()) {
            $transaction['linked_id'] = $requestData['payment_id'];
            $transaction['transaction'] = [
                ['date' => date('Y-m-d'), 'account_id' => $invoice['category_id'], 'debit_amount' => $requestData['amount'], 'credit_amount' => 0, 'module' => Module::INVOICE, 'module_id' => $invoice->id]
            ];
            TransactionController::saveTransaction($transaction);
            return response()->json(['status' => 200, 'message' => 'Successfully saved payment.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot saved payment.']);
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
        $sessionUser = SessionUser::getUser();
        $company = null;
        if (!empty($sessionUser['client_company_id'])) {
            $company = ClientCompany::find($sessionUser['client_company_id']);
        }
        $category = Category::select('others', 'name')->find($invoice['category_id']);
        $others = json_decode($category['others']);
        $category['email'] = $others->email ?? '';
        $category['phone'] = $others->phone ?? '';
        $category['address'] = $others->address ?? '';
        $invoice['customer_company'] = $category;
        $invoice['company'] = $company;
        $invoice['amount'] = number_format($invoice['amount'], 2);
        $invoiceItem = InvoiceItem::select('invoice_item.id', 'invoice_item.date', 'invoice_item.car_number', 'invoice_item.quantity', 'invoice_item.price', 'invoice_item.subtotal', 'products.name as product_name')
            ->leftJoin('products', 'products.id', 'invoice_item.product_id')
            ->where('invoice_item.invoice_id', $requestData['id'])
            ->get()
            ->toArray();
        foreach ($invoiceItem as &$item) {
            $item['price'] = number_format($item['price'], 2);
            $item['subtotal'] = number_format($item['subtotal'], 2);
            $item['quantity'] = number_format($item['quantity'], 2);
            $item['date'] = !empty($item['date']) ? Helpers::formatDate($item['date'], FuelMatixDateTimeFormat::STANDARD_DATE) : '';
        }
        $invoice['invoice_item'] = $invoiceItem;
        return response()->json(['status' => 200, 'data' => $invoice]);
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
        $sessionUser = SessionUser::getUser();
        $company = null;
        if (!empty($sessionUser['client_company_id'])) {
            $company = ClientCompany::find($sessionUser['client_company_id']);
        }
        $category = Category::select('others', 'name')->find($invoice['category_id']);
        $others = json_decode($category['others']);
        $category['email'] = $others->email ?? '';
        $category['phone'] = $others->phone ?? '';
        $category['address'] = $others->address ?? '';
        $invoice['customer_company'] = $category;
        $invoice['company'] = $company;
        $invoice['amount'] = number_format($invoice['amount'], 2);
        $invoiceItem = InvoiceItem::select('invoice_item.id', 'invoice_item.date', 'invoice_item.car_number', 'invoice_item.quantity', 'invoice_item.price', 'invoice_item.subtotal', 'products.name as product_name')
            ->leftJoin('products', 'products.id', 'invoice_item.product_id')
            ->where('invoice_item.invoice_id', $requestData['id'])
            ->get()
            ->toArray();
        foreach ($invoiceItem as &$item) {
            $item['price'] = number_format($item['price'], 2);
            $item['subtotal'] = number_format($item['subtotal'], 2);
            $item['quantity'] = number_format($item['quantity'], 2);
            $item['date'] = !empty($item['date']) ? Helpers::formatDate($item['date'], FuelMatixDateTimeFormat::STANDARD_DATE) : '';
        }
        $invoice['invoice_item'] = $invoiceItem;
        $pdf = Pdf::loadView('pdf.invoice', ['data' => $invoice]);
        return $pdf->output();
    }
    /**
     * @param Request $request
     */
    public function globalPayment(Request $request)
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'amount' => 'required',
            'company_id' => 'required',
            'payment_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $companyCategory = Category::where('id', $requestData['company_id'])->first();
        if (!$companyCategory instanceof Category) {
            return response()->json(['status' => 500, 'message' => 'Cannot find [company].']);
        }
        $paymentCategory = Category::where('id', $requestData['payment_id'])->first();
        if (!$paymentCategory instanceof Category) {
            return response()->json(['status' => 500, 'message' => 'Cannot find [payment].']);
        }
        $sessionUser = SessionUser::getUser();
        $invoices = Invoice::where('client_company_id', $sessionUser['client_company_id'])
            ->where('status', '!=', 'paid')
            ->get();
        $amount = $requestData['amount'];
        $totalPaidAmount = 0;
        if (count($invoices) > 0) {
            foreach ($invoices as $invoice) {
                if ($invoice['amount'] < $amount) {
                    $paymentAmount = $invoice['amount'];
                    $amount = $amount - $paymentAmount;
                    $status = 'paid';
                } else {
                    $paymentAmount = $amount;
                    $status = 'partial paid';
                    $amount = 0;
                }
                $totalPaidAmount += $paymentAmount;
                $invoice->paid_amount = $paymentAmount;
                $invoice->status = $status;
                $invoice->save();
            }
        }
        if ($totalPaidAmount > 0) {
            $transaction['linked_id'] = $requestData['payment_id'];
            $transaction['transaction'] = [
                ['date' => date('Y-m-d'), 'account_id' => $requestData['company_id'], 'debit_amount' => $totalPaidAmount, 'credit_amount' => 0, 'module' => Module::INVOICE, 'module_id' => $invoice->id]
            ];
            TransactionController::saveTransaction($transaction);
        }
        if ($amount > 0) {
            $advancePaymentData = [
                'category' => $companyCategory['category'],
                'amount' => $amount,
                'company_id' => $requestData['company_id'],
                'payment_id' => $requestData['payment_id']
            ];
            InvoiceRepository::advancePayment($advancePaymentData);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully saved invoice payment.']);
    }
}
