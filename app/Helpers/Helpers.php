<?php

namespace App\Helpers;

use Carbon\Carbon;

class Helpers
{
    public static function formatDate($date, $format = 'd/m/Y')
    {
        $timestamp = Carbon::createFromFormat('Y-m-d H:i:s', $date, 'UTC')
            ->setTimezone('Asia/Dhaka');
        return $timestamp->format($format);
    }
}
