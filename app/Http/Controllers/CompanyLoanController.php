<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyLoanPaymentRequest;
use App\Http\Requests\CompanyLoanRequest;
use App\Services\CompanyLoanService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $response = $this->companyLoanService->list($request->input('limit', 10));
            return response()->json([
                'status' => 200,
                'data' => $response
            ]);
        } catch (Exception $exception) {
            Log::error('Company Loan Save Error');
            Log::error($exception->getMessage());
            return response()->json([
                'status' => 500,
                'message' => $exception->getMessage()
            ]);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function single(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 500,
                    'errors'=> $validator->errors()
                ]);
            }
            $response = $this->companyLoanService->single($request->id);
            return response()->json($response);
        } catch (Exception $exception) {
            Log::error('Company Loan Save Error');
            Log::error($exception->getMessage());
            return response()->json([
                'status' => 500,
                'message' => $exception->getMessage()
            ]);
        }
    }


}
