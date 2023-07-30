<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PosMachineController extends Controller
{
    public function save(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'name' => 'required',
            'tds' => 'required',
            'bank_category_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $posMachine = Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('category', AccountCategory::POS_MACHINE)->first();
        if ($posMachine == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find account pos machine group.']);
        }
        $others = [
            'tds' => $inputData['tds'] ?? null,
            'bank_category_id' => $inputData['bank_category_id'] ?? null,
        ];
        $category_hericy = json_decode($posMachine['category_hericy']);
        array_push($category_hericy, $inputData['name']);
        $category_hericy = json_encode($category_hericy);
        $category = new Category();
        $category->category = $inputData['name'];
        $category->parent_category = $posMachine->id;
        $category->type = $posMachine->type;
        $category->category_hericy = $category_hericy;
        $category->others = json_encode($others);
        $category->client_company_id = $inputData['session_user']['client_company_id'];
        if ($category->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully saved pos machine.']);
        }
        return response()->json(['status' => 500, 'errors' => 'Cannot saved pos machine.']);
    }
    public function list(Request $request)
    {
        $inputData = $request->all();
        $limit = isset($inputData['limit']) ? $inputData['limit'] : 10;
        $keyword = isset($inputData['keyword']) ? $inputData['keyword'] : '';
        $order_by = isset($inputData['order_by']) ? $inputData['order_by'] : 'id';
        $order_mode = isset($inputData['order_mode']) ? $inputData['order_mode'] : 'DESC';
        $accountReceivable = Category::select('id')->where('client_company_id', $inputData['session_user']['client_company_id'])->where('category', AccountCategory::POS_MACHINE)->first();
        $result = Category::select('id', 'category as name', 'others')
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->where('parent_category', $accountReceivable->id);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('category', 'LIKE', '%'.$keyword.'%');
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
                $data['bank_name'] = $category->category;
            }
            unset($data['others']);
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function single(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = Category::select('id', 'category as name', 'others')->find($inputData['id']);
        $others = json_decode($result['others']);
        $result['tds'] = $others != null ? $others->tds : null;
        $result['bank_category_id'] = $others != null ? $others->bank_category_id : null;
        unset($result['others']);
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function update(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
            'name' => 'required',
            'tds' => 'required',
            'bank_category_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $category = Category::find($inputData['id']);
        if ($category == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find pos machine.']);
        }
        $posMachine = Category::where('client_company_id', $inputData['session_user']['client_company_id'])->where('category', AccountCategory::POS_MACHINE)->first();
        if ($posMachine == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find account pos machine group.']);
        }
        $others = [
            'tds' => $inputData['tds'] ?? null,
            'bank_category_id' => $inputData['bank_category_id'] ?? null,
        ];
        $category_hericy = json_decode($posMachine['category_hericy']);
        array_push($category_hericy, $inputData['name']);
        $category_hericy = json_encode($category_hericy);
        $category->category = $inputData['name'];
        $category->category_hericy = $category_hericy;
        $category->others = json_encode($others);
        if ($category->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully updated pos machine.']);
        }
        return response()->json(['status' => 500, 'errors' => 'Cannot updated pos machine.']);
    }
}
