<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\Module;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Repository\CategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CreditCompanyController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }

        $others = [
            'email' => $inputData['email'] ?? null,
            'phone' => $inputData['phone'] ?? null,
            'address' => $inputData['address'] ?? null,
            'contact_person' => $inputData['contact_person'] ?? null,
        ];
        $data = [
            'name' => $inputData['name'],
            'credit_limit' => $inputData['credit_limit'] ?? null,
            'others' => json_encode($others)
        ];

        $sessionUser = SessionUser::getUser();
        $category = Category::where('slug', strtolower(AccountCategory::ACCOUNT_RECEIVABLE))->where('client_company_id', $sessionUser['client_company_id'])->first();
        if (!$category instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [account receivable] category.']);
        }
        $accountReceivableCategory = CategoryRepository::saveCategory($data, $category['id'], null);
        if (!$accountReceivableCategory instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot saved credit company.']);
        }
        $data = [
            'name' => $request['name'],
            'module_id' => $accountReceivableCategory['id']
        ];
        $category = Category::where('slug', strtolower(AccountCategory::DRIVER_SALE))->where('client_company_id', $sessionUser['client_company_id'])->first();
        if (!$category instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [driver sale] category.']);
        }

        $driverSaleCategory = CategoryRepository::saveCategory($data, $category['id'], Module::DRIVER_SALE);
        if (!$driverSaleCategory instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot saved [driver sale] category.']);
        }
        $category = Category::where('slug', strtolower(AccountCategory::UN_EARNED_REVENUE))->where('client_company_id', $sessionUser['client_company_id'])->first();
        if (!$category instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [un earned revenue] category.']);
        }
        $unEarnedRevenueCategory = CategoryRepository::saveCategory($data, $category['id'], Module::UN_EARNED_REVENUE);
        if (!$unEarnedRevenueCategory instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot saved [un earned revenue] category.']);
        }

        $category = Category::where('slug', strtolower(AccountCategory::UN_AUTHORIZED_BILL))->where('client_company_id', $sessionUser['client_company_id'])->first();
        if (!$category instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [un earned revenue] category.']);
        }
        $unAuthorizedBillCategory = CategoryRepository::saveCategory($data, $category['id'], Module::UN_AUTHORIZED_BILL);
        if (!$unAuthorizedBillCategory instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot saved [un authorized bill] category.']);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully saved credit company.']);
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
        $accountReceivable = Category::select('id')->where('client_company_id', $inputData['session_user']['client_company_id'])->where('slug', strtolower(AccountCategory::ACCOUNT_RECEIVABLE))->first();
        if (!$accountReceivable instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [account receivable] category.']);
        }
        $result = Category::select('id', 'name', 'credit_limit', 'others')
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->where('parent_category', $accountReceivable->id);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('name', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
        foreach ($result as &$data) {
            $others = json_decode($data['others']);
            $data['email'] = $others != null ? $others->email : null;
            $data['phone'] = $others != null ? $others->phone : null;
            $data['contact_person'] = $others != null ? $others->contact_person : null;
            $data['address'] = $others != null ? $others->address : null;
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
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = Category::select('id', 'name', 'others', 'credit_limit')->find($inputData['id']);
        $others = json_decode($result['others']);
        $result['email'] = $others != null ? $others->email : null;
        $result['phone'] = $others != null ? $others->phone : null;
        $result['contact_person'] = $others != null ? $others->contact_person : null;
        $result['address'] = $others != null ? $others->address : null;
        unset($result['others']);
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
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $accountReceivable = Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('slug', strtolower(AccountCategory::ACCOUNT_RECEIVABLE))->first();
        if (!$accountReceivable instanceof Category) {
            return response()->json(['status' => 400, 'error' => 'Cannot find [account receivable] category.']);
        }
        $category = Category::find($inputData['id']);
        if (!$category instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [company].']);
        }
        $driverSaleCategory =  Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('module_id', $category['id'])->where('module', Module::DRIVER_SALE)->first();
        if (!$driverSaleCategory instanceof Category) {
            return response()->json(['status' => 400, 'error' => 'Cannot find [driver sale] category.']);
        }
        $unEarnRevenueCategory =  Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('module_id', $category['id'])->where('module', Module::UN_EARNED_REVENUE)->first();
        if (!$unEarnRevenueCategory instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [un eran revenue] category.']);
        }
        $others = [
            'email' => $inputData['email'] ?? null,
            'phone' => $inputData['phone'] ?? null,
            'address' => $inputData['address'] ?? null,
            'contact_person' => $inputData['contact_person'] ?? null,
        ];
        $data = [
            'name' => $inputData['name'],
            'credit_limit' => $inputData['credit_limit'] ?? null,
            'others' => json_encode($others),
        ];
        $updateCategory = CategoryRepository::updateCategory($category, $data);
        if (!$updateCategory instanceof Category) {
            return response()->json(['status' => 500, 'errors' => 'Cannot updated credit company.']);
        }
        $data = [
            'name' => $inputData['name']
        ];
        $updateCategory = CategoryRepository::updateCategory($driverSaleCategory, $data);
        if (!$updateCategory instanceof Category) {
            return response()->json(['status' => 500, 'errors' => 'Cannot updated category [driver sale].']);
        }
        $updateCategory = CategoryRepository::updateCategory($unEarnRevenueCategory, $data);
        if (!$updateCategory instanceof Category) {
            return response()->json(['status' => 500, 'errors' => 'Cannot updated category [un eran revenue].']);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully updated credit company.']);
    }
}
