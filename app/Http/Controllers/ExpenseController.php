<?php

namespace App\Http\Controllers;

use App\Common\FuelMatixDateTimeFormat;
use App\Common\FuelMatixStatus;
use App\Common\Module;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Expense;
use App\Repository\TransactionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
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
            'category_id' => 'required|integer',
            'amount' => 'required|numeric',
            'payment_id' => 'required|integer',
            'shift_sale_id' => 'nullable|integer',
        ]);

        // If validation fails, return a 400 Bad Request response
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
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

        // Find the source category
        $paymentCategory = Category::find($request->input('payment_id'));
        if (!$paymentCategory) {
            return response()->json([
                'status' => 500,
                'errors' => ['payment_id' => ['Payment category cannot be found.']]
            ]);
        }

        // Check if the source category has enough balance
        $availableBalance = $paymentCategory->checkAvailableBalance($request->input('amount'));
        if (!$availableBalance) {
            return response()->json([
                'status' => 300,
                'message' => 'Not enough balance in '.$paymentCategory['name'].'.'
            ]);
        }

        // Handle file upload
        $file_path = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $destinationPath = public_path('uploads');
            $file_path = time() . '_' . $file->getClientOriginalName(); // Generate a unique file name
            $file->move($destinationPath, $file_path);
        }

        // Create and save the expense record
        $sessionUser = SessionUser::getUser();
        $expense = new Expense();
        $expense->date = Carbon::parse($request->input('date'). date(' H:i:s'), SessionUser::TIMEZONE)->format('Y-m-d H:i:s');
        $expense->category_id = $request->input('category_id');
        $expense->amount = $request->input('amount');
        $expense->payment_id = $request->input('payment_id');
        $expense->remarks = $request->input('remarks') ?? null;
        $expense->shift_sale_id = $request->input('shift_sale_id') ?? null;
        $expense->file = $file_path;
        $expense->status = FuelMatixStatus::PENDING;
        $expense->client_company_id = $sessionUser['client_company_id'];
        $expense->user_id = $sessionUser['id'];

        // Save the expense record
        if (!$expense->save()) {
            return response()->json([
                'status' => 500,
                'message' => 'Cannot save expense.'
            ]);
        }

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
        $sessionUser = SessionUser::getUser();

        // Build the query to fetch expenses
        $result = Expense::select('expense.id', 'expense.date', 'expense.amount', 'c.name as expense', 'c1.name as payment', 'expense.status', 'users.name as approve_by', 'expense.file')
            ->leftJoin('categories as c', 'c.id', 'expense.category_id')
            ->leftJoin('categories as c1', 'c1.id', 'expense.payment_id')
            ->leftJoin('users', 'users.id', '=', 'expense.approve_by')
            ->where('expense.client_company_id', $sessionUser['client_company_id']);

        // Apply keyword filtering if provided
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('c.name', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('c1.name', 'LIKE', '%'.$keyword.'%');
            });
        }

        // Order by 'id' in descending order and paginate the results
        $result = $result->orderBy('id', 'DESC')->paginate($limit);

        // Format the results
        foreach ($result as &$data) {
            $data['amount_format'] = number_format($data['amount'], 2);
            $data['date'] = Helpers::formatDate($data['date'], FuelMatixDateTimeFormat::STANDARD_DATE);
        }

        // Return the results as JSON
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
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
            'shift_sale_id' => 'nullable|integer',
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
        foreach ($result as &$data) {
            $total += $data['amount'];
            $data['date'] = Helpers::formatDate($data['date'], FuelMatixDateTimeFormat::STANDARD_DATE);
            $data['approve_date'] = Helpers::formatDate($data['approve_date'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME);
            $data['amount'] = number_format($data['amount'], 2);
        }
        return response()->json(['status' => 200, 'data' => $result, 'total' => number_format($total, 2)]);
    }
}
