<?php

namespace App\Http\Controllers;

use App\Repository\ReportRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function dailyLog(Request $request)
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'date' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $filter = [
            'date' => $requestData['date']
        ];
        $result = ReportRepository::dailyLog($filter);
        return response()->json(['status' => 200, 'data' => $result]);
    }
}
