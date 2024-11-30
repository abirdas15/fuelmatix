<?php

namespace App\Http\Controllers;

use App\Common\FuelMatixDateTimeFormat;
use App\Common\Module;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Repository\TransactionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StaffLoanController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function staffList(Request $request): JsonResponse
    {
        $sessionUser = SessionUser::getUser();
        $userIds = User::select('id')
            ->where('client_company_id', $sessionUser->client_company_id)
            ->where('loan_status', 1)
            ->pluck('id')
            ->toArray();
        $result = Category::select(
            'id',
            'name'
        )
            ->where('module', Module::STAFF_LOAN_RECEIVABLE)
            ->whereIn('module_id', $userIds)
            ->get()
            ->toArray();
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function loanSave(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'from_category_id' => 'required|integer|exists:categories,id',
            'to_category_id' => 'required|integer|exists:categories,id',
            'amount' => 'required|numeric|min:0',
        ],[
            'form_category_id.required' => 'Cash or Bank field is required.',
            'to_category_id.required' => 'Staff field is required.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }
        $fromCategory = Category::where('id', $request->input('from_category_id'))->first();
        $availableBalance = $fromCategory->checkAvailableBalance($request->input('amount'));
        if (!$availableBalance) {
            return response()->json([
                'status' => 300,
                'message' => 'Not enough balance in ' . $fromCategory['name'] . '.'
            ]);
        }
        DB::transaction((function () use ($request, $fromCategory, $availableBalance) {
            $transactionData = [
                ['date' => $request->input('date'), 'account_id' => $request->input(['to_category_id']), 'description' => $request->input('remarks'), 'debit_amount' => $request->input('amount'), 'credit_amount' => 0, 'module' => Module::STAFF_LOAN_RECEIVABLE],
                ['date' => $request->input('date'), 'account_id' => $request->input(['from_category_id']), 'description' => $request->input('remarks'), 'debit_amount' => 0, 'credit_amount' => $request->input('amount'), 'module' => Module::STAFF_LOAN_RECEIVABLE],
            ];
            // Save the transaction data
            TransactionRepository::saveTransaction($transactionData);
        }));
        return response()->json([
            'status' => 200,
            'message' => 'Loan has been saved successfully.'
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function loanList(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $sessionUser = SessionUser::getUser();
        $result = Transaction::select(
            'transactions.id',
            'transactions.date as date',
            'transactions.debit_amount as loan_amount',
            'c1.name as staff_name',
            'c2.name as payment_method',
        )
            ->join('categories as c1', 'c1.id', '=', 'transactions.account_id')
            ->join('transactions as t1', 't1.linked_id', '=', 'transactions.id')
            ->join('categories as c2', 'c2.id', '=', 't1.account_id')
            ->with('staff_loan_payment')
            ->where('transactions.module', Module::STAFF_LOAN_RECEIVABLE)
            ->where('transactions.client_company_id', $sessionUser->client_company_id)
            ->havingRaw('transactions.debit_amount > 0')
            ->orderBy('transactions.id', 'DESC')
            ->groupBy('transactions.id')
            ->paginate($limit);
        foreach ($result as &$data) {
            $totalPayment = 0;
            foreach ($data['staff_loan_payment'] as $paymentData) {
                $totalPayment += $paymentData['credit_amount'];
            }
            $data['payment_amount'] = $totalPayment;
            $data['due_amount'] = $data['loan_amount'] - $data['payment_amount'];
            $data['date'] = Helpers::formatDate($data['date'], FuelMatixDateTimeFormat::STANDARD_DATE);
            $data['loan_amount_format'] = number_format($data['loan_amount'], $sessionUser['currency_precision']);
            $data['payment_amount_format'] = !empty($data['payment_amount']) ? number_format($data['payment_amount'], $sessionUser['currency_precision']) : '';
            $data['due_amount_format'] = !empty($data['due_amount']) ? number_format($data['due_amount'], $sessionUser['currency_precision']) : '';
        }
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function loanPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:transactions,id',
            'payment_id' => 'required|integer|exists:categories,id',
            'amount' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }
        $transaction = Transaction::where('id', $request->input('id'))->first();
        DB::transaction((function () use ($request, $transaction) {
            $sessionUser = SessionUser::getUser();
            $transactionData = [
                ['date' => date('Y-m-d'), 'account_id' => $transaction->account_id, 'debit_amount' => 0, 'credit_amount' => $request->input('amount'), 'module' => Module::STAFF_LOAN_PAYMENT, 'module_id' => $transaction->id],
                ['date' => date('Y-m-d'), 'account_id' => $request->input(['payment_id']), 'debit_amount' => $request->input('amount'), 'credit_amount' => 0, 'module' => Module::STAFF_LOAN_PAYMENT, 'module_id' => $transaction->id],
            ];
            TransactionRepository::saveTransaction($transactionData);
        }));
        return response()->json([
            'status' => 200,
            'message' => 'Payment has been saved successfully.'
        ]);
    }
}
