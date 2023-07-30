<?php

namespace App\Http\Controllers;

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
            if ($newTransaction->save()) {
                $id = $newTransaction->id;
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
                $newTransaction->module = $transaction['module'] ?? 'accounting';
                $newTransaction->module_id = $transaction['module_id'] ?? null;
                $newTransaction->parent_id = $id;
                $newTransaction->save();
                $previous = Transaction::find($id);
                $previous->parent_id = $newTransaction->id;
                $previous->save();

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
    public static function saveInStock($stockData)
    {
        $stock = Stock::select('*', DB::raw('DATE(date) as date'))
            ->where('client_company_id', $stockData['client_company_id'])
            ->where('date', $stockData['date'])
            ->where('module', 'product')->where('module_id', $stockData['product_id'])
            ->first();
        if ($stock == null) {
            $previousStock = Stock::select('*', DB::raw('DATE(date) as date'))
                ->where('client_company_id', $stockData['client_company_id'])
                ->where('module', 'product')->where('module_id', $stockData['product_id'])
                ->orderBy('id', 'DESC')
                ->first();
            $stock = new Stock();
            if ($previousStock == null) {
                $stock->date = $stockData['date'];
                $stock->module = 'product';
                $stock->module_id = $stockData['product_id'];
                $stock->opening_stock = $stockData['opening_stock'];
                $stock->out_stock = 0;
                $stock->in_stock = $stockData['in_stock'];
                $stock->closing_stock = $stockData['opening_stock'] +  $stockData['in_stock'];
            } else {
                $stock->date = $stockData['date'];
                $stock->module = 'product';
                $stock->module_id = $stockData['product_id'];
                $stock->opening_stock = $previousStock['closing_stock'];
                $stock->in_stock = $stockData['in_stock'];
                $stock->closing_stock = $previousStock['closing_stock'] + $stockData['in_stock'];
            }
        } else {
            $stock->in_stock = $stock->in_stock + $stockData['in_stock'];
            $stock->closing_stock = $stock->opening_stock + $stock->in_stock - $stock->out_stock;
        }
        $stock->client_company_id = $stockData['client_company_id'];
        $stock->save();
    }
    public static function saveOutStock($stockData)
    {
        $stock = Stock::select('*', DB::raw('DATE(date) as date'))
            ->where('client_company_id', $stockData['client_company_id'])
            ->where('date', $stockData['date'])
            ->where('module', 'product')->where('module_id', $stockData['product_id'])
            ->first();
        if ($stock == null) {
            $previousStock = Stock::select('*', DB::raw('DATE(date) as date'))
                ->where('client_company_id', $stockData['client_company_id'])
                ->where('module', 'product')->where('module_id', $stockData['product_id'])
                ->orderBy('id', 'DESC')
                ->first();
            $stock = new Stock();
            if ($previousStock == null) {
                $stock->date = $stockData['date'];
                $stock->module = 'product';
                $stock->module_id = $stockData['product_id'];
                $stock->opening_stock = $stockData['opening_stock'];
                $stock->out_stock = $stockData['out_stock'];
                $stock->in_stock = 0;
                $stock->closing_stock =  $stock->opening_stock + $stock->in_stock -  $stockData['out_stock'];
            } else {
                $stock->date = $stockData['date'];
                $stock->module = 'product';
                $stock->module_id = $stockData['product_id'];
                $stock->opening_stock = $previousStock['closing_stock'];
                $stock->in_stock = 0;
                $stock->out_stock = $stockData['out_stock'];
                $stock->closing_stock = $previousStock['closing_stock'] - $stockData['out_stock'];
            }
        } else {
            $stock->out_stock = $stock->out_stock + $stockData['out_stock'];
            $stock->closing_stock = $stock->opening_stock + $stock->in_stock - $stock->out_stock;
        }
        $stock->client_company_id = $stockData['client_company_id'];
        $stock->save();
    }
}
