<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\Module;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Transaction;
use App\Repository\CategoryRepository;
use App\Repository\TransactionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BankController extends Controller
{
    /**
     * Saves a new bank category with an optional opening balance.
     *
     * This function validates the incoming request data, retrieves the bank category,
     * and saves a new category with the provided name and opening balance.
     * It also adds the opening balance to the category if specified.
     *
     * @param Request $request The incoming HTTP request containing the category data.
     * @return JsonResponse A JSON response indicating success or failure.
     */
    public function save(Request $request): JsonResponse
    {
        // Validate the request data for name (required) and opening_balance (optional)
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

        // Get the current authenticated session user
        $sessionUser = SessionUser::getUser();

        // Retrieve the bank category associated with the user's company
        $bank = Category::where('client_company_id', $sessionUser['client_company_id'])
            ->where('slug', strtolower(AccountCategory::BANK))
            ->first();

        // If the bank category does not exist, return a JSON response with an error message and status 400
        if (!$bank instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [bank] category.'
            ]);
        }

        // Prepare the data for the new category
        $categoryData = [
            'name' => $request->input('name'),
            'opening_balance' => $request->input('opening_balance') ?? null,
        ];

        // Save the new category in the repository under the bank category
        $category = CategoryRepository::saveCategory($categoryData, $bank['id']);

        // If the category could not be saved, return a JSON response with an error message and status 400
        if (!$category instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot save [bank]'
            ]);
        }

        // Add the opening balance to the saved category if it was specified
        $category->addOpeningBalance();

        // Return a JSON response indicating success with status 200
        return response()->json([
            'status' => 200,
            'message' => 'Successfully saved bank.'
        ]);
    }

    /**
     * Retrieves a paginated list of bank categories for the authenticated user's company.
     *
     * This function allows filtering by keyword and supports sorting and pagination.
     *
     * @param Request $request The incoming HTTP request containing filtering, sorting, and pagination parameters.
     * @return JsonResponse A JSON response containing the list of categories or an error message.
     */
    public function list(Request $request): JsonResponse
    {
        // Get the currently authenticated session user
        $sessionUser = SessionUser::getUser();

        // Retrieve the limit for pagination, with a default value of 10 if not provided
        $limit = $request->input('limit', 10);

        // Retrieve the keyword for filtering, with an empty string as default if not provided
        $keyword = $request->input('keyword', '');

        // Retrieve the column to sort by, defaulting to 'id' if not provided
        $order_by = $request->input('order_by', 'id');

        // Retrieve the sort direction, defaulting to 'DESC' if not provided
        $order_mode = $request->input('order_mode', 'DESC');

        // Retrieve the bank category associated with the user's company
        $bank = Category::where('client_company_id', $sessionUser['client_company_id'])
            ->where('slug', strtolower(AccountCategory::BANK))
            ->first();

        // If the bank category does not exist, return a JSON response with an error message and status 400
        if (!$bank instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [bank] category.'
            ]);
        }

        // Prepare the query to select id, name, and opening_balance for categories under the bank
        $result = Category::select('id', 'name', 'opening_balance')
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->where('parent_category', $bank->id);

        // If a keyword is provided, add a filter to search within the 'name' field
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('name', 'LIKE', '%' . $keyword . '%');
            });
        }

        // Order the results by the specified column and direction
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit); // Paginate the results based on the specified limit

        // Return a JSON response with the status 200 and the paginated data
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }

    /**
     * Retrieves a single category by its ID.
     *
     * This function validates the request to ensure the ID is provided and then retrieves
     * the category with the specified ID, returning its id, name, and opening balance.
     *
     * @param Request $request The incoming HTTP request containing the category ID.
     * @return JsonResponse A JSON response containing the category data or validation errors.
     */
    public function single(Request $request): JsonResponse
    {
        // Retrieve all input data from the request
        $inputData = $request->all();

        // Validate that 'id' is required and must be an integer
        $validator = Validator::make($inputData, [
            'id' => 'required|integer'
        ]);

        // If validation fails, return a JSON response with validation errors and status 500
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Retrieve the category by ID, selecting only the id, name, and opening_balance fields
        $result = Category::select('id', 'name', 'opening_balance')->find($inputData['id']);

        // Return a JSON response with the status 200 and the category data
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }

    /**
     * Updates an existing bank category with new data.
     *
     * This function validates the request, checks for the existence of the category by its ID,
     * and updates the category's name and opening balance if provided. It also manages the opening balance.
     *
     * @param Request $request The incoming HTTP request containing the update data.
     * @return JsonResponse A JSON response indicating the success or failure of the update.
     */
    public function update(Request $request): JsonResponse
    {
        // Validate the request data: 'id' is required and must be an integer,
        // 'name' is required and must be a string, 'opening_balance' is optional but must be numeric if provided
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
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

        // Find the category by its ID
        $category = Category::find($request->input('id'));

        // If the category does not exist, return a JSON response with an error message and status 400
        if (!$category instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [bank].'
            ]);
        }

        // Prepare the data for updating the category
        $categoryData = [
            'name' => $request->input('name'),
            'opening_balance' => $request->input('opening_balance') ?? null
        ];

        // Update the category using the repository method
        $category = CategoryRepository::updateCategory($category, $categoryData);

        // If the category could not be updated, return a JSON response with an error message and status 400
        if (!$category instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot update [bank].'
            ]);
        }

        // Add the opening balance to the updated category if specified
        $category->addOpeningBalance();

        // Return a JSON response indicating success with status 200
        return response()->json([
            'status' => 200,
            'message' => 'Successfully updated bank.'
        ]);
    }

    /**
     * Deletes a bank category and its related transactions.
     *
     * This function validates the request, checks for the existence of the category by its ID,
     * deletes associated transactions and the category's opening balance, and then deletes the category itself.
     *
     * @param Request $request The incoming HTTP request containing the category ID to delete.
     * @return JsonResponse A JSON response indicating the success or failure of the deletion.
     */
    public function delete(Request $request): JsonResponse
    {
        // Validate the request data: 'id' is required and must be an integer
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer'
        ]);

        // If validation fails, return a JSON response with validation errors and status 500
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Find the category by its ID
        $category = Category::find($request->input('id'));

        // If the category does not exist, return a JSON response with an error message and status 400
        if (!$category instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find bank.'
            ]);
        }
        $category->deleteCategory();

        // Return a JSON response indicating successful deletion with status 200
        return response()->json([
            'status' => 200,
            'message' => 'Successfully deleted bank.'
        ]);
    }

}
