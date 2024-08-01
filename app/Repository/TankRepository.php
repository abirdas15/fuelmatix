<?php

namespace App\Repository;

use App\Helpers\SessionUser;
use App\Models\BstiChart;
use App\Models\Tank;
use App\Models\TankLog;

class TankRepository
{
    /**
     * Save a new tank reading or update an existing one based on the type.
     *
     * @param array $initialData
     * @return TankLog|array
     */
    public static function readingSave(array $initialData)
    {
        // Retrieve the session user
        $sessionUser = SessionUser::getUser();

        // Initialize a new TankLog object
        $reading = new TankLog();

        // Check if the type is 'opening stock'
        if ($initialData['type'] == 'opening stock') {
            // Retrieve an existing TankLog entry with 'opening stock' type for the given tank_id
            $tankLog = TankLog::where('tank_id', $initialData['tank_id'])
                ->where('type', 'opening stock')
                ->first();

            // If such an entry exists, use it for updating
            if ($tankLog instanceof TankLog) {
                $reading = $tankLog;
            }
        }

        // Set the attributes for the TankLog object
        $reading->tank_id = $initialData['tank_id'];
        $reading->date = $initialData['date'];
        $reading->height = $initialData['height'];
        $reading->type = $initialData['type'];
        $reading->volume = $initialData['volume'];
        $reading->client_company_id = $sessionUser['client_company_id'];

        // Save the TankLog object to the database
        if (!$reading->save()) {
            // Return an error response if saving fails
            return ['status' => 400, 'message' => 'Cannot save tank reading.'];
        }

        // Return the saved or updated TankLog object
        return $reading;
    }

    public static function getHeight($data)
    {
        $tank_id = $data['tank_id'] ?? null;
        if (!empty($requestData['product_id'])) {
            $tank = Tank::where('product_id', $requestData['product_id'])->first();
            if ($tank instanceof Tank) {
                $tank_id = $tank['id'];
            }
        }
        $bstiChart = BstiChart::select('height')->where('tank_id', $tank_id) ->where('volume', '=', floor($data['volume']))
            ->first();
        return $bstiChart['height'] ?? 0;
    }
}
