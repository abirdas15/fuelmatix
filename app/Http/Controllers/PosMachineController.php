<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\Module;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Transaction;
use App\Repository\CategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PosMachineController extends Controller
{
    /**
     * Save a new POS machine and bank expense category based on the request data.
     *
     * @param Request $request The HTTP request containing the data to save.
     * @return JsonResponse JSON response indicating the success or failure of the operation.
     */
    public function save(Request $request): JsonResponse
    {
        // Validate the incoming request data.
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'tds' => 'required|numeric',
            'bank_category_id' => 'required|integer',
        ]);

        // If validation fails, return a JSON response with status 500 and the validation errors.
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Get the session user.
        $sessionUser = SessionUser::getUser();

        // Find the POS Machine category for the current client's company, identified by a specific slug.
        $posMachine = Category::where('client_company_id', $sessionUser['client_company_id'])
            ->where('slug', strtolower(AccountCategory::POS_MACHINE))
            ->first();

        // If the POS Machine category does not exist, create it under the Current Assets group.
        if (!$posMachine instanceof Category) {
            $posMachine = CategoryRepository::saveCategoryByParentGroup(AccountCategory::POS_MACHINE, AccountCategory::CURRENT_ASSETS);
        }

        // If the POS Machine category could not be created, return a JSON response with status 400 and an error message.
        if (!$posMachine instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Something went wrong!'
            ]);
        }

        // Find the Bank Expense category for the current client's company, identified by a specific slug.
        $bankExpense = Category::where('client_company_id', $sessionUser['client_company_id'])
            ->where('slug', strtolower(AccountCategory::BANK_EXPENSE))
            ->first();

        // If the Bank Expense category does not exist, create it under the Expenses group.
        if (!$bankExpense instanceof Category) {
            $bankExpense = CategoryRepository::saveCategoryByParentGroup(AccountCategory::BANK_EXPENSE, AccountCategory::EXPENSES);
        }

        // If the Bank Expense category could not be created, return a JSON response with status 400 and an error message.
        if (!$bankExpense instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Something went wrong!'
            ]);
        }

        // Prepare the data for the new POS Machine category.
        $data = [
            'name' => $request->input('name'),
            'others' => json_encode([
                'tds' => $request->input('tds'),
                'bank_category_id' => $request->input('bank_category_id'),
            ]),
        ];

        // Save the new POS Machine category.
        $newPosMachine = CategoryRepository::saveCategory($data, $posMachine['id'], Module::POS_MACHINE);

        // If the new POS Machine category could not be saved, return a JSON response with status 400 and an error message.
        if (!$newPosMachine instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot save POS machine.'
            ]);
        }

        // Save the new Bank Expense category, associating it with the POS Machine module.
        $newBankExpense = CategoryRepository::saveCategory([
            'name' => $request->input('name'),
            'module_id' => $newPosMachine['id'],
        ], $bankExpense['id'], Module::POS_MACHINE);

        // If the new Bank Expense category could not be saved, return a JSON response with status 400 and an error message.
        if (!$newBankExpense instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot save POS machine.'
            ]);
        }

        // Return a JSON response indicating the POS machine and bank expense categories were saved successfully.
        return response()->json([
            'status' => 200,
            'message' => 'Successfully saved POS machine.'
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $limit = $inputData['limit'] ?? 10;
        $keyword = $inputData['keyword'] ?? '';
        $order_by = $inputData['order_by'] ?? 'id';
        $order_mode = $inputData['order_mode'] ?? 'DESC';
        $posMachineCategory = Category::select('id')->where('client_company_id', $inputData['session_user']['client_company_id'])->where('slug', strtolower(AccountCategory::POS_MACHINE))->first();
        $result = Category::select('id', 'name', 'others')
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->where('parent_category', $posMachineCategory->id);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('name', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
        foreach ($result as &$data) {
            $others = json_decode($data['others']);
            $data['tds'] = $others != null ? $others->tds : null;
            $bank_category_id = $others != null ? $others->bank_category_id : null;
            $data['bank_name'] = '';
            if (!empty($bank_category_id)) {
                $category = Category::find($bank_category_id);
                $data['bank_name'] = $category->name;
            }
            unset($data['others']);
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function single(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = Category::select('id', 'name', 'others')->find($inputData['id']);
        $others = json_decode($result['others']);
        $result['tds'] = $others != null ? $others->tds : null;
        $result['bank_category_id'] = $others != null ? $others->bank_category_id : null;
        unset($result['others']);
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * Handles the update of a POS Machine category and associated categories.
     *
     * @param Request $request The incoming request containing update data.
     * @return JsonResponse A JSON response indicating the success or failure of the operation.
     */
    public function update(Request $request): JsonResponse
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'name' => 'required|string',
            'tds' => 'required|numeric',
            'bank_category_id' => 'required|integer',
        ]);

        // If validation fails, return an error response with the validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Find the category by ID
        $category = Category::find($request->input('id'));
        if (!$category instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find [pos machine].'
            ]);
        }

        // Get the session user information
        $sessionUser = SessionUser::getUser();

        // Retrieve or create the POS Machine category
        $posMachine = Category::where('client_company_id', $sessionUser['client_company_id'])
            ->where('name', strtolower(AccountCategory::POS_MACHINE))
            ->first();
        if (!$posMachine instanceof Category) {
            $posMachine = CategoryRepository::saveCategoryByParentGroup(AccountCategory::POS_MACHINE, AccountCategory::CURRENT_ASSETS);
            if (!$posMachine instanceof Category) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Something went wrong!'
                ]);
            }
        }

        // Retrieve or create the Bank Expense category
        $bankExpense = Category::where('client_company_id', $sessionUser['client_company_id'])
            ->where('slug', strtolower(AccountCategory::BANK_EXPENSE))
            ->first();
        if (!$bankExpense instanceof Category) {
            $bankExpense = CategoryRepository::saveCategoryByParentGroup(AccountCategory::BANK_EXPENSE, AccountCategory::EXPENSES);
            if (!$bankExpense instanceof Category) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Something went wrong!'
                ]);
            }
        }

        // Prepare the data for updating the category
        $data = [
            'name' => $request->input('name'),
            'others' => json_encode([
                'tds' => $request->input('tds'),
                'bank_category_id' => $request->input('bank_category_id'),
            ])
        ];

        // Update the POS Machine category
        $updatePosMachine = CategoryRepository::updateCategory($category, $data);
        if (!$updatePosMachine instanceof Category) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot updated [pos machine].'
            ]);
        }

        // Check and update or create the related expense category
        $expenseCategory = Category::where('module', Module::POS_MACHINE)
            ->where('module_id', $category->id)
            ->first();
        if (!$expenseCategory instanceof Category) {
            $bankExpense->saveSubCategory($category);
        } else {
            CategoryRepository::updateCategory($expenseCategory, ['name' => $category->name]);
        }

        // Return a successful response
        return response()->json([
            'status' => 200,
            'message' => 'Successfully updated pos machine.'
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $transaction = Transaction::where('account_id', $requestData['id'])->where('linked_id', $requestData['id'])->first();
        if ($transaction instanceof Transaction) {
            return response()->json(['status' => 400, 'message' => 'Cannot delete [pos machine]']);
        }
        Category::where('id', $requestData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully delete pos machine.']);
    }
}
