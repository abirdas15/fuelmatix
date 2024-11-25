<?php

namespace App\Http\Controllers;

use App\Helpers\SessionUser;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $sessionUser = SessionUser::getUser();
        $arrayVoucher = [];
        for ($i = $requestData['from_number']; $i <= $requestData['to_number']; $i++) {
            $voucherNumber = Voucher::where('company_id', $requestData['company_id'])
                ->where('voucher_number', $i)
                ->where('client_company_id', $sessionUser['client_company_id'])
                ->first();
            if ($voucherNumber instanceof Voucher) {
                continue;
            }
            $arrayVoucher[] = [
                'company_id' => $requestData['company_id'],
                'voucher_number' => $i,
                'validity' => $requestData['validity'],
                'client_company_id' => $sessionUser['client_company_id'],
                'prefix' => $requestData['prefix'] ?? '',
                'suffix' => $requestData['suffix'] ?? '',
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
        $keyword = $request->input('keyword', '');
        $sessionUser = SessionUser::getUser();
        $result = Voucher::select(
            'voucher.id',
            DB::raw('CONCAT(
                        COALESCE(voucher.prefix, ""),
                        IF(voucher.prefix IS NOT NULL AND voucher.suffix IS NOT NULL, "-", ""),
                        voucher.voucher_number,
                        IF(voucher.suffix IS NOT NULL, CONCAT("-", voucher.suffix), "")
                    ) as voucher_number'),
            'voucher.validity',
            'voucher.status',
            'categories.name as company_name'
        )
            ->leftJoin('categories', 'categories.id', '=', 'voucher.company_id')
            ->where('voucher.client_company_id', $sessionUser['client_company_id']);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('voucher.voucher_number', 'like', '%' . $keyword . '%');
            });
        }
        $result = $result->paginate($limit);
        foreach ($result as &$data) {
            $data['status'] = ucfirst($data['status']);
            $data['validity'] = date('d/m/Y', strtotime($data['validity']));
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
}
