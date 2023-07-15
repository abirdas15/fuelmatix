<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'error' => $validator->errors()]);
        }
        $remember = isset($input['remember']) && $input['remember'] == 1 ? true : false;
        $credential = [
            'email' => $inputData['email'],
            'password' => $inputData['password']
        ];
        if (Auth::attempt($credential, $remember)) {
            $userInfo = User::where('email', $inputData['email'])->first();
            $userInfo->save();
            return response()->json(['status' => 200, 'msg' => 'Successfully login.', 'data' => User::ParseData($userInfo)]);
        }
        return response()->json(['status' => 500, 'error' => ['email' => 'Invalid credentials! Please try again.']]);
    }
    public function logout(Request $request)
    {
        Auth::logout();
        return response()->json(['status' => 200, 'message' => 'Successfully logout.']);
    }
}
