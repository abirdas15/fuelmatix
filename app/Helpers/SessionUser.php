<?php

namespace App\Helpers;

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
        return $sessionUser;
    }
}
