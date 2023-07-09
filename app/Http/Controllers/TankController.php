<?php

namespace App\Http\Controllers;

use App\Models\BstiChart;
use App\Models\Tank;
use App\Models\TankLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TankController extends Controller
{
    public function save(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'tank_name' => 'required',
            'capacity' => 'required',
            'height' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $tank = new Tank();
        $tank->tank_name = $inputData['tank_name'];
        $tank->capacity = $inputData['capacity'];
        $tank->height = $inputData['height'];
        if ($tank->save()) {
            if ($request->file('file')) {
                $file = $request->file('file');
                $spreadsheet = IOFactory::load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $row_limit = $sheet->getHighestDataRow();
                $row_range = range(2, $row_limit);
                $bstiChartData = [];
                foreach ($row_range as $row) {
                    $bstiChartData[] = [
                        'height' => $sheet->getCell('A' . $row)->getValue(),
                        'volume' => $sheet->getCell('B' . $row)->getValue(),
                        'tank_id' => $tank->id,
                    ];
                }
                if (count($bstiChartData) > 0) {
                    BstiChart::insert($bstiChartData);
                }
            }
            return response()->json(['status' => 200, 'message' => 'Successfully saved tank.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot saved tank.']);
    }
    public function list(Request $request)
    {
        $inputData = $request->all();
        $limit = isset($inputData['limit']) ? $inputData['limit'] : 10;
        $keyword = isset($inputData['keyword']) ? $inputData['keyword'] : '';
        $order_by = isset($inputData['order_by']) ? $inputData['order_by'] : 'id';
        $order_mode = isset($inputData['order_mode']) ? $inputData['order_mode'] : 'DESC';
        $result = Tank::select('tank.id' ,'tank.tank_name', 'tank.height', DB::raw('MAX(tank_log.id) as tank_log_id'), 'tank.capacity', 'tank_log.height as tank_log_height', 'tank_log.water_height as tank_log_water_height')
            ->leftJoin('tank_log' ,function ($q) {
                $q->on('tank_log.tank_id', '=', 'tank.id');
                $q->orderBy('tank_log.id', 'DESC');
                $q->groupBy('tank_log.tank_id');
            });
        $result = $result->groupBy('tank_log.tank_id')
            ->paginate($limit);
        foreach ($result as &$data) {
            $data['percent'] = 0;
            if ($data['capacity'] > 0 && $data['tank_log_height'] > 0) {
                $data['percent'] = number_format(($data['tank_log_height'] / $data['capacity']) * 100, 2);
            }
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function single(Request $request)
    {
        $inputData = $request->all();
        $result = Tank::select('id', 'tank_name', 'height', 'capacity')->find($inputData['id']);
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function update(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
            'tank_name' => 'required',
            'capacity' => 'required',
            'height' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $tank = Tank::find($inputData['id']);
        if ($tank == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find tank.']);
        }
        $tank->tank_name = $inputData['tank_name'];
        $tank->capacity = $inputData['capacity'];
        $tank->height = $inputData['height'];
        if ($tank->save()) {
            if ($request->file('file')) {
                BstiChart::where('tank_id', $inputData['id'])->delete();
                $file = $request->file('file');
                $spreadsheet = IOFactory::load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $row_limit = $sheet->getHighestDataRow();
                $row_range = range(2, $row_limit);
                $bstiChartData = [];
                foreach ($row_range as $row) {
                    $bstiChartData[] = [
                        'height' => $sheet->getCell('A' . $row)->getValue(),
                        'volume' => $sheet->getCell('B' . $row)->getValue(),
                        'tank_id' => $tank->id,
                    ];
                }
                if (count($bstiChartData) > 0) {
                    BstiChart::insert($bstiChartData);
                }
            }
            return response()->json(['status' => 200, 'message' => 'Successfully updated tank.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot updated tank.']);
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
        Tank::where('id', $inputData['id'])->delete();
        BstiChart::where('tank_id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted tank.']);
    }
    public function readingSave(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'tank_id' => 'required',
            'date' => 'required',
            'height' => 'required',
            'water_height' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $bstiChart = BstiChart::where('tank_id', $inputData['tank_id'])
            ->where('height', '=', floor($inputData['height']))
            ->first();
        $reading = new TankLog();
        $reading->tank_id = $inputData['tank_id'];
        $reading->date = $inputData['date'];
        $reading->height = $inputData['height'];
        $reading->water_height = $inputData['water_height'];
        $reading->volume = $bstiChart != null ? $bstiChart->volume : 0;
        if ($reading->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully saved tank reading.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot saved tank reading.']);
    }
    public function readingList(Request $request)
    {
        $inputData = $request->all();
        $limit = isset($inputData['limit']) ? $inputData['limit'] : 10;
        $keyword = isset($inputData['keyword']) ? $inputData['keyword'] : '';
        $order_by = isset($inputData['order_by']) ? $inputData['order_by'] : 'tank_log.id';
        $order_mode = isset($inputData['order_mode']) ? $inputData['order_mode'] : 'DESC';
        $result = TankLog::select('tank_log.id', 'tank_log.date', 'tank_log.height', 'tank_log.water_height', 'tank_log.volume', 'tank.tank_name')
            ->leftJoin('tank', 'tank.id', '=', 'tank_log.tank_id');
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('tank.tank_name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('tank_log.height', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('tank_log.water_height', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('tank_log.volume', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
        foreach ($result as $data) {
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
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = TankLog::select('id', 'height', 'water_height', 'tank_id')->find($inputData['id']);
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function readingUpdate(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
            'tank_id' => 'required',
            'date' => 'required',
            'height' => 'required',
            'water_height' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $reading = TankLog::find($inputData['id']);
        if ($reading == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find tank reading.']);
        }
        $bstiChart = BstiChart::where('tank_id', $inputData['tank_id'])
            ->where('height', '=', floor($inputData['height']))
            ->first();
        $reading->tank_id = $inputData['tank_id'];
        $reading->date = $inputData['date'];
        $reading->height = $inputData['height'];
        $reading->water_height = $inputData['water_height'];
        $reading->volume = $bstiChart != null ? $bstiChart->volume : 0;
        if ($reading->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully updated tank reading.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot updated tank reading.']);
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
        TankLog::where('id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted tank reading.']);
    }
}
