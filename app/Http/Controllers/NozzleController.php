<?php

namespace App\Http\Controllers;

use App\Common\FuelMatixDateTimeFormat;
use App\Helpers\Helpers;
use App\Models\Nozzle;
use App\Models\NozzleReading;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NozzleController extends Controller
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
            return response()->json(['status' => 200, 'message' => 'Successfully saved nozzle.']);
        }
        return response()->json(['status' => 400, 'message' => 'Cannot saved [nozzle].']);
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
        $product_id = $request['product_id'] ?? '';
        $result = Nozzle::select('nozzles.id', 'nozzles.name', 'dispensers.dispenser_name')
            ->leftJoin('dispensers', 'dispensers.id', '=', 'nozzles.dispenser_id')
            ->where('nozzles.client_company_id', $inputData['session_user']['client_company_id']);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('nozzles.name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('dispensers.dispenser_name', 'LIKE', '%'.$keyword.'%');
            });
        }
        if (!empty($product_id)) {
            $result->where(function($q) use ($product_id) {
                $q->where('dispensers.product_id', $product_id);
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
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = Nozzle::find($inputData['id']);
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
            'dispenser_id' => 'required'
        ],[
            'dispenser_id.required' => 'The dispenser field is required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $nozzle = Nozzle::find($inputData['id']);
        if (!$nozzle instanceof Nozzle) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [nozzle].']);
        }
        $nozzle->name = $inputData['name'];
        $nozzle->dispenser_id = $inputData['dispenser_id'];
        $nozzle->opening_stock = $inputData['opening_stock'] ?? null;
        if ($nozzle->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully updated nozzle.']);
        }
        return response()->json(['status' => 400, 'message' => 'Cannot updated nozzle.']);
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
        $nozzle = Nozzle::find($inputData['id']);
        if (!$nozzle instanceof Nozzle) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [nozzle].']);
        }
        Nozzle::where('id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully delete [nozzle].']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function readingSave(Request $request): JsonResponse
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
        $reading->date = $inputData['date'].' '.date('H:i:s');
        $reading->nozzle_id = $inputData['nozzle_id'];
        $reading->reading = $inputData['reading'];
        $reading->type = $inputData['type'];
        $reading->client_company_id = $inputData['session_user']['client_company_id'];
        if ($reading->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully save reading.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot save reading.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function readingList(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $limit = $inputData['limit'] ?? 10;
        $keyword = $inputData['keyword'] ?? '';
        $order_by = $inputData['order_by'] ?? 'id';
        $order_mode = $inputData['order_mode'] ?? 'DESC';
        $result = NozzleReading::select('nozzle_readings.id', 'nozzle_readings.date', 'nozzle_readings.reading', 'nozzles.name')
            ->leftJoin('nozzles', 'nozzles.id', '=', 'nozzle_readings.nozzle_id')
            ->where('nozzle_readings.client_company_id', $inputData['session_user']['client_company_id']);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('nozzles.name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('nozzle_readings.reading', 'LIKE', '%'.$keyword.'%');
            });
        }
        if (!empty($inputData['start_date']) && !empty($inputData['end_date'])) {
            $result->where(function($q) use ($inputData) {
                $q->whereBetween('date', [$inputData['start_date'], $inputData['end_date']]);
            });
        }
        if (!empty($inputData['nozzle_id'])) {
            $result->where(function($q) use ($inputData) {
                $q->where('nozzle_id', $inputData['nozzle_id']);
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
        foreach ($result as &$data) {
            $data['date'] = Helpers::formatDate($data['date'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME);
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function readingSingle(Request $request): JsonResponse
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
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function readingUpdate(Request $request): JsonResponse
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
        if (!$reading  instanceof NozzleReading) {
            return response()->json(['status' => 400, 'message' => 'Cannot find reading..']);
        }
        $reading->date = $inputData['date'];
        $reading->nozzle_id = $inputData['nozzle_id'];
        $reading->reading = $inputData['reading'];
        $reading->type = $inputData['type'];
        if ($reading->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully updated reading.']);
        }
        return response()->json(['status' => 400, 'message' => 'Cannot update reading.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function readingDelete(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $reading = NozzleReading::find($inputData['id']);
        if (!$reading instanceof NozzleReading) {
            return response()->json(['status' => 400, 'message' => 'Cannot find reading..']);
        }
        NozzleReading::where('id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted reading.']);
    }
}
