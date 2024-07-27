<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\FuelMatixDateTimeFormat;
use App\Common\FuelMatixStatus;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\ClientCompany;
use App\Models\ShiftSale;
use App\Models\TankRefill;
use App\Models\Transaction;
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
            'date' => $requestData['date'],
            'shift_sale_id' => $requestData['shift_sale_id'] ?? ''
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
            ->where('shift_sale.status', FuelMatixStatus::END);
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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function windfallReport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date|string',
            'end_date' => 'required|date|string'
        ],[
            'start_date.required' => 'The date field is required.'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }
        $sessionUser = SessionUser::getUser();
        $productId = $request->input('product_id', '');
        $shiftSale = ShiftSale::select(
            'shift_sale.id',
            'shift_sale.date',
            'shift_sale.net_profit',
            'products.name as product_name',
            DB::raw('shift_sale.net_profit_amount as amount'),
            DB::raw("'Shift Sale' as source")
        )
            ->leftJoin('products', 'products.id', '=', 'shift_sale.product_id')
            ->whereBetween('shift_sale.date', [$request['start_date'], $request['end_date']])
            ->whereNotNull('shift_sale.net_profit_amount')
            ->where('shift_sale.net_profit_amount', '!=', 0)
            ->where('shift_sale.client_company_id', $sessionUser['client_company_id']);
        if (!empty($productId)) {
            $shiftSale->where(function($q) use ($productId){
               $q->where('products.id', $productId);
            });
        }

        $tankRefill = TankRefill::select(
            'tank_refill.id',
            'tank_refill.date',
            'tank_refill.net_profit',
            'products.name as product_name',
            DB::raw('tank_refill.net_profit_amount as amount'),
            DB::raw("'Tank Refill' as source")
        )
            ->whereBetween('tank_refill.date', [$request['start_date'], $request['end_date']])
            ->leftJoin('tank', 'tank.id', '=', 'tank_refill.tank_id')
            ->leftJoin('products', 'products.id', '=', 'tank.product_id')
            ->whereNotNull('tank_refill.net_profit_amount')
            ->where('tank_refill.net_profit_amount', '!=', 0)
            ->where('tank_refill.client_company_id', $sessionUser['client_company_id']);
        if (!empty($productId)) {
            $tankRefill->where(function($q) use ($productId){
                $q->where('products.id', $productId);
            });
        }

        $queryResult = $shiftSale->union($tankRefill)
            ->orderBy('date', 'ASC')
            ->get()
            ->toArray();
        $total = 0;
        foreach ($queryResult as &$result) {
            if ($result['net_profit'] > 0) {
                $result['status'] = 'Profit';
            } else {
                $result['status'] = 'Loss';
            }
            $result['quantity'] = number_format(abs($result['net_profit']), 2);
            $result['date'] = date('d/m/Y', strtotime($result['date']));
            $total += $result['amount'];
            $result['amount'] = number_format(abs($result['amount']), 2);
        }

        return response()->json([
            'status' => 200,
            'data' => $queryResult,
            'total' => [
                'status' => $total > 0 ? 'Profit' : 'Loss',
                'amount' => number_format(abs($total), 2)
            ]
        ]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function creditCompanyReport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date|string',
            'end_date' => 'required|date|string',
            'category_id' => 'required|integer'
        ],[
            'start_date.required' => 'The date field is required.',
            'category_id.required' => 'The company field is required.'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }
        $sessionUser = SessionUser::getUser();

        $openingBalance = Transaction::select(DB::raw('SUM(credit_amount - debit_amount) as opening_balance'))
            ->where('date', '<', $request['start_date'])
            ->where('account_id', $request['category_id'])
            ->first();
        $openingBalance = $openingBalance['opening_balance'] ?? 0;
        $result = Transaction::select('date', 'categories.name as company_name', DB::raw('SUM(credit_amount) as bill_amount'), DB::raw('SUM(debit_amount) as paid_amount'))
            ->whereBetween('date', [$request['start_date'], $request['end_date']])
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->where('account_id', $request['category_id'])
            ->groupBy('date')
            ->get()
            ->toArray();
        $dueAmount = $openingBalance;
        foreach ($result as  &$item) {
            $dueAmount += $item['bill_amount'] - $item['paid_amount'];
            $item['due_amount'] = $dueAmount;
            $item['bill_amount'] = !empty($item['bill_amount']) ? number_format($item['bill_amount'], 2) : '';
            $item['paid_amount'] = !empty($item['paid_amount']) ? number_format($item['paid_amount'], 2) : '';
            $item['due_amount'] = !empty($item['due_amount']) ? number_format($item['due_amount'], 2) : '';
        }
        return response()->json([
            'status' => 200,
            'data' => $result,
            'opening_balance' => [
                'amount' => $openingBalance,
                'amount_format' => number_format(abs($openingBalance), 2)
            ]
        ]);
    }
}
