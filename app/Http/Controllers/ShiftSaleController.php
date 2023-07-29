<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Models\Category;
use App\Models\Dispenser;
use App\Models\ProductPrice;
use App\Models\ShiftSale;
use App\Models\ShiftSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShiftSaleController extends Controller
{
    public function save(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'date' => 'required',
            'product_id' => 'required',
            'start_reading' => 'required',
            'end_reading' => 'required',
            'consumption' => 'required',
            'amount' => 'required',
            'dispensers' => 'required|array',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $category = Category::where('category', AccountCategory::INCOME)->where('client_company_id', $inputData['session_user']['client_company_id'])->first();
        $incomeCategory = Category::where('parent_category', $category['id'])
            ->where('module', 'product')
            ->where('module_id', $inputData['product_id'])
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->first();
        if ($incomeCategory == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot fin account income category.']);
        }
        $category = Category::where('category', AccountCategory::STOCK_IN_HAND)->where('client_company_id', $inputData['session_user']['client_company_id'])->first();
        $stockCategory = Category::where('parent_category', $category['id'])
            ->where('module', 'product')
            ->where('module_id', $inputData['product_id'])
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->first();
        if ($stockCategory == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot fin account stock category.']);
        }
        $category = Category::where('category', AccountCategory::COST_OF_GOOD_SOLD)->where('client_company_id', $inputData['session_user']['client_company_id'])->first();
        $costOfGoodSoldCategory = Category::where('parent_category', $category['id'])
            ->where('module', 'product')
            ->where('module_id', $inputData['product_id'])
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->first();
        if ($costOfGoodSoldCategory == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot fin account stock of good sold category.']);
        }
        $productPrices = ProductPrice::where('client_company_id', $inputData['session_user']['client_company_id'])->where('product_id', $inputData['product_id'])->where('stock_quantity', '>', 0)->get();
        $shiftSale = new ShiftSale();
        $shiftSale->date = $inputData['date'];
        $shiftSale->product_id = $inputData['product_id'];
        $shiftSale->start_reading = $inputData['start_reading'];
        $shiftSale->end_reading = $inputData['end_reading'];
        $shiftSale->consumption = $inputData['consumption'];
        $shiftSale->amount = $inputData['amount'];
        $shiftSale->user_id = $inputData['session_user']['id'];
        $shiftSale->client_company_id = $inputData['session_user']['client_company_id'];
        if ($shiftSale->save()) {
            $totalNozzleConsumption = 0;
            foreach ($inputData['dispensers'] as $dispenser) {
                foreach ($dispenser['nozzle'] as $nozzle) {
                    $totalNozzleConsumption += $nozzle['consumption'];
                    $shiftSaleSummary = new ShiftSummary();
                    $shiftSaleSummary->shift_sale_id = $shiftSale->id;
                    $shiftSaleSummary->nozzle_id = $nozzle['id'];
                    $shiftSaleSummary->start_reading = $nozzle['start_reading'];
                    $shiftSaleSummary->end_reading = $nozzle['end_reading'];
                    $shiftSaleSummary->consumption = $nozzle['consumption'];
                    $shiftSaleSummary->amount = $nozzle['amount'];
                    $shiftSaleSummary->save();
                }
            }
            $buyingPrice = 0;
            foreach ($productPrices as $productPrice) {
                if ($productPrice['stock_quantity'] > $totalNozzleConsumption) {
                    $productPrice['stock_quantity'] = $productPrice['stock_quantity'] - $totalNozzleConsumption;
                    $buyingPrice = $productPrice['unit_price'] * $totalNozzleConsumption;
                    $productPrice->save();
                    break;
                } else {
                    $totalNozzleConsumption = $totalNozzleConsumption - $productPrice['stock_quantity'];
                    $buyingPrice = $buyingPrice + ($productPrice['unit_price'] * $totalNozzleConsumption);
                    $productPrice['stock_quantity'] = 0;
                    $productPrice->save();
                }
            }
            $transactionData['linked_id'] = $incomeCategory['id'];
            foreach ($inputData['categories'] as $category) {
                $transactionData['transaction'][] = [
                    ['date' => $inputData['date'], 'account_id' => $category['id'], 'debit_amount' => $category['amount'], 'credit_amount' => 0, 'module' => 'shift sale', 'module_id' => $shiftSale->id]
                ];
            }
            TransactionController::saveTransaction($transactionData);
            $transactionData = [];
            $transactionData['linked_id'] = $stockCategory['id'];
            $transactionData['transaction'] = [
                ['date' => $inputData['date'], 'account_id' => $costOfGoodSoldCategory['id'], 'debit_amount' => $buyingPrice, 'credit_amount' => 0, 'module' => 'shift sale', 'module_id' => $shiftSale->id]
            ];
            TransactionController::saveTransaction($transactionData);
            return response()->json(['status' => 200, 'message' => 'Successfully saved shift sale.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot saved shift sale.']);
    }
    public function list(Request $request)
    {
        $inputData = $request->all();
        $limit = isset($inputData['limit']) ? $inputData['limit'] : 10;
        $keyword = isset($inputData['keyword']) ? $inputData['keyword'] : '';
        $order_by = isset($inputData['order_by']) ? $inputData['order_by'] : 'shift_sale.id';
        $order_mode = isset($inputData['order_mode']) ? $inputData['order_mode'] : 'DESC';
        $result = ShiftSale::select('shift_sale.*', 'products.name as product_name', 'users.name as user_name')
            ->leftJoin('products', 'products.id', 'shift_sale.product_id')
            ->leftJoin('users', 'users.id','=', 'shift_sale.user_id')
            ->where('shift_sale.client_company_id', $inputData['session_user']['client_company_id']);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('products.product_name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('users.name', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy($order_by, $order_mode)
            ->paginate($limit);
        foreach ($result as &$data) {
            $data['date'] = date('d/m/Y', strtotime($data['date']));
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function single(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $result = ShiftSale::find($inputData['id']);
        $shiftSummary = ShiftSummary::where('shift_sale_id', $inputData['id'])->get()->keyBy('nozzle_id');
        $dispensers = Dispenser::select('id', 'dispenser_name')
            ->where('product_id', $result['product_id'])
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->with(['nozzle' => function($q) {
                $q->select('nozzles.id', 'nozzles.dispenser_id', 'nozzles.name');
            }])
            ->get()
            ->toArray();
        foreach ($dispensers as &$dispenser) {
            foreach ($dispenser['nozzle'] as &$nozzle) {
                $nozzle['start_reading'] = isset($shiftSummary[$nozzle['id']]) ? $shiftSummary[$nozzle['id']]['start_reading'] : 0;
                $nozzle['end_reading'] = isset($shiftSummary[$nozzle['id']]) ? $shiftSummary[$nozzle['id']]['end_reading'] : 0;
                $nozzle['consumption'] = isset($shiftSummary[$nozzle['id']]) ? $shiftSummary[$nozzle['id']]['consumption'] : 0;
                $nozzle['amount'] = isset($shiftSummary[$nozzle['id']]) ? $shiftSummary[$nozzle['id']]['amount'] : 0;
            }
        }
        $result['dispensers'] = $dispensers;
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function update(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
            'date' => 'required',
            'product_id' => 'required',
            'start_reading' => 'required',
            'end_reading' => 'required',
            'consumption' => 'required',
            'amount' => 'required',
            'dispensers' => 'required|array',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $shiftSale = ShiftSale::find($inputData['id']);
        if ($shiftSale == null) {
            return response()->json(['status' => 500, 'error' => 'Cannot find shift sale.']);
        }
        $shiftSale->date = $inputData['date'];
        $shiftSale->product_id = $inputData['product_id'];
        $shiftSale->start_reading = $inputData['start_reading'];
        $shiftSale->end_reading = $inputData['end_reading'];
        $shiftSale->consumption = $inputData['consumption'];
        $shiftSale->amount = $inputData['amount'];
        if ($shiftSale->save()) {
            ShiftSummary::where('shift_sale_id', $inputData['id'])->delete();
            foreach ($inputData['dispensers'] as $dispenser) {
                foreach ($dispenser['nozzle'] as $nozzle) {
                    $shiftSaleSummary = new ShiftSummary();
                    $shiftSaleSummary->shift_sale_id = $inputData['id'];
                    $shiftSaleSummary->nozzle_id = $nozzle['id'];
                    $shiftSaleSummary->start_reading = $nozzle['start_reading'];
                    $shiftSaleSummary->end_reading = $nozzle['end_reading'];
                    $shiftSaleSummary->consumption = $nozzle['consumption'];
                    $shiftSaleSummary->amount = $nozzle['amount'];
                    $shiftSaleSummary->save();
                }
            }
            return response()->json(['status' => 200, 'message' => 'Successfully updated shift sale.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot updated shift sale.']);
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
        ShiftSale::where('id', $inputData['id'])->delete();
        ShiftSummary::where('shift_sale_id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted shift sale.']);
    }
    public function getCategory(Request $request)
    {
        $inputData = $request->all();
        $cash = Category::select('id', 'category as name')
            ->where('category', AccountCategory::CASH)
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->first();
        $accountReceivable = Category::select('id')
            ->where('category', AccountCategory::ACCOUNT_RECEIVABLE)
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->first();
        $result['cash'] = $cash;
        $result['companies'] = Category::select('id', 'category as name')
            ->where('parent_category', $accountReceivable->id)
            ->get()
            ->toArray();
        return response()->json(['status' => 200, 'data' => $result]);
    }
}
