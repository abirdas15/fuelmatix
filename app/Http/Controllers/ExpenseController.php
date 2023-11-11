<?php

namespace App\Http\Controllers;

use App\Common\FuelMatixStatus;
use App\Common\Module;
use App\Helpers\SessionUser;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $expense->date = date('Y-m-d');
        $expense->category_id = $inputData['category_id'];
        $expense->amount = $inputData['amount'];
        $expense->payment_id = $inputData['payment_id'];
        $expense->remarks = $inputData['remarks'] ?? null;
        $expense->file = $file_path;
        $expense->status = FuelMatixStatus::PENDING;
        $expense->client_company_id = $sessionUser['client_company_id'];
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

        $result = Expense::select('expense.id', 'expense.date', 'expense.amount',  'c.name as expense', 'c1.name as payment', 'expense.status')
            ->leftJoin('categories as c', 'c.id', 'expense.category_id')
            ->leftJoin('categories as c1', 'c1.id', 'expense.payment_id')
            ->where('expense.client_company_id', $sessionUser['client_company_id']);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('c.name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('c1.name', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy('status', 'DESC')
            ->paginate($limit);
        foreach ($result as &$data) {
            $data['date'] = date('d/m/Y', strtotime($data['date']));
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
        $result = Expense::select('id', 'category_id', 'payment_id', 'amount', 'file', 'remarks')
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
            ['date' => date('Y-m-d'), 'description' => $expense['remarks'], 'account_id' => $expense['payment_id'], 'debit_amount' => $expense['amount'], 'credit_amount' => 0, 'module' => Module::EXPENSE, 'module_id' => $expense['id'], 'file' => $expense['file']]
        ];
        $data['linked_id'] = $expense['category_id'];
        TransactionController::saveTransaction($data);
        $expense->status = FuelMatixStatus::APPROVE;
        $expense->save();
        return response()->json(['status' => 200, 'message' => 'Successfully approve expense.']);
    }
}
