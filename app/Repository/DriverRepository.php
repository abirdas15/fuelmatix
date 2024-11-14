<?php

namespace App\Repository;

use App\Helpers\SessionUser;
use App\Models\Driver;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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
        $driverModel->un_authorized_bill_id = $data['un_authorized_bill_id'];
        $driverModel->client_company_id = $sessionUser['client_company_id'];
        if (!$driverModel->save()) {
            return false;
        }
        return $driverModel;
    }

    /**
     * @param integer $id
     * @return mixed
     */
    public static function getDriverAmount(int $id)
    {
        $sessionUser = SessionUser::getUser();
        $result =  Transaction::select(DB::raw('SUM(debit_amount) as debit_amount'), DB::raw('SUM(credit_amount) as credit_amount'))
            ->where('account_id', $id)
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first();
        if (!empty($result)) {
            return $result['credit_amount'] - $result['debit_amount'];
        }
        return 0;
    }
}
