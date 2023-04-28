<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LedgerController extends Controller
{
    public static function get(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'start_date' => 'required',
            'end_date' => 'required',
            'account_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = Transaction::select('date', 'transactions.description', 'debit_amount', 'credit_amount', 'categories.type')
            ->whereBetween('date', [$inputData['start_date'], $inputData['end_date']])
            ->where('account_id', $inputData['account_id'])
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->orderBy('date', 'ASC')
            ->get()
            ->toArray();
        foreach ($result as $key => &$data) {
            $data['date'] = date('d/m/Y', strtotime($data['date']));
            if ($data['type'] == 'income') {
                if ($key == 0) {
                    $data['balance'] = $data['debit_amount'] - $data['credit_amount'];
                } else {
                    $data['balance'] = $result[$key - 1]['balance'] + ($data['debit_amount'] - $data['credit_amount']);
                }
            } else  if ($data['type'] == 'expenses') {
                if ($key == 0) {
                    $data['balance'] = $data['credit_amount'] - $data['debit_amount'];
                } else {
                    $data['balance'] = $result[$key - 1]['balance'] + ($data['credit_amount'] - $data['debit_amount']);
                }
            } else  if ($data['type'] == 'assets') {
                if ($key == 0) {
                    $data['balance'] = $data['credit_amount'] - $data['debit_amount'];
                } else {
                    $data['balance'] = $result[$key - 1]['balance'] + ($data['credit_amount'] - $data['debit_amount']);
                }
            } else  if ($data['type'] == 'liabilities' || $data['type'] == 'equity') {
                if ($key == 0) {
                    $data['balance'] = $data['debit_amount'] - $data['credit_amount'];
                } else {
                    $data['balance'] = $result[$key - 1]['balance'] + ($data['debit_amount'] - $data['credit_amount']);
                }
            }
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
}
