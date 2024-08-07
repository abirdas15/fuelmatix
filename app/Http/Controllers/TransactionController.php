<?php

namespace App\Http\Controllers;

use App\Common\FuelMatixDateTimeFormat;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Car;
use App\Models\Category;
use App\Models\ClientCompany;
use App\Models\Stock;
use App\Models\Transaction;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
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
    /**
     * @param array $inputData
     * @return bool
     */
    public static function saveTransaction(array $inputData): bool
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
            $newTransaction->module = $transaction['module'] ?? 'accounting';
            $newTransaction->module_id = $transaction['module_id'] ?? null;
            $newTransaction->opening_balance = $transaction['opening_balance'] ?? 0;
            $newTransaction->client_company_id = $sessionUser['client_company_id'];
            $newTransaction->user_id = $sessionUser['id'];
            $newTransaction->created_at = Carbon::parse($transaction['date'].' '.date('H:i:s'))->format(FuelMatixDateTimeFormat::DATABASE_DATE_TIME);
            $newTransaction->car_id = $transaction['car_id'] ?? null;
            $newTransaction->voucher_no = $transaction['voucher_no'] ?? null;
            $newTransaction->quantity = $transaction['quantity'] ?? 0;
            if ($newTransaction->save()) {
                $id = $newTransaction->id;
                $newTransaction = new Transaction();
                $newTransaction->date = $transaction['date'];
                $newTransaction->description = $transaction['description'] ?? null;
                $newTransaction->account_id = $inputData['linked_id'];
                $newTransaction->debit_amount = $transaction['credit_amount'] ?? 0;
                $newTransaction->credit_amount = $transaction['debit_amount'] ?? 0;
                $newTransaction->linked_id = $transaction['account_id'];
                $newTransaction->module = $transaction['module'] ?? 'accounting';
                $newTransaction->module_id = $transaction['module_id'] ?? null;
                $newTransaction->opening_balance = $transaction['opening_balance'] ?? 0;
                $newTransaction->client_company_id = $sessionUser['client_company_id'];
                $newTransaction->user_id = $sessionUser['id'];
                $newTransaction->created_at = Carbon::parse($transaction['date'].' '.date('H:i:s'))->format(FuelMatixDateTimeFormat::DATABASE_DATE_TIME);
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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function single(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $category = Category::find($inputData['id']);
        $result = Transaction::select('id', 'created_at', 'account_id', 'debit_amount', 'credit_amount', 'description')
            ->where('linked_id', $inputData['id'])
            ->orderBy('created_at', 'ASC')
            ->get()
            ->toArray();
        foreach ($result as $key => &$data) {
            $data['date'] = Helpers::formatDate($data['created_at'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME);
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
    /**
     * @param Request $request
     * @return JsonResponse
     * */
    public function split(Request $request)
    {
        $requestData = $request->all();
        $company = ClientCompany::find($requestData['session_user']['client_company_id']);
        $validator = Validator::make($requestData, [
            'id' => 'required',
            'data' => 'required|array',
            'data.*.voucher_number' => $company['voucher_check'] == 1 ? 'required' : 'nullable',
            'data.*.amount' => 'required'
        ],[
            'data.*.amount.required' => 'Amount is required.',
            'data.*.voucher_number.required' => 'Voucher is required.'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $transaction = Transaction::where('id', $requestData['id'])->first();
        if (!$transaction instanceof Transaction) {
            return response()->json(['status' => 500, 'message' => 'Cannot find transaction.']);
        }
        $totalAmount = array_sum(array_column($requestData['data'], 'amount'));
        $amountColumn = $transaction['debit_amount'] == 0 ? 'credit_amount' : 'debit_amount';
        if ($totalAmount != $transaction[$amountColumn]) {
            return response()->json(['status' => 300, 'error' => 'Your transaction amount are not same.']);
        }
        if ($company['voucher_check'] == 1) {
            $voucherError = [];
            foreach ($requestData['data'] as $key => $data) {
                $voucher = Voucher::where('voucher_number', $data['voucher_number'])->where('company_id', $transaction['linked_id'])->where('status', 'pending')->first();
                if (!$voucher instanceof Voucher) {
                    $voucherError['data.'. $key . '.voucher_number'][0] = 'Voucher is not valid.';
                }
            }
            if (count($voucherError) > 0) {
                return response()->json(['status' => 500, 'errors' => $voucherError]);
            }
        }
        $transactionData = [];
        foreach ($requestData['data'] as $data) {
            $carId = null;
            $car = Car::where('car_number', $data['description'])->first();
            if ($car instanceof Car) {
                $carId = $car->id;
            }
            $transactionData[] = [
                'date' => $transaction['date'],
                'account_id' => $transaction['account_id'],
                'description' => $data['description'] ?? '',
                'debit_amount' => $transaction['credit_amount'] == 0 ? $data['amount'] : 0,
                'credit_amount' => $transaction['debit_amount'] == 0 ? $data['amount'] : 0,
                'file' => $transaction['file'] ?? null,
                'linked_id' => $transaction['linked_id'],
                'user_id' => $transaction['user_id'],
                'module' => $transaction['module'],
                'module_id' => $transaction['module_id'],
                'client_company_id' => $transaction['client_company_id'],
                'parent_id' => $transaction['parent_id'],
                'car_id' => $carId,
                'voucher_no' => $data['voucher_number'],
                'created_at' => Carbon::parse($transaction['date']. date(' H:i:s'), SessionUser::TIMEZONE),
            ];
        }
        Transaction::insert($transactionData);
        Transaction::where('id', $requestData['id'])->delete();
        if ($company['voucher_check'] == 1) {
            foreach ($requestData['data'] as  $data) {
                $voucher = Voucher::where('voucher_number', $data['voucher_number'])->where('company_id', $transaction['linked_id'])->where('status', 'pending')->first();
                $voucher->status = 'done';
                $voucher->save();
            }
        }
        return response()->json(['status' => 200, 'message' => 'Successfully split transaction.']);
    }
}
