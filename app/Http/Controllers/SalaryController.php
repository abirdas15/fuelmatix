<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\Module;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\ClientCompany;
use App\Models\Transaction;
use App\Repository\EmployeeRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Rmunate\Utilities\SpellNumber;

class SalaryController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function searchEmployee(Request $request): JsonResponse
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
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
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
        $date = $requestData['year'].'-'.$requestData['month'].'-01 h:i:s';
        $employeeIds = array_column($requestData['employees'], 'id');
        Transaction::where('client_company_id', $sessionUser['client_company_id'])
            ->where('date', $date)
            ->where('module', Module::SALARY)
            ->where(function($q) use ($employeeIds) {
                $q->whereIn('account_id', $employeeIds);
                $q->orWhereIn('linked_id', $employeeIds);
            })
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
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
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
            ->where('slug', strtolower(AccountCategory::SALARY_EXPENSE))
            ->first();
        if (!$salaryCategory instanceof Category) {
            return response()->json(['status' => 500, 'errors' => 'Can not find account salary category.']);
        }
        $result = Transaction::select('transactions.id', 'transactions.debit_amount as amount', 'c.name', 'transactions.date', 'c1.name as payment_method')
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
                $q->where('c.name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('c1.name', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($orderBy, $orderMode)
            ->paginate($limit);
        foreach ($result as &$data) {
            $data['date'] = date('F, Y', strtotime($data['date']));
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCategory(Request $request): JsonResponse
    {
        $sessionUser = SessionUser::getUser();
        $cashInHand = Category::select('id')
            ->where('slug', strtolower(AccountCategory::CASH_IM_HAND))
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first();
        $bank = Category::select('id')
            ->where('slug', strtolower(AccountCategory::BANK))
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first();
        $result = Category::select('id', 'name')
            ->whereIn('parent_category', [$bank->id, $cashInHand->id])
            ->get()
            ->toArray();
        return response()->json(['status' => 200, 'data' => $result]);
    }

    /**
     * @param Request $request
     * @return JsonResponse|string
     */
    public function print(Request $request)
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $sessionUser = SessionUser::getUser();
        $result = Transaction::select('date', 'debit_amount as amount', 'c1.name as employee_name', 'c1.rfid as employee_id', 'c1.others', 'c2.name as bank_name')
            ->leftJoin('categories as c1', 'c1.id', '=', 'transactions.linked_id')
            ->leftJoin('categories as c2', 'c2.id', '=', 'transactions.account_id')
            ->where('transactions.id', $requestData['id'])
            ->first();
        $result['others'] = json_decode($result['others']);
        $result['amount_in_word'] = Helpers::convertNumberToWord($result['amount']);
        $company = ClientCompany::find($sessionUser['client_company_id']);
        $result['company_name'] = $company['name'];
        $result['address'] = $company['address'];
        $result['date'] = date('F Y', strtotime($result['date']));
        $result['pay_date'] = date('d, F Y', strtotime($result['date']));
        $result['position'] = $result['others']->position ?? '';
        $pdf = Pdf::loadView('pdf.salary-report', ['data' => $result]);
        return $pdf->output();
    }
}
