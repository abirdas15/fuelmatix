<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
           'email' => 'required|string|email|max:255',
           'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors(),
            ]);
        }
        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ];
        if (Auth::guard('admin')->attempt($credentials)) {
            return response()->json([
                'status' => 200,
                'message' => 'Login Successful',
            ]);
        }
        return response()->json([
            'status' => 500,
            'errors' => ['email' => 'Invalid Credentials'],
        ]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('admin')->logout();
        return response()->json([
            'status' => 200,
            'message' => 'Logout Successful',
        ]);
    }
}
