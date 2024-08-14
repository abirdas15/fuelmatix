<?php

namespace App\Repository;

use App\Common\AccountCategory;
use App\Common\Module;
use App\Helpers\SessionUser;
use App\Http\Controllers\TransactionController;
use App\Models\BstiChart;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tank;
use App\Models\TankLog;
use App\Models\TankRefill;
use App\Models\TankRefillHistory;
use App\Models\TankRefillTotal;
use Carbon\Carbon;

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

    /**
     * Saves tank refill data and related transactions.
     *
     * @param array $initialData Initial data for tank refill.
     * @param Product $product The product associated with the tank refill.
     * @return TankRefillTotal|array The saved tank refill total or an error response.
     */
    public static function saveTankRefill(array $initialData, Product $product)
    {
        // Retrieve the current session user.
        $sessionUser = SessionUser::getUser();

        // Retrieve the loss category for evaporative losses.
        $lossCategory = Category::where('slug', strtolower(AccountCategory::EVAPORATIVE))
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first();

        // Retrieve the cost of goods sold category and its child category for the product.
        $category = Category::where('slug', strtolower(AccountCategory::DIRECT_INCOME))->where('client_company_id', $sessionUser['client_company_id'])->first();
        $incomeCategory = Category::where('parent_category', $category['id'])
            ->where('module', 'product')
            ->where('module_id', $product['id'])
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first();

        // Create a new TankRefillTotal instance and populate its fields.
        $date = $initialData['date'];
        $tankRefillTotal = new TankRefillTotal();
        $tankRefillTotal->date = $date;
        $tankRefillTotal->time = date('H:i:s');
        $tankRefillTotal->product_id = $initialData['product_id'];
        $tankRefillTotal->pay_order_id = $initialData['pay_order_id'];
        $tankRefillTotal->quantity = $initialData['quantity'];
        $tankRefillTotal->total_refill_volume = $initialData['total_refill_volume'];
        $tankRefillTotal->net_profit = $initialData['net_profit'] ?? 0;
        $tankRefillTotal->net_profit_amount = $initialData['net_profit'] * $product['buying_price'] ?? 0;
        $tankRefillTotal->shift_id = $initialData['shift_id'];
        $tankRefillTotal->user_id = $sessionUser['id'];
        $tankRefillTotal->client_company_id = $sessionUser['client_company_id'];

        // Save the TankRefillTotal instance and check for errors.
        if (!$tankRefillTotal->save()) {
            return [
                'status' => 400,
                'message' => 'Cannot save tank refill.'
            ];
        }

        // Calculate per volume profit if applicable.
        $totalNetProfit = $initialData['net_profit'];
        $perVolumeProfit = 0;
        if ($totalNetProfit != 0 && $initialData['total_refill_volume'] != 0) {
            $perVolumeProfit = $totalNetProfit / $initialData['total_refill_volume'];
        }


        // Process each tank in the initial data.
        foreach ($initialData['tanks'] as $tank) {
            $dipSale = $tank['dip_sale'] ?? 0;
            $netProfit = $perVolumeProfit * $dipSale;
            $netProfitAmount = $netProfit * $product['buying_price'];

            // Create and save a new TankRefill instance.
            $tankRefill = new TankRefill();
            $tankRefill->refill_id = $tankRefillTotal->id;
            $tankRefill->tank_id = $tank['id'];
            $tankRefill->start_reading = $tank['start_reading'] ?? 0;
            $tankRefill->end_reading = $tank['end_reading'] ?? 0;
            $tankRefill->dip_sale = $dipSale;
            $tankRefill->net_profit = $netProfit;
            $tankRefill->net_profit_amount = $netProfitAmount;

            if (!$tankRefill->save()) {
                TankRefillTotal::where('id', $tankRefillTotal->id)->delete();
                return [
                    'status' => 400,
                    'message' => 'Cannot save tank refill.'
                ];
            }

            // Process each dispenser for the tank.
            foreach ($tank['dispensers'] as $dispenser) {
                foreach ($dispenser['nozzle'] as $nozzle) {
                    $tankRefillHistory = new TankRefillHistory();
                    $tankRefillHistory->tank_refill_id = $tankRefill->id;
                    $tankRefillHistory->nozzle_id = $nozzle['id'];
                    $tankRefillHistory->start_reading = $nozzle['start_reading'];
                    $tankRefillHistory->end_reading = $nozzle['end_reading'];
                    $tankRefillHistory->sale = $nozzle['sale'];

                    if (!$tankRefillHistory->save()) {
                        TankRefillHistory::where('tank_refill_id', $tankRefill->id)->delete();
                        TankRefill::where('id', $tankRefill->id)->delete();
                        TankRefillTotal::where('id', $tankRefillTotal->id)->delete();
                        return [
                            'status' => 400,
                            'message' => 'Cannot save tank refill.'
                        ];
                    }

                    NozzleRepository::readingSave([
                        'date' => date('Y-m-d'),
                        'nozzle_id' => $date,
                        'reading' => $nozzle['end_reading'],
                        'type' => 'tank refill',
                    ]);
                }
            }

            // Save tank reading data.
            TankRepository::readingSave([
                'tank_id' => $tank['id'],
                'date' => $date,
                'height' => $tank['end_reading_mm'] ?? 0,
                'volume' => $tank['end_reading'] ?? 0,
                'type' => 'tank refill',
            ]);

            // Retrieve stock category and save transaction data.
            $stockCategory = Category::where('module', Module::TANK)
                ->where('module_id', $tank['id'])
                ->where('client_company_id', $sessionUser['client_company_id'])
                ->first();

            $stockAmount = $dipSale * $product['buying_price'];
            if ($stockCategory instanceof Category && !empty($stockAmount)) {
                $transactionData = [
                    ['date' => $date, 'account_id' => $stockCategory['id'], 'debit_amount' => $stockAmount,  'credit_amount' => 0, 'module' => Module::TANK_REFILL, 'module_id' => $tankRefill['id']]
                ];
                // Add the vendor's transaction entry
                $creditAmount = $stockAmount;
                if ($netProfitAmount < 0) {
                    $creditAmount += abs($netProfitAmount);
                    $transactionData[] = ['date' => $date, 'account_id' => $lossCategory['id'], 'debit_amount' => abs($netProfitAmount), 'credit_amount' => 0, 'module' => Module::TANK_REFILL, 'module_id' => $tankRefill['id']];
                } else if ($netProfitAmount > 0) {
                    $creditAmount -= abs($netProfitAmount);
                    $transactionData[] = ['date' => $date, 'account_id' => $incomeCategory['id'], 'debit_amount' => 0, 'credit_amount' => abs($netProfitAmount), 'module' => Module::TANK_REFILL, 'module_id' => $tankRefill['id']];
                }

                $transactionData[] = ['date' => $date, 'account_id' => $initialData['vendor_id'], 'debit_amount' => 0, 'credit_amount' => $creditAmount, 'module' => Module::TANK_REFILL, 'module_id' => $tankRefill['id']];
                // Save the transaction data
                TransactionRepository::saveTransaction($transactionData);
            }
        }

        return $tankRefillTotal;
    }

}
