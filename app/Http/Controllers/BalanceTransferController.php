<?php

namespace App\Http\Controllers;

use App\Common\FuelMatixDateTimeFormat;
use App\Common\FuelMatixStatus;
use App\Common\Module;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\BalanceTransfer;
use App\Models\Category;
use App\Repository\TransactionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BalanceTransferController extends Controller
{
    /**
     * Save a new balance transfer between categories.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'from_category_id' => 'required|integer',
            'to_category_id' => 'required|integer',
            'amount' => 'required|numeric',
            'date' => 'required|date'
        ]);

        // Return validation errors if there are any
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Retrieve the currently authenticated user
        $sessionUser = SessionUser::getUser();

        // Find the destination category
        $categoryTo = Category::find($request->input('to_category_id'));
        if (!$categoryTo instanceof Category) {
            return response()->json([
                'status' => 500,
                'errors' => ['to_category_id' => ['Destination category cannot be found.']]
            ]);
        }

        // Find the source category
        $categoryFrom = Category::find($request->input('from_category_id'));
        if (!$categoryFrom instanceof Category) {
            return response()->json([
                'status' => 500,
                'errors' => ['from_category_id' => ['Source category cannot be found.']]
            ]);
        }

        // Check if the source category has enough balance
        $availableBalance = $categoryFrom->checkAvailableBalance($request->input('amount'));
        if (!$availableBalance) {
            return response()->json([
                'status' => 300,
                'message' => 'Not enough balance in source category.'
            ]);
        }

        // Create and save a new balance transfer
        $balanceTransfer = new BalanceTransfer();
        $balanceTransfer->date = $request->input('date');
        $balanceTransfer->from_category_id = $request->input('from_category_id');
        $balanceTransfer->to_category_id = $request->input('to_category_id');
        $balanceTransfer->amount = $request->input('amount');
        $balanceTransfer->remarks = $request->input('remarks') ?? null;
        $balanceTransfer->client_company_id = $sessionUser['client_company_id'];

        if (!$balanceTransfer->save()) {
            return response()->json([
                'status' => 500,
                'message' => 'Cannot save balance transfer'
            ]);
        }

        // Return a success response
        return response()->json([
            'status' => 200,
            'message' => 'Successfully saved balance transfer.'
        ]);
    }
    /**
     * Retrieve a paginated list of balance transfers with optional sorting and pagination.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        // Get the currently authenticated user
        $sessionUser = SessionUser::getUser();

        // Retrieve pagination limit, sorting field, and sorting order from the request
        $limit = $request->input('limit', 10);
        $orderBy = $request->input('order_by', 'balance_transfer.id');
        $orderMode = $request->input('order_mode', 'DESC');

        // Build the query to get balance transfers with related category and user information
        $result = BalanceTransfer::select(
            'balance_transfer.id',
            'balance_transfer.date',
            'balance_transfer.amount',
            'balance_transfer.status',
            'c1.name as from_category_name',
            'c2.name as to_category_name',
            'users.name as approve_by'
        )
            ->leftJoin('categories as c1', 'c1.id', '=', 'balance_transfer.from_category_id')
            ->leftJoin('categories as c2', 'c2.id', '=', 'balance_transfer.to_category_id')
            ->leftJoin('users', 'users.id', '=', 'balance_transfer.approve_by')
            ->where('balance_transfer.client_company_id', $sessionUser['client_company_id']);

        // Apply sorting and pagination
        $result = $result->orderBy($orderBy, $orderMode)
            ->paginate($limit);

        // Format the date and amount for each item in the result
        foreach ($result as &$data) {
            $data['date'] = Helpers::formatDate($data['date'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME);
            $data['amount_format'] = number_format($data['amount'], 2);
        }

        // Return the paginated result as JSON
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }

    /**
     * Retrieve a single balance transfer record by its ID.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function single(Request $request): JsonResponse
    {
        // Validate the incoming request to ensure 'id' is provided and is an integer
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        // If validation fails, return an error response
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Find the balance transfer record by the provided ID
        $result = BalanceTransfer::find($request->input('id'));

        // Return the result in a JSON response
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }

    /**
     * Update an existing balance transfer record.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        // Validate the incoming request to ensure required fields are provided and valid
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'from_category_id' => 'required|integer',
            'to_category_id' => 'required|integer',
            'amount' => 'required|numeric',
            'date' => 'required|date'
        ]);

        // If validation fails, return a 400 Bad Request response
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        // Find the destination category
        $categoryTo = Category::find($request->input('to_category_id'));
        if (!$categoryTo) {
            return response()->json([
                'status' => 404,
                'errors' => ['to_category_id' => ['Destination category cannot be found.']]
            ]);
        }

        // Find the source category
        $categoryFrom = Category::find($request->input('from_category_id'));
        if (!$categoryFrom) {
            return response()->json([
                'status' => 404,
                'errors' => ['from_category_id' => ['Source category cannot be found.']]
            ]);
        }

        // Check if the source category has enough balance
        $availableBalance = $categoryFrom->checkAvailableBalance($request->input('amount'));
        if (!$availableBalance) {
            return response()->json([
                'status' => 400,
                'message' => 'Not enough balance in source category.'
            ]);
        }

        // Find the balance transfer record to update
        $balanceTransfer = BalanceTransfer::find($request->input('id'));
        if (!$balanceTransfer) {
            return response()->json([
                'status' => 404,
                'message' => 'Cannot find balance transfer.'
            ]);
        }

        // Check if the balance transfer is already approved
        if ($balanceTransfer->status == FuelMatixStatus::APPROVE) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot update approved balance transfer.'
            ]);
        }

        // Update the balance transfer record
        $balanceTransfer->date = $request->input('date');
        $balanceTransfer->from_category_id = $request->input('from_category_id');
        $balanceTransfer->to_category_id = $request->input('to_category_id');
        $balanceTransfer->amount = $request->input('amount');
        $balanceTransfer->remarks = $request->input('remarks') ?? null;

        // Save the updated balance transfer record
        if (!$balanceTransfer->save()) {
            return response()->json([
                'status' => 500,
                'message' => 'Cannot update balance transfer.'
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Successfully updated balance transfer.'
        ]);
    }

    /**
     * Approve a balance transfer.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function approve(Request $request): JsonResponse
    {
        // Validate the incoming request to ensure 'id' is provided and is an integer
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        // If validation fails, return a 500 Internal Server Error response
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        $sessionUser = SessionUser::getUser();
        $balanceTransfer = BalanceTransfer::find($request->input('id'));

        // Check if the balance transfer record exists
        if (!$balanceTransfer) {
            return response()->json([
                'status' => 500,
                'message' => 'Cannot find balance transfer.'
            ]);
        }

        // Check if the balance transfer is already approved
        if ($balanceTransfer->status == FuelMatixStatus::APPROVE) {
            return response()->json([
                'status' => 500,
                'message' => 'Balance transfer has already been approved.'
            ]);
        }

        // Find the source category for the balance transfer
        $categoryFrom = Category::find($balanceTransfer->from_category_id);
        if (!$categoryFrom) {
            return response()->json([
                'status' => 500,
                'errors' => ['from_category_id' => ['Source category cannot be found.']]
            ]);
        }

        // Check if the source category has enough balance
        $availableBalance = $categoryFrom->checkAvailableBalance($balanceTransfer->amount);
        if (!$availableBalance) {
            return response()->json([
                'status' => 300,
                'message' => 'Not enough balance in source category.'
            ]);
        }

        // Prepare and save the transaction data
        $transactionData = [
            ['date' => date('Y-m-d', strtotime($balanceTransfer['date'])), 'account_id' => $balanceTransfer['to_category_id'], 'description' => $balanceTransfer['remarks'], 'debit_amount' => $balanceTransfer->amount, 'credit_amount' => 0, 'module' => Module::BALANCE_TRANSFER, 'module_id' => $balanceTransfer->id],
            ['date' => date('Y-m-d', strtotime($balanceTransfer['date'])), 'account_id' => $balanceTransfer['from_category_id'], 'description' => $balanceTransfer['remarks'], 'debit_amount' => 0, 'credit_amount' => $balanceTransfer->amount, 'module' => Module::BALANCE_TRANSFER, 'module_id' => $balanceTransfer->id],
        ];
        TransactionRepository::saveTransaction($transactionData);

        // Update the balance transfer status and save
        $balanceTransfer->status = FuelMatixStatus::APPROVE;
        $balanceTransfer->approve_by = $sessionUser['id'];
        $balanceTransfer->save();

        return response()->json([
            'status' => 200,
            'message' => 'Successfully approved balance transfer.'
        ]);
    }
    /**
     * Delete a balance transfer record.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        // Validate the incoming request to ensure required fields are provided and valid
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        // If validation fails, return a 500 Internal Server Error response
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Find the balance transfer record to delete
        $balanceTransfer = BalanceTransfer::find($request->input('id'));
        if (!$balanceTransfer) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find balance transfer.'
            ]);
        }

        // Check if the balance transfer is already approved
        if ($balanceTransfer->status == FuelMatixStatus::APPROVE) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot delete approved balance transfer.'
            ]);
        }

        // Delete the balance transfer record
        BalanceTransfer::where('id', $request->input('id'))->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Successfully deleted balance transfer.'
        ]);
    }
}
