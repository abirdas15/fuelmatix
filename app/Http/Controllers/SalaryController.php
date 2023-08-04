<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\Module;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Transaction;
use App\Repository\EmployeeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SalaryController extends Controller
{
    public function searchEmployee(Request $request)
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'month' => 'required',
            'year' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $employees = EmployeeRepository::list($requestData);
        $employeeId = [];
        foreach ($employees as $employee) {
            $employeeId[] = $employee['id'];
        }
        $transaction = Transaction::select('linked_id as employee_id','debit_amount as amount', 'account_id as category_id')
            ->whereIn('linked_id', $employeeId)
            ->whereMonth('date', $requestData['month'])
            ->whereYear('date', $requestData['year'])
            ->get()
            ->keyBy('employee_id')
            ->toArray();
        foreach ($employees as &$employee) {
            $employee['salary'] = isset($transaction[$employee['id']]) ? $transaction[$employee['id']]['amount'] : $employee['salary'];
            $employee['category_id'] = isset($transaction[$employee['id']]) ? $transaction[$employee['id']]['category_id'] : '';
        }
        return response()->json(['status' => 200, 'data' => $employees]);
    }
    public function save(Request $request)
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'month' => 'required',
            'year' => 'required',
            'employees' => 'required|array',
            'employees.*.id' => 'required',
            'employees.*.salary' => 'required',
            'employees.*.category_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $sessionUser = SessionUser::getUser();
        $date = $requestData['year'].'-'.$requestData['month'].'-01';
        $employeeIds = array_column($requestData['employees'], 'id');
        Transaction::where('client_company_id', $sessionUser['client_company_id'])
            ->where('date', $date)
            ->where('module', Module::SALARY)
            ->whereIn('account_id', $employeeIds)
            ->orWhereIn('linked_id', $employeeIds)
            ->delete();
        foreach ($requestData['employees'] as $employee) {
            $transactions  = [];
            $transactions['linked_id'] = $employee['id'];
            $transactions['transaction'] = [
                ['date' => $date, 'account_id' => $employee['category_id'], 'debit_amount' => $employee['salary'], 'credit_amount' => 0, 'module' => Module::SALARY]
            ];
            TransactionController::saveTransaction($transactions);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully saved salary.']);
    }
    public function list(Request $request)
    {
        $requestData = $request->all();
        $sessionUser = SessionUser::getUser();
        $limit = $requestData['limit'] ?? 10;
        $orderBy = $requestData['order_by'] ?? 'id';
        $orderMode = $requestData['order_mode'] ?? 'DESC';
        $keyword = $requestData['keyword'] ?? '';
        $month = $requestData['month'] ?? '';
        $year = $requestData['year'] ?? '';
        $salaryCategory = Category::select('id')
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->where('category', AccountCategory::SALARY_EXPENSE)
            ->first();
        if (!$salaryCategory instanceof Category) {
            return response()->json(['status' => 500, 'errors' => 'Can not find account salary category.']);
        }
        $result = Transaction::select('transactions.id', 'transactions.debit_amount as amount', 'c.category as name', 'transactions.date', 'c1.category as payment_method')
            ->leftJoin('categories as c', 'c.id', '=', 'transactions.linked_id')
            ->leftJoin('categories as c1', 'c1.id', '=', 'transactions.account_id')
            ->where('c.parent_category', $salaryCategory->id)
            ->where('transactions.client_company_id', $sessionUser['client_company_id']);
        if (!empty($month)) {
            $result->where(function($q) use ($month) {
                $q->whereMonth('date', $month);
            });
        }
        if (!empty($year)) {
            $result->where(function($q) use ($year) {
                $q->whereYear('date', $year);
            });
        }
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('c.category', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('c1.category', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($orderBy, $orderMode)
            ->paginate($limit);
        foreach ($result as &$data) {
            $data['date'] = date('F, Y', strtotime($data['date']));
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
}
