<?php

namespace App\Http\Controllers;

use App\Common\FuelMatixDateTimeFormat;
use App\Common\FuelMatixStatus;
use App\Common\Module;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\BalanceTransfer;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            'from_category_id' => 'required',
            'to_category_id' => 'required',
            'amount' => 'required',
            'date' => 'required|date'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $sessionUser = SessionUser::getUser();

        $category = Category::find($requestData['from_category_id']);
        if (!$category instanceof Category) {
            return response()->json(['status' => 400, 'message' => "Cannot find asset."]);
        }

        $transaction = Transaction::select(DB::raw('SUM(debit_amount) as debit_amount'), DB::raw('SUM(credit_amount) as credit_amount'))
            ->where('linked_id', $requestData['from_category_id'])
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first();
        $assetAmount = $transaction['debit_amount'] ?? 0 - $transaction['credit_amount'] ?? 0;
        if ($assetAmount < $request['amount']) {
            return response()->json(['status' => 300, 'message' => 'Not enough balance in '.$category['name'].'.']);
        }

        $balanceTransfer = new BalanceTransfer();
        $balanceTransfer->date = $requestData['date'];
        $balanceTransfer->from_category_id = $requestData['from_category_id'];
        $balanceTransfer->to_category_id = $requestData['to_category_id'];
        $balanceTransfer->amount = $requestData['amount'];
        $balanceTransfer->remarks = $requestData['remarks'] ?? null;
        $balanceTransfer->client_company_id = $sessionUser['client_company_id'];
        if ($balanceTransfer->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully saved balance transfer.']);
        }
        return response()->json(['status' => 500, 'message' => 'Cannot saved balance transfer']);
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
        $result = BalanceTransfer::select('balance_transfer.id', 'balance_transfer.date', 'balance_transfer.amount', 'balance_transfer.status', 'c1.name as from_category_name', 'c2.name as to_category_name', 'users.name as approve_by')
            ->leftJoin('categories as c1', 'c1.id', '=', 'balance_transfer.from_category_id')
            ->leftJoin('categories as c2', 'c2.id', '=', 'balance_transfer.to_category_id')
            ->leftJoin('users', 'users.id', '=', 'balance_transfer.approve_by')
            ->where('balance_transfer.client_company_id', $sessionUser['client_company_id']);
        $result = $result->orderBy('balance_transfer.id', 'DESC')
            ->paginate($limit);
        foreach ($result as &$data) {
            $data['date'] = Helpers::formatDate($data['date'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME);
            $data['amount_format'] = number_format($data['amount'], 2);
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function single(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = BalanceTransfer::find($requestData['id']);
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required',
            'from_category_id' => 'required',
            'to_category_id' => 'required',
            'amount' => 'required',
            'date' => 'required|date'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $balanceTransfer = BalanceTransfer::find($requestData['id']);
        if (!$balanceTransfer instanceof BalanceTransfer) {
            return response()->json(['status' => 500, 'message' => 'Cannot find balance transfer..']);
        }
        if ($balanceTransfer['status'] == FuelMatixStatus::APPROVE) {
            return response()->json(['status' => 500, 'message' => 'Cannot update balance transfer.']);
        }
        $balanceTransfer->date = $requestData['date'];
        $balanceTransfer->from_category_id = $requestData['from_category_id'];
        $balanceTransfer->to_category_id = $requestData['to_category_id'];
        $balanceTransfer->amount = $requestData['amount'];
        $balanceTransfer->remarks = $requestData['remarks'] ?? null;
        if ($balanceTransfer->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully updated balance transfer.']);
        }
        return response()->json(['status' => 500, 'message' => 'Cannot updated balance transfer.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function approve(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $balanceTransfer = BalanceTransfer::find($requestData['id']);
        if (!$balanceTransfer instanceof BalanceTransfer) {
            return response()->json(['status' => 500, 'message' => 'Cannot find balance transfer..']);
        }
        if ($balanceTransfer['status'] == FuelMatixStatus::APPROVE) {
            return response()->json(['status' => 500, 'message' => 'You already have been transfer balance.']);
        }
        $transactionData['linked_id'] = $balanceTransfer['to_category_id'];
        $transactionData['transaction'] = [
            ['date' => Helpers::formatDate($balanceTransfer['date'], 'Y-m-d'), 'account_id' => $balanceTransfer['from_category_id'], 'debit_amount' => $balanceTransfer['amount'], 'credit_amount' => 0, 'module' => Module::BALANCE_TRANSFER, 'module_id' => $balanceTransfer->id]
        ];
        $response = TransactionController::saveTransaction($transactionData);
        if ($response) {
            $balanceTransfer->status = FuelMatixStatus::APPROVE;
            $balanceTransfer->approve_by = Auth::user()->id;
            $balanceTransfer->save();
            return response()->json(['status' => 200, 'message' => 'Successfully approved balance transfer.']);
        }
        return response()->json(['status' => 500, 'message' => 'Cannot approved balance transfer.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $balanceTransfer = BalanceTransfer::find($requestData['id']);
        if (!$balanceTransfer instanceof BalanceTransfer) {
            return response()->json(['status' => 500, 'message' => 'Cannot find balance transfer..']);
        }
        if ($balanceTransfer['status'] == FuelMatixStatus::APPROVE) {
            return response()->json(['status' => 500, 'message' => 'Cannot delete balance transfer.']);
        }
        BalanceTransfer::where('id', $requestData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted balance transfer.']);
    }
}
