<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\FuelMatixDateTimeFormat;
use App\Common\FuelMatixStatus;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\ClientCompany;
use App\Models\FuelAdjustment;
use App\Models\PayOrderData;
use App\Models\Product;
use App\Models\ShiftSale;
use App\Models\ShiftTotal;
use App\Models\Tank;
use App\Models\TankRefill;
use App\Models\TankRefillTotal;
use App\Models\Transaction;
use App\Repository\ReportRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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
     * Generates a sales report based on provided date range and optional filters.
     *
     * This method retrieves and aggregates sales data within a specified date range. It filters the
     * data by product, dispenser, and nozzle if provided. The results are formatted and returned as a
     * JSON response.
     *
     * @param Request $request The HTTP request containing the filters and date range.
     * @return JsonResponse A JSON response containing the sales report data.
     */
    public function salesReport(Request $request): JsonResponse
    {
        // Validate the input parameters
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);

        // Return validation errors if validation fails
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }

        // Retrieve optional filters from the request
        $productId = $request->input('product_id', '');
        $dispenserId = $request->input('dispenser_id', '');
        $nozzleId = $request->input('nozzle_id', '');

        // Get the current session user
        $sessionUser = SessionUser::getUser();

        // Parse and set the start and end dates for the query
        $startDate = Carbon::parse($request->input('start_date'), SessionUser::TIMEZONE)->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'), SessionUser::TIMEZONE)->endOfDay();

        // Query the ShiftTotal table and join with related tables
        $result = ShiftTotal::select(
            'shift_total.start_date',
            DB::raw('SUM(shift_summary.consumption) as quantity'),
            DB::raw('SUM(shift_summary.amount) as amount'),
            'shift_total.product_id',
            'products.name as product_name',
            'shift_summary.nozzle_id',
            'nozzles.name as nozzle_name',
            'shift_summary.dispenser_id',
            'dispensers.dispenser_name'
        )
            ->leftJoin('shift_sale', 'shift_sale.shift_id', '=', 'shift_total.id')
            ->leftJoin('shift_summary', 'shift_summary.shift_sale_id', '=', 'shift_sale.id')
            ->leftJoin('products', 'products.id', '=', 'shift_total.product_id')
            ->leftJoin('nozzles', 'nozzles.id', '=', 'shift_summary.nozzle_id')
            ->leftJoin('dispensers', 'dispensers.id', '=', 'shift_summary.dispenser_id')
            ->whereBetween('start_date', [$startDate, $endDate])
            ->where('shift_total.client_company_id', $sessionUser['client_company_id'])
            ->where('shift_total.status', FuelMatixStatus::END);

        // Apply filters if provided
        if (!empty($productId)) {
            $result->where('shift_total.product_id', $productId);
        }
        if (!empty($dispenserId)) {
            $result->where('shift_summary.dispenser_id', $dispenserId);
        }
        if (!empty($nozzleId)) {
            $result->where('shift_summary.nozzle_id', $nozzleId);
        }

        // Order and group the results
        $result = $result->orderBy(DB::raw('DATE(start_date)'), 'ASC')
            ->groupBy(DB::raw('DATE(start_date)'))
            ->groupBy('shift_total.product_id')
            ->get()
            ->toArray();

        // Format the date field in the results
        foreach ($result as &$data) {
            $data['date'] = Helpers::formatDate($data['start_date'], FuelMatixDateTimeFormat::STANDARD_DATE);
        }

        // Return the sales report data as a JSON response
        return response()->json(['status' => 200, 'data' => $result]);
    }


    /**
     * Generates a windfall report based on provided date range and optional product filter.
     *
     * This method retrieves and aggregates net profit data for shift sales and tank refills within a
     * specified date range. It applies optional product filtering and formats the results for output.
     *
     * @param Request $request The HTTP request containing the date range and optional product filter.
     * @return JsonResponse A JSON response containing the windfall report data.
     */
    public function windfallReport(Request $request): JsonResponse
    {
        // Validate the input parameters
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date|string',
            'end_date' => 'required|date|string'
        ], [
            'start_date.required' => 'The date field is required.'
        ]);

        // Return validation errors if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Get the current session user
        $sessionUser = SessionUser::getUser();

        // Retrieve optional product filter from the request
        $productId = $request->input('product_id', '');
        $startDate = Carbon::parse($request->input('start_date'), SessionUser::TIMEZONE)->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'), SessionUser::TIMEZONE)->endOfDay();

        // Query for shift sales data
        $shiftSale = ShiftSale::select(
            'shift_total.id',
            DB::raw('DATE(shift_total.start_date) as date'),
            'shift_sale.net_profit',
            'products.name as product_name',
            DB::raw('shift_sale.net_profit_amount as amount'),
            DB::raw("'Shift Sale' as source")
        )
            ->leftJoin('shift_total', 'shift_total.id', '=', 'shift_sale.shift_id')
            ->leftJoin('products', 'products.id', '=', 'shift_total.product_id')
            ->whereBetween('shift_total.start_date', [$startDate, $endDate])
            ->where('shift_sale.net_profit_amount', '!=', 0)
            ->where('shift_total.client_company_id', $sessionUser['client_company_id']);

        // Apply product filter if provided
        if (!empty($productId)) {
            $shiftSale->where('shift_total.product_id', $productId);
        }

        // Query for tank refills data
        $tankRefill = TankRefill::select(
            'tank_refill_total.id',
            'tank_refill_total.date',
            'tank_refill.net_profit',
            'products.name as product_name',
            DB::raw('tank_refill.net_profit_amount as amount'),
            DB::raw("'Tank Refill' as source")
        )
            ->leftJoin('tank_refill_total', 'tank_refill_total.id', '=', 'tank_refill.refill_id')
            ->leftJoin('tank', 'tank.id', '=', 'tank_refill.tank_id')
            ->leftJoin('products', 'products.id', '=', 'tank.product_id')
            ->whereBetween('tank_refill_total.date', [$request['start_date'], $request['end_date']])
            ->whereNotNull('tank_refill.net_profit_amount')
            ->where('tank_refill.net_profit_amount', '!=', 0)
            ->where('tank_refill_total.client_company_id', $sessionUser['client_company_id']);

        // Apply product filter if provided
        if (!empty($productId)) {
            $tankRefill->where('products.id', $productId);
        }

        // Combine shift sale and tank refill results and order by date
        $queryResult = $shiftSale->union($tankRefill)
            ->orderBy('date', 'ASC')
            ->get()
            ->toArray();

        // Initialize total amount
        $total = 0;

        // Format results and calculate total amount
        foreach ($queryResult as &$result) {
            $result['status'] = $result['net_profit'] > 0 ? 'Profit' : 'Loss';
            $result['quantity'] = number_format(abs($result['net_profit']), 2);
            $result['date'] = date('d/m/Y', strtotime($result['date']));
            $total += $result['amount'];
            $result['amount'] = number_format(abs($result['amount']), 2);
        }

        // Return the windfall report data as a JSON response
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
     * Generates a credit company report based on provided date range and category filter.
     *
     * This method calculates the opening balance, bill amounts, and paid amounts for a specified
     * account category within a given date range. It also computes the due amount for each date
     * in the report and formats the results for output.
     *
     * @param Request $request The HTTP request containing the date range, category filter, and other parameters.
     * @return JsonResponse A JSON response containing the credit company report data.
     */
    public function creditCompanyReport(Request $request): JsonResponse
    {
        // Validate the input parameters
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date|string',
            'end_date' => 'required|date|string',
            'category_id' => 'required|integer'
        ], [
            'start_date.required' => 'The date field is required.',
            'category_id.required' => 'The company field is required.'
        ]);

        // Return validation errors if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Get the current session user
        $sessionUser = SessionUser::getUser();

        // Calculate the opening balance for the account category before the start date
        $openingBalance = Transaction::select(DB::raw('SUM(debit_amount - credit_amount) as opening_balance'))
            ->where('date', '<', $request['start_date'])
            ->where('account_id', $request['category_id'])
            ->first();

        // Default opening balance to 0 if no data is found
        $openingBalance = $openingBalance['opening_balance'] ?? 0;

        // Query for transactions within the date range, grouped by date
        $result = Transaction::select(
            'date',
            'categories.name as company_name',
            DB::raw('SUM(credit_amount) as paid_amount'),
            DB::raw('SUM(debit_amount) as bill_amount')
        )
            ->whereBetween('date', [$request['start_date'], $request['end_date']])
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->where('account_id', $request['category_id'])
            ->groupBy('date')
            ->get()
            ->toArray();

        // Initialize the due amount with the opening balance
        $dueAmount = $openingBalance;

        // Process and format the results
        foreach ($result as &$item) {
            $dueAmount += $item['bill_amount'] - $item['paid_amount'];
            $item['due_amount'] = $dueAmount;
            $item['bill_amount'] = !empty($item['bill_amount']) ? number_format($item['bill_amount'], 2) : '';
            $item['paid_amount'] = !empty($item['paid_amount']) ? number_format($item['paid_amount'], 2) : '';
            $item['due_amount'] = !empty($item['due_amount']) ? number_format($item['due_amount'], 2) : '';
        }

        // Return the credit company report data as a JSON response
        return response()->json([
            'status' => 200,
            'data' => $result,
            'opening_balance' => [
                'amount' => $openingBalance,
                'amount_format' => number_format(abs($openingBalance), 2)
            ]
        ]);
    }

    public function driverReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date|string',
            'end_date' => 'required|date|string',
        ],[
            'start_date.required' => 'The date field is required.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }
        $companyId = $request->input('company_id', '');
        $sessionUser = SessionUser::getUser();
        $result = Transaction::select('transactions.id', 'categories.name as company_name', 'transactions.date', 'car.car_number', 'transactions.voucher_no', DB::raw('SUM(transactions.debit_amount) as bill'), DB::raw('SUM(transactions.quantity) as quantity'))
            ->leftJoin('car', 'car.id', '=', 'transactions.car_id')
            ->leftJoin('categories', 'categories.id', '=', 'transactions.linked_id')
            ->whereBetween('transactions.date', [$request['start_date'], $request['end_date']])
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->whereNotNull('transactions.car_id');

        if (!empty($companyId)) {
            $result->where(function($q) use ($companyId){
                $q->where('transactions.linked_id', $companyId);
            });
        }
        $result = $result->groupBy('transactions.date')
            ->groupBy('transactions.car_id')
            ->orderBy('transactions.date', 'ASC')
            ->get()
            ->toArray();
        $totalBill = 0;
        $totalQuantity = 0;
        foreach ($result as &$item) {
            $totalBill += $item['bill'];
            $totalQuantity += $item['quantity'];
            $item['date'] = date('d/m/Y', strtotime($item['date']));
            $item['bill'] = !empty($item['bill']) ? number_format($item['bill'], 2) : '';
            $item['quantity'] = !empty($item['quantity']) ? number_format($item['quantity'], 2) : '';
        }
        return response()->json([
            'status' => 200,
            'data' => $result,
            'total'=> [
                'bill' => number_format($totalBill, 2),
                'quantity' => number_format($totalQuantity, 2),
            ]
        ]);

    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function stockSummary(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }
        $sessionUser = SessionUser::getUser();
        $products = Product::select('products.id', 'products.name as product_name', 'product_types.tank', 'products.selling_price')
            ->leftJoin('product_types', 'product_types.id', '=', 'products.type_id')
            ->where('products.client_company_id', $sessionUser['client_company_id'])
            ->where('product_types.tank', '1')
            ->with(['tanks' => function($q) {
                $q->select('id', 'product_id', 'tank_name', 'opening_stock');
            },'tanks.dispensers' => function($q) {
                $q->select('id', 'tank_id', 'dispenser_name');
            }, 'tanks.dispensers.nozzle' => function($q) {
                $q->select('id', 'dispenser_id', 'name as nozzle_name', 'opening_stock');
            }])
            ->get()
            ->toArray();
        $productIds = array_column($products, 'id');
        $startDate = Carbon::parse($request->get('date'), SessionUser::TIMEZONE)->startOfDay();
        $endDate = Carbon::parse($request->get('date'), SessionUser::TIMEZONE)->endOfDay();

        $shiftSale = ShiftTotal::select(
            'shift_sale.tank_id',
            'shift_summary.nozzle_id',
            DB::raw('MIN(shift_summary.start_reading) as start_reading'),
            DB::raw('MAX(shift_summary.end_reading) as end_reading'),
            DB::raw('MIN(shift_sale.start_reading) as tank_start_reading'),
            DB::raw('MAX(shift_sale.end_reading) as tank_end_reading'),
        )
            ->leftJoin('shift_sale', 'shift_sale.shift_id', '=', 'shift_total.id')
            ->leftJoin('shift_summary', 'shift_summary.shift_sale_id', '=', 'shift_sale.id')
            ->where('shift_total.client_company_id', $sessionUser['client_company_id'])
            ->whereBetween('start_date', [$startDate, $endDate])
            ->where('shift_total.status', FuelMatixStatus::END)
            ->whereNotNull('shift_summary.nozzle_id')
            ->groupBy('shift_summary.nozzle_id', 'shift_sale.tank_id')
            ->get()
            ->toArray();
        // Initialize arrays to hold the results
        $shiftSaleByNozzleId = [];
        $shiftSaleByTankId = [];

        // Organize the results into two separate arrays
        foreach ($shiftSale as $sale) {
            $tankId = $sale['tank_id'];
            $nozzleId = $sale['nozzle_id'];

            // Add to array keyed by nozzle_id
            $shiftSaleByNozzleId[$nozzleId] = $sale;

            // Add to array keyed by tank_id
            $shiftSaleByTankId[$tankId] = $sale;
        }


        $fuelAdjustment = FuelAdjustment::select('fuel_adjustment.product_id', DB::raw('SUM(fuel_adjustment_data.quantity) as total_quantity'))
            ->leftJoin('fuel_adjustment_data', 'fuel_adjustment_data.fuel_adjustment_id', '=', 'fuel_adjustment.id')
            ->where('fuel_adjustment.client_company_id', $sessionUser['client_company_id'])
            ->whereNotNull('nozzle_id')
            ->whereIn('product_id', $productIds)
            ->whereBetween('fuel_adjustment.date', [$startDate, $endDate])
            ->groupBy('fuel_adjustment.product_id')
            ->get()
            ->keyBy('product_id')
            ->toArray();

        $tankRefill = TankRefillTotal::select(DB::raw('SUM(tank_refill.dip_sale) as volume'), 'tank_refill.tank_id')
            ->leftJoin('tank_refill', 'tank_refill.refill_id', '=', 'tank_refill_total.id')
            ->where('tank_refill_total.client_company_id', $sessionUser['client_company_id'])
            ->where('tank_refill_total.date', $request->input('date'))
            ->get()
            ->keyBy('tank_id')
            ->toArray();

        $payOrder = PayOrderData::select(DB::raw('SUM(pay_order_data.quantity) as quantity'), 'pay_order_data.product_id')
            ->leftJoin('pay_order', 'pay_order.id', '=', 'pay_order_data.pay_order_id')
            ->where('status', FuelMatixStatus::PENDING)
            ->where('pay_order.client_company_id', $sessionUser['client_company_id'])
            ->whereIn('product_id', $productIds)
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id')
            ->toArray();
        foreach ($products as &$product) {
            $totalSale = 0;
            $totalEndReading = 0;
            $totalRefill = 0;
            $totalAmount = 0;
            foreach ($product['tanks'] as &$tank) {
                foreach ($tank['dispensers'] as &$dispenser) {
                    foreach ($dispenser['nozzle'] as &$nozzle) {
                        $nozzle['start_reading'] = $shiftSaleByNozzleId[$nozzle['id']]['start_reading'] ?? 0;
                        $nozzle['end_reading'] = $shiftSaleByNozzleId[$nozzle['id']]['end_reading'] ?? 0;
                        $nozzle['sale'] = $nozzle['end_reading'] - $nozzle['start_reading'];
                        $nozzle['start_reading_format'] = $nozzle['start_reading'] > 0 ?  number_format($nozzle['start_reading'], 2) : '-';
                        $nozzle['end_reading_format'] = $nozzle['end_reading'] > 0 ? number_format($nozzle['end_reading'], 2) : '-';
                        $nozzle['sale_format'] = $nozzle['sale'] > 0 ? number_format($nozzle['sale'], 2) : '-';
                        $totalSale += $nozzle['sale'];
                        $nozzle['unit_price_format'] = number_format($product['selling_price'], 2);
                        $nozzle['amount'] = $nozzle['sale'] * $product['selling_price'];
                        $nozzle['amount_format'] = $nozzle['amount'] > 0 ?number_format($nozzle['amount'], 2) : '-';
                        $totalAmount += $nozzle['amount'];
                    }
                }
                $tank['end_reading'] = $shiftSaleByTankId[$tank['id']]['tank_end_reading'] ?? 0;
                if (isset($tankRefill[$tank['id']]['volume'])) {
                    $tank['refill'] = $tankRefill[$tank['id']]['volume'];
                } else {
                    $tank['refill'] = 0;
                }
                $tank['end_reading_format'] = $tank['end_reading'] > 0 ? number_format($tank['end_reading'], 2) : '-';
                $totalEndReading += $tank['end_reading'];
                $totalRefill += $tank['refill'];
            }
            $product['total'] = $totalSale > 0 ? number_format($totalSale, 2) : '-';
            $product['subtotal_amount'] = $totalAmount > 0 ? number_format($totalAmount, 2) : '-';
            $adjustment = $fuelAdjustment[$product['id']]['total_quantity'] ?? 0;
            $product['adjustment'] = $adjustment > 0 ? number_format($adjustment, 2) : '-' ;
            $product['adjustment_amount'] = $adjustment > 0 ? number_format($adjustment * $product['selling_price'], 2) : '-' ;

            $totalQuantity = $totalSale - $adjustment;
            $product['total_sale'] = $totalQuantity > 0 ? number_format($totalQuantity, 2) : '-';
            $product['total_amount'] = ($totalAmount - ($adjustment * $product['selling_price'])) > 0 ? number_format($totalAmount - ($adjustment * $product['selling_price']), 2) : '-';
            $product['end_reading'] = $totalEndReading > 0 ? number_format($totalEndReading, 2) : '-';
            $product['tank_refill'] = $totalRefill > 0 ? number_format($totalRefill, 2) : '-';
            $totalByProduct = $totalEndReading + $totalRefill;
            $product['total_by_product'] = $totalByProduct > 0 ? number_format($totalByProduct, 2) : '-';
            $payOrderQuantity = $payOrder[$product['id']]['quantity'] ?? 0;
            $product['pay_order'] = $payOrderQuantity > 0 ? number_format($payOrderQuantity, 2) : '-';
            $product['closing_balance'] = ($totalEndReading + $payOrderQuantity) > 0 ? number_format($totalEndReading + $payOrderQuantity, 2) : '-';
            $gainLoss = $totalByProduct != 0 && $totalQuantity != 0 ? ($totalByProduct - $totalQuantity) / $totalQuantity : 0 ;
            $product['gain_loss'] = $gainLoss;
            $product['gain_loss_format'] = $gainLoss > 0 ? number_format(abs($gainLoss), 2) .'%' : '-';
        }
        $accountReceivable = Category::where('client_company_id', $sessionUser['client_company_id'])->where('slug', strtolower( AccountCategory::ACCOUNT_RECEIVABLE))->first();
        $transaction = Transaction::select('transactions.account_id as id',  DB::raw("SUM(transactions.debit_amount) as amount"), 'categories.name', DB::raw('SUM(transactions.quantity) as quantity'), 'c1.name as product_name')
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->leftJoin('transactions as t1', 't1.linked_id', '=', 'transactions.id')
            ->leftJoin('categories as c1', 'c1.id', '=', 't1.account_id')
            ->where('transactions.date', $request->input('date'))
            ->where('categories.parent_category', $accountReceivable->id)
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->having('amount', '>', 0)
            ->groupBy('transactions.id')
            ->get()
            ->toArray();
        $totalQuantity = 0;
        $totalAmount = 0;
        foreach ($transaction as &$data) {
            $data['amount_format'] = number_format($data['amount'], 2);
            $totalQuantity += $data['quantity'];
            $totalAmount += $data['amount'];
        }
        return response()->json([
            'status' => 200,
            'data' => $products,
            'companySales' => $transaction,
            'total' => [
                'quantity' => number_format($totalQuantity, 2),
                'amount' => number_format($totalAmount, 2)
            ]
        ]);
    }
}
