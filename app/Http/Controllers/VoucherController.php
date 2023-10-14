<?php

namespace App\Http\Controllers;

use App\Helpers\SessionUser;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VoucherController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'company_id' => 'required|integer',
            'from_number' => 'required|integer',
            'to_number' => 'required|integer',
            'validity' => 'required|date'
        ],[
            'company_id.required' => 'The company field is required.'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $arrayVoucher = [];
        $sessionUser = SessionUser::getUser();
        for ($i = $requestData['from_number']; $i <= $requestData['to_number']; $i++) {
            $arrayVoucher[] = [
                'company_id' => $requestData['company_id'],
                'voucher_number' => $i,
                'validity' => $requestData['validity'],
                'client_company_id' => $sessionUser['client_company_id'],
            ];
        }
        Voucher::insert($arrayVoucher);
        return response()->json(['status' => 200, 'message' => 'Successfully generated voucher.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $limit = $requestData['limit'] ?? 10;
        $sessionUser = SessionUser::getUser();
        $result = Voucher::select('voucher.id', 'voucher.voucher_number', 'voucher.validity', 'voucher.status', 'categories.name as company_name')
            ->leftJoin('categories', 'categories.id', '=', 'voucher.company_id')
            ->where('voucher.client_company_id', $sessionUser['client_company_id'])
            ->paginate($limit);
        foreach ($result as &$data) {
            $data['status'] = ucfirst($data['status']);
            $data['validity'] = date('d/m/Y', strtotime($data['validity']));
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
}
