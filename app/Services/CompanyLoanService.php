<?php

namespace App\Services;

use App\Common\Module;
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
