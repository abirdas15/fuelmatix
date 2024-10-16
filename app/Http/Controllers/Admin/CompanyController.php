<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientCompany;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCompany(Request $request): JsonResponse
    {
        $orderBy = $request->input('order_by', 'id');
        $orderMode = $request->input('order_mode', 'DESC');
        $limit = $request->input('limit', 20);
        $keyword = $request->input('keyword') ?? '';
        $result = ClientCompany::select('id', 'name', 'address', 'phone_number', 'email');
        if (!empty($keyword)) {
            $result->where(function ($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%');
                $query->orWhere('phone_number', 'like', '%' . $keyword . '%');
                $query->orWhere('email', 'like', '%' . $keyword . '%');
            });
        }
        $result = $result->orderBy($orderBy, $orderMode)
            ->paginate($limit);
        return response()->json([
            'status' => 200,
            'companies' => $result,
            'message' => 'Successfully retrieved data!'
        ]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCompany(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'company.name' => 'required|string|max:255',
            'company.email' => 'required|string|email|max:255',
            'company.address' => 'required|string',
            'company.phone_number' => 'required|string|max:255',
            'company.sale_mismatch_allow' => 'nullable|numeric',
            'company.expense_approve' => 'nullable|numeric',
            'company.currency_precision' => 'nullable|numeric',
            'company.quantity_precision' => 'nullable|numeric',
            'user.name' => 'required|string|max:255',
            'user.email' => 'required|string|email|max:255',
            'user.password' => 'required|string|min:6|confirmed',
            'user.password_confirmation' => 'required|string|min:6',
            'user.phone' => 'nullable|string|max:255',
            'user.address' => 'nullable|string',
        ],[
            'company.name.required' => 'Company name is required!',
            'company.name.string' => 'Company name must be a string!',
            'company.name.max' => 'Company name is too long!',
            'company.email.required' => 'Company email is required!',
            'company.email.string' => 'Company email must be a string!',
            'company.email.max' => 'Company email is too long!',
            'company.email.email' => 'Company email must be a valid email!',
            'company.email.unique' => 'Company email has already been taken!',
            'company.address.required' => 'Company address is required!',
            'company.address.string' => 'Company address must be a string!',
            'company.phone_number.required' => 'Company phone number is required!',
            'company.phone_number.string' => 'Company phone number must be a string!',
            'company.phone_number.max' => 'Company phone number is too long!',
            'company.sale_mismatch_allow.numeric' => 'Sale mismatch allow must be numeric!',
            'company.expense_approve.numeric' => 'Expense approve must be numeric!',
            'user.name.required' => 'User name is required!',
            'user.name.string' => 'User name must be a string!',
            'user.name.max' => 'User name is too long!',
            'user.email.required' => 'User email is required!',
            'user.email.string' => 'User email must be a string!',
            'user.email.max' => 'User email is too long!',
            'user.email.email' => 'User email must be a valid email!',
            'user.email.unique' => 'User email has already been taken!',
            'user.password.required' => 'User password is required!',
            'user.password.min' => 'User password must be at least 6 characters!',
            'user.password.confirmed' => 'User password does not match!',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->messages(),
            ]);
        }
        $user = User::where('email', $request->input('user.email'))->first();
        if ($user instanceof User) {
            return response()->json([
                'status' => 500,
                'errors' => ['user.email' => ['User email has already been taken!']],
            ]);
        }
        $clientCompany = new ClientCompany();
        $clientCompany->name = $request->input('company.name');
        $clientCompany->address = $request->input('company.address');
        $clientCompany->email = $request->input('company.email');
        $clientCompany->phone_number = $request->input('company.phone_number');
        $clientCompany->sale_mismatch_allow = $request->input('company.sale_mismatch_allow');
        $clientCompany->expense_approve = $request->input('company.expense_approve');
        $clientCompany->currency_precision = $request->input('company.currency_precision');
        $clientCompany->quantity_precision = $request->input('company.quantity_precision');
        $clientCompany->header_text = $request->input('company.header_text');
        $clientCompany->footer_text = $request->input('company.footer_text');
        $clientCompany->voucher_check = $request->input('company.voucher_check');
        if (!$clientCompany->save()) {
            return response()->json([
                'message' => 'Company cannot be saved!',
                'status' => 300,
            ]);
        }

        $role = new Role();
        $role->name = 'Admin';
        $role->is_default = 1;
        $role->client_company_id = $clientCompany->id;
        if (!$role->save()) {
            ClientCompany::where('id', $clientCompany->id)->delete();
            return response()->json([
                'status' => 300,
                'message' => 'Role cannot be saved!',
            ]);
        }
        $user = new User();
        $user->name = $request->input('user.name');
        $user->email = $request->input('user.email');
        $user->address = $request->input('user.address');
        $user->phone = $request->input('user.phone');
        $user->password = bcrypt($request->input('user.password'));
        $user->role_id = $role->id;
        $user->client_company_id = $clientCompany->id;
        if (!$user->save()) {
            ClientCompany::where('id', $clientCompany->id)->delete();
            Role::where('client_company_id', $clientCompany->id)->delete();
            return response()->json([
                'status' => 300,
                'message' => 'User cannot be saved!',
            ]);
        }
        $permissionData = [];
        $permission = Permission::getAllPermission();
        foreach ($permission as $permissionName) {
            $permissionData[] = [
                'name' => $permissionName,
                'role_id' => $role->id,
                'client_company_id' => $clientCompany->id
            ];
        }
        Permission::insert($permissionData);
        Artisan::call('fuel:matix:category '.$clientCompany->id);
        return response()->json([
            'status' => 200,
            'message' => 'Company created successfully!',
        ]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function singleCompany(Request $request): JsonResponse
    {
        $company = ClientCompany::where('id', $request->input('id'))->first();
        return response()->json([
            'status' => 200,
            'company' => $company,
        ]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateCompany(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'address' => 'required|string|max:255',
            'phone_number' => 'required|string|max:255',
            'sale_mismatch_allow' => 'nullable|numeric',
            'expense_approve' => 'nullable|numeric',
            'currency_precision' => 'nullable|numeric',
            'quantity_precision' => 'nullable|numeric',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->messages(),
            ]);
        }
        $company = ClientCompany::find($request->input('id'));
        if (!$company instanceof ClientCompany) {
            return response()->json([
                'status' => 300,
                'message' => 'Company cannot be found!',
            ]);
        }
        $company->name = $request->input('name');
        $company->email = $request->input('email');
        $company->address = $request->input('address');
        $company->phone_number = $request->input('phone_number');
        $company->sale_mismatch_allow = $request->input('sale_mismatch_allow');
        $company->expense_approve = $request->input('expense_approve');
        $company->currency_precision = $request->input('currency_precision');
        $company->quantity_precision = $request->input('quantity_precision');
        $company->voucher_check = $request->input('voucher_check');
        $company->header_text = $request->input('header_text');
        $company->footer_text = $request->input('footer_text');
        if (!$company->save()) {
            return response()->json([
                'status' => 300,
                'message' => 'Company cannot be updated!',
            ]);
        }
        return response()->json([
            'status' => 200,
            'message' => 'Company updated successfully!',
        ]);
    }
}
