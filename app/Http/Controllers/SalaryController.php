<?php

namespace App\Http\Controllers;

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
}
