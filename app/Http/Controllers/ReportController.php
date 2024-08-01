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
use App\Models\ShiftTotal;
use App\Models\TankRefill;
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

        // Query for shift sales data
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

        // Apply product filter if provided
        if (!empty($productId)) {
            $shiftSale->where('products.id', $productId);
        }

        // Query for tank refills data
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
        $openingBalance = Transaction::select(DB::raw('SUM(credit_amount - debit_amount) as opening_balance'))
            ->where('date', '<', $request['start_date'])
            ->where('account_id', $request['category_id'])
            ->first();

        // Default opening balance to 0 if no data is found
        $openingBalance = $openingBalance['opening_balance'] ?? 0;

        // Query for transactions within the date range, grouped by date
        $result = Transaction::select(
            'date',
            'categories.name as company_name',
            DB::raw('SUM(credit_amount) as bill_amount'),
            DB::raw('SUM(debit_amount) as paid_amount')
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
}
