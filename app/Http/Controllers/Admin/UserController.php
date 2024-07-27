<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getUser(Request $request): JsonResponse
    {
        $orderBy = $request->get('order_by', 'id');
        $orderMode = $request->get('order_mode', 'desc');
        $limit = $request->get('limit', 20);
        $keyword = $request->get('keyword') ?? '';
        $result = User::select('users.id', 'users.name', 'users.email', 'roles.name as role', 'client_company.name as company_name')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->join('client_company', 'users.client_company_id', '=', 'client_company.id');
        if (!empty($keyword)) {
            $result->where(function ($query) use ($keyword) {
                $query->where('users.name', 'like', '%' . $keyword . '%');
                $query->orWhere('client_company.name', 'like', '%' . $keyword . '%');
                $query->orWhere('users.email', 'like', '%' . $keyword . '%');
            });
        }
        $result = $result->orderBy($orderBy, $orderMode)
            ->paginate($limit);
        return response()->json([
            'status' => 200,
            'users' => $result,
            'message' => 'Successfully fetched users!'
        ]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function singleUser(Request $request): JsonResponse
    {
        $result = User::select('id', 'name', 'email', 'phone', 'address')
            ->where('id', $request->input('id'))
            ->first();
        return response()->json([
            'status' => 200,
            'user' => $result,
        ]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors(),
            ]);
        }
        $user = User::where('email', $request->input('email'))->where('id', '!=', $request->input('id'))->first();
        if ($user instanceof User) {
            return response()->json([
                'status' => 500,
                'errors' => ['email' => ['Email already have been taken.']],
            ]);
        }
        $user = User::find($request->input('id'));
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->phone = $request->input('phone');
        $user->address = $request->input('address');
        $user->save();
        return response()->json([
            'status' => 200,
            'message' => 'Successfully updated user!'
        ]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors(),
            ]);
        }
        $user = User::find($request->input('id'));
        $user->password = bcrypt($request->input('password'));
        $user->save();
        return response()->json([
            'status' => 200,
            'message' => 'Successfully updated password!'
        ]);
    }
}
