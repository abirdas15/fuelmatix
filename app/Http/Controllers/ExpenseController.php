<?php

namespace App\Http\Controllers;

use App\Common\FuelMatixDateTimeFormat;
use App\Common\FuelMatixStatus;
use App\Common\Module;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\ClientCompany;
use App\Models\Expense;
use App\Models\User;
use App\Repository\ExpenseRepository;
use App\Repository\TransactionRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    /**
     * Save a new expense record.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        // Validate the incoming request to ensure required fields are provided and valid
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'expense' => 'required|array',
            'expense.*.category_id' => 'required|integer',
            'expense.*.amount' => 'required|numeric',
            'expense.*.payment_id' => 'required|numeric',
        ],[
            'expense.*.category_id.required' => 'The category field is required.',
            'expense.*.payment_id.required' => 'The payment field is required.',
            'expense.*.amount.required' => 'The amount field is required.',
        ]);

        // If validation fails, return a 400 Bad Request response
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }
        foreach ($request->input('expense') as $key => $expense) {
            // Find the destination category
            $category = Category::find($expense['category_id']);
            if (!$category instanceof Category) {
                return response()->json([
                    'status' => 500,
                    'errors' => [
                        "expense.$key.category_id" => ['Category cannot be found.']
                    ]
                ]);
            }

            // Find the source category
            $paymentCategory = Category::find($expense['payment_id']);
            if (!$paymentCategory instanceof Category) {
                return response()->json([
                    'status' => 500,
                    'errors' => [
                        "expense.$key.payment_id" => ['Payment category cannot be found.']
                    ]
                ]);
            }

            // Check if the source category has enough balance
            $availableBalance = $paymentCategory->checkAvailableBalance($expense['amount']);
            if (!$availableBalance) {
                return response()->json([
                    'status' => 500,
                    'errors' => [
                        "expense.$key.payment_id" => ['Not enough balance in ' . $paymentCategory['name'] . '.']
                    ]
                ]);
            }
        }
        DB::transaction(function() use ($request) {
            foreach ($request->input('expense') as $key => $expense) {
                $file_path = null;
                if ($request->hasFile("expense.$key.file")) {
                    $file = $request->file("expense.$key.file"); // Access the file using the request object

                    // Ensure the file is valid
                    if ($file->isValid()) {
                        $destinationPath = public_path('uploads');
                        $file_path = time() . '_' . $file->getClientOriginalName();
                        $file->move($destinationPath, $file_path);
                    }
                }
                $sessionUser = SessionUser::getUser();
                $expenseModel = new Expense();
                $expenseModel->date = Carbon::parse($request->input('date'). date(' H:i:s'), SessionUser::TIMEZONE)->format('Y-m-d H:i:s');
                $expenseModel->category_id = $expense['category_id'];
                $expenseModel->amount = $expense['amount'];
                $expenseModel->payment_id = $expense['payment_id'];
                $expenseModel->remarks = $expense['remarks'] ?? null;
                $expenseModel->paid_to = $expense['paid_to'] ?? null;
                $expenseModel->shift_sale_id = $request->input('shift_sale_id') ?? null;
                $expenseModel->file = $file_path;
                $expenseModel->status = FuelMatixStatus::PENDING;
                $expenseModel->client_company_id = $sessionUser['client_company_id'];
                $expenseModel->user_id = $sessionUser['id'];
                if (!$expenseModel->save()) {
                    return response()->json([
                        'status' => 500,
                        'message' => 'Cannot save expense.'
                    ]);
                }
            }
        });

        return response()->json([
            'status' => 200,
            'message' => 'Successfully saved expense.'
        ]);
    }

    /**
     * Retrieve a paginated list of expenses with optional filtering.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        // Retrieve all input data and set default values
        $inputData = $request->all();
        $limit = $request->input('limit', 10);
        $keyword = $request->input('keyword', '');

        $response = ExpenseRepository::list([
            'limit' => $limit,
            'keyword' => $keyword,
            'start_date' => $inputData['start_date'],
            'end_date' => $inputData['end_date'],
        ]);
        // Return the results as JSON
        return response()->json([
            'status' => 200,
            'data' => $response
        ]);
    }
    /**
     * @param Request $request
     * @return string
     */
    public function listExport(Request $request): string
    {
        // Retrieve all input data and set default values
        $inputData = $request->all();
        $limit = $request->input('limit', 10);
        $keyword = $request->input('keyword', '');

        $response = ExpenseRepository::list([
            'limit' => $limit,
            'keyword' => $keyword,
            'start_date' => $inputData['start_date'] ?? '',
            'end_date' => $inputData['end_date'] ?? '',
            'ids' => $inputData['ids'],
            'page' => 1
        ]);
        $sessionUser = SessionUser::getUser();
        $company = ClientCompany::where('id', $sessionUser['client_company_id'])->first();
        $startDate = !empty($inputData['start_date']) ? Carbon::parse($inputData['start_date'])->format(FuelMatixDateTimeFormat::STANDARD_DATE) : null;
        $endDate = !empty($inputData['start_date']) ? Carbon::parse($inputData['end_date'])->format(FuelMatixDateTimeFormat::STANDARD_DATE) : null;
        $pdf = Pdf::loadView('pdf.expense-list', [
            'data' => $response,
            'date' => $startDate.'-'.$endDate,
            'company' => $company,
            'print_at' => Carbon::now()->format('F j, Y h:i A')
        ]);
        return $pdf->output();
    }

    /**
     * Retrieve a single expense by its ID.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function single(Request $request): JsonResponse
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }

        // Retrieve the expense with the given ID
        $result = Expense::select('id', 'category_id', 'payment_id', 'amount', 'file', 'remarks', 'date', 'shift_sale_id')
            ->where('id', $request->input('id'))
            ->first();

        // Format the date
        if ($result) {
            $result['date'] = Helpers::formatDate($result['date'], FuelMatixDateTimeFormat::ONLY_DATE);
        }

        // Return the result as JSON
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'date' => 'required|date',
            'category_id' => 'required|integer',
            'amount' => 'required|numeric',
            'payment_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Retrieve the expense with the given ID
        $expense = Expense::find($request->input('id'));
        if (!$expense) {
            return response()->json([
                'status' => 500,
                'error' => 'Cannot find expense.'
            ]);
        }

        // Find the destination category
        $category = Category::find($request->input('category_id'));
        if (!$category) {
            return response()->json([
                'status' => 500,
                'errors' => ['category_id' => ['Category cannot be found.']]
            ]);
        }

        // Find the payment category
        $paymentCategory = Category::find($request->input('payment_id'));
        if (!$paymentCategory) {
            return response()->json([
                'status' => 500,
                'errors' => ['payment_id' => ['Payment category cannot be found.']]
            ]);
        }

        // Check if the payment category has enough balance
        $availableBalance = $paymentCategory->checkAvailableBalance($request->input('amount'));
        if (!$availableBalance) {
            return response()->json([
                'status' => 300,
                'message' => 'Not enough balance in ' . $paymentCategory->name . '.'
            ]);
        }

        // Handle file upload
        $file_path = $expense->file;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $destinationPath = public_path('uploads');
            $file_path = $file->getClientOriginalName();
            $file->move($destinationPath, $file_path);
        }

        // Update expense details
        $expense->date = Carbon::parse($request->input('date') . ' ' . date('H:i:s'), SessionUser::TIMEZONE)->format('Y-m-d H:i:s');
        $expense->category_id = $request->input('category_id');
        $expense->amount = $request->input('amount');
        $expense->payment_id = $request->input('payment_id');
        $expense->paid_to = $request->input('paid_to') ?? null;
        $expense->remarks = $request->input('remarks') ?? null;
        $expense->shift_sale_id = $request->input('shift_sale_id') ?? null;
        $expense->file = $file_path;

        // Save the updated expense
        if (!$expense->save()) {
            return response()->json([
                'status' => 500,
                'error' => 'Cannot save expense.'
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Successfully updated expense.'
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Find the expense with the given ID
        $expense = Expense::find($request->input('id'));
        if (!$expense) {
            return response()->json([
                'status' => 500,
                'message' => 'Cannot find expense..'
            ]);
        }

        // Check if the expense status is APPROVE
        if ($expense->status == FuelMatixStatus::APPROVE) {
            return response()->json([
                'status' => 500,
                'message' => 'Cannot delete expense.'
            ]);
        }

        // Delete the expense
        $expense->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Successfully deleted expense.'
        ]);
    }

    /**
     * Approves an expense.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function approve(Request $request): JsonResponse
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer', // 'id' is required and must be an integer
        ]);

        // If validation fails, return error response with validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Get the currently logged-in user
        $sessionUser = SessionUser::getUser();

        // Find the expense by ID
        $expense = Expense::find($request->input('id'));
        if (!$expense instanceof Expense) {
            // If expense not found, return error response
            return response()->json([
                'status' => 500,
                'error' => 'Cannot find expense.'
            ]);
        }

        // Check if the expense is already approved
        if ($expense['status'] == FuelMatixStatus::APPROVE) {
            return response()->json([
                'status' => 500,
                'error' => 'Expense already has been approved.'
            ]);
        }

        // Find the payment category associated with the expense
        $paymentCategory = Category::find($expense['payment_id']);
        if (!$paymentCategory) {
            // If payment category not found, return error response
            return response()->json([
                'status' => 500,
                'errors' => ['payment_id' => ['Payment category cannot be found.']]
            ]);
        }

        // Check if the payment category has enough balance to cover the expense
        $availableBalance = $paymentCategory->checkAvailableBalance($expense['amount']);
        if (!$availableBalance) {
            return response()->json([
                'status' => 300,
                'message' => 'Not enough balance in ' . $paymentCategory->name . '.'
            ]);
        }

        // Prepare transaction data for updating financial records
        $transactionData = [
            ['date' => date('Y-m-d', strtotime($expense['date'])), 'account_id' => $expense['category_id'], 'description' => $expense['remarks'], 'debit_amount' => $expense->amount, 'credit_amount' => 0, 'module' => Module::EXPENSE, 'module_id' => $expense->id],
            ['date' => date('Y-m-d', strtotime($expense['date'])), 'account_id' => $expense['payment_id'], 'description' => $expense['remarks'], 'debit_amount' => 0, 'credit_amount' => $expense->amount, 'module' => Module::EXPENSE, 'module_id' => $expense->id],
        ];
        // Save the transaction data
        TransactionRepository::saveTransaction($transactionData);

        // Update expense status and other details
        $expense->status = FuelMatixStatus::APPROVE;
        $expense->approve_by = $sessionUser['id'];
        $expense->approve_date = Carbon::now(SessionUser::TIMEZONE);
        $expense->save();

        // Return success response
        return response()->json([
            'status' => 200,
            'message' => 'Successfully approved expense.'
        ]);
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
        $sessionUser = SessionUser::getUser();
        foreach ($result as &$data) {
            $total += $data['amount'];
            $data['date'] = Helpers::formatDate($data['date'], FuelMatixDateTimeFormat::STANDARD_DATE);
            $data['approve_date'] = Helpers::formatDate($data['approve_date'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME);
            $data['amount'] = number_format($data['amount'], $sessionUser['currency_precision']);
        }
        return response()->json(['status' => 200, 'data' => $result, 'total' => number_format($total, $sessionUser['currency_precision'])]);
    }
    /**
     * @param Request $request
     * @return string
     */
    public function exportPdf(Request $request): string
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }
        $sessionUser = SessionUser::getUser();
        $data = Expense::where('id', $request->input('id'))->first();
        $data['amount_format'] = number_format($data['amount'], $sessionUser['currency_precision']);
        $data['number_text'] = Helpers::convertNumberToWord($data['amount']);
        $data['date'] = Helpers::formatDate($data['date'], FuelMatixDateTimeFormat::STANDARD_DATE);
        $category = Category::where('id', $data['category_id'])->first();
        $sessionUser = SessionUser::getUser();
        $company = ClientCompany::where('id', $sessionUser['client_company_id'])->first();
        $paymentCategory = Category::where('id', $data['payment_id'])->first();

        $approve_date = !empty($data['approve_date']) ? Helpers::formatDate($data['approve_date'], FuelMatixDateTimeFormat::STANDARD_DATE) : '';
        $approve_by = '';
        $user = User::where('id', $data['approve_by'])->first();
        if ($user instanceof User) {
            $approve_by = $user->name;
        }
        $pdf = Pdf::loadView('pdf.expense-memo', [
            'company' => $company,
            'data' => $data,
            'category_name' => $category['name'],
            'payment_method' => $paymentCategory['name'],
            'approve_date' => $approve_date,
            'approve_by' => $approve_by,
        ]);
        return $pdf->output();
    }
}
