<?php

namespace App\Services;

use App\Common\FuelMatixDateTimeFormat;
use App\Common\Module;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Transaction;
use App\Repository\TransactionRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class CompanyLoanService
{
    /**
     * @param array $data
     * @return bool
     */
    public function save(array $data): bool
    {
        DB::transaction((function () use ($data) {
            $transactionData = [
                ['date' => $data['date'], 'account_id' => $data['to_category_id'], 'description' => $data['remarks'], 'debit_amount' => $data['amount'], 'credit_amount' => 0, 'module' => Module::COMPANY_LOAN],
                ['date' => $data['date'], 'account_id' => $data['from_category_id'], 'description' => $data['remarks'], 'debit_amount' => 0, 'credit_amount' => $data['amount'], 'module' => Module::COMPANY_LOAN],
            ];
            // Save the transaction data
            TransactionRepository::saveTransaction($transactionData);
        }));
        return true;
    }

    /**
     * @param int $limit
     * @return mixed
     */
    public function list(int $limit)
    {
        $sessionUser = SessionUser::getUser();
        $result = Transaction::select(
            'transactions.account_id',
            'transactions.id',
            'transactions.date as date',
            DB::raw('SUM(transactions.credit_amount) as loan_amount'),
            'c1.name as company_name',
        )
            ->join('categories as c1', 'c1.id', '=', 'transactions.account_id')
            ->with('company_loan_payment')
            ->whereIn('transactions.module', [Module::COMPANY_LOAN])
            ->where('transactions.client_company_id', $sessionUser->client_company_id)
            ->groupBy('transactions.account_id')
            ->havingRaw('SUM(transactions.credit_amount) > 0')
            ->orderBy('transactions.id', 'DESC')
            ->paginate($limit);

        foreach ($result as &$data) {
            $totalPayment = 0;
            foreach ($data['company_loan_payment'] as $paymentData) {
                $totalPayment += $paymentData['credit_amount'];
            }
            $data['payment_amount'] = $totalPayment;
            $data['due_amount'] = $data['loan_amount'] - $data['payment_amount'];
            $data['date'] = Helpers::formatDate($data['date'], FuelMatixDateTimeFormat::STANDARD_DATE);
            $data['loan_amount_format'] = number_format($data['loan_amount'], $sessionUser['currency_precision']);
            $data['payment_amount_format'] = !empty($data['payment_amount']) ? number_format($data['payment_amount'], $sessionUser['currency_precision']) : '';
            $data['due_amount_format'] = !empty($data['due_amount']) ? number_format($data['due_amount'], $sessionUser['currency_precision']) : '';
        }
        return $result;
    }
     /**
      * @param array $data
     * @return bool
     * @throws Exception
     */
    public function payment(array $data): bool
    {
        $transaction = Transaction::where('id', $data['id'])->first();
        if (! $transaction instanceof Transaction) {
            throw new Exception('Transaction is not exist');
        }
        DB::transaction((function () use ($data, $transaction) {
            $transactionData = [
                ['date' => date('Y-m-d'), 'account_id' => $transaction->account_id, 'debit_amount' => $data['amount'], 'credit_amount' => 0, 'module' => Module::COMPANY_LOAN_PAYMENT, 'module_id' => $transaction->id],
                ['date' => date('Y-m-d'), 'account_id' => $data['payment_id'], 'debit_amount' => 0, 'credit_amount' => $data['amount'], 'module' => Module::COMPANY_LOAN_PAYMENT, 'module_id' => $transaction->id],
            ];
            TransactionRepository::saveTransaction($transactionData);
        }));
        return true;
    }
}
