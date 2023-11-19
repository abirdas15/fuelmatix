<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CarController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'company_id' => 'required|integer',
            'keyword' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = Car::select('car_number')
            ->where('company_id', $requestData['company_id'])
            ->where('car_number', 'LIKE', '%'.$requestData['keyword'].'%')
            ->get()
            ->toArray();
        return response()->json(['status' => 200, 'data' => $result]);
    }
}
