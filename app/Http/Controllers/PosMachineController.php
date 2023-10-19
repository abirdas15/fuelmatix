<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Models\Category;
use App\Models\Transaction;
use App\Repository\CategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PosMachineController extends Controller
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
            'tds' => 'required',
            'bank_category_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $posMachine = Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('slug', strtolower(AccountCategory::POS_MACHINE))->first();
        if (!$posMachine instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find account pos machine group.']);
        }
        $others = [
            'tds' => $inputData['tds'] ?? null,
            'bank_category_id' => $inputData['bank_category_id'] ?? null,
        ];
        $data = [
            'name' => $inputData['name'],
            'others' => json_encode($others)
        ];
        $newPosMachine = CategoryRepository::saveCategory($data, $posMachine['id']);
        if (!$newPosMachine instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot saved pos machine.']);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully saved pos machine.']);
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
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required|integer',
            'name' => 'required|string',
            'tds' => 'required',
            'bank_category_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $category = Category::find($inputData['id']);
        if (!$category instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [pos machine].']);
        }
        $posMachine = Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('name', strtolower(AccountCategory::POS_MACHINE))->first();
        if (!$posMachine instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find account pos machine group.']);
        }
        $others = [
            'tds' => $inputData['tds'] ?? null,
            'bank_category_id' => $inputData['bank_category_id'] ?? null,
        ];

        $data = [
            'name' => $inputData['name'],
            'others' => json_encode($others)
        ];
        $updatePosMachine = CategoryRepository::updateCategory($category, $data);
        if (!$updatePosMachine instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot updated [pos machine].']);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully updated pos machine.']);
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
