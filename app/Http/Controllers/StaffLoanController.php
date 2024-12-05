<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
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
        $staffLoanReceivable = Category::select('id')
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->where('slug', strtolower(AccountCategory::STAFF_LOAN_RECEIVABLES))
            ->first();
        $result = Category::select(
            'id',
            'name'
        )
            ->where('parent_category', $staffLoanReceivable->id)
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
            'transactions.account_id as id',
            'transactions.date as date',
            DB::raw('SUM(transactions.debit_amount) as loan_amount'),
            'c1.name as staff_name',
            'c2.name as payment_method'
        )
            ->join('categories as c1', 'c1.id', '=', 'transactions.account_id')
            ->join('transactions as t1', 't1.linked_id', '=', 'transactions.id')
            ->join('categories as c2', 'c2.id', '=', 't1.account_id')
            ->with('staff_loan_payment')
            ->where('transactions.module', Module::STAFF_LOAN_RECEIVABLE)
            ->where('transactions.client_company_id', $sessionUser->client_company_id)
            ->groupBy('transactions.account_id')
            ->havingRaw('SUM(transactions.debit_amount) > 0')
            ->orderBy('transactions.id', 'DESC')
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
    public function single(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors'=> $validator->errors()
            ]);
        }
        $sessionUser = SessionUser::getUser();
        $result = Transaction::select(
            'transactions.id',
            'transactions.date as date',
            'transactions.account_id',
            'transactions.debit_amount',
            'transactions.credit_amount',
            'c1.name as payment_method'
        )
            ->join('transactions as t1', 't1.linked_id', '=', 'transactions.id')
            ->join('categories as c1', 'c1.id', '=', 't1.account_id')
            ->where('transactions.account_id', $request->input('id'))
            ->get()
            ->toArray();
        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($result as &$data) {
            $totalDebit += $data['debit_amount'];
            $totalCredit += $data['credit_amount'];
            $data['debit_amount'] = !empty($data['debit_amount']) ? number_format($data['debit_amount'], $sessionUser['currency_precision']) : '';
            $data['credit_amount'] = !empty($data['credit_amount']) ? number_format($data['credit_amount'], $sessionUser['currency_precision']) : '';
            $data['date'] = Helpers::formatDate($data['date'], FuelMatixDateTimeFormat::STANDARD_DATE);
        }
        return response()->json([
            'status' => 200,
            'data' => $result,
            'total' => [
                'debit_amount' => number_format($totalDebit, $sessionUser['currency_precision']),
                'credit_amount' => number_format($totalCredit, $sessionUser['currency_precision']),
                'due_amount' => number_format($totalDebit - $totalCredit, $sessionUser['currency_precision'])
            ]
        ]);
    }
}
