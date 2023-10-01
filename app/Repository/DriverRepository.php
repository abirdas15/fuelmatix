<?php

namespace App\Repository;

use App\Helpers\SessionUser;
use App\Models\Driver;
use App\Models\User;

class DriverRepository
{
    /**
     * @param array $data
     * @param User $sessionUser
     * @return Driver|false
     */
    public static function save(array $data, User $sessionUser)
    {
        $driverModel = new Driver();
        $driverModel->name = $data['name'];
        $driverModel->company_id = $data['company_id'];
        $driverModel->email = $data['email'];
        $driverModel->phone_number = $data['phone_number'];
        $driverModel->driver_expense_id = $data['driver_expense_id'];
        $driverModel->driver_liability_id = $data['driver_liability_id'];
        $driverModel->client_company_id = $sessionUser['client_company_id'];
        if (!$driverModel->save()) {
            return false;
        }
        return $driverModel;
    }
}
