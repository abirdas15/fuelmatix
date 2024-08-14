<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Repository\CategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'role_id' => 'required|integer',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'cashier_balance' => 'nullable|boolean',
            'opening_balance' => 'nullable|numeric'
        ]);

        // Return validation errors if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Get the currently logged-in user
        $sessionUser = SessionUser::getUser();

        // Create a new User instance
        $user = new User();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->role_id = $request->input('role_id');
        $user->password = bcrypt($request->input('password')); // Hash the password
        $user->phone = $request->input('phone') ?? null; // Set phone if provided, else null
        $user->address = $request->input('address') ?? null; // Set address if provided, else null
        $user->client_company_id = $sessionUser['client_company_id'];
        $user->cashier_balance = !empty($request->input('cashier_balance')) ? 1 : 0; // Set cashier balance flag

        // Save the user and check if it was successful
        if (!$user->save()) {
            return response()->json(['status' => 500, 'message' => 'Cannot save user.']);
        }

        // If cashier_balance is set, handle cash in hand category
        if (!empty($request->input('cashier_balance'))) {
            // Find the "cash in hand" category for the user's company
            $cashInHandCategory = Category::where('client_company_id', $sessionUser['client_company_id'])
                ->where('slug', strtolower(AccountCategory::CASH_IN_HAND))
                ->first();

            if (!$cashInHandCategory instanceof Category) {
                return response()->json(['status' => 400, 'message' => 'Cannot find cash in hand category.']);
            }

            // Prepare data for the new category
            $categoryData = [
                'name' => $request->input('name'),
                'opening_balance' => $request->input('opening_balance', 0) // Default to 0 if not provided
            ];

            // Save or update the cash in hand category
            $cashInHandCategory = CategoryRepository::saveCategory($categoryData, $cashInHandCategory->id, null);

            // Update user's category_id with the new category ID
            if ($cashInHandCategory instanceof Category) {
                $user->category_id = $cashInHandCategory->id;
                $user->save();
            }

            // Add the opening balance to the category
            $cashInHandCategory->addOpeningBalance();
        }

        // Return success response
        return response()->json([
            'status' => 200,
            'message' => 'Successfully saved user.'
        ]);
    }

    /**
     * Retrieves a paginated list of users with optional filtering and sorting.
     *
     * @param Request $request The HTTP request object containing input parameters.
     * @return JsonResponse The JSON response containing the paginated list of users.
     */
    public function list(Request $request): JsonResponse
    {
        // Get the currently logged-in user's session information
        $sessionUser = SessionUser::getUser();

        // Get the limit for pagination, defaulting to 10 if not provided
        $limit = $request->input('limit', 10);

        // Get the column to sort by, defaulting to 'id' if not provided
        $orderBy = $request->input('order_by', 'id');

        // Get the sorting direction, defaulting to 'DESC' if not provided
        $orderMode = $request->input('order_mode', 'DESC');

        // Get the search keyword from the request, defaulting to an empty string if not provided
        $keyword = $request->input('keyword', '');

        // Query the User model with selected columns and join with roles and categories
        $result = User::select('users.id', 'users.name', 'users.email', 'users.phone', 'users.category_id', 'users.address', 'roles.name as role', 'categories.opening_balance')
            ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
            ->leftJoin('categories', 'categories.id', '=', 'users.category_id')
            ->where('users.client_company_id', $sessionUser['client_company_id']);

        // Apply keyword search if a keyword is provided
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('users.name', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('users.email', 'LIKE', '%'.$keyword.'%');
            });
        }

        // Order the results and paginate based on the limit
        $result = $result->orderBy($orderBy, $orderMode)
            ->paginate($limit);

        // Return the paginated results as a JSON response
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }

    /**
     * Retrieves a single user's details based on the provided ID.
     *
     * @param Request $request The HTTP request object containing the user ID.
     * @return JsonResponse The JSON response containing the user's details.
     */
    public function single(Request $request): JsonResponse
    {
        // Validate the request input to ensure the 'id' is provided and is an integer
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer'
        ]);

        // If validation fails, return a JSON response with status 500 and validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Retrieve the user details based on the provided ID
        $result = User::select('id', 'name', 'email', 'phone', 'address', 'cashier_balance', 'role_id', 'category_id')
            ->where('id', $request->input('id'))
            ->first();

        // Initialize 'opening_balance' to null
        $result['opening_balance'] = null;

        // If the user has a category ID, find the category and set the 'opening_balance'
        if (!empty($result['category_id'])) {
            $category = Category::find($result['category_id']);
            if ($category instanceof Category) {
                $result['opening_balance'] = $category['opening_balance'];
            }
        }

        // Return the user's details as a JSON response
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }


    /**
     * Updates a user's details based on the provided input.
     *
     * @param Request $request The HTTP request object containing the user details to update.
     * @return JsonResponse The JSON response indicating the result of the update operation.
     */
    public function update(Request $request): JsonResponse
    {
        // Validate the request input to ensure all required fields are provided and correctly formatted
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'name' => 'required|string',
            'role_id' => 'required|integer',
            'email' => 'required|email',
            'password' => 'sometimes|min:6',
            'opening_balance' => 'nullable|numeric'
        ]);

        // If validation fails, return a JSON response with status 500 and validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Retrieve the session user information
        $sessionUser = SessionUser::getUser();

        // Check if another user with the same email exists within the same company
        $user = User::where('client_company_id', $sessionUser['client_company_id'])
            ->where('email', $request->input('email'))
            ->where('id', '!=', $request->input('id'))
            ->first();

        // If another user with the same email exists, return an error response
        if ($user instanceof User) {
            return response()->json([
                'status' => 500,
                'errors' => ['email' => ['The email has already been taken.']]
            ]);
        }

        // Find the user to update
        $user = User::find($request->input('id'));

        // If the user is not found, return an error response
        if (!$user instanceof User) {
            return response()->json([
                'status' => 500,
                'message' => 'Cannot find user'
            ]);
        }

        // Update the user details with the provided data
        $user->name = $request->input('name');
        $user->role_id = $request->input('role_id');
        $user->email = $request->input('email');

        // Update the password if provided
        if (!empty($request->input('password'))) {
            $user->password = bcrypt($request->input('password'));
        }

        // Update optional fields
        $user->phone = $request->input('phone') ?? null;
        $user->address = $request->input('address') ?? null;
        $user->client_company_id = $sessionUser['client_company_id'];
        $user->cashier_balance = !empty($request->input('cashier_balance')) ? 1 : 0;

        // Save the user and return an error response if saving fails
        if (!$user->save()) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot update user.'
            ]);
        }

        // Prepare category data for cash-in-hand category
        $categoryData = [
            'name' => $request->input('name'),
            'opening_balance' => $request->input('opening_balance'),
        ];

        // Find or create the cash-in-hand category
        $cashInHandCategory = Category::where('client_company_id', $sessionUser['client_company_id'])
            ->where('slug', strtolower(AccountCategory::CASH_IN_HAND))
            ->first();

        // If the cash-in-hand category is not found, return an error response
        if (!$cashInHandCategory instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [cash in hand] category'
            ]);
        }

        // Handle cash-in-hand category creation or update based on the user's cashier balance
        if (!empty($request->input('cashier_balance')) && empty($user->category_id)) {
            $cashInHandCategory = CategoryRepository::saveCategory($categoryData, $cashInHandCategory->id, null);
            if ($cashInHandCategory instanceof Category) {
                $user->category_id = $cashInHandCategory->id;
                $user->save();
            }
        } else if (!empty($request->input('cashier_balance')) && !empty($user->category_id)) {
            $category = Category::find($user->category_id);
            if (!$category instanceof Category) {
                $cashInHandCategory = CategoryRepository::saveCategory($categoryData, $cashInHandCategory->id, null);
                $user->category_id = $cashInHandCategory->id;
                $user->save();
            } else {
                $cashInHandCategory = CategoryRepository::updateCategory($category, $categoryData);
            }
        }

        // Add opening balance if applicable
        if ($cashInHandCategory instanceof Category && !empty($request->input('opening_balance'))) {
            $cashInHandCategory->addOpeningBalance();
        }

        // Return a success response
        return response()->json([
            'status' => 200,
            'message' => 'Successfully updated user.'
        ]);
    }

    /**
     * Deletes a user based on the provided ID.
     *
     * @param Request $request The HTTP request object containing the user ID to delete.
     * @return JsonResponse The JSON response indicating the result of the delete operation.
     */
    public function delete(Request $request): JsonResponse
    {
        // Retrieve all request data
        $requestData = $request->all();

        // Validate the request input to ensure 'id' is provided
        $validator = Validator::make($requestData, [
            'id' => 'required'
        ]);

        // If validation fails, return a JSON response with status 500 and validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Find the user by ID
        $user = User::find($requestData['id']);

        // If the user is not found, return an error response
        if (!$user instanceof User) {
            return response()->json([
                'status' => 500,
                'message' => 'Cannot find user.'
            ]);
        }

        // If the user has a category ID, find the category and delete it
        if (!empty($user['category_id'])) {
            $category = Category::where('id', $user['category_id'])->first();
            if ($category) {
                $category->deleteCategory();
            }
        }

        // Delete the user
        User::where('id', $requestData['id'])->delete();

        // Return a success response
        return response()->json([
            'status' => 200,
            'message' => 'Successfully deleted user.'
        ]);
    }

}
