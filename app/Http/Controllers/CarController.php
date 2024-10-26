<?php

namespace App\Http\Controllers;

use App\Helpers\SessionUser;
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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'company_id' => 'required',
            'car_number' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $sessionUser = SessionUser::getUser();
        $car = new Car();
        $car->company_id = $requestData['company_id'];
        $car->car_number = $requestData['car_number'];
        $car->client_company_id = $sessionUser['client_company_id'];
        if (!$car->save()) {
            return response()->json(['status' => 400, 'message' => 'Cannot save car.'], 422);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully saved car.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $limit = $requestData['limit'] ?? 10;
        $company_id = $requestData['company_id'] ?? '';
        $sessionUser = SessionUser::getUser();
        $keyword = $request['keyword'] ?? '';
        $result = Car::select('car.id', 'car.car_number', 'categories.name as company_name')
            ->leftJoin('categories', 'categories.id', '=', 'car.company_id')
            ->where('car.client_company_id', $sessionUser['client_company_id']);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('car.car_number', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('categories.name', 'LIKE', '%'.$keyword.'%');
            });
        }
        if (!empty($company_id)) {
            $result->where(function($q) use ($company_id) {
               $q->where('company_id', $company_id);
            });
        }
        $result = $result->orderBy('id', 'DESC')
            ->paginate($limit);
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function single(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = Car::select('id', 'car_number', 'company_id')
            ->where('id', $requestData['id'])
            ->first();
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'company_id' => 'required',
            'car_number' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $car = Car::find($requestData['id']);
        if (!$car instanceof Car) {
            return response()->json(['status' => 400, 'message' => 'Cannot find car.']);
        }
        $car->company_id = $requestData['company_id'];
        $car->car_number = $requestData['car_number'];
        if (!$car->save()) {
            return response()->json(['status' => 400, 'message' => 'Cannot update car.'], 422);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully updated car.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        Car::where('id', $requestData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted car.']);
    }
}
