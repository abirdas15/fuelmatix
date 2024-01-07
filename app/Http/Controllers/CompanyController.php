<?php

namespace App\Http\Controllers;

use App\Helpers\SessionUser;
use App\Models\ClientCompany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function single(): JsonResponse
    {
        $sessionUser = SessionUser::getUser();
        $result = ClientCompany::where('id', $sessionUser['client_company_id'])->first();
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request)
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $sessionUser = SessionUser::getUser();
        $company = ClientCompany::where('id', $sessionUser['client_company_id'])->first();
        if (!$company instanceof ClientCompany) {
            return response()->json(['status' => 500, 'message' => 'Cannot find client company.']);
        }
        $company->name = $requestData['name'];
        $company->address = $requestData['address'] ?? null;
        $company->email = $requestData['email'] ?? null;
        $company->phone_number = $requestData['phone_number'] ?? null;
        $company->sale_mismatch_allow = $requestData['sale_mismatch_allow'] ?? null;
        $company->voucher_check = $requestData['voucher_check'] ?? 0;
        $company->header_text = $requestData['header_text'] ?? 0;
        $company->footer_text = $requestData['footer_text'] ?? 0;
        $company->expense_approve = $requestData['expense_approve'] ?? null;
        if ($company->save()) {
            return response()->json(['status' => 200, 'message' => 'Successfully saved company.']);
        }
        return response()->json(['status' => 500, 'message' => 'Cannot saved company.']);
    }
}
