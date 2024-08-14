<?php

namespace App\Repository;

use App\Common\FuelMatixDateTimeFormat;
use App\Helpers\SessionUser;
use App\Models\Transaction;
use Carbon\Carbon;

class TransactionRepository
{
    /**
     * Save a series of transactions to the database.
     *
     * @param array $initialData An array of transaction data where each item represents a transaction.
     */
    public static function saveTransaction(array $initialData)
    {
        // Retrieve the currently authenticated user from the session.
        $sessionUser = SessionUser::getUser();

        // Initialize $linkedId to null for linking transactions.
        $linkedId = null;

        // Iterate over each transaction data item in the provided array.
        foreach ($initialData as $key => $data) {
            // Create a new Transaction instance.
            $transaction = new Transaction();

            // Set the transaction date.
            $transaction->date = $data['date'];

            // Set the transaction description, if provided.
            $transaction->description = $data['description'] ?? null;

            // Set the account ID for the transaction.
            $transaction->account_id = $data['account_id'];

            // Set the debit amount, defaulting to 0 if not provided.
            $transaction->debit_amount = $data['debit_amount'] ?? 0;

            // Set the credit amount, defaulting to 0 if not provided.
            $transaction->credit_amount = $data['credit_amount'] ?? 0;

            // Link this transaction to a previous one, if $linkedId is set.
            $transaction->linked_id = $linkedId;

            // Attach a file to the transaction, if provided.
            $transaction->file = $data['file'] ?? null;

            // Assign the module name, defaulting to 'accounting'.
            $transaction->module = $data['module'] ?? 'accounting';

            // Set the module ID, if provided.
            $transaction->module_id = $data['module_id'] ?? null;

            // Set the opening balance, defaulting to 0 if not provided.
            $transaction->opening_balance = $data['opening_balance'] ?? 0;

            // Set the client company ID from the session user data.
            $transaction->client_company_id = $sessionUser['client_company_id'];

            // Set the user ID from the session user data.
            $transaction->user_id = $sessionUser['id'];

            // Set the creation date and time, formatted for the database.
            $transaction->created_at = Carbon::parse($data['date'].' '.date('H:i:s'))
                ->format(FuelMatixDateTimeFormat::DATABASE_DATE_TIME);

            // Set the car ID, if provided.
            $transaction->car_id = $data['car_id'] ?? null;

            // Set the voucher number, if provided.
            $transaction->voucher_no = $data['voucher_no'] ?? null;

            // Set the quantity, defaulting to 0 if not provided.
            $transaction->quantity = $data['quantity'] ?? 0;

            // Save the transaction to the database.
            $transaction->save();

            // On the first iteration, set $linkedId to the ID of this transaction.
            if ($key == 0) {
                $linkedId = $transaction->id;
            }
        }
    }

}
