<?php

namespace App\Repository;

use App\Helpers\SessionUser;
use App\Models\BstiChart;
use App\Models\TankLog;

class TankRepository
{
    /**
     * @param array $data
     * @return TankLog|array
     */
    public static function readingSave(array $data)
    {
        $sessionUser = SessionUser::getUser();
        $bstiChart = BstiChart::where('tank_id', $data['tank_id'])
            ->where('height', '=', floor($data['height']))
            ->first();
        $reading = new TankLog();
        $reading->tank_id = $data['tank_id'];
        $reading->date = $data['date'].' '.date('H:i:s');
        $reading->height = $data['height'];
        $reading->water_height = $data['water_height'] ?? null;
        $reading->type = $data['type'];
        $reading->volume = $bstiChart != null ? $bstiChart->volume : 0;
        $reading->client_company_id = $sessionUser['client_company_id'];
        if (!$reading->save()) {
            return ['status' => 400, 'message' => 'Cannot save tank reading.'];
        }
        return $reading;
    }
}
