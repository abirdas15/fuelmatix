<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientCompany;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function summary(Request $request): JsonResponse
    {
        $result['total_user'] = self::countTotalUser();
        $result['total_company'] = self::countTotalCompany();
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }
    public function countTotalCompany()
    {
        return ClientCompany::count();
    }
    public static function countTotalUser()
    {
        return User::count();
    }
}
