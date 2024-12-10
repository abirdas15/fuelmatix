<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\FuelMatixDateTimeFormat;
use App\Common\FuelMatixStatus;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\ClientCompany;
use App\Models\Product;
use App\Models\Sale;
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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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
            'end_date' => 'required|date',
            'product_id' => 'required|integer'
        ]);

        // Return validation errors if validation fails
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $product = Product::find($request->input('product_id'));
        if (!$product instanceof Product) {
            return response()->json([
                'status' => 404,
                'errors' => 'Invalid product'
            ]);
        }
        $filter = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];
        $response = ReportRepository::salesReport($filter, $product);
        return response()->json([
            'status' => 200,
            'data' => $response
        ]);
    }
    public static function salesReportReportPDF(Request $request)
    {
        // Validate the input parameters
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'product_id' => 'required|integer'
        ]);

        // Return validation errors if validation fails
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $product = Product::find($request->input('product_id'));
        if (!$product instanceof Product) {
            return response()->json([
                'status' => 404,
                'errors' => 'Invalid product'
            ]);
        }
        $filter = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];
        $response = ReportRepository::salesReport($filter, $product);
        $sessionUser = SessionUser::getUser();
        $company = ClientCompany::where('id', $sessionUser['client_company_id'])->first();
        $pdf = Pdf::loadView('pdf.sales-report', [
            'data' => $response,
            'date' => Carbon::parse($request->input('start_date'))->format('F j, Y').' - '.Carbon::parse($request->input('end_date'))->format('F j, Y'),
            'company' => $company,
            'print_at' => Carbon::now()->format('F j, Y h:i A'),
        ]);
        $pdf->setPaper('a4', 'landscape');
        return $pdf->output();
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
        $filter = [
            'product_id' => $request->input('product_id', ''),
            'start_date' => $request->input('start_date', ''),
            'end_date' => $request->input('end_date', ''),
        ];

        $result = ReportRepository::windfallReport($filter);
        // Return the windfall report data as a JSON response
        return response()->json($result);
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

        $filter = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'category_id' => $request->input('category_id')
        ];

       $result = ReportRepository::creditCompanyReport($filter);

        // Return the credit company report data as a JSON response
        return response()->json($result);
    }
    /**
     * @param Request $request
     * @return string
     */
    public function creditCompanyReportExportPDF(Request $request): string
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
        $creditCompany = Category::where('id', $request->input('category_id'))->first();
        if (!$creditCompany instanceof Category) {
            return response()->json([
                'status' => 404,
                'message' => 'Cannnot find company.'
            ]);
        }
        $filter = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'category_id' => $request->input('category_id')
        ];

        $result = ReportRepository::creditCompanyReport($filter);
        $sessionUser = SessionUser::getUser();
        $company = ClientCompany::where('id', $sessionUser['client_company_id'])->first();
        $pdf = Pdf::loadView('pdf.credit-company-report', [
            'data' => $result,
            'date' => Carbon::parse($request->input('start_date'))->format('F j, Y').' - '.Carbon::parse($request->input('end_date'))->format('F j, Y'),
            'company' => $company,
            'print_at' => Carbon::now()->format('F j, Y h:i A'),
            'name' => $creditCompany->name
        ]);
        return $pdf->output();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function driverReport(Request $request): JsonResponse
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
        $filter = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'company_id' => $request->input('company_id', ''),
            'car_number' => $request->input('car_number', '')
        ];
        $result = ReportRepository::driverReport($filter);
        return response()->json($result);

    }
    /**
     * @param Request $request
     * @return string
     */
    public function driverReportExportPDF(Request $request):string
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
        $filter = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'company_id' => $request->input('company_id', ''),
            'car_number' => $request->input('car_number', '')
        ];
        $creditCompany = Category::where('id', $request->input('company_id'))->first();
        $result = ReportRepository::driverReport($filter);
        $sessionUser = SessionUser::getUser();
        $company = ClientCompany::where('id', $sessionUser['client_company_id'])->first();
        $pdf = Pdf::loadView('pdf.driver-report', [
            'data' => $result,
            'date' => Carbon::parse($request->input('date'))->format('F j, Y'),
            'company' => $company,
            'print_at' => Carbon::now()->format('F j, Y h:i A'),
            'name' => $creditCompany->name ?? ''
        ]);
        return $pdf->output();

    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function stockSummary(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }
        $result = ReportRepository::stockSummary($request->input('date'));
        return response()->json($result);
    }
    /**
     * @param Request $request
     * @return string
     */
    public function stockSummaryExportPDF(Request $request): string
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }
        $sessionUser = SessionUser::getUser();
        $company = ClientCompany::where('id', $sessionUser['client_company_id'])->first();
        $summary = ReportRepository::stockSummary($request->input('date'));
        $pdf = Pdf::loadView('pdf.stock-summary', [
            'data' => $summary,
            'date' => $request->input('date'),
            'company' => $company,
            'print_at' => Carbon::now()->format('F j, Y h:i A')
        ]);
        return $pdf->output();
    }
    public function stockSummaryExportExcel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }
        $sessionUser = SessionUser::getUser();
        $company = ClientCompany::where('id', $sessionUser['client_company_id'])->first();
        $data = ReportRepository::stockSummary($request->input('date'));

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

// Set the header
        $sheet->setCellValue('A1', 'Stock Summary');
        $sheet->setCellValue('A2', 'Date: ' . $request->input('date'));

// Apply bold style for headers
        $headerFont = [
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
        ];

// Set header styles
        $sheet->getStyle('A1')->applyFromArray($headerFont);
        $sheet->getStyle('A2')->applyFromArray($headerFont);

        $row = 4; // Start from the 4th row

// Iterate through products to display stock details
        foreach ($data['data'] as $product) {
            $sheet->setCellValue('A' . $row, $product['product_name']);
            $sheet->getStyle('A' . $row)->applyFromArray($headerFont);
            $row++;

            // Table Header
            $sheet->setCellValue('A' . $row, 'Nozzle');
            $sheet->setCellValue('B' . $row, 'Current Meter');
            $sheet->setCellValue('C' . $row, 'Previous Meter');
            $sheet->setCellValue('D' . $row, 'Sale');
            $sheet->setCellValue('E' . $row, 'Unit Price');
            $sheet->setCellValue('F' . $row, 'Amount');

            // Apply background color for the header
            $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['argb' => Color::COLOR_YELLOW],
                ],
            ]);
            $row++;

            foreach ($product['tanks'] as $tank) {
                foreach ($tank['dispensers'] as $dispenser) {
                    foreach ($dispenser['nozzle'] as $nozzle) {
                        $sheet->setCellValue('A' . $row, $nozzle['nozzle_name']);
                        $sheet->setCellValue('B' . $row, $nozzle['end_reading_format']);
                        $sheet->setCellValue('C' . $row, $nozzle['start_reading_format']);
                        $sheet->setCellValue('D' . $row, $nozzle['sale_format']);
                        $sheet->setCellValue('E' . $row, $nozzle['unit_price_format']);
                        $sheet->setCellValue('F' . $row, $nozzle['amount_format']);
                        $row++;
                    }
                }
            }

            // Subtotal
            $sheet->setCellValue('D' . $row, 'Sub Total:');
            $sheet->setCellValue('E' . $row, $product['total']);
            $sheet->setCellValue('F' . $row, $product['subtotal_amount']);
            $sheet->getStyle('D' . $row)->applyFromArray($headerFont);
            $row++;

            // Less adjustment
            $sheet->setCellValue('D' . $row, 'Less: Meter Test');
            $sheet->setCellValue('E' . $row, $product['adjustment']);
            $sheet->setCellValue('F' . $row, $product['adjustment_amount']);
            $sheet->getStyle('D' . $row)->applyFromArray($headerFont);
            $row++;

            // Total
            $sheet->setCellValue('D' . $row, 'Total');
            $sheet->setCellValue('E' . $row, $product['total_sale']);
            $sheet->setCellValue('F' . $row, $product['total_amount']);
            $sheet->getStyle('D' . $row)->applyFromArray($headerFont);
            $row++;
        }

// Grand Total
        $sheet->setCellValue('E' . $row, 'Grand Total:');
        $sheet->setCellValue('F' . $row, $data['total']['grandTotal']);
        $sheet->getStyle('E' . $row)->applyFromArray($headerFont);
        $row += 2; // Add some space before the next section

// Received and Under Tank Summary
        $sheet->setCellValue('A' . $row, 'Received and Under Tank Summary');
        $sheet->getStyle('A' . $row)->applyFromArray($headerFont);
        $row++;
        $sheet->setCellValue('A' . $row, 'U/Tank Name');
        $sheet->setCellValue('B' . $row, 'Previous Balance');
        $sheet->setCellValue('C' . $row, 'Receive');
        $sheet->setCellValue('D' . $row, 'Total');
        $sheet->setCellValue('E' . $row, 'Gain/Loss Ratio');

// Apply background color for the header
        $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => Color::COLOR_YELLOW],
            ],
        ]);
        $row++;

        foreach ($data['data'] as $product) {
            $sheet->setCellValue('A' . $row, $product['product_name']);
            $sheet->setCellValue('B' . $row, $product['end_reading']);
            $sheet->setCellValue('C' . $row, $product['tank_refill']);
            $sheet->setCellValue('D' . $row, $product['total_by_product']);
            $sheet->setCellValue('E' . $row, $product['gain_loss_format']);
            $row++;

            // Under Tank details
            $sheet->setCellValue('A' . $row, $product['product_name'] . ' Under Tank');
            $sheet->getStyle('A' . $row)->applyFromArray($headerFont);
            $row++;
            $sheet->setCellValue('A' . $row, 'U/Tank Name');
            $sheet->setCellValue('B' . $row, 'U/Tank as per DIP');
            $sheet->setCellValue('C' . $row, 'In Tank Lorry');
            $sheet->setCellValue('D' . $row, 'Closing Balance');

            // Apply background color for the header
            $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['argb' => Color::COLOR_YELLOW],
                ],
            ]);
            $row++;

            foreach ($product['tanks'] as $tank) {
                $sheet->setCellValue('A' . $row, $tank['tank_name']);
                $sheet->setCellValue('B' . $row, $tank['end_reading_format']);
                $sheet->setCellValue('C' . $row, $product['pay_order']);
                $sheet->setCellValue('D' . $row, $product['closing_balance']);
                $row++;
            }
        }

        // Company Sales
        $sheet->setCellValue('A' . $row, 'Company Sale');
        $sheet->getStyle('A' . $row)->applyFromArray($headerFont);
        $row++;
        $sheet->setCellValue('A' . $row, 'Company Name');
        $sheet->setCellValue('B' . $row, 'Product Name');
        $sheet->setCellValue('C' . $row, 'Quantity');
        $sheet->setCellValue('D' . $row, 'Amount');

        // Apply background color for the header
        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => Color::COLOR_YELLOW],
            ],
        ]);
        $row++;

        foreach ($data['companySales'] as $companySales) {
            $sheet->setCellValue('A' . $row, $companySales['name']);
            $sheet->setCellValue('B' . $row, $companySales['product_name']);
            $sheet->setCellValue('C' . $row, $companySales['quantity']);
            $sheet->setCellValue('D' . $row, $companySales['amount_format']);
            $row++;
        }

        // Total for Company Sales
        $sheet->setCellValue('B' . $row, 'Total:');
        $sheet->setCellValue('C' . $row, $data['total']['quantity']);
        $sheet->setCellValue('D' . $row, $data['total']['amount']);
        $sheet->getStyle('B' . $row)->getFont()->setBold(true);
        $sheet->getStyle('C' . $row)->getFont()->setBold(true);
        $sheet->getStyle('D' . $row)->getFont()->setBold(true);
        $row += 2;

        // Company Paid
        $sheet->setCellValue('A' . $row, 'Company Paid');
        $sheet->getStyle('A' . $row)->applyFromArray($headerFont);
        $row++;
        $sheet->setCellValue('A' . $row, 'Company Name');
        $sheet->setCellValue('B' . $row, 'Payment Method');
        $sheet->setCellValue('C' . $row, 'Amount');

        // Apply background color for the header
        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => Color::COLOR_YELLOW],
            ],
        ]);
        $row++;

        foreach ($data['companyPaid'] as $each) {
            $sheet->setCellValue('A' . $row, $each['name']);
            $sheet->setCellValue('B' . $row, $each['product_name']);
            $sheet->setCellValue('C' . $row, $each['paid_amount_format']);
            $row++;
        }

        // Total for Company Paid
        $sheet->setCellValue('B' . $row, 'Total:');
        $sheet->setCellValue('C' . $row, $data['total']['paid_amount']);
        $sheet->getStyle('B' . $row)->getFont()->setBold(true);
        $sheet->getStyle('C' . $row)->getFont()->setBold(true);
        $row += 2;

        // Credit Company Product Sale
        $sheet->setCellValue('A' . $row, 'Credit Company Product Sale');
        $sheet->getStyle('A' . $row)->applyFromArray($headerFont);
        $row++;
        $sheet->setCellValue('A' . $row, 'Product Name');
        $sheet->setCellValue('B' . $row, 'Quantity');
        $sheet->setCellValue('C' . $row, 'Amount');

        // Apply background color for the header
        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => Color::COLOR_YELLOW],
            ],
        ]);
        $row++;

        foreach ($data['productSales'] as $productSale) {
            $sheet->setCellValue('A' . $row, $productSale['product_name']);
            $sheet->setCellValue('B' . $row, $productSale['quantity']);
            $sheet->setCellValue('C' . $row, $productSale['amount_format']);
            $row++;
        }

        // Total for Product Sales
        $sheet->setCellValue('A' . $row, 'Total:');
        $sheet->setCellValue('B' . $row, $data['total']['quantity']);
        $sheet->setCellValue('C' . $row, $data['total']['amount']);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('B' . $row)->getFont()->setBold(true);
        $sheet->getStyle('C' . $row)->getFont()->setBold(true);
        $row += 2;

        // Expenses
        $sheet->setCellValue('A' . $row, 'Expense');
        $sheet->getStyle('A' . $row)->applyFromArray($headerFont);
        $row++;
        $sheet->setCellValue('A' . $row, 'Expense Category');
        $sheet->setCellValue('B' . $row, 'Payment Type');
        $sheet->setCellValue('C' . $row, 'Amount');

        // Apply background color for the header
        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => Color::COLOR_YELLOW],
            ],
        ]);
        $row++;

        foreach ($data['expenses'] as $expense) {
            $sheet->setCellValue('A' . $row, $expense['expense_type']);
            $sheet->setCellValue('B' . $row, $expense['payment_method']);
            $sheet->setCellValue('C' . $row, $expense['amount_format']);
            $row++;
        }

        // Total for Expenses
        $sheet->setCellValue('B' . $row, 'Total:');
        $sheet->setCellValue('C' . $row, $data['total']['expense']);
        $sheet->getStyle('B' . $row)->getFont()->setBold(true);
        $sheet->getStyle('C' . $row)->getFont()->setBold(true);
        $row += 2;

        // POS Sale
        $sheet->setCellValue('A' . $row, 'Pos Sale');
        $sheet->getStyle('A' . $row)->applyFromArray($headerFont);
        $row++;
        $sheet->setCellValue('A' . $row, 'Name');
        $sheet->setCellValue('B' . $row, 'Quantity');
        $sheet->setCellValue('C' . $row, 'Unit Price');
        $sheet->setCellValue('D' . $row, 'Amount');

        // Apply background color for the header
        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => Color::COLOR_YELLOW],
            ],
        ]);
        $row++;

        foreach ($data['posSales'] as $posSale) {
            $sheet->setCellValue('A' . $row, $posSale['category_name']);
            $sheet->setCellValue('B' . $row, $posSale['quantity']);
            $sheet->setCellValue('C' . $row, $posSale['price']);
            $sheet->setCellValue('D' . $row, $posSale['amount']);
            $row++;
        }

        // Total for POS Sales
        $sheet->setCellValue('C' . $row, 'Total:');
        $sheet->setCellValue('D' . $row, $data['total']['posSaleTotalAmount']);
        $sheet->getStyle('C' . $row)->getFont()->setBold(true);
        $sheet->getStyle('D' . $row)->getFont()->setBold(true);
        $row += 2;

        // Asset Transfer
        $sheet->setCellValue('A' . $row, 'Asset Transfer');
        $sheet->getStyle('A' . $row)->applyFromArray($headerFont);
        $row++;
        $sheet->setCellValue('A' . $row, 'From');
        $sheet->setCellValue('B' . $row, 'To');
        $sheet->setCellValue('C' . $row, 'Amount');

        // Apply background color for the header
        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => Color::COLOR_YELLOW],
            ],
        ]);
        $row++;

        foreach ($data['assetTransfer'] as $assetTransfer) {
            $sheet->setCellValue('A' . $row, $assetTransfer['from_category']);
            $sheet->setCellValue('B' . $row, $assetTransfer['to_category']);
            $sheet->setCellValue('C' . $row, $assetTransfer['amount']);
            $row++;
        }

        // Total for Asset Transfer
        $sheet->setCellValue('B' . $row, 'Total:');
        $sheet->setCellValue('C' . $row, $data['total']['totalTransferAmount']);
        $sheet->getStyle('B' . $row)->getFont()->setBold(true);
        $sheet->getStyle('C' . $row)->getFont()->setBold(true);

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }
    public function windfallReportPDF(Request $request)
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
        $filter = [
            'product_id' => $request->input('product_id', ''),
            'start_date' => $request->input('start_date', ''),
            'end_date' => $request->input('end_date', ''),
        ];

        $result = ReportRepository::windfallReport($filter);
        $sessionUser = SessionUser::getUser();
        $company = ClientCompany::where('id', $sessionUser['client_company_id'])->first();
        $pdf = Pdf::loadView('pdf.windfall-report', [
            'data' => $result,
            'date' => Carbon::parse($request->input('start_date'))->format('F j, Y').' - '.Carbon::parse($request->input('end_date'))->format('F j, Y'),
            'company' => $company,
            'print_at' => Carbon::now()->format('F j, Y h:i A')
        ]);
        return $pdf->output();
    }
    /**
     * @param Request $request
     * @return string
     */
    public function vendorReportExportPDF(Request $request): string
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'vendor_id' => 'required|integer'
        ]);

        // Return validation errors if validation fails
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $vendor = Category::where('id', $request->input('vendor_id'))->first();
        if (!$vendor instanceof Category) {
            return response()->json([
                'status' => 404,
                'message' => 'Cannot find vendor.'
            ]);
        }
        $filter = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'vendor_id' => $request->input('vendor_id')
        ];

        $result = ReportRepository::vendorReport($filter);
        $sessionUser = SessionUser::getUser();
        $company = ClientCompany::where('id', $sessionUser['client_company_id'])->first();
        $pdf = Pdf::loadView('pdf.vendor-report', [
            'data' => $result,
            'date' => Carbon::parse($request->input('start_date'))->format('F j, Y').' - '.Carbon::parse($request->input('end_date'))->format('F j, Y'),
            'company' => $company,
            'print_at' => Carbon::now()->format('F j, Y h:i A'),
            'vendor_name' => $vendor->name
        ]);
        return $pdf->output();
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function companySummary(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date|string',
            'end_date' => 'required|date|string',
            'company_id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }
        $response = ReportRepository::companySummary([
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'company_id' => $request->input('company_id')
        ]);
        return response()->json($response);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function companySummaryExportPDF(Request $request): string
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date|string',
            'end_date' => 'required|date|string',
            'company_id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }
        $response = ReportRepository::companySummary([
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'company_id' => $request->input('company_id')
        ]);
        $sessionUser = SessionUser::getUser();
        $company = ClientCompany::where('id', $sessionUser['client_company_id'])->first();
        $pdf = Pdf::loadView('pdf.company-summary', [
            'data' => $response,
            'date' => Carbon::parse($request->input('start_date'))->format('F j, Y').' - '.Carbon::parse($request->input('end_date'))->format('F j, Y'),
            'company' => $company,
            'print_at' => Carbon::now()->format('F j, Y h:i A'),
        ]);
        return $pdf->output();
    }
    public function companySummaryDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date|string',
            'end_date' => 'required|date|string',
            'company_id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }
        $response = ReportRepository::companySummaryDetails([
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'company_id' => $request->input('company_id')
        ]);
        return response()->json($response);
    }
    public function companySummaryDetailsExportPDF(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date|string',
            'end_date' => 'required|date|string',
            'company_id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }
        $creditCompany = Category::where('id', $request->input('company_id'))->first();
        $response = ReportRepository::companySummaryDetails([
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'company_id' => $request->input('company_id')
        ]);
        $sessionUser = SessionUser::getUser();
        $company = ClientCompany::where('id', $sessionUser['client_company_id'])->first();
        $pdf = Pdf::loadView('pdf.company-summary-details', [
            'data' => $response,
            'date' => Carbon::parse($request->input('start_date'))->format('F j, Y').' - '.Carbon::parse($request->input('end_date'))->format('F j, Y'),
            'company' => $company,
            'print_at' => Carbon::now()->format('F j, Y h:i A'),
            'name' => $creditCompany->name
        ]);
        return $pdf->output();
    }
    public function posMachine(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required',
            'end_date' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }
        $filter = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];
        $result = ReportRepository::posMachineReport($filter);
        return response()->json($result);
    }
    public function posMachineExportPdf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required',
            'end_date' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }
        $sessionUser = SessionUser::getUser();
        $company = ClientCompany::where('id', $sessionUser['client_company_id'])->first();
        $filter = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];
        $result = ReportRepository::posMachineReport($filter);
        $pdf = Pdf::loadView('pdf.pos-machine-report', [
            'data' => $result,
            'company' => $company,
        ]);
        $pdf->setPaper('a4', 'landscape');
        return $pdf->output();
    }

}
