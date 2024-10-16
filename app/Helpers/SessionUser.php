<?php

namespace App\Helpers;

use App\Models\ClientCompany;
use App\Models\User;

class SessionUser
{
    const TIMEZONE = 'Asia/Dhaka';
    public static function getUser()
    {
        $requestData = request()->all();
        $sessionUser = $requestData['session_user'] ?? null;
        if (empty($sessionUser)) {
            return ['error' => 'Cannot find [user] authentication.'];
        }
        if (!$sessionUser instanceof User) {
            return ['error' => 'Cannot authenticate [user] session.'];
        }
        $clientCompany = ClientCompany::where('id', $sessionUser['client_company_id'])->first();
        $sessionUser['quantity_precision'] = $clientCompany->quantity_precision ?? 2;
        $sessionUser['currency_precision'] = $clientCompany->currency_precision ?? 0;
        return $sessionUser;
    }
}
