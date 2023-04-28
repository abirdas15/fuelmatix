<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TrailBalanceController extends Controller
{
    public function get(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'start_date' => 'required',
            'end_date' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = Transaction::select('categories.category', DB::raw('SUM(debit_amount) as debit_amount'), DB::raw('SUM(credit_amount) as credit_amount'))
            ->whereBetween('date', [$inputData['start_date'], $inputData['end_date']])
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->groupBy('account_id')
            ->get()
            ->toArray();
        $total_debit_amount = 0;
        $total_credit_amount = 0;
        foreach ($result as $data) {
            $total_debit_amount = $total_debit_amount + $data['debit_amount'];
            $total_credit_amount = $total_credit_amount + $data['credit_amount'];
        }
        return response()->json(['status' => 200, 'data' => $result, 'total_debit_amount' => $total_debit_amount, 'total_credit_amount' => $total_credit_amount]);
    }
}
