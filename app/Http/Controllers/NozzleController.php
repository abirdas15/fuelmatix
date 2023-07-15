<?php

namespace App\Http\Controllers;

use App\Models\Nozzle;
use App\Models\NozzleReading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NozzleController extends Controller
{
    public function save(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'name' => 'required',
            'dispenser_id' => 'required'
        ],[
            'dispenser_id.required' => 'The dispenser field is required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $nozzle = new Nozzle();
        $nozzle->name = $inputData['name'];
        $nozzle->dispenser_id = $inputData['dispenser_id'];
        $nozzle->opening_stock = $inputData['opening_stock'] ?? null;
        $nozzle->client_company_id = $inputData['session_user']['client_company_id'];
        if ($nozzle->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully save nozzle.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot save nozzle.']);
    }
    public function list(Request $request)
    {
        $inputData = $request->all();
        $limit = isset($inputData['limit']) ? $inputData['limit'] : 10;
        $keyword = isset($inputData['keyword']) ? $inputData['keyword'] : '';
        $order_by = isset($inputData['order_by']) ? $inputData['order_by'] : 'id';
        $order_mode = isset($inputData['order_mode']) ? $inputData['order_mode'] : 'DESC';
        $result = Nozzle::select('nozzles.id', 'nozzles.name', 'dispensers.dispenser_name')
            ->leftJoin('dispensers', 'dispensers.id', '=', 'nozzles.dispenser_id')
            ->where('nozzles.client_company_id', $inputData['session_user']['client_company_id']);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('nozzles.name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('dispensers.dispenser_name', 'LIKE', '%'.$keyword.'%');
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
        $result = Nozzle::find($inputData['id']);
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function update(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
            'name' => 'required',
            'dispenser_id' => 'required'
        ],[
            'dispenser_id.required' => 'The dispenser field is required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $nozzle = Nozzle::find($inputData['id']);
        if ($nozzle == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find nozzle.']);
        }
        $nozzle->name = $inputData['name'];
        $nozzle->dispenser_id = $inputData['dispenser_id'];
        $nozzle->opening_stock = $inputData['opening_stock'] ?? null;
        if ($nozzle->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully update nozzle.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot update nozzle.']);
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
        $nozzle = Nozzle::find($inputData['id']);
        if ($nozzle == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find nozzle.']);
        }
        Nozzle::where('id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully delete nozzle.']);
    }
    public function readingSave(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'nozzle_id' => 'required',
            'date' => 'required',
            'reading' => 'required',
            'type' => 'required',
        ],[
            'nozzle_id.required' => 'The nozzle field is required.'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $reading = new NozzleReading();
        $reading->date = $inputData['date'];
        $reading->nozzle_id = $inputData['nozzle_id'];
        $reading->reading = $inputData['reading'];
        $reading->type = $inputData['type'];
        $reading->client_company_id = $inputData['session_user']['client_company_id'];
        if ($reading->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully save reading.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot save reading.']);
    }
    public function readingList(Request $request)
    {
        $inputData = $request->all();
        $limit = isset($inputData['limit']) ? $inputData['limit'] : 10;
        $keyword = isset($inputData['keyword']) ? $inputData['keyword'] : '';
        $order_by = isset($inputData['order_by']) ? $inputData['order_by'] : 'id';
        $order_mode = isset($inputData['order_mode']) ? $inputData['order_mode'] : 'DESC';
        $result = NozzleReading::select('nozzle_readings.id', 'nozzle_readings.date', 'nozzle_readings.reading', 'nozzles.name')
            ->leftJoin('nozzles', 'nozzles.id', '=', 'nozzle_readings.nozzle_id')
            ->where('nozzle_readings.client_company_id', $inputData['session_user']['client_company_id']);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('nozzles.name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('nozzle_readings.reading', 'LIKE', '%'.$keyword.'%');
            });
        }
        if (isset($inputData['start_date']) && !empty($inputData['start_date']) && isset($inputData['end_date']) && !empty($inputData['end_date'])) {
            $result->where(function($q) use ($inputData) {
                $q->whereBetween('date', [$inputData['start_date'], $inputData['end_date']]);
            });
        }
        if (isset($inputData['nozzle_id']) && !empty($inputData['nozzle_id'])) {
            $result->where(function($q) use ($inputData) {
                $q->where('nozzle_id', $inputData['nozzle_id']);
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
        foreach ($result as &$data) {
            $data['date'] = date('d/m/Y', strtotime($data['date']));
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function readingSingle(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 200, 'message' => $validator->errors()]);
        }
        $result = NozzleReading::find($inputData['id']);
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function readingUpdate(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
            'nozzle_id' => 'required',
            'date' => 'required',
            'reading' => 'required',
            'type' => 'required',
        ],[
            'nozzle_id.required' => 'The nozzle field is required.'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $reading = NozzleReading::find($inputData['id']);
        if ($reading == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find reading..']);
        }
        $reading->date = $inputData['date'];
        $reading->nozzle_id = $inputData['nozzle_id'];
        $reading->reading = $inputData['reading'];
        $reading->type = $inputData['type'];
        if ($reading->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully udpate reading.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot update reading.']);
    }
    public function readingDelete(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $reading = NozzleReading::find($inputData['id']);
        if ($reading == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find reading..']);
        }
        NozzleReading::where('id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully delete reading.']);
    }
}
