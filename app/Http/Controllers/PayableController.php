<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PayableController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * */
    public static function get(Request $request): JsonResponse
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
        $payableCategory = Category::where('client_company_id', $sessionUser['client_company_id'])->where('slug', strtolower(AccountCategory::ACCOUNT_PAYABLE))->first();
        $payableId = Category::select('id')->where('parent_category', $payableCategory->id)->get()->pluck('id')->toArray();
        $result = Transaction::select('categories.name as category', DB::raw('SUM(debit_amount - credit_amount) as balance'))
            ->whereBetween('date', [$inputData['start_date'], $inputData['end_date']])
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->whereIn('account_id', $payableId)
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
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
