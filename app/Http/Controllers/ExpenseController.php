<?php

namespace App\Http\Controllers;

use App\Common\FuelMatixDateTimeFormat;
use App\Common\FuelMatixStatus;
use App\Common\Module;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'date' => 'required|date',
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
        $sessionUser = SessionUser::getUser();
        $expense = new Expense();
        $expense->date = $inputData['date'];
        $expense->category_id = $inputData['category_id'];
        $expense->amount = $inputData['amount'];
        $expense->payment_id = $inputData['payment_id'];
        $expense->remarks = $inputData['remarks'] ?? null;
        $expense->file = $file_path;
        $expense->status = FuelMatixStatus::PENDING;
        $expense->client_company_id = $sessionUser['client_company_id'];
        $expense->user_id = Auth::user()->id;
        if (!$expense->save()) {
            return response()->json(['status' => 500, 'message' => 'Cannot save expense.']);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully save expense.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $limit = $inputData['limit'] ?? 10;
        $keyword = $inputData['keyword'] ?? '';
        $sessionUser = SessionUser::getUser();

        $result = Expense::select('expense.id', 'expense.date', 'expense.amount',  'c.name as expense', 'c1.name as payment', 'expense.status', 'users.name as approve_by')
            ->leftJoin('categories as c', 'c.id', 'expense.category_id')
            ->leftJoin('categories as c1', 'c1.id', 'expense.payment_id')
            ->leftJoin('users', 'users.id', '=', 'expense.approve_by')
            ->where('expense.client_company_id', $sessionUser['client_company_id']);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('c.name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('c1.name', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy('id', 'DESC')
            ->paginate($limit);
        foreach ($result as &$data) {
            $data['amount_format'] = number_format($data['amount'], 2);
            $data['date'] = Helpers::formatDate($data['date'], FuelMatixDateTimeFormat::STANDARD_DATE);
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function single(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = Expense::select('id', 'category_id', 'payment_id', 'amount', 'file', 'remarks', 'date')
            ->where('id', $inputData['id'])
            ->first();
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
            'date' => 'required|date',
            'category_id' => 'required',
            'amount' => 'required',
            'payment_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $expense = Expense::find($inputData['id']);
        if (!$expense instanceof Expense) {
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
        $expense->date = $inputData['date'];
        $expense->category_id = $inputData['category_id'];
        $expense->amount = $inputData['amount'];
        $expense->payment_id = $inputData['payment_id'];
        $expense->remarks = $inputData['remarks'] ?? null;
        $expense->file = $file_path;
        if (!$expense->save()) {
            return response()->json(['status' => 500, 'error' => 'Cannot save expense.']);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully updated expense.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $expense = Expense::find($inputData['id']);
        if (!$expense instanceof Expense) {
            return response()->json(['status' => 500, 'message' => 'Cannot find expense..']);
        }
        if ($expense['status'] == FuelMatixStatus::APPROVE) {
            return response()->json(['status' => 500, 'message' => 'Cannot delete Expense.']);
        }
        Expense::where('id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted expenses.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function approve(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $expense = Expense::find($inputData['id']);
        if (!$expense instanceof Expense) {
            return response()->json(['status' => 500, 'error' => 'Cannot find expense.']);
        }
        if ($expense['status'] == FuelMatixStatus::APPROVE) {
            return response()->json(['status' => 500, 'error' => 'Expense already have been approve.']);
        }
        $data['transaction'] = [
            ['date' => $expense['date'], 'description' => $expense['remarks'], 'account_id' => $expense['payment_id'], 'debit_amount' => $expense['amount'], 'credit_amount' => 0, 'module' => Module::EXPENSE, 'module_id' => $expense['id'], 'file' => $expense['file']]
        ];
        $data['linked_id'] = $expense['category_id'];
        TransactionController::saveTransaction($data);
        $expense->status = FuelMatixStatus::APPROVE;
        $expense->approve_by = Auth::user()->id;
        $expense->approve_date = Carbon::now('UTC');
        $expense->save();
        return response()->json(['status' => 200, 'message' => 'Successfully approve expense.']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function report(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $category_id = $requestData['category_id'] ?? '';
        $request_by = $requestData['request_by'] ?? '';
        $approve_by = $requestData['approve_by'] ?? '';
        $payment_category_id = $requestData['payment_category_id'] ?? '';
        $result = Expense::select('expense.id', 'expense.date', 'c1.name as expense_type', 'c2.name as payment_method', 'expense.amount', 'expense.remarks', 'expense.approve_date', 'u1.name as approve_by', 'u2.name as request_by')
            ->leftJoin('categories as c1', 'c1.id', '=', 'expense.category_id')
            ->leftJoin('categories as c2', 'c2.id', '=', 'expense.payment_id')
            ->leftJoin('users as u1', 'u1.id', '=', 'expense.approve_by')
            ->leftJoin('users as u2', 'u2.id', '=', 'expense.user_id')
            ->whereBetween('date', [$requestData['start_date'], $requestData['end_date']])
            ->where('expense.client_company_id', $requestData['session_user']['client_company_id']);
        if (!empty($category_id)) {
            $result->where(function($q) use ($category_id) {
                $q->where('expense.category_id', $category_id);
            });
        }
        if (!empty($request_by)) {
            $result->where(function($q) use ($request_by) {
                $q->where('expense.user_id', $request_by);
            });
        }
        if (!empty($approve_by)) {
            $result->where(function($q) use ($approve_by) {
                $q->where('expense.approve_by', $approve_by);
            });
        }
        if (!empty($payment_category_id)) {
            $result->where(function($q) use ($payment_category_id) {
                $q->where('expense.payment_id', $payment_category_id);
            });
        }
        $result = $result->orderBy('date', 'ASC')
            ->get()
            ->toArray();
        $total = 0;
        foreach ($result as &$data) {
            $total += $data['amount'];
            $data['date'] = Helpers::formatDate($data['date'], FuelMatixDateTimeFormat::STANDARD_DATE);
            $data['approve_date'] = Helpers::formatDate($data['approve_date'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME);
            $data['amount'] = number_format($data['amount'], 2);
        }
        return response()->json(['status' => 200, 'data' => $result, 'total' => number_format($total, 2)]);
    }
}
