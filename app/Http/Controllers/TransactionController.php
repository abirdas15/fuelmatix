<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function save(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'transaction' => 'required',
            'linked_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        foreach ($inputData['transaction'] as $transaction) {
            $newTransaction = new Transaction();
            $newTransaction->date = $transaction['date'];
            $newTransaction->descripton = $transaction['description'];
            $newTransaction->account_id = $transaction['account_id'];
            $newTransaction->debit_amount = $transaction['debit_amount'] ?? 0;
            $newTransaction->credit_amount = $transaction['credit_amount'] ?? 0;
            $newTransaction->linked_id = $inputData['linked_id'];
            $newTransaction->save();

            $category = Category::with('parent')->where('id', $newTransaction->account_id)->first();
            self::updateCategoryBalance($category, ($newTransaction['debit_amount'] - $newTransaction['credit_amount']));

            $newTransaction = new Transaction();
            $newTransaction->date = $transaction['date'];
            $newTransaction->descripton = null;
            $newTransaction->account_id = $inputData['linked_id'];
            $newTransaction->debit_amount = $transaction['credit_amount'] ?? 0;
            $newTransaction->credit_amount = $transaction['debit_amount'] ?? 0;
            $newTransaction->linked_id = $transaction['account_id'];
            $newTransaction->save();

            $category = Category::with('parent')->where('id', $newTransaction->account_id)->first();
            self::updateCategoryBalance($category, ($transaction['debit_amount'] - $transaction['credit_amount']));
        }
        return response()->json(['status' => 200, 'message' => 'Successfully save transaction.']);
    }
    public static function updateCategoryBalance($category, $balance)
    {
        $categoryObj = Category::find($category['id']);
        $categoryObj->balance = $categoryObj->balance + $balance;
        $categoryObj->save();
        if ($category['parent'] != null) {
            self::updateCategoryBalance($category['parent'], $balance);
        }
        return true;
    }
}
