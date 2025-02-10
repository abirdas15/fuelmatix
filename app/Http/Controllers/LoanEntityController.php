<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanEntityRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use App\Services\LoanEntityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoanEntityController extends Controller
{
    public $loanEntityService;
    public function __construct(LoanEntityService $loanEntityService)
    {
        $this->loanEntityService = $loanEntityService;
    }

    /**
     * @param LoanEntityRequest $request
     * @return JsonResponse
     */
    public function save(LoanEntityRequest $request): JsonResponse
    {
        try {
            $data = $request->except('session_user');
            $this->loanEntityService->save($data);
            return response()->json([
                'status' => 200,
                'message' => 'Loan entity saved successfully'
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 500,
                'message' => $exception->getMessage()
            ]);
        }
    }
    /**
     * @param LoanEntityRequest $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $data = $request->except('session_user');
            $response = $this->loanEntityService->list($data);
            return response()->json([
                'status' => 200,
                'data' => $response
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 500,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function single(Request $request): JsonResponse
    {
        try {
            // Retrieve all input data from the request
            $inputData = $request->all();

            // Validate that 'id' is required and must be an integer
            $validator = Validator::make($inputData, [
                'id' => 'required|integer'
            ]);

            // If validation fails, return a JSON response with validation errors and status 500
            if ($validator->fails()) {
                return response()->json([
                    'status' => 500,
                    'errors' => $validator->errors()
                ]);
            }
            $response = $this->loanEntityService->single($inputData['id']);
            return response()->json([
                'status' => 200,
                'data' => $response
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 500,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function update(LoanEntityRequest $request): JsonResponse
    {
        try {
            $data = $request->except('session_user');
            $this->loanEntityService->update($data);
            return response()->json([
                'status' => 200,
                'message' => 'Loan entity updated successfully'
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 500,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function delete(Request $request): JsonResponse
    {
        try {
            // Validate the request data: 'id' is required and must be an integer
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer'
            ]);

            // If validation fails, return a JSON response with validation errors and status 500
            if ($validator->fails()) {
                return response()->json([
                    'status' => 500,
                    'errors' => $validator->errors()
                ]);
            }

            $this->loanEntityService->delete($request['id']);
            return response()->json([
                'status' => 200,
                'message' => 'Loan entity deleted successfully'
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 500,
                'message' => $exception->getMessage()
            ]);
        }
    }
}
