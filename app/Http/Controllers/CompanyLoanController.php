<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyLoanPaymentRequest;
use App\Http\Requests\CompanyLoanRequest;
use App\Services\CompanyLoanService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CompanyLoanController extends Controller
{
    public $companyLoanService;
    public function __construct(CompanyLoanService $companyLoanService)
    {
        $this->companyLoanService = $companyLoanService;
    }

    /**
     * @param CompanyLoanRequest $request
     * @return JsonResponse
     */
    public function save(CompanyLoanRequest $request): JsonResponse
    {
        try {
            $data = $request->except('session_user');
            $this->companyLoanService->save($data);
            return response()->json([
                'status' => 200,
                'message' => 'Successfully save company loan.'
            ]);
        } catch (Exception $exception) {
            Log::error('Company Loan Save Error:');
            Log::error($exception->getMessage());
            return response()->json([
                'status' => 500,
                'message' => $exception->getMessage()
            ]);
        }
    }

    /**
     * @param CompanyLoanPaymentRequest $request
     * @return JsonResponse
     */
    public function payment(CompanyLoanPaymentRequest $request): JsonResponse
    {
        try {
            $data = $request->except('session_user');
            $this->companyLoanService->payment($data);
            return response()->json([
                'status' => 200,
                'message' => 'Successfully payment loan.'
            ]);
        } catch (Exception $exception) {
            Log::error('Company Loan Payment Error:');
            Log::error($exception->getMessage());
            return response()->json([
                'status' => 500,
                'message' => $exception->getMessage()
            ]);
        }
    }
}
