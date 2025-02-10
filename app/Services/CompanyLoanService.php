<?php

namespace App\Services;

use App\Common\Module;
use App\Repository\TransactionRepository;
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
                ['date' => $data['date'], 'account_id' => $data['from_category_id'], 'description' => $data['remarks'], 'debit_amount' => $data['amount'], 'credit_amount' => 0, 'module' => Module::COMPANY_LOAN],
                ['date' => $data['date'], 'account_id' => $data['to_category_id'], 'description' => $data['remarks'], 'debit_amount' => 0, 'credit_amount' => $data['amount'], 'module' => Module::COMPANY_LOAN],
            ];
            // Save the transaction data
            TransactionRepository::saveTransaction($transactionData);
        }));
        return true;
    }
}
