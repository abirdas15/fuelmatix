<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanEntityRequest;
use App\Models\Category;
use Exception;
use Illuminate\Http\JsonResponse;
use App\Services\LoanEntityService;

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
}
