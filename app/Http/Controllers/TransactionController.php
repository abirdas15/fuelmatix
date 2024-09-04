<?php

namespace App\Http\Controllers;

use App\Common\FuelMatixCategoryType;
use App\Common\FuelMatixDateTimeFormat;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Car;
use App\Models\Category;
use App\Models\ClientCompany;
use App\Models\Stock;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Repository\TransactionRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'transaction' => 'required|array',
            'transaction.*.account_id' => 'required|integer',
            'transaction.*.debit_amount' => 'nullable|numeric',
            'transaction.*.credit_amount' => 'nullable|numeric',
            'linked_id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }
        foreach ($request->input('transaction') as $transaction) {
            TransactionRepository::saveTransaction([
                ['date' => $transaction['date'], 'account_id' => $request->input('linked_id'), 'debit_amount' => !empty($transaction['debit_amount']) ? $transaction['debit_amount'] : 0, 'credit_amount' => !empty($transaction['credit_amount']) ? $transaction['credit_amount'] : 0, 'description' => $transaction['description']],
                ['date' => $transaction['date'], 'account_id' => $transaction['account_id'], 'debit_amount' => !empty($transaction['credit_amount']) ? $transaction['credit_amount'] : 0, 'credit_amount' => !empty($transaction['debit_amount']) ? $transaction['debit_amount'] : 0, 'description' => $transaction['description']],
            ]);
        }
        return response()->json([
            'status' => 200,
            'message' => 'Successfully save transaction.'
        ]);
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
     * Retrieve and return transaction details for a specific category, calculating balances.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function single(Request $request): JsonResponse
    {
        // Get all input data from the request.
        $inputData = $request->all();

        // Validate the input data to ensure 'id' is provided and is an integer.
        $validator = Validator::make($inputData, [
            'id' => 'required|integer'
        ]);

        // If validation fails, return a 500 status with the validation errors.
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }

        // Find the category based on the provided 'id'.
        $category = Category::find($inputData['id']);

        // Retrieve the transactions for the given category.
        // Transactions are selected with linked transaction data (via left join).
        // Results are ordered by creation date and grouped by transaction ID.
        $result = Transaction::select(
            'transactions.id',
            'transactions.created_at',
            'transactions.account_id',
            'transactions.debit_amount',
            'transactions.credit_amount',
            'transactions.description',
            DB::raw('COALESCE(t1.account_id, t2.account_id) as linked_id')
        )
            ->leftJoin('transactions as t1', 't1.linked_id', '=', 'transactions.id')
            ->leftJoin('transactions as t2', 't2.id', '=', 'transactions.linked_id')
            ->where('transactions.account_id', $inputData['id'])
            ->orderBy('transactions.created_at', 'ASC')
            ->groupBy('transactions.id')
            ->get()
            ->toArray();

        // Initialize balance to 0.
        $balance = 0;

        // Loop through each transaction and calculate the balance.
        foreach ($result as $key => &$data) {
            // Format the transaction date.
            $data['date'] = Helpers::formatDate($data['created_at'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME);

            // Calculate the difference between credit and debit amounts.
            $difference = $data['credit_amount'] - $data['debit_amount'];

            // Determine how to calculate the balance based on the category type.
            if ($category->type == FuelMatixCategoryType::INCOME) {
                $balance = $difference;
            } elseif ($category->type == FuelMatixCategoryType::EXPENSE || $category->type == FuelMatixCategoryType::ASSET) {
                $balance = -$difference;
            } elseif ($category->type == FuelMatixCategoryType::LIABILITIES || $category->type == FuelMatixCategoryType::EQUITY) {
                $balance = $difference;
            }

            // Set the balance for the first transaction, or add to the previous balance for subsequent transactions.
            if ($key == 0) {
                $data['balance'] = $balance;
            } else {
                $data['balance'] = $result[$key - 1]['balance'] + $balance;
            }
        }

        // Return the result with a 200 status.
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
                $voucher = Voucher::where('voucher_number', $data['voucher_number'])->where('company_id', $transaction['account_id'])->where('status', 'pending')->first();
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
                $voucher = Voucher::where('voucher_number', $data['voucher_number'])->where('company_id', $transaction['account_id'])->where('status', 'pending')->first();
                $voucher->status = 'done';
                $voucher->save();
            }
        }
        return response()->json(['status' => 200, 'message' => 'Successfully split transaction.']);
    }
}
