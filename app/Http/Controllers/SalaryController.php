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
use App\Repository\TransactionRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Rmunate\Utilities\SpellNumber;

class SalaryController extends Controller
{
    /**
     * Search employees and retrieve their transaction data for a given month and year.
     *
     * @param Request $request The HTTP request object containing the search parameters.
     * @return JsonResponse The JSON response containing the employee data and their transactions.
     */
    public function searchEmployee(Request $request): JsonResponse
    {
        // Validate the request to ensure 'month' and 'year' are within the acceptable range
        $validator = Validator::make($request->all(), [
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer',
        ]);

        // If validation fails, return the validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Retrieve the list of employees based on the search criteria
        $employees = EmployeeRepository::list($request->all());
        if ($employees->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No employees found for the provided criteria.'
            ]);
        }

        // Extract employee IDs for transaction lookup
        $employeeIds = $employees->pluck('id')->toArray();

        // Retrieve transactions for the given month and year
        $transactions = Transaction::select('linked_id as employee_id', 'debit_amount as amount', 'account_id as category_id')
            ->whereIn('linked_id', $employeeIds)
            ->whereMonth('date', $request->input('month'))
            ->whereYear('date', $request->input('year'))
            ->get()
            ->keyBy('employee_id')
            ->toArray();

        // Merge transaction data with employee data
        foreach ($employees as &$employee) {
            $transaction = $transactions[$employee['id']] ?? null;
            $employee['salary'] = $transaction['amount'] ?? $employee['salary'];
            $employee['category_id'] = $transaction['category_id'] ?? '';
            $employee['checked'] = true;
        }

        // Return the combined data with a success status
        return response()->json([
            'status' => 200,
            'data' => $employees
        ]);
    }

    /**
     * Save employee salary transactions for a given month and year.
     *
     * @param Request $request The HTTP request containing salary details.
     * @return JsonResponse The JSON response indicating success or errors.
     */
    public function save(Request $request): JsonResponse
    {
        // Validate the request input
        $validator = Validator::make($request->all(), [
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:' . date('Y'),
            'employees' => 'required|array',
            'employees.*.id' => 'required|integer',
            'employees.*.salary' => 'required|numeric|min:0',
            'employees.*.category_id' => 'required|integer',
        ]);

        // If validation fails, return errors
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Retrieve the session user and construct the date
        $sessionUser = SessionUser::getUser();
        $date = sprintf('%04d-%02d-01', $request->input('year'), $request->input('month'));

        // Extract employee IDs from the request
        $employeeIds = array_column($request->input('employees'), 'id');

        // Delete existing salary transactions for the provided month and year
        $transactionIds = Transaction::where('client_company_id', $sessionUser['client_company_id'])
            ->where('date', $date)
            ->where('module', Module::SALARY)
            ->where('account_id', $employeeIds)
            ->pluck('id')
            ->toArray();
        Transaction::whereIn('account_id', $employeeIds)
            ->orWhereIn('linked_id', $transactionIds)
            ->delete();

        // Loop through each employee and save the transactions
        foreach ($request->input('employees') as $employee) {
            if (!empty($employee['checked'])) {
                $transactions = [
                    [
                        'date' => $date,
                        'account_id' => $employee['id'],
                        'debit_amount' => $employee['salary'],
                        'credit_amount' => 0,
                        'module' => Module::SALARY,
                        'client_company_id' => $sessionUser['client_company_id']
                    ],
                    [
                        'date' => $date,
                        'account_id' => $employee['category_id'],
                        'debit_amount' => 0,
                        'credit_amount' => $employee['salary'],
                        'module' => Module::SALARY,
                        'client_company_id' => $sessionUser['client_company_id']
                    ]
                ];
                TransactionRepository::saveTransaction($transactions);
            }
        }

        // Return a success response
        return response()->json([
            'status' => 200,
            'message' => 'Successfully saved salary.'
        ]);
    }

    /**
     * List salary-related transactions with filtering and pagination.
     *
     * @param Request $request The HTTP request containing filtering and pagination options.
     * @return JsonResponse The JSON response containing the list of transactions.
     */
    public function list(Request $request): JsonResponse
    {
        // Retrieve session user and input parameters with defaults
        $sessionUser = SessionUser::getUser();
        $limit = (int) $request->input('limit', 10);
        $orderBy = $request->input('order_by', 'id');
        $orderMode = $request->input('order_mode', 'DESC');
        $keyword = $request->input('keyword', '');
        $month = $request->input('month', '');
        $year = $request->input('year', '');

        // Fetch the salary category for the user's company
        $salaryCategory = Category::select('id')
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->where('slug', strtolower(AccountCategory::SALARY_EXPENSE))
            ->first();

        // If salary category is not found, return an error response
        if (!$salaryCategory instanceof Category) {
            return response()->json([
                'status' => 500,
                'errors' => 'Cannot find account salary category.'
            ]);
        }

        // Build the base query for fetching transactions
        $query = Transaction::select(
            'transactions.id',
            'transactions.debit_amount as amount',
            'c.name',
            'transactions.date',
            'c1.name as payment_method'
        )
            ->leftJoin('categories as c', 'c.id', '=', 'transactions.account_id')
            ->leftJoin('transactions as t1', 't1.linked_id', '=', 'transactions.id')
            ->leftJoin('categories as c1', 'c1.id', '=', 't1.account_id')
            ->where('c.parent_category', $salaryCategory->id)
            ->where('transactions.client_company_id', $sessionUser['client_company_id']);

        // Apply month filter if provided
        if (!empty($month)) {
            $query->whereMonth('transactions.date', $month);
        }

        // Apply year filter if provided
        if (!empty($year)) {
            $query->whereYear('transactions.date', $year);
        }

        // Apply keyword search filter if provided
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('c.name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('c1.name', 'LIKE', '%' . $keyword . '%');
            });
        }

        // Group, order, and paginate the results
        $transactions = $query->groupBy('transactions.id')
            ->orderBy($orderBy, $orderMode)
            ->paginate($limit);

        // Format the date in the response
        foreach ($transactions as &$transaction) {
            $transaction->date = date('F, Y', strtotime($transaction->date));
        }

        // Return the response with the transaction data
        return response()->json([
            'status' => 200,
            'data' => $transactions
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCategory(Request $request): JsonResponse
    {
        $sessionUser = SessionUser::getUser();
        $cashInHand = Category::select('id')
            ->where('slug', strtolower(AccountCategory::CASH_IN_HAND))
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
     * Generate and return a PDF salary report for a specific transaction.
     *
     * @param Request $request The HTTP request containing the transaction ID.
     * @return JsonResponse|string The generated PDF or a JSON error response.
     */
    public function print(Request $request)
    {
        // Validate the incoming request to ensure 'id' is provided and is an integer
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        // Return validation errors if validation fails
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }

        // Retrieve the session user and transaction details
        $sessionUser = SessionUser::getUser();
        $transaction = Transaction::select(
            'transactions.date',
            'transactions.debit_amount as amount',
            'c1.name as employee_name',
            'c1.rfid as employee_id',
            'c1.others',
            'c2.name as bank_name'
        )
            ->leftJoin('categories as c1', 'c1.id', '=', 'transactions.account_id')
            ->leftJoin('transactions as t1', 't1.linked_id', '=', 'transactions.id')
            ->leftJoin('categories as c2', 'c2.id', '=', 't1.account_id')
            ->where('transactions.id', $request->input('id'))
            ->first();

        // Check if the transaction was found
        if (!$transaction) {
            return response()->json(['status' => 404, 'errors' => 'Transaction not found.']);
        }

        // Decode the JSON stored in the 'others' field and handle null cases
        $transaction->others = json_decode($transaction->others, true);

        // Convert the amount to words
        $transaction->amount_in_word = Helpers::convertNumberToWord($transaction->amount);

        // Retrieve the company information
        $company = ClientCompany::find($sessionUser['client_company_id']);
        if (!$company) {
            return response()->json(['status' => 404, 'errors' => 'Company not found.']);
        }

        // Add company details to the transaction data
        $transaction->company_name = $company->name;
        $transaction->address = $company->address;

        // Format the dates
        $transaction->date = date('F Y', strtotime($transaction->date));
        $transaction->pay_date = date('d, F Y', strtotime($transaction->date));

        // Extract and assign the employee position if available
        $transaction->position = $transaction->others['position'] ?? '';

        // Generate the PDF using the 'pdf.salary-report' view and the transaction data
        $pdf = Pdf::loadView('pdf.salary-report', ['data' => $transaction]);

        // Return the generated PDF output
        return $pdf->output();
    }

}
