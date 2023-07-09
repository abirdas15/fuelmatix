<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    public function save(Request $request)
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
        $data['transaction'] = [
            ['date' => date('Y-m-d'), 'description' => $inputData['remarks'], 'account_id' => $inputData['payment_id'], 'debit_amount' => $inputData['amount'], 'credit_amount' => 0, 'module' => 'expense', 'file' => $file_path]
        ];
        $data['linked_id'] = $inputData['category_id'];
        TransactionController::saveTransaction($data);
        return response()->json(['status' => 200, 'message' => 'Successfully save expense.']);
    }
    public function list(Request $request)
    {
        $inputData = $request->all();
        $limit = isset($inputData['limit']) ? $inputData['limit'] : 10;
        $keyword = isset($inputData['keyword']) ? $inputData['keyword'] : '';
        $order_by = isset($inputData['order_by']) ? $inputData['order_by'] : 'id';
        $order_mode = isset($inputData['order_mode']) ? $inputData['order_mode'] : 'DESC';
        $result = Transaction::select('transactions.id', 'transactions.date', 'transactions.debit_amount as amount',  'c.category as expense', 'c1.category as payment')
            ->leftJoin('categories as c', 'c.id', 'transactions.linked_id')
            ->leftJoin('categories as c1', 'c1.id', 'transactions.account_id')
            ->where('transactions.module', 'expense')
            ->where('transactions.debit_amount', '>', 0);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('c.category', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('c1.category', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
        foreach ($result as &$data) {
            $data['date'] = date('d/m/Y', strtotime($data['date']));
        }
        return response()->json(['status' => 200, 'data' => $result]);
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
        $result = Transaction::select('id', 'debit_amount as amount', 'linked_id as category_id', 'account_id as payment_id', 'file', 'description as remarks')
            ->where('id', $inputData['id'])
            ->first();
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function update(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
            'category_id' => 'required',
            'amount' => 'required',
            'payment_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $transaction = Transaction::find($inputData['id']);
        if ($transaction == null) {
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
        return response()->json(['status' => 200, 'message' => 'Successfully updated expense.']);
    }
    public function delete(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $transaction = Transaction::where('id', $inputData['id'])->first();
        if ($transaction != null) {
            TransactionController::deleteTransaction($inputData['id']);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully deleted expenses.']);
    }
}
