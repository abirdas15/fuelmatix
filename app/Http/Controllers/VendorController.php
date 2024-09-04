<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\FuelMatixDateTimeFormat;
use App\Common\Module;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Transaction;
use App\Repository\CategoryRepository;
use App\Repository\ReportRepository;
use App\Repository\TransactionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat\Wizard\Currency;

class VendorController extends Controller
{

    /**
     * Saves a new vendor category under the 'account payable' category.
     *
     * This function validates the request, checks for the existence of the 'account payable' category,
     * saves the new vendor category under it, and adds the opening balance if provided.
     *
     * @param Request $request The incoming HTTP request containing the category details.
     * @return JsonResponse A JSON response indicating the success or failure of the save operation.
     */
    public function save(Request $request): JsonResponse
    {
        // Validate the request data: 'name' is required and must be a string, 'opening_balance' is optional but must be numeric if provided
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'opening_balance' => 'nullable|numeric'
        ]);

        // If validation fails, return a JSON response with validation errors and status 500
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Get the current session user
        $sessionUser = SessionUser::getUser();

        // Find the 'account payable' category for the current user's company
        $accountPayable = Category::where('client_company_id', $sessionUser['client_company_id'])
            ->where('slug', strtolower(AccountCategory::ACCOUNT_PAYABLE))
            ->first();

        // If 'account payable' category does not exist, return a JSON response with an error message and status 400
        if (!$accountPayable instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [account payable] category.'
            ]);
        }

        // Prepare the category data for saving
        $categoryData = [
            'name' => $request->input('name'),
            'opening_balance' => $request->input('opening_balance') ?? null,
        ];

        // Save the new vendor category under the 'account payable' category
        $newCategory = CategoryRepository::saveCategory($categoryData, $accountPayable['id']);

        // If saving the new category fails, return a JSON response with an error message and status 400
        if (!$newCategory instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot save [vendor].'
            ]);
        }

        // Add the opening balance to the newly created category
        $newCategory->addOpeningBalance();

        // Return a JSON response indicating successful saving with status 200
        return response()->json([
            'status' => 200,
            'message' => 'Successfully saved vendor.'
        ]);
    }

    /**
     * List categories based on specified filters and parameters.
     *
     * @param Request $request The incoming HTTP request containing filters and pagination parameters.
     * @return JsonResponse Returns a JSON response with the status and data.
     */
    public function list(Request $request): JsonResponse
    {
        // Retrieve parameters from the request with default values
        $limit = $request->input('limit', 10);
        $keyword = $request->input('keyword', '');
        $order_by = $request->input('order_by', 'id');
        $order_mode = $request->input('order_mode', 'DESC');

        // Get the session user details
        $sessionUser = SessionUser::getUser();

        // Find the 'account payable' category for the current client company
        $accountPayable = Category::where('client_company_id', $sessionUser['client_company_id'])
            ->where('slug', strtolower(AccountCategory::ACCOUNT_PAYABLE))
            ->first();

        // Return an error response if the category is not found
        if (!$accountPayable instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [account payable] category.'
            ]);
        }

        // Build the query to fetch categories with aggregated transaction amounts
        $result = Category::select('categories.id', 'categories.name', DB::raw('SUM(credit_amount - debit_amount) as amount'), 'categories.opening_balance')
            ->leftJoin('transactions', 'transactions.account_id', '=', 'categories.id')
            ->where('categories.client_company_id', $sessionUser['client_company_id'])
            ->where('categories.parent_category', $accountPayable['id']);

        // Apply keyword filter if provided
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('categories.name', 'LIKE', '%'.$keyword.'%');
            });
        }

        // Apply ordering and pagination
        $result = $result->groupBy('categories.id')
            ->orderBy($order_by, $order_mode)
            ->paginate($limit);

        // Format the amount field for each result item
        foreach ($result as &$data) {
            $data['amount_format'] = !empty($data['amount']) ? number_format($data['amount'], 2) : null;
            $data['opening_balance'] = !empty($data['opening_balance']) ? number_format($data['opening_balance'], 2) : null;
        }

        // Return a successful JSON response with the data
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }

    /**
     * Retrieve a single category based on its ID.
     *
     * @param Request $request The incoming HTTP request containing the category ID.
     * @return JsonResponse Returns a JSON response with the status and data.
     */
    public function single(Request $request): JsonResponse
    {
        // Validate the request to ensure the 'id' parameter is provided and is an integer
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer'
        ]);

        // If validation fails, return a JSON response with error details
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Retrieve the category based on the provided ID
        $result = Category::select('id', 'name', 'opening_balance')->find($request->input('id'));

        // Return a JSON response with the status and the retrieved category data
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }

    /**
     * Update an existing category based on the provided request data.
     *
     * @param Request $request The incoming HTTP request containing category update data.
     * @return JsonResponse Returns a JSON response with the status of the operation.
     */
    public function update(Request $request): JsonResponse
    {
        // Validate the request to ensure 'id', 'name' are provided and 'opening_balance' is a valid number
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'name' => 'required|string',
            'opening_balance' => 'nullable|numeric'
        ]);

        // If validation fails, return a JSON response with error details
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Find the category based on the provided ID
        $category = Category::find($request->input('id'));

        // If the category does not exist, return an error response
        if (!$category instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find vendor.'
            ]);
        }

        // Update the category with the new name and opening balance
        $categoryData = [
            'name' => $request->input('name'),
            'opening_balance' => $request->input('opening_balance') ?? null
        ];

        // Save the updated category using the repository
        $updateCategory = CategoryRepository::updateCategory($category, $categoryData);

        // If the update operation fails, return an error response
        if (!$updateCategory instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot update [vendor].'
            ]);
        }

        // Add opening balance
        $updateCategory->addOpeningBalance();

        // Return a success response indicating the update was successful
        return response()->json([
            'status' => 200,
            'message' => 'Successfully updated vendor.'
        ]);
    }

    /**
     * Delete an existing category based on the provided ID.
     *
     * @param Request $request The incoming HTTP request containing the category ID to delete.
     * @return JsonResponse Returns a JSON response indicating the success or failure of the delete operation.
     */
    public function delete(Request $request): JsonResponse
    {
        // Validate the request to ensure 'id' is provided
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer'
        ]);

        // If validation fails, return a JSON response with error details
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Find the category using the provided ID
        $category = Category::find($request->input('id'));

        // If the category does not exist, return an error response
        if (!$category instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find bank.'
            ]);
        }

        // Perform the deletion of the category
        $category->deleteCategory();

        // Return a success response indicating that the category was successfully deleted
        return response()->json([
            'status' => 200,
            'message' => 'Successfully deleted vendor.'
        ]);
    }

    /**
     * Handles the payment process by validating input, checking available balance,
     * and saving the transaction if sufficient funds are available.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function payment(Request $request): JsonResponse
    {
        // Validate incoming request parameters
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required|integer',
            'payment_id' => 'required|integer',
            'amount' => 'required|numeric'
        ]);

        // If validation fails, return an error response with validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Retrieve the vendor category based on the provided vendor ID
        $vendor = Category::where('id', $request->input('vendor_id'))->first();
        if (!$vendor instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find vendor.'
            ]);
        }

        // Retrieve the payment category based on the provided payment ID
        $paymentCategory = Category::where('id', $request->input('payment_id'))->first();
        if (!$paymentCategory instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find payment method.'
            ]);
        }

        // Check if the payment category has sufficient balance
        $availableBalance = $paymentCategory->checkAvailableBalance($request->input('amount'));
        if (!$availableBalance) {
            return response()->json([
                'status' => 500,
                'errors' => ['payment_id' => ['The balance for '.$paymentCategory['name'].' is not sufficient.']]
            ]);
        }

        // Prepare transaction data for the payment
        $transaction = [
            ['date' => date('Y-m-d'), 'account_id' => $request->input('vendor_id'), 'debit_amount' => $request->input('amount'), 'credit_amount' => 0],
            ['date' => date('Y-m-d'), 'account_id' => $request->input('payment_id'), 'debit_amount' => 0, 'credit_amount' => $request->input('amount')]
        ];

        // Save the transaction
        TransactionRepository::saveTransaction($transaction);

        // Return a success response
        return response()->json([
            'status' => 200,
            'message' => 'Successfully saved payment.'
        ]);
    }

    /**
     * Generate a financial report based on the provided date range and vendor.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public static function report(Request $request): JsonResponse
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'vendor_id' => 'required|integer'
        ]);

        // Return validation errors if validation fails
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $filter = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'vendor_id' => $request->input('vendor_id')
        ];

        $result = ReportRepository::vendorReport($filter);

        // Return the formatted report data and totals in the response
        return response()->json($result);
    }

}
