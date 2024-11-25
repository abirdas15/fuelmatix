<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'email' => 'required',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $field = 'phone';
        if(filter_var($inputData['email'], FILTER_VALIDATE_EMAIL)) {
            $field = 'email';
        }

        $user = User::where($field, $inputData['email'])->first();
        if ($user == null) {
            return response()->json([
                'status' => 500,
                'errors' => ['email' => ['Invalid credentials! Please try again.']]
            ]);
        }
        if (!Hash::check($inputData['password'], $user->password)) {
            return response()->json([
                'status' => 500,
                'errors' => ['password' => ['Password is not correct! Please try again.']]
            ]);
        }
        $access_token = $user->createToken('authToken')->accessToken;
        return response()->json([
            'status' => 200,
            'message' => 'Successfully login.',
            'data' => User::ParseData($user),
            'access_token' => $access_token
        ]);
    }
    public function logout(Request $request)
    {
        Auth::logout();
        return response()->json(['status' => 200, 'message' => 'Successfully logout.']);
    }
}
