<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\FuelMatixDateTimeFormat;
use App\Common\Module;
use App\Helpers\Helpers;
use App\Models\Category;
use App\Models\Transaction;
use App\Repository\CategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'name' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $accountPayable = Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('slug', strtolower(AccountCategory::ACCOUNT_PAYABLE))->first();
        if (!$accountPayable instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [account payable] category.']);
        }
        $categoryData = [
            'name' => $inputData['name'],
        ];
        $newCategory = CategoryRepository::saveCategory($categoryData, $accountPayable['id']);
        if (!$newCategory instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot saved [vendor].']);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully saved vendor.']);
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
        $accountPayable = Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('slug', strtolower(AccountCategory::ACCOUNT_PAYABLE))->first();
        if (!$accountPayable instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [account payable] category.']);
        }
        $result = Category::select('categories.id', 'categories.name', DB::raw('SUM(debit_amount - credit_amount) as amount'))
            ->leftJoin('transactions', 'transactions.account_id', '=', 'categories.id')
            ->where('categories.client_company_id', $inputData['session_user']['client_company_id'])
            ->where('parent_category', $accountPayable['id']);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('categories.name', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->groupBy('categories.id')->orderBy($order_by, $order_mode)
            ->paginate($limit);
        foreach ($result as &$data) {
            $data['amount_format'] = !empty($data['amount']) ? number_format($data['amount'], 2) : null;
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
        $result = Category::select('id', 'name')->find($inputData['id']);
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
            'id' => 'required',
            'name' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $category = Category::find($inputData['id']);
        if (!$category instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find bank.']);
        }
        $category->name = $inputData['name'];
        $categoryData = [
            'name' => $inputData['name'],
        ];
        $updateCategory = CategoryRepository::updateCategory($category, $categoryData);
        if (!$updateCategory instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot updated [vendor].']);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully updated vendor.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $transaction = Transaction::where('account_id', $inputData['id'])->orWhere('linked_id', $inputData['id'])->first();
        if ($transaction instanceof Transaction) {
            return response()->json(['status' => 400, 'message' => 'Cannot deleted [vendor].']);
        }
        Category::where('id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted vendor.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function payment(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'vendor_id' => 'required|integer',
            'payment_id' => 'required|integer',
            'amount' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $transaction['linked_id'] = $requestData['vendor_id'];
        $transaction['transaction'] = [
            ['date' => date('Y-m-d'), 'account_id' => $requestData['payment_id'], 'debit_amount' => $requestData['amount'], 'credit_amount' => 0, 'module' => Module::INVOICE_PAYMENT, 'module_id' => $requestData['vendor_id']]
        ];
        TransactionController::saveTransaction($transaction);
        return response()->json(['status' => 200, 'message' => 'Successfully save payment.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     * */
    public static function report(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'start_date' => 'required',
            'end_date' => 'required',
            'vendor_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = Transaction::select('transactions.id','date', DB::raw('SUM(credit_amount) as bill'), DB::raw('SUM(debit_amount) as paid'), 'categories.name as product')
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->whereBetween('date', [$requestData['start_date'], $requestData['end_date']])
            ->where('linked_id', $requestData['vendor_id'])
            ->groupBy('account_id')
            ->get()
            ->toArray();
        $total['bill'] = 0;
        $total['paid'] = 0;
        $total['balance'] = 0;
        $balance  = 0;
        foreach ($result as $key => &$data) {
            $data['product_name'] = '';
            $data['payment_method'] = '';
            if ($data['bill'] > 0) {
                $data['product_name'] = $data['product'];
            } else if ($data['paid'] > 0) {
                $data['payment_method'] = $data['product'];
            }
            $balance =  $balance + $data['bill'] - $data['paid'];
            $data['balance'] = $balance;
            $total['bill'] += $data['bill'];
            $total['paid'] += $data['paid'];
            $total['balance'] += $data['balance'];
            $data['date'] = Helpers::formatDate($data['date'], FuelMatixDateTimeFormat::STANDARD_DATE);
            $data['bill'] = number_format($data['bill'], 2);
            $data['paid'] = number_format($data['paid'], 2);
            $data['balance'] = number_format($data['balance'], 2);
        }
        $total['bill'] = number_format($total['bill'], 2);
        $total['paid'] = number_format($total['paid'], 2);
        $total['balance'] = number_format($balance, 2);
        return response()->json(['status' => 200, 'data' => $result, 'total' => $total]);
    }
}
