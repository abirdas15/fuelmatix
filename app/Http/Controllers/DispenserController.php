<?php

namespace App\Http\Controllers;

use App\Models\Dispenser;
use App\Models\DispenserReading;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DispenserController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'product_id' => 'required',
            'dispenser_name' => 'required',
            'mac' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $dispenser = new Dispenser();
        $dispenser->product_id = $inputData['product_id'];
        $dispenser->tank_id = $inputData['tank_id'];
        $dispenser->dispenser_name = $inputData['dispenser_name'];
        $dispenser->brand = $inputData['brand'];
        $dispenser->serial = $inputData['serial'];
        $dispenser->opening_stock = $inputData['opening_stock'] ?? null;
        $dispenser->mac = $inputData['mac'] ?? null;
        $dispenser->client_company_id = $inputData['session_user']['client_company_id'];
        if ($dispenser->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully save dispenser.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot save dispenser.']);
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
        $order_by = $inputData['order_by'] ?? 'dispensers.id';
        $order_mode = $inputData['order_mode'] ?? 'DESC';
        $product_id = $inputData['product_id'] ?? '';
        $result = Dispenser::select('dispensers.*', 'products.name as product_name', 'tank.tank_name')
            ->leftJoin('products', 'products.id', '=', 'dispensers.product_id')
            ->leftJoin('tank', 'tank.id', '=', 'dispensers.tank_id')
            ->where('dispensers.client_company_id', $inputData['session_user']['client_company_id']);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('dispensers.dispenser_name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('dispensers.brand', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('dispensers.serial', 'LIKE', '%'.$keyword.'%');
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
        $result = Dispenser::find($inputData['id']);
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
            'product_id' => 'required',
            'dispenser_name' => 'required',
            'mac' => 'nullable|string'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $dispenser = Dispenser::find($inputData['id']);
        if ($dispenser == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find dispenser..']);
        }
        $dispenser->product_id = $inputData['product_id'];
        $dispenser->tank_id = $inputData['tank_id'];
        $dispenser->dispenser_name = $inputData['dispenser_name'];
        $dispenser->brand = $inputData['brand'];
        $dispenser->serial = $inputData['serial'];
        $dispenser->mac = $inputData['mac'] ?? null;
        $dispenser->opening_stock = $inputData['opening_stock'] ?? null;
        if ($dispenser->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully update dispenser.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot update dispenser.']);
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
        $dispenser = Dispenser::find($inputData['id']);
        if ($dispenser == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find dispenser..']);
        }
        Dispenser::where('id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully delete dispenser.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function readingSave(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'dispenser_id' => 'required',
            'date' => 'required',
            'reading' => 'required',
            'type' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $reading = new DispenserReading();
        $reading->dispenser_id = $inputData['dispenser_id'];
        $reading->date = $inputData['date'];
        $reading->reading = $inputData['reading'];
        $reading->litter = $inputData['litter'] ?? null;
        $reading->type = $inputData['type'];
        $reading->client_company_id = $inputData['session_user']['client_company_id'];
        if ($reading->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully saved dispenser reading.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot saved dispense reading.']);
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
        $result = DispenserReading::select('dispenser_reading.id', 'dispenser_reading.date', 'dispenser_reading.reading', 'dispensers.dispenser_name')
            ->leftJoin('dispensers', 'dispensers.id', '=', 'dispenser_reading.dispenser_id')
            ->where('dispenser_reading.client_company_id', $inputData['session_user']['client_company_id']);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('dispensers.dispenser_name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('dispenser_reading.reading', 'LIKE', '%'.$keyword.'%');
            });
        }
        if (isset($inputData['start_date']) && !empty($inputData['start_date']) && isset($inputData['end_date']) && !empty($inputData['end_date'])) {
            $result->where(function($q) use ($inputData) {
                $q->whereBetween('date', [$inputData['start_date'], $inputData['end_date']]);
            });
        }
        if (isset($inputData['dispenser_id']) && !empty($inputData['dispenser_id'])) {
            $result->where(function($q) use ($inputData) {
                $q->where('dispenser_id', $inputData['dispenser_id']);
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
        foreach ($result as &$data) {
            $data['date'] = date('d/m/Y', strtotime($data['date']));
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
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = DispenserReading::find($inputData['id']);
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
            'dispenser_id' => 'required',
            'date' => 'required',
            'reading' => 'required',
            'type' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $reading = DispenserReading::find($inputData['id']);
        if ($reading ==  null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find dispense reading.']);
        }
        $reading->dispenser_id = $inputData['dispenser_id'];
        $reading->date = $inputData['date'];
        $reading->reading = $inputData['reading'];
        $reading->type = $inputData['type'];
        $reading->litter = $inputData['litter'] ?? null;
        if ($reading->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully updated dispenser reading.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot updated dispense reading.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function readingDelete(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $reading = DispenserReading::find($inputData['id']);
        if ($reading ==  null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find dispense reading.']);
        }
        DispenserReading::where('id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted dispenser reading.']);
    }
}
