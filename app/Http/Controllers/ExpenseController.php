<?php

namespace App\Http\Controllers;

use App\Helpers\SessionUser;
use App\Models\Expense;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'category_id' => 'required',
            'amount' => 'required',
            'payment_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $file_path = null;
        if ($request->file('file')) {
            $file = $_FILES;
            $file = $file['file'];
            $destinationPath = public_path('uploads');
            $file_path = $request->file('file')->getClientOriginalName();
            move_uploaded_file($file["tmp_name"], $destinationPath.'/'.$file_path);
        }
        $sessionUser = SessionUser::getUser();
        $expense = new Expense();
        $expense->date = date('Y-m-d');
        $expense->category_id = $inputData['category_id'];
        $expense->amount = $inputData['amount'];
        $expense->payment_id = $inputData['payment_id'];
        $expense->remarks = $inputData['remarks'] ?? null;
        $expense->file = $file_path;
        $expense->client_company_id = $sessionUser['client_company_id'];
        if (!$expense->save()) {
            return response()->json(['status' => 500, 'message' => 'Cannot save expense.']);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully save expense.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $limit = $inputData['limit'] ?? 10;
        $keyword = $inputData['keyword'] ?? '';
        $sessionUser = SessionUser::getUser();

        $pendingExpense = Expense::select('expense.id', 'expense.date', 'expense.amount',  'c.category as expense', 'c1.category as payment', DB::raw("'pending' as status"))
            ->leftJoin('categories as c', 'c.id', 'expense.category_id')
            ->leftJoin('categories as c1', 'c1.id', 'expense.payment_id')
            ->where('expense.client_company_id', $sessionUser['client_company_id']);

        $result = Transaction::select('transactions.id', 'transactions.date', 'transactions.debit_amount as amount',  'c.category as expense', 'c1.category as payment', DB::raw("'approve' as status"))
            ->leftJoin('categories as c', 'c.id', 'transactions.linked_id')
            ->leftJoin('categories as c1', 'c1.id', 'transactions.account_id')
            ->where('transactions.module', 'expense')
            ->where('transactions.debit_amount', '>', 0)
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->union($pendingExpense);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('c.category', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('c1.category', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy('status', 'DESC')
            ->paginate($limit);
        foreach ($result as &$data) {
            $data['date'] = date('d/m/Y', strtotime($data['date']));
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function single(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
            'status' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        if ($inputData['status'] == 'pending') {
            $result = Expense::select('id', 'category_id', 'payment_id', 'amount', 'file', 'remarks')
                ->where('id', $inputData['id'])
                ->first();
        } else {
            $result = Transaction::select('id', 'debit_amount as amount', 'linked_id as category_id', 'account_id as payment_id', 'file', 'description as remarks')
                ->where('id', $inputData['id'])
                ->first();
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
            'category_id' => 'required',
            'amount' => 'required',
            'payment_id' => 'required',
            'status' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        if ($inputData['status'] == 'pending') {
            $expense = Expense::find($inputData['id']);
            if (!$expense instanceof Expense) {
                return response()->json(['status' => 500, 'error' => 'Cannot find expense.']);
            }
            $file_path = $expense->file;
            if ($request->file('file')) {
                $file = $_FILES;
                $file = $file['file'];
                $destinationPath = public_path('uploads');
                $file_path = $request->file('file')->getClientOriginalName();
                move_uploaded_file($file["tmp_name"], $destinationPath.'/'.$file_path);
            }
            $expense->category_id = $inputData['category_id'];
            $expense->amount = $inputData['amount'];
            $expense->payment_id = $inputData['payment_id'];
            $expense->remarks = $inputData['remarks'] ?? null;
            $expense->file = $file_path;
            if (!$expense->save()) {
                return response()->json(['status' => 500, 'error' => 'Cannot save expense.']);
            }
        } else {
            $transaction = Transaction::find($inputData['id']);
            if (!$transaction instanceof Transaction) {
                return response()->json(['status' => 500, 'error' => 'Cannot find expense.']);
            }
            $file_path = $transaction->file;
            if ($request->file('file')) {
                $file = $_FILES;
                $file = $file['file'];
                $destinationPath = public_path('uploads');
                $file_path = $request->file('file')->getClientOriginalName();
                move_uploaded_file($file["tmp_name"], $destinationPath.'/'.$file_path);
            }
            $data['id'] = $transaction->id;
            $data['debit_amount'] = $inputData['amount'];
            $data['credit_amount'] = 0;
            $data['description'] = $inputData['remarks'];
            $data['file'] = $file_path;
            TransactionController::updateTransaction($data);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully updated expense.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
            'status' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        if ($inputData['status'] == 'pending') {
            Expense::where('id', $inputData['id'])->delete();
        } else {
            $transaction = Transaction::where('id', $inputData['id'])->first();
            if ($transaction instanceof Transaction) {
                TransactionController::deleteTransaction($inputData['id']);
            }
        }
        return response()->json(['status' => 200, 'message' => 'Successfully deleted expenses.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function approve(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $expense = Expense::find($inputData['id']);
        if (!$expense instanceof Expense) {
            return response()->json(['status' => 500, 'error' => 'Cannot find expense.']);
        }
        $data['transaction'] = [
            ['date' => date('Y-m-d'), 'description' => $expense['remarks'], 'account_id' => $expense['payment_id'], 'debit_amount' => $expense['amount'], 'credit_amount' => 0, 'module' => 'expense', 'file' => $expense['file']]
        ];
        $data['linked_id'] = $expense['category_id'];
        TransactionController::saveTransaction($data);
        Expense::where('id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully approve expense.']);
    }
}
