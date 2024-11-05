<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\Module;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\CompanyProductPrice;
use App\Models\Sale;
use App\Models\Transaction;
use App\Repository\CategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CreditCompanyController extends Controller
{
    /**
     * Save a new account with associated categories.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email',
            'phone' => 'required|string',
            'opening_balance' => 'nullable|numeric',
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Prepare additional fields for the 'others' JSON column
        $others = [
            'email' => $request->input('email') ?? null,
            'phone' => $request->input('phone') ?? null,
            'address' => $request->input('address') ?? null,
            'contact_person' => $request->input('contact_person') ?? null,
        ];

        // Prepare data for saving the account category
        $data = [
            'name' => $request->input('name'),
            'credit_limit' => $request->input('credit_limit') ?? null,
            'opening_balance' => $request->input('opening_balance') ?? null,
            'others' => json_encode($others)
        ];

        // Retrieve the current session user
        $sessionUser = SessionUser::getUser();

        if (!empty($request->input('parent_id'))) {
            $category = Category::where('id', $request->input('parent_id'))
                ->where('client_company_id', $sessionUser['client_company_id'])
                ->first();
        } else {
            // Find the 'account receivable' category for the current company
            $category = Category::where('slug', strtolower(AccountCategory::ACCOUNT_RECEIVABLE))
                ->where('client_company_id', $sessionUser['client_company_id'])
                ->first();
        }

        // Return an error if the 'account receivable' category is not found
        if (!$category instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [account receivable] category.'
            ]);
        }

        // Save the account receivable category using the prepared data
        $accountReceivableCategory = CategoryRepository::saveCategory($data, $category['id'], null);

        // Return an error if the account receivable category could not be saved
        if (!$accountReceivableCategory instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot save credit company.'
            ]);
        }

        // Add the opening balance to the account receivable category
        $accountReceivableCategory->addOpeningBalance();

        // Prepare data for the 'driver sale' category
        $data = [
            'name' => $request['name'],
            'module_id' => $accountReceivableCategory['id']
        ];

        // Find the 'driver sale' category for the current company
        $category = Category::where('slug', strtolower(AccountCategory::DRIVER_SALE))
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first();

        // Return an error if the 'driver sale' category is not found
        if (!$category instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [driver sale] category.'
            ]);
        }

        // Save the 'driver sale' category using the prepared data
        $driverSaleCategory = CategoryRepository::saveCategory($data, $category['id'], Module::DRIVER_SALE);

        // Return an error if the 'driver sale' category could not be saved
        if (!$driverSaleCategory instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot save [driver sale] category.'
            ]);
        }

        // Find the 'un earned revenue' category for the current company
        $category = Category::where('slug', strtolower(AccountCategory::UN_EARNED_REVENUE))
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first();

        // Return an error if the 'un earned revenue' category is not found
        if (!$category instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [un earned revenue] category.'
            ]);
        }

        // Save the 'un earned revenue' category using the prepared data
        $unEarnedRevenueCategory = CategoryRepository::saveCategory($data, $category['id'], Module::UN_EARNED_REVENUE);

        // Return an error if the 'un earned revenue' category could not be saved
        if (!$unEarnedRevenueCategory instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot save [un earned revenue] category.'
            ]);
        }

        // Find the 'un authorized bill' category for the current company
        $category = Category::where('slug', strtolower(AccountCategory::UN_AUTHORIZED_BILL))
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first();

        // Return an error if the 'un authorized bill' category is not found
        if (!$category instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [un authorized bill] category.'
            ]);
        }

        // Save the 'un authorized bill' category using the prepared data
        $unAuthorizedBillCategory = CategoryRepository::saveCategory($data, $category['id'], Module::UN_AUTHORIZED_BILL);

        // Return an error if the 'un authorized bill' category could not be saved
        if (!$unAuthorizedBillCategory instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot save [un authorized bill] category.'
            ]);
        }

        // Save the product price associated with the category
        $category->saveProductPrice($request->input('product_price'));

        // Return a success message after all operations are completed
        return response()->json([
            'status' => 200,
            'message' => 'Successfully saved credit company.'
        ]);
    }
    /**
     * Retrieve a paginated list of account receivable categories.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        // Retrieve query parameters with default values
        $limit = $request->input('limit', 10);
        $keyword = $request->input('keyword', '');
        $order_by = $request->input('order_by', 'id');
        $order_mode = $request->input('order_mode', 'DESC');

        // Retrieve the current session user
        $sessionUser = SessionUser::getUser();

        // Find the 'account receivable' category for the current company
        $accountReceivable = Category::select('id')
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->where('slug', strtolower(AccountCategory::ACCOUNT_RECEIVABLE))
            ->first();

        // Return an error if the 'account receivable' category is not found
        if (!$accountReceivable instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [account receivable] category.'
            ]);
        }

        // Query to select relevant fields from the Category model
        $result = Category::select('categories.id', 'categories.name', 'categories.credit_limit', 'categories.others', 'categories.opening_balance', 'c2.name as parent_company', 'categories.parent_category')
            ->leftJoin('categories as c2', 'c2.id', '=', 'categories.parent_category')
            ->where('categories.client_company_id', $sessionUser['client_company_id'])
            ->whereJsonContains('categories.category_ids', $accountReceivable->id)
            ->where('categories.id', '!=', $accountReceivable->id);

        // Apply a keyword filter if provided
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('categories.name', 'LIKE', '%' . $keyword . '%');
            });
        }

        // Apply ordering to the result set
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);

        // Process each result to extract the 'others' JSON data
        foreach ($result as &$data) {
            if ($data['parent_category'] == $accountReceivable->id) {
                $data['parent_company'] = null;
            }
            $others = json_decode($data['others']);
            $data['email'] = $others != null ? $others->email : null;
            $data['phone'] = $others != null ? $others->phone : null;
            $data['contact_person'] = $others != null ? $others->contact_person : null;
            $data['address'] = $others != null ? $others->address : null;
            unset($data['others']); // Remove the raw 'others' field from the result
            $data['opening_balance'] = !empty($data['opening_balance']) ? number_format($data['opening_balance'], $sessionUser['currency_precision']) : null;
        }

        // Return the paginated result set as a JSON response
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }

    /**
     * Retrieve the details of a single category by its ID.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function single(Request $request): JsonResponse
    {
        // Validate the request to ensure 'id' is provided
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Find the category by ID and select specific fields
        $result = Category::select('id', 'name', 'others', 'credit_limit', 'opening_balance', 'parent_category as parent_id')
            ->find($request->input('id'));

        // Decode the 'others' JSON field to extract additional information
        $others = json_decode($result['others']);
        $result['email'] = $others != null ? $others->email : null;
        $result['phone'] = $others != null ? $others->phone : null;
        $result['contact_person'] = $others != null ? $others->contact_person : null;
        $result['address'] = $others != null ? $others->address : null;

        // Remove the raw 'others' field from the result
        unset($result['others']);

        // Retrieve product prices associated with the company and category
        $result['product_price'] = CompanyProductPrice::select('product_id', 'price')
            ->where('company_id', $request->input('id'))
            ->get()
            ->toArray();

        // Return the category details as a JSON response
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }
    public function delete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }
        $transaction = Transaction::where('account_id', $request->input('id'))->first();
        if ($transaction instanceof Transaction) {
            return response()->json([
                'status' => 300,
                'message' => 'This company already have many transaction. Please delete transaction first.'
            ]);
        }
        $sale = Sale::where('payment_category_id', $request->input('id'))->first();
        if ($sale instanceof Sale) {
            return response()->json([
                'status' => 300,
                'message' => 'This company already have many sale. Please delete sale first.'
            ]);
        }
        Category::where('id', $request->input('id'))->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Successfully deleted credit company.'
        ]);
    }
    /**
     * Update a category and related categories based on the provided data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'name' => 'required|string',
            'email' => 'required|string|email',
            'phone' => 'required|string',
            'opening_balance' => 'nullable|numeric'
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Get the currently authenticated user
        $sessionUser = SessionUser::getUser();

        $accountReceivable = Category::where('slug', strtolower(AccountCategory::ACCOUNT_RECEIVABLE))
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first();

        // Return an error if the 'Account Receivable' category is not found
        if (!$accountReceivable instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [account receivable] category.'
            ]);
        }

        // Find the category to be updated by ID
        $category = Category::find($request->input('id'));
        if (!$category instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [company].'
            ]);
        }

        // Find related categories (Driver Sale and Un-Earned Revenue)
        $driverSaleCategory = Category::where('client_company_id', $sessionUser['client_company_id'])
            ->where('module_id', $category['id'])
            ->where('module', Module::DRIVER_SALE)
            ->first();
        if (!$driverSaleCategory instanceof Category) {
            return response()->json([
                'status' => 400,
                'error' => 'Cannot find [driver sale] category.'
            ]);
        }

        $unEarnRevenueCategory = Category::where('client_company_id', $sessionUser['client_company_id'])
            ->where('module_id', $category['id'])
            ->where('module', Module::UN_EARNED_REVENUE)
            ->first();
        if (!$unEarnRevenueCategory instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [un earned revenue] category.'
            ]);
        }

        // Prepare the 'others' data to be stored as a JSON string
        $others = [
            'email' => $request->input('email') ?? null,
            'phone' => $request->input('phone') ?? null,
            'address' => $request->input('address') ?? null,
            'contact_person' => $request->input('contact_person') ?? null,
        ];

        // Prepare the data to update the category
        $data = [
            'name' => $request->input('name'),
            'credit_limit' => $request->input('credit_limit') ?? null,
            'opening_balance' => $request->input('opening_balance') ?? null,
            'others' => json_encode($others),
        ];

        // Update the main category
        $updateCategory = CategoryRepository::updateAccountReceivableCategory($category, $data, $request->input('parent_id'));
        if (!$updateCategory instanceof Category) {
            return response()->json([
                'status' => 500,
                'errors' => 'Cannot update credit company.'
            ]);
        }

        // Add the opening balance to the category
        $updateCategory->addOpeningBalance();

        // Save the product prices associated with the category
        $updateCategory->saveProductPrice($request->input('product_price'));

        // Update the related 'Driver Sale' category
        $data = ['name' => $request->input('name')];
        $updateCategory = CategoryRepository::updateCategory($driverSaleCategory, $data);
        if (!$updateCategory instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot update category [driver sale].'
            ]);
        }

        // Update the related 'Un-Earned Revenue' category
        $updateCategory = CategoryRepository::updateCategory($unEarnRevenueCategory, $data);
        if (!$updateCategory instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot update category [un earned revenue].'
            ]);
        }

        // Return a success message
        return response()->json([
            'status' => 200,
            'message' => 'Successfully updated credit company.'
        ]);
    }

}
