<?php

namespace App\Repository;

use App\Helpers\SessionUser;
use App\Models\NozzleReading;

class NozzleRepository
{
    public static function readingSave($data)
    {
        $sessionUser = SessionUser::getUser();
        $reading = new NozzleReading();
        $reading->date = $data['date'].' '.date('H:i:s');
        $reading->nozzle_id = $data['nozzle_id'];
        $reading->reading = $data['reading'];
        $reading->type = $data['type'];
        $reading->client_company_id = $sessionUser['client_company_id'];
        if (!$reading->save()) {
            return ['status' => 400, 'message' => 'Cannot save nozzle reading.'];
        }
        return $reading;
    }
}
