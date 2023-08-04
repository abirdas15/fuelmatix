<?php

namespace App\Http\Controllers;

use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Stock;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $sessionUser = SessionUser::getUser();
        foreach ($inputData['transaction'] as $transaction) {
            $newTransaction = new Transaction();
            $newTransaction->date = $transaction['date'];
            $newTransaction->description = $transaction['description'] ?? null;
            $newTransaction->account_id = $transaction['account_id'];
            $newTransaction->debit_amount = $transaction['debit_amount'] ?? 0;
            $newTransaction->credit_amount = $transaction['credit_amount'] ?? 0;
            $newTransaction->file = $transaction['file'] ?? null;
            $newTransaction->linked_id = $inputData['linked_id'];
            $newTransaction->added_by = Auth::user()->id;
            $newTransaction->module = $transaction['module'] ?? 'accounting';
            $newTransaction->module_id = $transaction['module_id'] ?? null;
            $newTransaction->client_company_id = $sessionUser['client_company_id'];
            if ($newTransaction->save()) {
                $id = $newTransaction->id;
                $newTransaction = new Transaction();
                $newTransaction->date = $transaction['date'];
                $newTransaction->description = null;
                $newTransaction->account_id = $inputData['linked_id'];
                $newTransaction->debit_amount = $transaction['credit_amount'] ?? 0;
                $newTransaction->credit_amount = $transaction['debit_amount'] ?? 0;
                $newTransaction->linked_id = $transaction['account_id'];
                $newTransaction->added_by = Auth::user()->id;
                $newTransaction->module = $transaction['module'] ?? 'accounting';
                $newTransaction->module_id = $transaction['module_id'] ?? null;
                $newTransaction->client_company_id = $sessionUser['client_company_id'];
                $newTransaction->parent_id = $id;
                $newTransaction->save();
                $previous = Transaction::find($id);
                $previous->parent_id = $newTransaction->id;
                $previous->save();
            }
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
    public static function updateTransaction($inputData)
    {
        $transaction = Transaction::find($inputData['id']);
        $transaction->debit_amount = $inputData['debit_amount'];
        $transaction->credit_amount = $inputData['credit_amount'];
        $transaction->description = $inputData['description'] ?? null;
        $transaction->file = $inputData['file'] ?? null;
        $credit_amount = $transaction->getOriginal('credit_amount') - $transaction->getAttribute('credit_amount');
        $debit_amount = $transaction->getOriginal('debit_amount') - $transaction->getAttribute('debit_amount');
        $transaction->save();
        $category = Category::with('parent')->where('id', $transaction->account_id)->first();
        $balance = 0;
        if ($category['type'] == 'expenses') {
            $balance = $debit_amount - $credit_amount;
        } else if ($category['type'] == 'income') {
            $balance = $credit_amount - $debit_amount;
        }  else if ($category['type'] == 'assets') {
            $balance = $debit_amount - $credit_amount;
        } else if ($category['type'] == 'liabilities') {
            $balance = $credit_amount - $debit_amount;
        } else if ($category['type'] == 'equity') {
            $balance = $credit_amount - $debit_amount;
        }
        self::updateCategoryBalance($category, $balance);

        $transaction = Transaction::where('parent_id', $inputData['id'])->first();
        $transaction->debit_amount = $inputData['credit_amount'];
        $transaction->credit_amount = $inputData['debit_amount'];
        $credit_amount = $transaction->getOriginal('credit_amount') - $transaction->getAttribute('credit_amount');
        $debit_amount = $transaction->getOriginal('debit_amount') - $transaction->getAttribute('debit_amount');
        $transaction->save();
        $category = Category::with('parent')->where('id', $transaction->account_id)->first();
        $balance = 0;
        if ($category['type'] == 'expenses') {
            $balance = $debit_amount - $credit_amount;
        } else if ($category['type'] == 'income') {
            $balance = $credit_amount - $debit_amount;
        }  else if ($category['type'] == 'assets') {
            $balance = $debit_amount - $credit_amount;
        } else if ($category['type'] == 'liabilities') {
            $balance = $credit_amount - $debit_amount;
        } else if ($category['type'] == 'equity') {
            $balance = $credit_amount - $debit_amount;
        }
        self::updateCategoryBalance($category, $balance);
        return true;
    }
    public static function deleteTransaction($id)
    {
        $transaction = Transaction::find($id);
        $category = Category::with('parent')->where('id', $transaction->account_id)->first();
        $balance = 0;
        if ($category['type'] == 'expenses') {
            $balance = $transaction['debit_amount'] - $transaction['credit_amount'];
        } else if ($category['type'] == 'income') {
            $balance = $transaction['credit_amount'] - $transaction['debit_amount'];
        }  else if ($category['type'] == 'assets') {
            $balance = $transaction['debit_amount'] - $transaction['credit_amount'];
        } else if ($category['type'] == 'liabilities') {
            $balance = $transaction['credit_amount'] - $transaction['debit_amount'];
        } else if ($category['type'] == 'equity') {
            $balance = $transaction['credit_amount'] - $transaction['debit_amount'];
        }
        self::updateCategoryBalance($category, $balance);
        Transaction::where('id', $transaction->id)->delete();
        $transaction = Transaction::where('parent_id', $id)->first();
        $category = Category::with('parent')->where('id', $transaction->account_id)->first();
        $balance = 0;
        if ($category['type'] == 'expenses') {
            $balance = $transaction['debit_amount'] - $transaction['credit_amount'];
        } else if ($category['type'] == 'income') {
            $balance = $transaction['credit_amount'] - $transaction['debit_amount'];
        }  else if ($category['type'] == 'assets') {
            $balance = $transaction['debit_amount'] - $transaction['credit_amount'];
        } else if ($category['type'] == 'liabilities') {
            $balance = $transaction['credit_amount'] - $transaction['debit_amount'];
        } else if ($category['type'] == 'equity') {
            $balance = $transaction['credit_amount'] - $transaction['debit_amount'];
        }
        self::updateCategoryBalance($category, $balance);
        Transaction::where('id', $transaction->id)->delete();
    }
    public static function saveStock($stockData)
    {
        $stockModel = new Stock();
        $stockModel->date = $stockData['date'];
        $stockModel->module = 'product';
        $stockModel->module_id = $stockData['product_id'];
        $stockModel->opening_stock = $stockData['opening_stock'];
        $stockModel->out_stock = $stockData['out_stock'];
        $stockModel->in_stock = $stockData['in_stock'];
        $stockModel->closing_stock = $stockModel->opening_stock + $stockModel->in_stock - $stockModel->out_stock;
        $stockModel->client_company_id = $stockData['client_company_id'];
        $stockModel->save();
    }
}
