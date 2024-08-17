<?php

namespace App\Repository;

use App\Common\AccountCategory;
use App\Common\FuelMatixDateTimeFormat;
use App\Common\FuelMatixStatus;
use App\Common\Module;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\SaleData;
use App\Models\ShiftSale;
use App\Models\ShiftSaleTransaction;
use App\Models\ShiftSummary;
use App\Models\ShiftTotal;
use App\Models\Tank;
use App\Models\TankLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ShiftSaleRepository
{
    /**
     * Starts the shift sale, initializes and logs the necessary data.
     *
     * @param array $initialData
     * @return ShiftTotal|array
     */
    public static function startShiftSale(array $initialData)
    {
        // Get the session user
        $sessionUser = SessionUser::getUser();

        // Create a new ShiftTotal object and initialize its properties
        $shiftTotal = new ShiftTotal();
        $shiftTotal->start_date = $initialData['date'];
        $shiftTotal->product_id = $initialData['product_id'];
        $shiftTotal->status = FuelMatixStatus::START;
        $shiftTotal->amount = 0;
        $shiftTotal->consumption = 0;
        $shiftTotal->client_company_id = $sessionUser['client_company_id'];
        $shiftTotal->user_id = $sessionUser['id'];

        // Save the ShiftTotal object to the database
        if (!$shiftTotal->save()) {
            // Return an error response if the ShiftTotal object could not be saved
            return [
                'status' => 400,
                'message' => 'Cannot start shift sale.'
            ];
        }

        // Iterate over each tank in the initial data
        foreach ($initialData['tanks'] as $tank) {
            // Create a new ShiftSale object and initialize its properties
            $shiftSale = new ShiftSale();
            $shiftSale->shift_id = $shiftTotal['id'];
            $shiftSale->tank_id = $tank['id'];
            $shiftSale->start_reading = $tank['start_reading'];
            $shiftSale->end_reading = 0;
            $shiftSale->consumption = 0;

            // Save the ShiftSale object to the database
            if (!$shiftSale->save()) {
                // If the ShiftSale object could not be saved, delete the ShiftTotal object and return an error response
                ShiftTotal::where('id', $shiftTotal->id)->delete();
                return [
                    'status' => 400,
                    'message' => 'Cannot start shift sale.'
                ];
            }

            // Iterate over each dispenser in the tank
            foreach ($tank['dispensers'] as $dispenser) {
                // Iterate over each nozzle in the dispenser
                foreach ($dispenser['nozzle'] as $nozzle) {
                    // Create a new ShiftSummary object and initialize its properties
                    $shiftSaleSummary = new ShiftSummary();
                    $shiftSaleSummary->shift_sale_id = $shiftSale->id;
                    $shiftSaleSummary->dispenser_id = $dispenser['id'];
                    $shiftSaleSummary->nozzle_id = $nozzle['id'];
                    $shiftSaleSummary->start_reading = $nozzle['start_reading'];
                    $shiftSaleSummary->end_reading = 0;
                    $shiftSaleSummary->adjustment = 0;
                    $shiftSaleSummary->consumption = 0;
                    $shiftSaleSummary->amount = 0;

                    // Save the ShiftSummary object to the database
                    if (!$shiftSaleSummary->save()) {
                        // If the ShiftSummary object could not be saved, delete the ShiftTotal and ShiftSale objects and return an error response
                        ShiftTotal::where('id', $shiftTotal->id)->delete();
                        ShiftSale::where('shift_id', $shiftTotal->id)->delete();
                        return [
                            'status' => 400,
                            'message' => 'Cannot start shift sale.'
                        ];
                    }
                }
            }
        }

        // Return the ShiftTotal object if everything was successful
        return $shiftTotal;
    }


    /**
     * Ends the shift sale, processes consumption and sales data, and logs transactions.
     *
     * @param ShiftTotal $shiftTotal
     * @param Product $product
     * @param array $initialData
     * @return ShiftTotal|array
     */
    public static function shiftSaleEnd(ShiftTotal $shiftTotal, Product $product, array $initialData)
    {
        // Get the current session user
        $sessionUser = SessionUser::getUser();

        // Fetch the income category for the product
        $category = Category::where('slug', strtolower(AccountCategory::DIRECT_INCOME))
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first();
        $incomeCategory = Category::where('parent_category', $category['id'])
            ->where('module', Module::PRODUCT)
            ->where('module_id', $initialData['product_id'])
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first();

        // If income category is not found, return an error
        if (!$incomeCategory instanceof Category) {
            return ['status' => 400, 'message' => 'Cannot find account income category.'];
        }

        // Fetch the cost of goods sold category for the product
        $category = Category::where('slug', strtolower(AccountCategory::COST_OF_GOOD_SOLD))
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first();
        $costOfGoodSoldCategory = Category::where('parent_category', $category['id'])
            ->where('module', 'product')
            ->where('module_id', $initialData['product_id'])
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first();

        // If cost of goods sold category is not found, return an error
        if (!$costOfGoodSoldCategory instanceof Category) {
            return ['status' => 400, 'message' => 'Cannot find account stock of good sold category.'];
        }

        // Calculate total consumption from all tanks and nozzles
        $nozzleTotalConsumption = 0;
        foreach ($initialData['tanks'] as &$tank) {
            $tank['total_consumption'] = 0;
            foreach ($tank['dispensers'] as $dispenser) {
                foreach ($dispenser['nozzle'] as $nozzle) {
                    $tank['total_consumption'] += $nozzle['consumption'];
                    $nozzleTotalConsumption += $nozzle['consumption'];
                }
            }
        }

        // Adjust end readings for tanks with noDIPShow flag
        foreach ($initialData['tanks'] as &$tank) {
            if ($tank['noDIPShow'] == 1) {
                $tank['consumption'] = $tank['total_consumption'];
                $tank['end_reading'] = $tank['start_reading'] - $tank['consumption'];
            }
        }

        // Check if tanks have enough fuel if tank check is enabled
        if (!empty($initialData['tank']) && $initialData['tank'] == 1) {
            foreach ($initialData['tanks'] as $item) {
                $tankLog = TankLog::select('id', 'tank_id', 'height', 'water_height', 'volume')
                    ->where('tank_id', $item['id'])
                    ->orderBy('id', 'DESC')
                    ->first();
                if ($tankLog instanceof TankLog && $tankLog['volume'] < $item['total_consumption']) {
                    return [
                        'status' => 400,
                        'message' => 'Your tank has not enough fuel. Please refill your tank.'
                    ];
                }
            }
        }
        $posSale = SaleData::select('sale_data.sale_id', 'sale_data.id', DB::raw('SUM(sale_data.quantity) as quantity'), DB::raw('SUM(sale_data.subtotal) as amount'), 'sale.payment_category_id as category_id', 'sale.voucher_number as voucher_no', 'car_id', 'driver_id')
            ->leftJoin('sale', 'sale.id', '=', 'sale_data.sale_id')
            ->where('sale_data.shift_sale_id', $shiftTotal->id)
            ->groupBy('sale.id')
            ->get()
            ->toArray();
        if (count($posSale) > 0) {
            foreach ($posSale as $eachSale) {
                $initialData['categories'][] = [
                    'amount' => $eachSale['amount'],
                    'category_id' => $eachSale['category_id'],
                    'liter' => $eachSale['quantity'],
                    'car_id' => $eachSale['car_id'],
                    'voucher_no' => $eachSale['voucher_no'],
                    'driver_id' => $eachSale['driver_id'],
                    'module' => Module::POS_SALE,
                    'module_id' => $eachSale['id'],
                ];
            }
        }
        DB::transaction(function() use ($shiftTotal, $product, $initialData, $sessionUser, $nozzleTotalConsumption, $incomeCategory, $costOfGoodSoldCategory) {
            // Set shift end details and save
            if ($initialData['status'] == 'previous') {
                $shiftTotal->product_id = $initialData['product_id'];
                $shiftTotal->start_date = Carbon::parse($initialData['date'], SessionUser::TIMEZONE)->startOfDay();
                $shiftTotal->end_date = Carbon::parse($initialData['date'], SessionUser::TIMEZONE)->endOfDay();
                $shiftTotal->user_id = $sessionUser['id'];
                $shiftTotal->client_company_id = $sessionUser['client_company_id'];
            } else {
                $shiftTotal->end_date = Carbon::now(SessionUser::TIMEZONE);
            }
            $shiftTotal->status = FuelMatixStatus::END;
            $shiftTotal->consumption = $nozzleTotalConsumption;
            $shiftTotal->amount = $nozzleTotalConsumption * $product['selling_price'];
            $shiftTotal->save();

            // Process each tank's shift sale details
            foreach ($initialData['tanks'] as $eachTank) {
                $nozzleConsumption = 0;
                foreach ($eachTank['dispensers'] as $dispenser) {
                    foreach ($dispenser['nozzle'] as $nozzle) {
                        $nozzleConsumption += $nozzle['consumption'];
                    }
                }

                // Calculate net profit and loss amount
                $netProfit = $nozzleConsumption - $eachTank['consumption'];
                $lossAmount = $netProfit * $product['buying_price'];

                // Fetch or create shift sale record for the tank
                $shiftSale = ShiftSale::where('tank_id', $eachTank['id'])
                    ->where('shift_id', $initialData['shift_id'])
                    ->first();
                if ($initialData['status'] == 'previous') {
                    $shiftSale = new ShiftSale();
                }
                $shiftSale->shift_id = $shiftTotal['id'];
                $shiftSale->tank_id = $eachTank['id'];
                $shiftSale->start_reading = $eachTank['start_reading'];
                $shiftSale->end_reading = $eachTank['end_reading'];
                $shiftSale->adjustment = $eachTank['adjustment'];
                $shiftSale->tank_refill = $eachTank['tank_refill'];
                $shiftSale->consumption = $eachTank['consumption'];
                $shiftSale->amount = $eachTank['consumption'] * $product['selling_price'];
                $shiftSale->net_profit = $netProfit ?? 0;
                $shiftSale->net_profit_amount = $lossAmount ?? 0;
                $shiftSale->save();

                // Save tank reading if tank check is enabled
                if ($initialData['tank'] == 1) {
                    $height = Tank::findHeight($eachTank['id'], $eachTank['end_reading']);
                    TankRepository::readingSave([
                        'tank_id' => $eachTank['id'],
                        'date' => date('Y-m-d', strtotime($initialData['date'])),
                        'volume' => $eachTank['end_reading'],
                        'height' => $height,
                        'type' => 'shift sell',
                    ]);
                }

                // Process shift sale summaries for each nozzle
                foreach ($eachTank['dispensers'] as $dispenser) {
                    foreach ($dispenser['nozzle'] as $nozzle) {
                        $shiftSaleSummary = ShiftSummary::where('shift_sale_id', $shiftSale['id'])
                            ->where('dispenser_id', $dispenser['id'])
                            ->where('nozzle_id', $nozzle['id'])
                            ->first();
                        if ($initialData['status'] == 'previous') {
                            $shiftSaleSummary = new ShiftSummary();
                        }
                        if ($shiftSaleSummary instanceof ShiftSummary) {
                            $shiftSaleSummary->shift_sale_id = $shiftSale->id;
                            $shiftSaleSummary->dispenser_id = $dispenser['id'];
                            $shiftSaleSummary->nozzle_id = $nozzle['id'];
                            $shiftSaleSummary->start_reading = $nozzle['start_reading'];
                            $shiftSaleSummary->end_reading = $nozzle['end_reading'] != 0 ? $nozzle['end_reading'] : $nozzle['start_reading'];
                            $shiftSaleSummary->adjustment = $nozzle['adjustment'];
                            $shiftSaleSummary->consumption = $nozzle['consumption'];
                            $shiftSaleSummary->amount = $nozzle['consumption'] * $product['selling_price'];
                            $shiftSaleSummary->save();

                            // Save nozzle reading
                            $readingData = [
                                'date' => $initialData['date'],
                                'nozzle_id' => $nozzle['id'],
                                'reading' => $nozzle['end_reading'],
                                'type' => 'shift sell',
                            ];
                            NozzleRepository::readingSave($readingData);
                        }
                    }
                }

                // Fetch stock category for the tank
                $stockCategory = Category::where('module', Module::TANK)
                    ->where('module_id', $eachTank['id'])
                    ->where('client_company_id', $sessionUser['client_company_id'])
                    ->first();
                if ($stockCategory instanceof Category) {
                    $buyingPrice = $eachTank['total_consumption'] * $product['buying_price'];

                    // Handle loss amount transaction
                    if ($lossAmount < 0) {
                        $lossCategory = Category::where('slug', strtolower(AccountCategory::EVAPORATIVE))
                            ->where('client_company_id', $initialData['session_user']['client_company_id'])
                            ->first();
                        if ($lossCategory instanceof Category) {
                            $description = 'Shift ID: ' . $shiftSale['id'] . ', Product: ' . $product['name'] . ', Loss: ' . abs($netProfit);
                            $transactionData = [
                                ['date' => date('Y-m-d', strtotime($initialData['date'])), 'description' => $description, 'account_id' => $lossCategory['id'], 'debit_amount' => abs($lossAmount), 'credit_amount' => 0],
                                ['date' => date('Y-m-d', strtotime($initialData['date'])), 'description' => $description, 'account_id' => $stockCategory['id'], 'debit_amount' => 0, 'credit_amount' => abs($lossAmount)],
                            ];
                            TransactionRepository::saveTransaction($transactionData);
                        }
                    } else if ($lossAmount > 0) {
                        // Handle profit amount transaction
                        $description = 'Shift ID: ' . $shiftSale['id'] . ', Product: ' . $product['name'] . ', Windfall: ' . abs($netProfit);
                        $transactionData = [
                            ['date' => date('Y-m-d', strtotime($initialData['date'])), 'description' => $description, 'account_id' =>$stockCategory['id'], 'debit_amount' => abs($lossAmount), 'credit_amount' => 0],
                            ['date' => date('Y-m-d', strtotime($initialData['date'])), 'description' => $description, 'account_id' => $incomeCategory['id'], 'debit_amount' => 0, 'credit_amount' => abs($lossAmount)],
                        ];
                        TransactionRepository::saveTransaction($transactionData);
                    }

                    if (!empty($buyingPrice)) {
                        // Set linked ID based on vendor
                        $linkedId = $stockCategory['id'];
                        if (!empty($product['vendor_id'])) {
                            $productType = ProductType::find($product['type_id']);
                            if ($productType instanceof ProductType && $productType['vendor'] == 1) {
                                $linkedId = $product['vendor_id'];
                            }
                        }

                        // Save transaction for cost of goods sold
                        $transactionData = [
                            ['date' => date('Y-m-d', strtotime($initialData['date'])), 'account_id' => $costOfGoodSoldCategory['id'], 'debit_amount' => $buyingPrice, 'credit_amount' => 0, 'module' => 'shift sale', 'module_id' => $shiftTotal['id']],
                            ['date' => date('Y-m-d', strtotime($initialData['date'])), 'account_id' => $linkedId, 'debit_amount' => 0, 'credit_amount' => $buyingPrice, 'module' => 'shift sale', 'module_id' => $shiftTotal['id']]
                        ];
                        TransactionRepository::saveTransaction($transactionData);
                    }
                }
            }

            // Save transactions for each category in initialData
            $shiftSaleTransaction = [];
            foreach ($initialData['categories'] as $category) {
                $transactionData = [
                    ['date' => date('Y-m-d', strtotime($initialData['date'])), 'account_id' => $category['category_id'], 'debit_amount' => $category['amount'], 'credit_amount' => 0, 'module' => $category['module'] ?? Module::SHIFT_SALE, 'module_id' => $category['module_id'] ?? $shiftTotal['id'], 'car_id' => $category['car_id'] ?? null, 'voucher_no' => $category['voucher_no'] ?? null, 'driver_id' => $category['driver_id'] ?? null, 'quantity' => $category['liter'] ?? null],
                    ['date' => date('Y-m-d', strtotime($initialData['date'])), 'account_id' => $incomeCategory['id'], 'debit_amount' => 0, 'credit_amount' => $category['amount'], 'module' =>  $category['module'] ?? Module::SHIFT_SALE, 'module_id' => $category['module_id'] ?? $shiftTotal['id'], 'car_id' => $category['car_id'] ?? null, 'voucher_no' => $category['voucher_no'] ?? null, 'driver_id' => $category['driver_id'] ?? null, 'quantity' => $category['liter'] ?? null]
                ];
                TransactionRepository::saveTransaction($transactionData);
                $shiftSaleTransaction[] = [
                    'shift_id' => $shiftTotal['id'],
                    'category_id' => $category['category_id'],
                    'amount' => $category['amount']
                ];
            }
            ShiftSaleTransaction::insert($shiftSaleTransaction);
        });
        return $shiftTotal;
    }

    /**
     * Get details of a single shift sale, including related products, tanks, dispensers, and categories.
     *
     * @param int $id The ID of the shift.
     * @return mixed The detailed shift sale information.
     */
    public static function getSingleShiftSale(int $id)
    {
        // Fetch the shift total details along with associated product and product type details
        $result = ShiftTotal::select('shift_total.*', 'products.name as product_name', 'product_types.tank', 'product_types.unit')
            ->leftJoin('products', 'products.id', '=', 'shift_total.product_id')
            ->leftJoin('product_types', 'product_types.id', '=', 'products.type_id')
            ->where('shift_total.id', $id)
            ->first();

        // Fetch the shift sale details along with tank information
        $shiftSale = ShiftSale::select(
            'shift_sale.id',
            'shift_sale.tank_id',
            'tank.tank_name',
            'shift_sale.start_reading',
            'shift_sale.end_reading',
            'shift_sale.tank_refill',
            'shift_sale.adjustment',
            'shift_sale.consumption',
            'shift_sale.amount',
            'shift_sale.net_profit'
        )
            ->where('shift_id', $id)
            ->with(['shift_summary' => function($q) {
                // Fetch shift summary details along with dispenser and nozzle information
                $q->select('shift_summary.*', 'dispensers.dispenser_name', 'nozzles.name as nozzle_name')
                    ->leftJoin('dispensers', 'dispensers.id', '=', 'shift_summary.dispenser_id')
                    ->leftJoin('nozzles', 'nozzles.id', '=', 'shift_summary.nozzle_id');
            }])
            ->leftJoin('tank', 'tank.id', '=', 'shift_sale.tank_id')
            ->get()
            ->toArray();

        // Initialize an empty array to hold the formatted shift sale data
        $formattedShiftSale = [];

        // Process each shift sale entry
        foreach ($shiftSale as $sale) {
            $tankId = $sale['tank_id'];

            // Check if the tank entry already exists in the formatted array
            $tankIndex = array_search($tankId, array_column($formattedShiftSale, 'tank_id'));

            // If tank entry does not exist, create a new entry
            if ($tankIndex === false) {
                $tankIndex = count($formattedShiftSale);
                $formattedShiftSale[$tankIndex] = [
                    'id' => $sale['id'],
                    'tank_id' => $tankId,
                    'tank_name' => $sale['tank_name'],
                    'start_reading' => !empty($sale['start_reading']) ? number_format($sale['start_reading'], 2) : '',
                    'end_reading' => !empty($sale['end_reading']) ? number_format($sale['end_reading'], 2) : '',
                    'tank_refill' => !empty($sale['tank_refill']) ? number_format($sale['tank_refill'], 2) : '',
                    'adjustment' => !empty($sale['adjustment']) ? number_format($sale['adjustment'], 2) : '',
                    'consumption' => !empty($sale['consumption']) ? number_format($sale['consumption'], 2) : '',
                    'amount' => $sale['amount'],
                    'net_profit' => $sale['net_profit'],
                    'dispensers' => []
                ];
            }

            // Process shift summaries to group by dispenser and nozzle
            foreach ($sale['shift_summary'] as $summary) {
                $dispenserId = $summary['dispenser_id'];

                // Check if the dispenser entry already exists in the formatted array
                $dispenserIndex = array_search($dispenserId, array_column($formattedShiftSale[$tankIndex]['dispensers'], 'dispenser_id'));

                // If dispenser entry does not exist, create a new entry
                if ($dispenserIndex === false) {
                    $dispenserIndex = count($formattedShiftSale[$tankIndex]['dispensers']);
                    $formattedShiftSale[$tankIndex]['dispensers'][$dispenserIndex] = [
                        'dispenser_name' => $summary['dispenser_name'],
                        'nozzles' => []
                    ];
                }

                // Add nozzle details to the dispenser entry
                $formattedShiftSale[$tankIndex]['dispensers'][$dispenserIndex]['nozzles'][] = [
                    'nozzle_name' => $summary['nozzle_name'],
                    'start_reading' => !empty($summary['start_reading']) ? number_format($summary['start_reading'], 2) : '',
                    'end_reading' => !empty($summary['end_reading']) ? number_format($summary['end_reading'], 2) : '',
                    'adjustment' => !empty($summary['adjustment']) ? number_format($summary['adjustment'], 2) : '',
                    'consumption' => !empty($summary['consumption']) ? number_format($summary['consumption'], 2) : '',
                    'amount' => $summary['amount']
                ];
            }
        }

        // Add the formatted shift sale data to the result
        $result['tanks'] = $formattedShiftSale;

        // Fetch category details associated with the shift sale
        $categories = ShiftSaleTransaction::select('shift_sale_transaction.category_id', 'shift_sale_transaction.amount', 'categories.name')
            ->leftJoin('categories', 'categories.id', '=', 'shift_sale_transaction.category_id')
            ->where('shift_id', $id)
            ->get()
            ->toArray();

        // Format the category amounts
        foreach ($categories as &$category) {
            $category['amount'] = number_format($category['amount'], 2);
        }

        // Add the formatted category data to the result
        $result['categories'] = $categories;

        // Format the start and end dates of the shift
        $result['start_date_format'] = Helpers::formatDate($result['start_date'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME);
        $result['end_date_format'] = Helpers::formatDate($result['end_date'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME);

        // Format the consumption and amount fields
        $result['consumption'] = !empty($result['consumption']) ? number_format($result['consumption'], 2) : '';
        $result['amount'] = !empty($result['amount']) ? number_format($result['amount'], 2) : '';

        // Return the result with all formatted data
        return $result;
    }



}
