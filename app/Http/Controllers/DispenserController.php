<?php

namespace App\Http\Controllers;

use App\Models\Dispenser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DispenserController extends Controller
{
    public function save(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'dispenser_name' => 'required',
            'brand' => 'required',
            'serial' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $dispenser = new Dispenser();
        $dispenser->dispenser_name = $inputData['dispenser_name'];
        $dispenser->brand = $inputData['brand'];
        $dispenser->serial = $inputData['serial'];
        if ($dispenser->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully save dispenser.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot save dispenser.']);
    }
    public function list(Request $request)
    {
        $inputData = $request->all();
        $limit = isset($inputData['limit']) ? $inputData['limit'] : 10;
        $keyword = isset($inputData['keyword']) ? $inputData['keyword'] : '';
        $order_by = isset($inputData['order_by']) ? $inputData['order_by'] : 'id';
        $order_mode = isset($inputData['order_mode']) ? $inputData['order_mode'] : 'DESC';
        $result = Dispenser::select('*');
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('dispenser_name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('brand', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('serial', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
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
        $result = Dispenser::find($inputData['id']);
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function update(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
            'dispenser_name' => 'required',
            'brand' => 'required',
            'serial' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $dispenser = Dispenser::find($inputData['id']);
        if ($dispenser == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find dispenser..']);
        }
        $dispenser->dispenser_name = $inputData['dispenser_name'];
        $dispenser->brand = $inputData['brand'];
        $dispenser->serial = $inputData['serial'];
        if ($dispenser->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully update dispenser.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot update dispenser.']);
    }
    public function delete(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $dispenser = Dispenser::find($inputData['id']);
        if ($dispenser == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find dispenser..']);
        }
        Dispenser::where('id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully delete dispenser.']);
    }
}
