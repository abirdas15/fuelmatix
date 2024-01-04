<?php

namespace App\Http\Controllers;

use App\Common\FuelMatixDateTimeFormat;
use App\Common\FuelMatixStatus;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\ClientCompany;
use App\Models\ShiftSale;
use App\Repository\ReportRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function dailyLog(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'date' => 'required|date'
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

    /**
     * @param Request $request
     * @return JsonResponse|string
     */
    public function dailyLogExportPdf(Request $request)
    {

        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'date' => 'required|date'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $filter = [
            'date' => $requestData['date']
        ];
        $result = ReportRepository::dailyLog($filter);
        $pdf = Pdf::loadView('pdf.daily-log', ['data' => $result]);
        return $pdf->output();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function salesReport(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'start_date' => 'required',
            'end_date' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $productId = $requestData['product_id'] ?? '';
        $dispenserId = $requestData['dispenser_id'] ?? '';
        $nozzleId = $requestData['nozzle_id'] ?? '';
        $sessionUser = SessionUser::getUser();
        $result = ShiftSale::select('shift_sale.date', DB::raw('SUM(shift_summary.consumption) as quantity'),DB::raw('SUM(shift_summary.amount) as amount'),  'shift_sale.product_id', 'products.name as product_name', 'shift_summary.nozzle_id', 'nozzles.name as nozzle_name', 'shift_summary.dispenser_id', 'dispensers.dispenser_name')
            ->leftJoin('shift_summary', 'shift_summary.shift_sale_id', '=', 'shift_sale.id')
            ->leftJoin('products', 'products.id', '=', 'shift_sale.product_id')
            ->leftJoin('nozzles', 'nozzles.id', '=', 'shift_summary.nozzle_id')
            ->leftJoin('dispensers', 'dispensers.id', '=', 'shift_summary.dispenser_id')
            ->whereBetween('date', [$requestData['start_date'], $requestData['end_date']])
            ->where('shift_sale.client_company_id', $sessionUser['client_company_id'])
            ->where('status', FuelMatixStatus::END);
        if (!empty($productId)) {
            $result->where(function($q) use ($productId){
                $q->where('shift_sale.product_id', $productId);
            });
        }
        if (!empty($dispenserId)) {
            $result->where(function($q) use ($dispenserId){
                $q->where('shift_summary.dispenser_id', $dispenserId);
            });
        }
        if (!empty($nozzleId)) {
            $result->where(function($q) use ($nozzleId){
                $q->where('shift_summary.nozzle_id', $nozzleId);
            });
        }
        $result = $result->orderBY('date', 'ASC')
            ->groupBy('date')
            ->groupBy('shift_sale.product_id')
            ->get()
            ->toArray();
        foreach ($result as &$data) {
            $data['date'] = Helpers::formatDate($data['date'], FuelMatixDateTimeFormat::STANDARD_DATE);
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
}
