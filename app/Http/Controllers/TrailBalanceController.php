<?php

namespace App\Http\Controllers;

use App\Helpers\SessionUser;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TrailBalanceController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * */
    public function get(Request $request): JsonResponse
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
        $result = Transaction::select(DB::raw("CONCAT(c1.name, ' (', c2.name , ')') as name"), DB::raw('SUM(debit_amount) as debit_amount'), DB::raw('SUM(credit_amount) as credit_amount'))
            ->whereBetween('date', [$inputData['start_date'], $inputData['end_date']])
            ->leftJoin('categories as c1', 'c1.id', '=', 'transactions.account_id')
            ->leftJoin('categories as c2', 'c2.id', '=', 'c1.parent_category')
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->groupBy('account_id')
            ->get()
            ->toArray();
        $total_debit_amount = 0;
        $total_credit_amount = 0;
        foreach ($result as $data) {
            $total_debit_amount = $total_debit_amount + $data['debit_amount'];
            $total_credit_amount = $total_credit_amount + $data['credit_amount'];
        }
        return response()->json([
            'status' => 200,
            'data' => $result,
            'total_debit_amount' => $total_debit_amount,
            'total_credit_amount' => $total_credit_amount
        ]);
    }
}
