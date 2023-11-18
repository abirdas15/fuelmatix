<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CompanyBillController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'month' => 'required',
            'year' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $startDate = date('Y-m-01', strtotime($requestData['year'].'-'.$requestData['month'].'-01'));
        $endDate = date('Y-m-t', strtotime($startDate));
        $limit = $requestData['limit'] ?? 10;
        $sessionUser = SessionUser::getUser();
        $accountReceivable = Category::where('client_company_id', $sessionUser['client_company_id'])->where('slug', strtolower( AccountCategory::ACCOUNT_RECEIVABLE))->first();
        $result = Transaction::select('transactions.linked_id as id',  DB::raw("SUM(transactions.debit_amount) as amount"), 'categories.name')
            ->leftJoin('categories', 'categories.id', '=', 'transactions.linked_id')
            ->whereBetween('date', [$startDate, $endDate])
            ->where('categories.parent_category', $accountReceivable->id)
            ->where('transactions.debit_amount', '>', 0)
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->groupBy('transactions.linked_id')
            ->paginate($limit);
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function download(Request $request)
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'month' => 'required',
            'year' => 'required',
            'company_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $startDate = date('Y-m-01', strtotime($requestData['year'].'-'.$requestData['month'].'-01'));
        $endDate = date('Y-m-t', strtotime($startDate));
        $result = Transaction::select(DB::raw("SUM(transactions.debit_amount) as amount"), 'categories.name')
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->whereBetween('date', [$startDate, $endDate])
            ->where('transactions.linked_id', $request['company_id'])
            ->groupBy('transactions.account_id')
            ->get()
            ->toArray();
        $company = Category::find($requestData['company_id']);
        $others = json_decode($company['others']);
        $company['email'] = $others != null ? $others->email : null;
        $company['phone'] = $others != null ? $others->phone : null;
        $company['contact_person'] = $others != null ? $others->contact_person : null;
        $company['address'] = $others != null ? $others->address : null;
        $company['date'] = date('F, Y', strtotime($startDate));
        $pdf = Pdf::loadView('pdf.company-bill', ['data' => $result, 'company' => $company]);
        return $pdf->output();
    }
}
