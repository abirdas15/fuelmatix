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
        $expense = new Expense();
        $expense->date = Carbon::now();
        $expense->category_id = $inputData['category_id'];
        $expense->amount = $inputData['amount'];
        $expense->payment_id = $inputData['payment_id'];
        $expense->file = $file_path;
        $expense->remarks = $inputData['remarks'] ?? null;
        if ($expense->save()) {
            $data['transaction'] = [
                ['date' => date('Y-m-d'), 'description' => $inputData['remarks'], 'account_id' => $inputData['payment_id'], 'debit_amount' => $inputData['amount'], 'credit_amount' => 0, 'type' => 'expenses', 'type_id' => $expense->id]
            ];
            $data['linked_id'] = $inputData['category_id'];
            TransactionController::saveTransaction($data);
            return response()->json(['status' => 200, 'message' => 'Successfully saved expense.']);
        }
        return response()->json(['status' => 500, 'message' => 'Cannot saved expense.']);
    }
    public function list(Request $request)
    {
        $inputData = $request->all();
        $limit = isset($inputData['limit']) ? $inputData['limit'] : 10;
        $keyword = isset($inputData['keyword']) ? $inputData['keyword'] : '';
        $order_by = isset($inputData['order_by']) ? $inputData['order_by'] : 'expense.id';
        $order_mode = isset($inputData['order_mode']) ? $inputData['order_mode'] : 'DESC';
        $result = Expense::select('expense.id', 'expense.date','expense.amount', 'c.category as expense', 'c1.category as payment')
            ->leftJoin('categories as c', 'c.id', 'expense.category_id')
            ->leftJoin('categories as c1', 'c1.id', 'expense.payment_id');
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
        $result = Expense::find($inputData['id']);
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
        $expense = Expense::find($inputData['id']);
        if ($expense == null) {
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
        $expense->date = Carbon::now();
        $expense->category_id = $inputData['category_id'];
        $expense->amount = $inputData['amount'];
        $expense->payment_id = $inputData['payment_id'];
        $expense->file = $file_path;
        $expense->remarks = $inputData['remarks'] ?? null;
        if ($expense->save()) {
            $transaction = Transaction::where('type_id', $inputData['id'])->first();
            $data['id'] = $transaction->id;
            $data['debit_amount'] = $inputData['amount'];
            $data['credit_amount'] = 0;
            TransactionController::updateTransaction($data);
            return response()->json(['status' => 200, 'message' => 'Successfully updated expense.']);
        }
        return response()->json(['status' => 500, 'message' => 'Cannot updated expense.']);
    }
    public function delete(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            ''
        ]);
    }
}
