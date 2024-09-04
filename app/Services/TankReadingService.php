<?php

namespace App\Services;

use App\Common\FuelMatixStatus;
use App\Helpers\SessionUser;
use App\Models\Dispenser;
use App\Models\FuelAdjustment;
use App\Models\FuelAdjustmentData;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\SaleData;
use App\Models\ShiftTotal;
use App\Models\Tank;
use App\Models\TankRefillTotal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TankReadingService
{
    protected $tanks;
    protected $date;
    protected $dispensers;
    protected $shiftId;
    protected $sessionUser;
    protected $fuelAdjustments;
    protected $tankRefill;
    protected $product;
    protected $result;
    protected $shiftTotal;
    protected $status;

    /**
     * Handles the process of reading and adjusting tank data for a given shift and product.
     *
     * This method orchestrates a sequence of steps to gather tank readings, dispenser data,
     * fuel adjustments, and tank refills. It ultimately returns a finalized result set with
     * all necessary adjustments applied.
     *
     * @param array $initialData - Contains data like shift ID, status, and potentially a date.
     * @param Product $product - The product for which the tank readings are being processed.
     * @return array - The finalized tank reading data with all adjustments applied.
     */
    public function tankReading(array $initialData, Product $product): array
    {
        // Retrieve the current session user and store their details.
        $this->sessionUser = SessionUser::getUser();

        // Store the shift ID from the initial data.
        $this->shiftId = $initialData['shift_id'];

        // Set the current date/time in the user's timezone.
        $this->date = Carbon::now(SessionUser::TIMEZONE);

        // If the status indicates a previous shift, adjust the date to the end of the given day.
        if ($initialData['status'] == FuelMatixStatus::PREVIOUS) {
            $this->date = Carbon::parse($initialData['date'], SessionUser::TIMEZONE)->endOfDay();
        }

        // Store the product being processed.
        $this->product = $product;

        // Store the status, if available, from the initial data.
        $this->status = $initialData['status'] ?? '';

        // Chain together various steps to gather and adjust data, and finalize the results.
        $this->tanks = $this->getShiftSale()
        ->getTanks()
        ->getDispensers()
        ->getFuelAdjustment()
        ->adjustFuelAdjustmentWithDispenser()
        ->getTankRefill()
        ->adjustTankRefillWithTank()
        ->finalize();

        // Return the processed result set.
        return $this->result;
    }

    /**
     * Finalizes the tank reading and adjustment process by calculating totals, formatting data,
     * and preparing the result set for the shift.
     *
     * @return $this - Returns the current instance with the finalized result set.
     */
    public function finalize(): TankReadingService
    {
        // Calculate the total consumption by summing up the 'consumption' values from all tanks.
        $consumption = array_sum(array_column($this->tanks, 'consumption'));

        // Calculate the total amount by multiplying the total consumption by the product's selling price.
        $amount = $consumption * $this->product->selling_price;

        // Retrieve the product type information based on the product's type ID.
        $productType = ProductType::where('id', $this->product->type_id)->first();

        // Initialize the result array with various data, including date, product ID, tanks data,
        // amount, selling price, unit, and other default values.
        $this->result = [
            'date' => Carbon::parse($this->date, SessionUser::TIMEZONE)->format('Y-m-d H:i:s'),
            'product_id' => $this->product->id,
            'tanks' => $this->tanks,
            'amount' => $amount,
            'selling_price' => $this->product->selling_price,
            'unit' => $productType['unit'],
            'tank' => $productType['tank'],
            'net_profit' => 0,
            'status' => FuelMatixStatus::START,
            'pos_sale' => [],
            'total_pos_sale_liter' => 0
        ];

        // If a shift total is available, modify the result based on the shift status.
        if ($this->shiftTotal instanceof ShiftTotal) {
            // If the shift status is 'END', set the status in the result to 'START'.
            if ($this->shiftTotal['status'] == FuelMatixStatus::END) {
                $this->result['status'] = FuelMatixStatus::START;
            } else {
                // Otherwise, set the date and status to indicate the shift is ending.
                $this->result['date'] = Carbon::parse($this->shiftTotal->date, SessionUser::TIMEZONE)->format('Y-m-d H:i:s');
                $this->result['status'] = 'end';
            }

            // Retrieve POS sales data for the current shift, grouped by payment category.
            $posSale = SaleData::select('sale_data.sale_id', 'sale_data.id', DB::raw('SUM(sale_data.quantity) as quantity'), DB::raw('SUM(sale_data.subtotal) as amount'), 'sale.payment_category_id as category_id')
                ->leftJoin('sale', 'sale.id', '=', 'sale_data.sale_id')
                ->where('shift_sale_id', $this->shiftTotal->id)
                ->groupBy('sale.payment_category_id')
                ->get()
                ->toArray();

            // Add the POS sales data and total sales in liters to the result.
            $this->result['pos_sale'] = $posSale;
            $this->result['total_pos_sale_liter'] = array_sum(array_column($posSale, 'quantity'));
        }

        // Include the shift ID in the result.
        $this->result['shift_id'] = $this->shiftId;

        // If a status was provided, override the status in the result.
        if (!empty($this->status)) {
            $this->result['status'] = $this->status;
        }

        // Return the current instance, now containing the finalized result set.
        return $this;
    }

    /**
     * Adjusts the tank readings with refills and fuel adjustments, calculating the fuel consumption
     * and updating the tank's data accordingly.
     *
     * @return $this - Returns the current instance after adjusting the tank data.
     */
    public function adjustTankRefillWithTank(): TankReadingService
    {
        // Iterate through each tank to apply adjustments.
        foreach ($this->tanks as &$tank) {
            // Initialize the adjustment value for the current tank.
            $adjustment = 0;

            // Check if there are any fuel adjustments provided.
            if (!empty($this->fuelAdjustments)) {
                // Apply fuel adjustments that are specific to the current tank.
                foreach ($this->fuelAdjustments as $adjustmentData) {
                    if (!empty($adjustmentData['tank_id']) && $adjustmentData['tank_id'] == $tank['id']) {
                        $adjustment += $adjustmentData['quantity'];
                    }
                }
            }

            // Set the 'noDIPShow' flag to indicate that no DIP reading should be shown.
            $tank['noDIPShow'] = 1;

            // If no shift sale ID is provided, set the starting reading to the end reading or opening stock.
            if (empty($this->shiftId)) {
                $tank['start_reading'] = $tank['end_reading'] ?? ($tank['opening_stock'] ?? 0);
            }

            // Calculate the starting reading in millimeters using the tank's height.
            $tank['start_reading_mm'] = Tank::findHeight($tank['id'], $tank['start_reading']);

            // Initialize end readings and refill readings.
            $tank['end_reading'] = 0;
            $tank['end_reading_mm'] = 0;
            $tank['tank_refill'] = isset($this->tankRefill[$tank['id']]) ? $this->tankRefill[$tank['id']]['dip_sale'] : 0;
            $tank['tank_refill_mm'] = 0;

            // Store the calculated adjustment and its millimeter equivalent.
            $tank['adjustment'] = $adjustment;
            $tank['adjustment_mm'] = 0;

            // Calculate the consumption based on starting reading, refill, end reading, and adjustments.
            $tank['consumption'] = $tank['start_reading'] + $tank['tank_refill'] - $tank['end_reading'] + $adjustment;
            $tank['consumption_mm'] = $tank['start_reading_mm'] + $tank['tank_refill_mm'] - $tank['end_reading_mm'];

            // Attach dispenser data to the tank if available.
            $tank['dispensers'] = $this->dispensers[$tank['id']] ?? [];

            // Remove the opening stock data as it's no longer needed.
            unset($tank['opening_stock']);
        }

        // Return the current instance after adjustments.
        return $this;
    }

    /**
     * Retrieves tank refill data based on the current status (previous or current shift).
     *
     * @return $this - Returns the current instance after fetching and processing the tank refill data.
     */
    public function getTankRefill(): TankReadingService
    {
        // Begin building the query to fetch tank refill data.
        $tankRefill = TankRefillTotal::select('tank_refill_total.*', 'tank_refill.tank_id', 'tank_refill.dip_sale')
            ->leftJoin('tank_refill', 'tank_refill.refill_id', '=', 'tank_refill_total.id');

        // Apply filter based on the status.
        if ($this->status == FuelMatixStatus::PREVIOUS) {
            // For previous status, filter by the date.
            $tankRefill->where(function($query) {
                $query->where('tank_refill_total.date', '=', date('Y-m-d', strtotime($this->date)));
            });
        } else {
            // For the current status, filter by the shift ID.
            $tankRefill->where(function($query) {
                $query->where('tank_refill_total.shift_id', '=', $this->shiftId);
            });
        }

        // Fetch the results and key them by tank_id for easy access later.
        $this->tankRefill = $tankRefill->get()->keyBy('tank_id')->toArray();

        // Return the current instance for method chaining.
        return $this;
    }

    /**
     * Adjusts the fuel consumption data for each nozzle based on fuel adjustments and updates dispenser data.
     *
     * @return $this - Returns the current instance after processing and adjusting the fuel data.
     */
    public function adjustFuelAdjustmentWithDispenser(): TankReadingService
    {
        $dispenserArray = [];

        // Iterate through each dispenser.
        foreach ($this->dispensers as &$dispenser) {
            // Process each nozzle associated with the current dispenser.
            foreach ($dispenser['nozzle'] as &$nozzle) {
                $adjustment = 0;

                // If there are any fuel adjustments, find the relevant adjustment for the nozzle.
                if (!empty($this->fuelAdjustments)) {
                    foreach ($this->fuelAdjustments as $adjustmentData) {
                        if (!empty($adjustmentData['nozzle_id']) && $adjustmentData['nozzle_id'] == $nozzle['id']) {
                            $adjustment = $adjustmentData['quantity'];
                        }
                    }
                }

                // Determine the start reading for the nozzle, defaulting to opening stock.
                $nozzle['start_reading'] = $nozzle['opening_stock'] ?? 0;

                // If there's data from the latest shift summary, use it to update the start reading.
                if (!empty($nozzle['latest_shift_summary'])) {
                    $nozzle['start_reading'] = !empty($nozzle['latest_shift_summary']['end_reading'])
                        ? $nozzle['latest_shift_summary']['end_reading']
                        : $nozzle['latest_shift_summary']['start_reading'];
                }

                // Initialize the end reading and adjustment values.
                $nozzle['end_reading'] = 0;
                $nozzle['adjustment'] = $adjustment;

                // Calculate the fuel consumption, considering the adjustment, and ensure it's non-negative.
                $nozzle['consumption'] = $nozzle['end_reading'] - $nozzle['start_reading'] - $adjustment;
                $nozzle['amount'] = 0; // Placeholder for the amount; can be calculated later.
                $nozzle['consumption'] = max($nozzle['consumption'], 0); // Ensure consumption is not negative.

                // Remove the `latest_shift_summary` to clean up the data.
                unset($nozzle['latest_shift_summary']);
            }

            // Organize the dispenser data by `tank_id`.
            $dispenserArray[$dispenser['tank_id']][] = $dispenser;
        }

        // Update the class dispensers property with the processed data.
        $this->dispensers = $dispenserArray;

        // Return the current instance for method chaining.
        return $this;
    }

    /**
     * Retrieves fuel adjustment data based on the current status and stores the detailed adjustments.
     * @return $this - Returns the current instance for method chaining.
     */
    public function getFuelAdjustment(): TankReadingService
    {
        // Initialize a query to select the necessary fields from the `FuelAdjustment` table.
        $fuelAdjustment = FuelAdjustment::select('id', 'loss_quantity');

        // Check if the status is 'PREVIOUS' to determine the date filter.
        if ($this->status == FuelMatixStatus::PREVIOUS) {
            // For the previous status, filter adjustments based on the exact date.
            $fuelAdjustment->where(function($query) {
                $query->where(DB::raw('DATE(date)'), '=', date('Y-m-d', strtotime($this->date)));
            });
        } else {
            // Otherwise, filter adjustments based on the shift ID.
            $fuelAdjustment->where(function($query) {
                $query->where('shift_sale_id', '=', $this->shiftId);
            });
        }

        // Execute the query and convert the results to an array.
        $fuelAdjustment = $fuelAdjustment->get()->toArray();

        // Extract the IDs of the fuel adjustments.
        $fuelAdjustmentId = array_column($fuelAdjustment, 'id');

        // Retrieve detailed adjustment data from `FuelAdjustmentData` based on the adjustment IDs.
        $this->fuelAdjustments = FuelAdjustmentData::whereIn('fuel_adjustment_id', $fuelAdjustmentId)->get()->toArray();

        // Return the current instance for method chaining.
        return $this;
    }

    /**
     * Retrieves dispenser data along with associated nozzles and their latest shift summaries.
     *
     * The results are stored in the `dispensers` property as an array for further processing.
     *
     * @return $this - Returns the current instance for method chaining.
     */
    public function getDispensers(): TankReadingService
    {
        // Build the query to select dispensers associated with the current product and client company.
        $this->dispensers = Dispenser::select('id', 'dispenser_name', 'tank_id')
            // Filter dispensers by product ID.
            ->where('product_id', $this->product->id)
            // Filter dispensers by client company ID.
            ->where('client_company_id', $this->sessionUser['client_company_id'])
            // Eager load the associated nozzles with their latest shift summary.
            ->with(['nozzle' => function ($query) {
                // Select specific fields for each nozzle.
                $query->select('id', 'dispenser_id', 'name', 'opening_stock', 'pf', 'max_value')
                    // Eager load the latest shift summary for each nozzle.
                    ->with(['latestShiftSummary' => function ($subQuery) {
                        // Select fields from the shift summary, join with shift sale and shift total tables,
                        // and filter based on the current date and shift ID.
                        $subQuery->select('shift_summary.id', 'shift_summary.nozzle_id', 'shift_summary.start_reading', 'shift_summary.end_reading')
                            ->join('shift_sale', 'shift_summary.shift_sale_id', '=', 'shift_sale.id')
                            ->join('shift_total', 'shift_sale.shift_id', '=', 'shift_total.id')
                            ->where('shift_total.start_date', '<=', $this->date)
                            ->whereColumn('shift_sale.shift_id', 'shift_total.id');
                    }]);
            }])
            // Execute the query and convert the result to an array.
            ->get()
            ->toArray();

        // Return the current instance for method chaining.
        return $this;
    }

    /**
     * Retrieves the shift total for the current product and date.
     *
     * The method returns the current instance (`$this`) to allow for method chaining.
     *
     * @return $this - Returns the current instance for method chaining.
     */
    public function getShiftSale(): TankReadingService
    {
        // Query to find the shift total record based on the product ID, date, status, and client company ID.
        $this->shiftTotal = ShiftTotal::where('product_id', $this->product->id)
            ->where('start_date', '<=', $this->date)
            ->where('status', FuelMatixStatus::START)
            ->where('client_company_id', $this->sessionUser['client_company_id'])
            ->first();

        // If a matching ShiftTotal record is found, update the shiftId and date properties.
        if ($this->shiftTotal instanceof ShiftTotal) {
            $this->shiftId = $this->shiftTotal->id;
            $this->date = Carbon::parse($this->shiftTotal->start_date, SessionUser::TIMEZONE);
        }

        // Return the current instance for method chaining.
        return $this;
    }

    /**
     * Retrieves tanks with their associated shift sale data and opening stock.
     *
     * The method returns the current instance (`$this`) to allow for method chaining.
     *
     * @return $this - Returns the current instance for method chaining.
     */
    public function getTanks(): TankReadingService
    {
        $this->tanks = Tank::select(
            'tank.id',
            'tank.tank_name',
            'shift_sale.start_reading',
            'shift_sale.end_reading',
            'tank.opening_stock'
        )
            ->leftJoin('shift_sale', function ($join) {
                $join->on('tank.id', '=', 'shift_sale.tank_id')
                    ->whereRaw('shift_sale.id = (
                    SELECT MAX(ss.id)
                    FROM shift_sale ss
                    JOIN shift_total st ON ss.shift_id = st.id
                    WHERE ss.tank_id = tank.id
                    AND st.start_date <= ?
                )', [$this->date]);
            })
            ->leftJoin('shift_total', 'shift_sale.shift_id', '=', 'shift_total.id')
            ->where('tank.product_id', $this->product->id)
            ->where('tank.client_company_id', $this->sessionUser['client_company_id'])
            ->get()
            ->toArray();

        return $this;
    }

}
