<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReceivableController extends Controller
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
        $result = Transaction::select('categories.category', DB::raw('SUM(credit_amount - debit_amount) as balance'))
            ->whereBetween('date', [$inputData['start_date'], $inputData['end_date']])
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->where('account_category', 6)
            ->groupBy('account_id')
            ->get()
            ->toArray();
        $total = 0;
        foreach ($result as $data) {
            $total = $total + $data['balance'];
        }
        return response()->json(['status' => 200, 'data' => $result, 'total' => $total]);
    }
}
