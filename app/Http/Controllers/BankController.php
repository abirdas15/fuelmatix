<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\Module;
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
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'name' => 'required|string',
            'opening_balance' => 'nullable|numeric'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $bank = Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('slug', strtolower(AccountCategory::BANK))->first();
        if (!$bank instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [bank] category.']);
        }
        $categoryData = [
            'name' => $inputData['name'],
            'opening_balance' => $inputData['opening_balance'] ?? null,
        ];
        $category = CategoryRepository::saveCategory($categoryData, $bank['id']);
        if (!$category instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot save [bank]']);
        }
        $deleteResponse = $category->deleteOpeningBalance();
        if ($deleteResponse) {
            if (!empty($request['opening_balance'])) {
                $retainEarning = Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('slug', strtolower(AccountCategory::RETAIN_EARNING))->first();
                if ($retainEarning instanceof Category) {
                    $transactionData['linked_id'] = $category['id'];
                    $transactionData['transaction'] = [
                        ['date' => "1970-01-01",  'account_id' => $retainEarning['id'], 'debit_amount' => $request['opening_balance'], 'credit_amount' => 0, 'opening_balance' => 1],
                    ];
                    TransactionController::saveTransaction($transactionData);
                }
            }
        }
        return response()->json(['status' => 200, 'message' => 'Successfully saved bank.']);
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
        $bank = Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('slug', strtolower(AccountCategory::BANK))->first();
        if (!$bank instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [bank] category.']);
        }
        $result = Category::select('id', 'name', 'opening_balance')
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->where('parent_category', $bank->id);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('name', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
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
        $result = Category::select('id', 'name', 'opening_balance')->find($inputData['id']);
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required|integer',
            'name' => 'required|string',
            'opening_balance' => 'nullable|numeric'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $category = Category::find($inputData['id']);
        if (!$category instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [bank].']);
        }
        $categoryData = [
            'name' => $inputData['name'],
            'opening_balance' => $inputData['opening_balance'] ?? null
        ];
        $category = CategoryRepository::updateCategory($category, $categoryData);
        if (!$category instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot updated [bank].']);
        }
        $deleteResponse = $category->deleteOpeningBalance();
        if ($deleteResponse) {
            if (!empty($request['opening_balance'])) {
                $retainEarning = Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('slug', strtolower(AccountCategory::RETAIN_EARNING))->first();
                if ($retainEarning instanceof Category) {
                    $transactionData['linked_id'] = $category['id'];
                    $transactionData['transaction'] = [
                        ['date' => "1970-01-01",  'account_id' => $retainEarning['id'], 'debit_amount' => $request['opening_balance'], 'credit_amount' => 0, 'opening_balance' => 1],
                    ];
                    TransactionController::saveTransaction($transactionData);
                }
            }
        }
        return response()->json(['status' => 200, 'message' => 'Successfully updated bank.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $category = Category::find($inputData['id']);
        if (!$category instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find bank.']);
        }
        $transaction = Transaction::where('account_id', $inputData['id'])->orWhere('linked_id', $inputData['id'])->first();
        if ($transaction instanceof Transaction) {
            return response()->json(['status' => 400, 'message' => 'Cannot delete [bank].']);
        }
        $category->deleteOpeningBalance();
        Category::where('id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted bank.']);
    }
}
