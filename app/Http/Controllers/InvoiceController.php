<?php

namespace App\Http\Controllers;

use App\Common\Module;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\ClientCompany;
use App\Models\Invoice;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    public function generate(Request $request)
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $transaction = Transaction::find($requestData['id']);
        if (!$transaction instanceof Transaction) {
            return response()->json(['status' => 500, 'error' => 'Cannot find company sale.']);
        }
        $invoice = Invoice::where('transaction_id', $requestData['id'])->first();
        if ($invoice instanceof Invoice) {
            return response()->json(['status' => 500, 'error' => 'Invoice already have been generated.']);
        }
        $sessionUser = SessionUser::getUser();
        $invoice = new Invoice();
        $invoice->invoice_number = Invoice::getInvoiceNumber();
        $invoice->date = date('Y-m-d');
        $invoice->transaction_id = $transaction['id'];
        $invoice->category_id = $transaction['linked_id'];
        $invoice->amount = $transaction['credit_amount'] == 0 ? $transaction['debit_amount'] : $transaction['credit_amount'];
        $invoice->status = 'due';
        $invoice->client_company_id = $sessionUser['client_company_id'];
        if ($invoice->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully generated invoice.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot generated invoice.']);
    }
    public function list(Request $request)
    {
        $requestData = $request->all();
        $sessionUser = SessionUser::getUser();
        $limit = $requestData['limit'] ?? 10;
        $orderBy = $requestData['order_by'] ?? 'invoices.id';
        $orderMode = $requestData['order_mode'] ?? 'DESC';
        $keyword = $requestData['keyword'] ?? '';
        $result = Invoice::select('invoices.*', 'categories.category as name', DB::raw('(invoices.amount - invoices.paid_amount) as due_amount'))
            ->leftJoin('categories', 'categories.id', '=', 'invoices.category_id')
            ->where('invoices.client_company_id', $sessionUser['client_company_id']);
        $result = $result->orderBy($orderBy, $orderMode)
            ->paginate($limit);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('categories.category', 'LIKE', '%'.$keyword.'%');
            });
        }
        foreach ($result as $data) {
            $data['date'] = date('d/m/Y', strtotime($data['date']));
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
    public function delete(Request $request)
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
        Transaction::where('module', Module::INVOICE)->where('module_id', $requestData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted invoice.']);
    }
    public function single(Request $request)
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
        $category = Category::select('others', 'category as name')->find($invoice['category_id']);
        $others = json_decode($category['others']);
        $category['email'] = $others->email ?? '';
        $category['phone'] = $others->phone ?? '';
        $category['address'] = $others->address ?? '';
        $invoice['customer_company'] = $category;
        $invoice['company'] = $company;
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
        $category = Category::select('others', 'category as name')->find($invoice['category_id']);
        $others = json_decode($category['others']);
        $category['email'] = $others->email ?? '';
        $category['phone'] = $others->phone ?? '';
        $category['address'] = $others->address ?? '';
        $invoice['customer_company'] = $category;
        $invoice['company'] = $company;
        $pdf = Pdf::loadView('pdf.invoice', ['data' => $invoice]);
        return $pdf->stream();
    }
}
