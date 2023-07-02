<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        self::saveTransaction($inputData);
        return response()->json(['status' => 200, 'message' => 'Successfully save transaction.']);
    }
    public static function saveTransaction($inputData)
    {
        foreach ($inputData['transaction'] as $transaction) {
            $newTransaction = new Transaction();
            $newTransaction->date = $transaction['date'];
            $newTransaction->description = $transaction['description'] ?? null;
            $newTransaction->account_id = $transaction['account_id'];
            $newTransaction->debit_amount = $transaction['debit_amount'] ?? 0;
            $newTransaction->credit_amount = $transaction['credit_amount'] ?? 0;
            $newTransaction->linked_id = $inputData['linked_id'];
            $newTransaction->added_by = Auth::user()->id;
            $newTransaction->type = $transaction['type'] ?? null;
            $newTransaction->type_id = $transaction['type_id'] ?? null;
            $newTransaction->save();

            $category = Category::with('parent')->where('id', $newTransaction->account_id)->first();

            $balance = 0;
            if ($category['type'] == 'expenses') {
                $balance = $newTransaction['credit_amount'] - $newTransaction['debit_amount'];
            } else if ($category['type'] == 'income') {
                $balance = $newTransaction['debit_amount'] - $newTransaction['credit_amount'];
            }  else if ($category['type'] == 'assets') {
                $balance = $newTransaction['credit_amount'] - $newTransaction['debit_amount'];
            } else if ($category['type'] == 'liabilities') {
                $balance = $newTransaction['debit_amount'] - $newTransaction['credit_amount'];
            } else if ($category['type'] == 'equity') {
                $balance = $newTransaction['debit_amount'] - $newTransaction['credit_amount'];
            }

            self::updateCategoryBalance($category, $balance);

            $newTransaction = new Transaction();
            $newTransaction->date = $transaction['date'];
            $newTransaction->description = null;
            $newTransaction->account_id = $inputData['linked_id'];
            $newTransaction->debit_amount = $transaction['credit_amount'] ?? 0;
            $newTransaction->credit_amount = $transaction['debit_amount'] ?? 0;
            $newTransaction->linked_id = $transaction['account_id'];
            $newTransaction->added_by = Auth::user()->id;
            $newTransaction->type = $transaction['type'] ?? null;
            $newTransaction->type_id = $transaction['type_id'] ?? null;
            $newTransaction->save();

            $category = Category::with('parent')->where('id', $newTransaction->account_id)->first();

            $balance = 0;
            if ($category['type'] == 'expenses') {
                $balance = $newTransaction['credit_amount'] - $newTransaction['debit_amount'];
            } else if ($category['type'] == 'income') {
                $balance = $newTransaction['debit_amount'] - $newTransaction['credit_amount'];
            }  else if ($category['type'] == 'assets') {
                $balance = $newTransaction['credit_amount'] - $newTransaction['debit_amount'];
            } else if ($category['type'] == 'liabilities') {
                $balance = $newTransaction['debit_amount'] - $newTransaction['credit_amount'];
            } else if ($category['type'] == 'equity') {
                $balance = $newTransaction['debit_amount'] - $newTransaction['credit_amount'];
            }
            self::updateCategoryBalance($category, $balance);
        }
        return true;
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
    public function single(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $category = Category::find($inputData['id']);
        $result = Transaction::select('id', 'date', 'account_id', 'debit_amount', 'credit_amount', 'description')
            ->where('linked_id', $inputData['id'])
            ->get()
            ->toArray();
        foreach ($result as $key => &$data) {
            if ($category->type == 'income') {
                if ($key == 0) {
                    $data['balance'] = $data['credit_amount'] - $data['debit_amount'];
                } else {
                    $data['balance'] = $result[$key - 1]['balance'] + ($data['credit_amount'] - $data['debit_amount']);
                }
            } else  if ($category->type == 'expenses') {
                if ($key == 0) {
                    $data['balance'] = $data['debit_amount'] - $data['credit_amount'];
                } else {
                    $data['balance'] = $result[$key - 1]['balance'] + ($data['debit_amount'] - $data['credit_amount']);
                }
            } else  if ($category->type == 'assets') {
                if ($key == 0) {
                    $data['balance'] = $data['debit_amount'] - $data['credit_amount'];
                } else {
                    $data['balance'] = $result[$key - 1]['balance'] + ($data['debit_amount'] - $data['credit_amount']);
                }
            } else  if ($category->type == 'liabilities' || $category->type == 'equity') {
                if ($key == 0) {
                    $data['balance'] = $data['credit_amount'] - $data['debit_amount'];
                } else {
                    $data['balance'] = $result[$key - 1]['balance'] + ($data['credit_amount'] - $data['debit_amount']);
                }
            }
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
}
