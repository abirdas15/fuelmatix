<?php

namespace App\Http\Controllers;

use App\Common\FuelMatixDateTimeFormat;
use App\Common\FuelMatixStatus;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\ClientCompany;
use App\Models\Product;
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
            'car_id' => $request->input('car_id', '')
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
            'date' => 'required|date',
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
            'date' => 'required|date',
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
            'date' => Carbon::parse($request->input('date'))->format('F j, Y'),
            'company' => $company,
            'print_at' => Carbon::now()->format('F j, Y h:i A')
        ]);
        return $pdf->output();
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

}
