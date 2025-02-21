<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Helpers\SessionUser;
use App\Models\Category;
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
        $sessionUser = SessionUser::getUser();
        $receivableCategory = Category::where('client_company_id', $sessionUser['client_company_id'])->where('slug', strtolower(AccountCategory::ACCOUNT_RECEIVABLE))->first();
        $result = Transaction::select('categories.name as category', DB::raw('SUM(debit_amount - credit_amount) as balance'))
            ->whereBetween('date', [$inputData['start_date'], $inputData['end_date']])
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->whereJsonContains('categories.category_ids', $receivableCategory->id)
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->groupBy('account_id')
            ->get()
            ->toArray();
        $total = 0;
        foreach ($result as &$data) {
            $total += $data['balance'];
            $data['balance_format'] = number_format(abs($data['balance']), $sessionUser['currency_precision']);
        }
        return response()->json([
            'status' => 200,
            'data' => $result,
            'total_format' => number_format(abs($total), $sessionUser['currency_precision']),
            'total' => $total,
        ]);
    }
}
