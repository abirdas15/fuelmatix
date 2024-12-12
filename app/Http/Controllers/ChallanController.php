<?php

namespace App\Http\Controllers;

use App\Common\FuelMatixDateTimeFormat;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Challan;
use App\Models\ChallanItem;
use App\Models\ClientCompany;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ChallanController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'challan_no' => 'required|string',
            'company_name' => 'required|string',
            'company_address' => 'nullable|string',
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric',
            'items.*.unit_price' => 'required|numeric',
            'items.*.total' => 'required|numeric',
        ],[
            'items.*.product_id.required' => 'The product field is required.',
            'items.*.quantity.required' => 'The quantity field is required.',
            'items.*.unit_price.required' => 'The unit_price field is required.',
            'items.*.total.required' => 'The total field is required.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }
        DB::transaction(function() use ($request) {
            $sessionUser = SessionUser::getUser();
            $challan = new Challan();
            $challan->date = $request->input('date');
            $challan->challan_no = $request->input('challan_no');
            $challan->company_name = $request->input('company_name');
            $challan->company_address = $request->input('company_address');
            $challan->client_company_id = $sessionUser['client_company_id'];
            $challan->save();
            foreach ($request->input('items') as $item) {
                $challanItem = new ChallanItem();
                $challanItem->challan_id = $challan->id;
                $challanItem->product_id = $item['product_id'];
                $challanItem->quantity = $item['quantity'];
                $challanItem->unit_price = $item['unit_price'];
                $challanItem->total = $item['total'];
                $challanItem->save();
            }
        });
        return response()->json([
            'status' => 200,
            'message' => 'Challan saved successfully!'
        ]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $sessionUser = SessionUser::getUser();
        $limit = $request->input('limit', 10);
        $result = Challan::select(
            'challans.id',
            'challans.date',
            'challans.challan_no',
            'challans.company_name',
            'challans.company_address',
            DB::raw('SUM(challan_items.total) as total'),
        )
            ->join('challan_items', 'challans.id', '=', 'challan_items.challan_id')
            ->where('challans.client_company_id', $sessionUser['client_company_id'])
            ->groupBy('challans.id')
            ->orderBy('challans.id', 'desc')
            ->paginate($limit);
        foreach ($result as &$item) {
            $item['date'] = Helpers::formatDate($item['date'], FuelMatixDateTimeFormat::STANDARD_DATE);
            $item['total_format'] = number_format($item['total'], $sessionUser['currency_precision']);
        }
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }
    public function exportPdf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }
        $sessionUser = SessionUser::getUser();
        $data = Challan::with(['items' => function ($query) {
            $query->select('challan_items.id', 'challan_items.challan_id', 'products.name as product_name', 'challan_items.quantity', 'challan_items.unit_price', 'challan_items.total')
                ->join('products', 'products.id', '=', 'challan_items.product_id');
        }])
            ->where('id', $request->input('id'))
            ->first();
        $total = 0;
        foreach ($data['items'] as &$item) {
            $total += $item['total'];
            $item['total'] = number_format($item['total'], $sessionUser['currency_precision']);
            $item['unit_price'] = number_format($item['unit_price'], $sessionUser['currency_precision']);
            $item['quantity'] = number_format($item['quantity'], $sessionUser['quantity_precision']);
        }
        $company = ClientCompany::where('id', $sessionUser['client_company_id'])->first();
        $pdf = Pdf::loadView('pdf.challan', [
            'data' => $data,
            'total' => number_format($total, $sessionUser['currency_precision']),
            'company' => $company
        ]);
        return $pdf->output();
    }
}
