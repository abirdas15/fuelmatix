<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\BalanceTransfer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BalanceTransferController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'from' => 'required',
            'to' => 'required',
            'amount' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $sessionUser = SessionUser::getUser();
        $balanceTransfer = new BalanceTransfer();
        $balanceTransfer->date = Carbon::now('UTC');
        $balanceTransfer->from_category_id = $requestData['from'];
        $balanceTransfer->to_category_id = $requestData['to'];
        $balanceTransfer->amount = $requestData['amount'];
        $balanceTransfer->remarks = $requestData['remarks'] ?? null;
        $balanceTransfer->client_company_id = $sessionUser['client_company_id'];
        if ($balanceTransfer->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully transfer balance']);
        }
        return response()->json(['status' => 500, 'message' => 'Cannot transfer balance']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $sessionUser = SessionUser::getUser();
        $requestData = $request->all();
        $limit = $requestData['limit'] ?? 10;
        $result = BalanceTransfer::select('balance_transfer.id', 'balance_transfer.date', 'balance_transfer.amount', 'balance_transfer.status', 'c1.category as from_category_name', 'c2.category as to_category_name')
            ->leftJoin('categories as c1', 'c1.id', '=', 'balance_transfer.from_category_id')
            ->leftJoin('categories as c2', 'c2.id', '=', 'balance_transfer.to_category_id')
            ->where('balance_transfer.client_company_id', $sessionUser['client_company_id']);
        $result = $result->orderBy('balance_transfer.id', 'DESC')
            ->paginate($limit);
        foreach ($result as &$data) {
            $data['date'] = Helpers::formatDate($data['date'], 'd/m/Y h:i A');
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
}
