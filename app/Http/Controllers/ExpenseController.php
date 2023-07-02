<?php

namespace App\Http\Controllers;

use App\Models\Expense;
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
                ['date' => date('Y-m-d'), 'account_id' => $inputData['payment_id'], 'debit_amount' => $inputData['amount'], 'credit_amount' => 0, 'type' => 'expenses', 'type_id' => $expense->id]
            ];
            $data['linked_id'] = $inputData['category_id'];
            TransactionController::saveTransaction($data);
            return response()->json(['status' => 200, 'message' => 'Successfully save expense.']);
        }
        return response()->json(['status' => 500, 'message' => 'Cannot save expense.']);
    }
}
