<?php

namespace App\Http\Controllers;

use App\Helpers\SessionUser;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function getProfile(Request $request)
    {
        $sessionUser = SessionUser::getUser();
        $user = User::where('id', $sessionUser['id'])->first();
        return response()->json([
            'status' => 200,
            'data' => User::ParseData($user)
        ]);
    }
}
