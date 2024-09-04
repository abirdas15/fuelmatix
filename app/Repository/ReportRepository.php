<?php

namespace App\Repository;

use App\Common\AccountCategory;
use App\Common\FuelMatixDateTimeFormat;
use App\Common\FuelMatixStatus;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Expense;
use App\Models\FuelAdjustment;
use App\Models\Invoice;
use App\Models\PayOrderData;
use App\Models\Product;
use App\Models\Sale;
use App\Models\ShiftSale;
use App\Models\ShiftTotal;
use App\Models\TankLog;
use App\Models\TankRefill;
use App\Models\TankRefillTotal;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportRepository
{
    /**
     * Retrieves the daily log data for a given date and optional filters.
     *
     * @param array $filter The filter parameters including 'date' (required) and other optional filters.
     * @return array An array containing the daily log data, including sales, expenses, due payments, due invoices, and asset balances.
     */
    public static function dailyLog(array $filter): array
    {
        // Retrieve shift sale data using the filter
        $shiftSale = self::getShiftSale($filter);

        // Retrieve POS sale data using the filter
        $posSale = self::getPosSale($filter);

        // Initialize the result array with shift sale and POS sale data
        $result['shift_sale'] = $shiftSale['data'];
        $result['pos_sale'] = $posSale['data'];

        // Retrieve and add tank refill data to the result array
        $result['tank_refill'] = self::getTankRefill($filter);

        // Retrieve and add stock data to the result array for the specified date
        $result['stock'] = self::getStock($filter['date']);

        // Retrieve and add expense data to the result array using the filter
        $result['expense'] = self::getAllExpense($filter);

        // Retrieve and add due payments data to the result array for the specified date
        $result['due_payments'] = self::getDuePayments($filter['date']);

        // Retrieve and add due invoice data to the result array for the specified date
        $result['due_invoice'] = self::getDueInvoice($filter['date']);

        // Retrieve and add asset balance (cash in hand) to the result array for the specified date
        $result['asset_balance']['cash'] = self::getAssetBalance($filter['date'], AccountCategory::CASH_IN_HAND);

        // Retrieve and add asset balance (bank) to the result array for the specified date
        $result['asset_balance']['bank'] = self::getAssetBalance($filter['date'], AccountCategory::BANK);

        // Calculate the total sale amount by summing shift sale and POS sale totals, formatted to 2 decimal places
        $result['total']['sale'] = number_format($shiftSale['total'] + $posSale['total'], 2);

        // Return the compiled daily log data
        return $result;
    }

    /**
     * Retrieves POS sale information for a given date and optional shift sale ID.
     *
     * @param array $filter The filter parameters including 'date' (required) and 'shift_sale_id' (optional).
     * @return array An array containing the POS sale data and the total amount.
     */
    public static function getPosSale(array $filter): array
    {
        // Get the current session user
        $sessionUser = SessionUser::getUser();

        // Query the Sale table for POS sales on the specified date and join with related tables
        $result = Sale::select(
            'products.name as product_name',
            DB::raw('SUM(quantity) as quantity'),
            DB::raw('SUM(subtotal) as amount'),
            'date',
            'product_types.unit'
        )
            ->leftJoin('sale_data', 'sale_data.sale_id', '=', 'sale.id')
            ->leftJoin('products', 'products.id', '=', 'sale_data.product_id')
            ->leftJoin('product_types', 'product_types.id', '=', 'products.type_id')
            ->where(DB::raw('DATE(date)'), $filter['date'])
            ->where('sale.client_company_id', $sessionUser['client_company_id'])
            ->where('product_types.shift_sale', '0');

        // Filter by shift sale ID if provided
        if (!empty($filter['shift_sale_id'])) {
            $result->where(function($q) use ($filter) {
                $q->where('sale_data.shift_sale_id', $filter['shift_sale_id']);
            });
        }

        // Group the results by product ID and retrieve the data as an array
        $result = $result->groupBy('sale_data.product_id')
            ->get()
            ->toArray();

        // Initialize the total amount
        $total = 0;

        // Format the POS sale data
        foreach ($result as &$data) {
            $total += $data['amount'];
            $data['time'] = Helpers::formatDate($data['date'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME);
            $data['amount'] = number_format($data['amount'], 2);
        }

        // Return the formatted POS sale data along with the total amount
        return [
            'data' => $result,
            'total' => $total
        ];
    }

    /**
     * Retrieves the asset balance for a specific account category up to a given date.
     *
     * This method calculates the net balance of transactions (debit minus credit) for all categories
     * under a specified account category up to the given date. It filters transactions by category,
     * computes the net amount, formats the data, and returns the result as an array.
     *
     * @param string $date The date up to which to retrieve the asset balance (in 'Y-m-d' format).
     * @param string $accountCategoryName The name of the account category to retrieve the balance for.
     * @return array An array containing the asset balance data, including category names and formatted amounts.
     */
    public static function getAssetBalance(string $date, string $accountCategoryName): array
    {
        // Get the current session user
        $sessionUser = SessionUser::getUser();

        // Retrieve the ID of the specified account category
        $accountCategory = Category::select('id')
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->where('slug', strtolower($accountCategoryName))
            ->first();

        // Retrieve the IDs of all subcategories under the specified account category
        $category = Category::select('id')
            ->where('parent_category', $accountCategory->id)
            ->get()
            ->pluck('id')
            ->toArray();

        // Query the Transaction table for asset balance, joining with the Categories table
        $transaction = Transaction::select(
            DB::raw('SUM(debit_amount - credit_amount) as amount'),
            'categories.name as category_name'
        )
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->leftJoin('categories', 'categories.id', 'transactions.linked_id')
            ->whereIn('transactions.linked_id', $category)
            ->where('transactions.date', '<=', $date)
            ->groupBy('transactions.linked_id')
            ->get()
            ->toArray();

        // Format the asset balance data
        foreach ($transaction as &$data) {
            $data['amount'] = number_format($data['amount'], 2); // Format the amount
        }

        // Return the formatted asset balance data
        return $transaction;
    }

    /**
     * Retrieves due invoices for a given date.
     *
     * This method queries the Invoice table to calculate the total amount due for each category
     * on a specified date. It filters invoices by date, computes the difference between the
     * invoice amount and the paid amount, formats the data, and returns the result as an array.
     *
     * @param string $date The date for which to retrieve due invoices (in 'Y-m-d' format).
     * @return array An array containing the due invoices data, including category names and formatted amounts.
     */
    public static function getDueInvoice(string $date): array
    {
        // Get the current session user
        $sessionUser = SessionUser::getUser();

        // Query the Invoice table for due invoices, joining with the Categories table
        $result = Invoice::select(
            DB::raw('SUM(amount - paid_amount) as amount'),
            'categories.name as category_name'
        )
            ->where('invoices.client_company_id', $sessionUser['client_company_id'])
            ->leftJoin('categories', 'categories.id', 'invoices.category_id')
            ->where('date', $date)
            ->having('amount', '>', 0)
            ->groupBy('invoices.category_id')
            ->get()
            ->toArray();

        // Format the due invoices data
        foreach ($result as &$data) {
            $data['amount'] = number_format($data['amount'], 2); // Format the amount
        }

        // Return the formatted due invoices data
        return $result;
    }

    /**
     * Retrieves due payments for a given date.
     *
     * This method queries the Transaction table to get the total credit amounts for categories
     * related to accounts payable on a specified date. It filters the transactions by category
     * and date, formats the data, and returns the result as an array.
     *
     * @param string $date The date for which to retrieve due payments (in 'Y-m-d' format).
     * @return array An array containing the due payments data, including category names and formatted amounts.
     */
    public static function getDuePayments(string $date): array
    {
        // Get the current session user
        $sessionUser = SessionUser::getUser();

        // Retrieve the category ID for accounts payable
        $accountPayable = Category::select('id')
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->where('slug', strtolower(AccountCategory::ACCOUNT_PAYABLE))
            ->first();

        // Query the Transaction table for due payments, joining with the Categories table
        $transaction = Transaction::select(
            DB::raw('SUM(credit_amount) as amount'),
            'categories.name as category_name'
        )
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->leftJoin('categories', 'categories.id', 'transactions.linked_id')
            ->where('categories.parent_category', $accountPayable->id)
            ->where('date', $date)
            ->having('amount', '>', 0)
            ->groupBy('transactions.linked_id')
            ->get()
            ->toArray();

        // Format the due payments data
        foreach ($transaction as &$data) {
            $data['amount'] = number_format($data['amount'], 2); // Format the amount
        }

        // Return the formatted due payments data
        return $transaction;
    }

    /**
     * Retrieves the cost of goods sold (COGS) expenses for a given date.
     *
     * @param string $date The date for which to retrieve the COGS expenses (in 'Y-m-d' format).
     * @return array An array containing the COGS expense data including category names and formatted amounts.
     */
    public static function getCostOfGoodSoldExpense(string $date): array
    {
        // Get the current session user
        $sessionUser = SessionUser::getUser();

        // Retrieve the category ID for cost of goods sold
        $costOfGoodSoldCategory = Category::select('id')
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->where('slug', strtolower(AccountCategory::COST_OF_GOOD_SOLD))
            ->first();

        // Query the Transaction table for COGS expenses, joining with the Categories table
        $transaction = Transaction::select(
            DB::raw('SUM(debit_amount) as amount'),
            'categories.name as category_name'
        )
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->leftJoin('categories', 'categories.id', 'transactions.linked_id')
            ->where('categories.parent_category', $costOfGoodSoldCategory->id)
            ->where('date', $date)
            ->having('amount', '>', 0)
            ->groupBy('transactions.linked_id')
            ->get()
            ->toArray();

        // Format the COGS expense data
        foreach ($transaction as &$data) {
            $data['amount'] = number_format($data['amount'], 2); // Format the amount
        }

        // Return the formatted COGS expense data
        return $transaction;
    }


    /**
     * Retrieves all approved expenses for a given filter.
     *
     * @param array $filter The filter parameters including 'date' (optional), 'start_date' (optional), 'end_date' (optional), and 'shift_sale_id' (optional).
     * @return array An array containing the expense data grouped by category, including the category name and formatted amount.
     */
    public static function getAllExpense(array $filter): array
    {
        // Get the current session user
        $sessionUser = SessionUser::getUser();

        // Query the Expense table for approved expenses and join with the Categories table
        $transaction = Expense::select(
            'categories.name as category_name',
            DB::raw('SUM(amount) as amount')
        )
            ->leftJoin('categories', 'categories.id', 'expense.category_id')
            ->where('expense.client_company_id', $sessionUser['client_company_id'])
            ->where('expense.status', 'approve');

        // Filter by date if provided
        if (!empty($filter['date'])) {
            $transaction->where(function($q) use ($filter) {
                $q->where(DB::raw('DATE(date)'), $filter['date']);
            });
        }

        // Filter by date range if provided
        if (!empty($filter['start_date']) && !empty($filter['end_date'])) {
            $transaction->where(function($q) use ($filter) {
                $q->whereBetween(DB::raw('DATE(date)'), [$filter['start_date'], $filter['end_date']]);
            });
        }

        // Filter by shift sale ID if provided
        if (!empty($filter['shift_sale_id'])) {
            $transaction->where(function($q) use ($filter) {
                $q->where('shift_sale_id', $filter['shift_sale_id']);
            });
        }

        // Group the results by category ID and retrieve the data as an array
        $transaction = $transaction->groupBy('category_id')
            ->get()
            ->toArray();

        // Format the expense data
        foreach ($transaction as &$data) {
            $data['_amount'] = $data['amount']; // Store the original amount
            $data['amount'] = number_format($data['amount'], 2); // Format the amount
        }

        // Return the formatted expense data
        return $transaction;
    }

    /**
     * Retrieves stock information for a given date.
     *
     * @param string $date The date for which to retrieve the stock information (in 'Y-m-d' format).
     * @return array An array containing the stock information including product name, unit, opening stock, and closing stock.
     */
    public static function getStock(string $date): array
    {
        // Get the current session user
        $sessionUser = SessionUser::getUser();

        // Query the TankLog table for stock volumes on the specified date and join with related tables
        $result = TankLog::select(
            'tank_log.id',
            'tank_log.volume',
            'products.id',
            'products.name as product_name',
            'product_types.unit'
        )
            ->leftJoin('tank', 'tank.id', 'tank_log.tank_id')
            ->leftJoin('products', 'products.id', 'tank.product_id')
            ->leftJoin('product_types', 'product_types.id', 'products.type_id')
            ->where(DB::raw('DATE(tank_log.date)'), $date)
            ->where('tank_log.client_company_id', $sessionUser['client_company_id'])
            ->get()
            ->toArray();

        // Initialize an array to group the data by product name
        $dataArray = [];
        foreach ($result as $data) {
            $dataArray[$data['product_name']][] = $data;
        }

        // Initialize the result array
        $resultArray = [];

        // Format the stock data by product
        foreach ($dataArray as $key => $data) {
            $resultArray[] = [
                'name' => $key,
                'unit' =>  $data[0]['unit'],
                'opening_stock' => $data[0]['volume'],
                'closing_stock' => count($data) > 1 ? $data[count($data) - 1]['volume'] : $data[0]['volume']
            ];
        }

        // Return the formatted stock data
        return $resultArray;
    }


    /**
     * @param array $filter
     * @return mixed
     */
    public static function getTankRefill(array  $filter)
    {
        $sessionUser = SessionUser::getUser();
        $result = TankRefillTotal::select('tank_refill_total.id', 'tank_refill_total.date', 'tank_refill_total.time', 'tank_refill_total.total_refill_volume as quantity', 'tank_refill_total.net_profit', 'products.name as product_name', 'product_types.unit')
            ->leftJoin('tank_refill', 'tank_refill.refill_id', 'tank_refill_total.id')
            ->leftJoin('tank', 'tank.id', 'tank_refill.tank_id')
            ->leftJoin('products', 'products.id', 'tank.product_id')
            ->leftJoin('product_types', 'product_types.id', 'products.type_id')
            ->where('tank_refill_total.date', $filter['date'])
            ->where('tank_refill_total.client_company_id', $sessionUser['client_company_id']);
        if (!empty($filter['shift_sale_id'])) {
            $result->where(function($q) use ($filter) {
                $q->where('tank_refill_total.shift_id', $filter['shift_sale_id']);
            });
        }
        $result = $result->get()
            ->toArray();
        foreach ($result as &$data) {
            $data['date'] = Helpers::formatDate($data['date']. ' '.$data['time'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME);
        }
        return $result;
    }
    /**
     * Retrieves shift sale information for a given date and optional shift sale ID.
     *
     * @param array $filter The filter parameters including 'date' (required) and 'shift_sale_id' (optional).
     * @return array An array containing the shift sale data and the total amount.
     */
    public static function getShiftSale(array $filter): array
    {
        // Parse the provided date into start and end timestamps for the whole day
        $startDate = Carbon::parse($filter['date'], SessionUser::TIMEZONE)->startOfDay();
        $endDate = Carbon::parse($filter['date'], SessionUser::TIMEZONE)->endOfDay();

        // Get the current session user
        $sessionUser = SessionUser::getUser();

        // Query the ShiftTotal table for shift sales within the specified date range and join with related tables
        $result = ShiftTotal::select(
            DB::raw('SUM(shift_sale.consumption) as consumption'),
            DB::raw('SUM(shift_sale.amount) as amount'),
            'shift_total.product_id',
            'shift_total.start_date',
            'shift_total.end_date',
            'products.name as product_name',
            'product_types.unit'
        )
            ->leftJoin('shift_sale', 'shift_sale.shift_id', '=' , 'shift_total.id')
            ->leftJoin('products', 'products.id', '=', 'shift_total.product_id')
            ->leftJoin('product_types', 'product_types.id', '=', 'products.type_id')
            ->where('shift_total.client_company_id', $sessionUser['client_company_id'])
            ->whereBetween('start_date', [$startDate, $endDate])
            ->where('shift_total.status', 'end');

        // Filter by shift sale ID if provided
        if (!empty($filter['shift_sale_id'])) {
            $result->where(function($q) use ($filter) {
                $q->where('shift_total.id', $filter['shift_sale_id']);
            });
        }

        // Group the results by shift_total ID and retrieve the data as an array
        $result = $result->groupBy('shift_total.id')
            ->get()
            ->toArray();

        // Initialize the total amount and the result array
        $total = 0;
        $resultArray = [];

        // Format the shift sale data
        foreach ($result as $data) {
            $total += $data['amount'];
            $resultArray[$data['product_name']][] = [
                'time' => 'Shift('.Helpers::formatDate($data['start_date'], FuelMatixDateTimeFormat::STANDARD_TIME).' - '.Helpers::formatDate($data['end_date'], FuelMatixDateTimeFormat::STANDARD_TIME).')',
                'quantity' => $data['consumption'],
                'unit' => $data['unit'],
                'amount' => number_format($data['amount'], 2)
            ];
        }

        // Prepare the final result array grouped by product name
        $finalResult = [];
        foreach ($resultArray as $key => $row) {
            $finalResult[] = [
                'product_name' => $key,
                'data' => $row
            ];
        }

        // Return the formatted shift sale data along with the total amount
        return [
            'data' => $finalResult,
            'total' => $total,
        ];
    }
    /**
     * @param string $date;
     */
    public static function stockSummary(string $date): array
    {
        $sessionUser = SessionUser::getUser();
        $products = Product::select('products.id', 'products.name as product_name', 'product_types.tank', 'products.selling_price')
            ->leftJoin('product_types', 'product_types.id', '=', 'products.type_id')
            ->where('products.client_company_id', $sessionUser['client_company_id'])
            ->where('product_types.tank', '1')
            ->with(['tanks' => function($q) {
                $q->select('id', 'product_id', 'tank_name', 'opening_stock');
            },'tanks.dispensers' => function($q) {
                $q->select('id', 'tank_id', 'dispenser_name');
            }, 'tanks.dispensers.nozzle' => function($q) {
                $q->select('id', 'dispenser_id', 'name as nozzle_name', 'opening_stock');
            }])
            ->get()
            ->toArray();
        $productIds = array_column($products, 'id');
        $startDate = Carbon::parse($date, SessionUser::TIMEZONE)->startOfDay();
        $endDate = Carbon::parse($date, SessionUser::TIMEZONE)->endOfDay();

        $shiftSale = ShiftTotal::select(
            'shift_sale.id',
            'shift_sale.tank_id',
            'shift_summary.nozzle_id',
            'shift_summary.start_reading',
            'shift_summary.end_reading',
            'shift_sale.start_reading as tank_start_reading',
            'shift_sale.end_reading as tank_end_reading',
        )
            ->leftJoin('shift_sale', 'shift_sale.shift_id', '=', 'shift_total.id')
            ->leftJoin('shift_summary', 'shift_summary.shift_sale_id', '=', 'shift_sale.id')
            ->where('shift_total.client_company_id', $sessionUser['client_company_id'])
            ->whereBetween('start_date', [$startDate, $endDate])
            ->where('shift_total.status', FuelMatixStatus::END)
            ->whereNotNull('shift_summary.nozzle_id')
            ->get()
            ->toArray();
        // Initialize arrays to hold the results
        $shiftSaleByNozzleId = [];
        $shiftSaleByTankId = [];

        // Process the fetched data
        foreach ($shiftSale as $sale) {
            $tankId = $sale['tank_id'];
            $nozzleId = $sale['nozzle_id'];

            // Keyed by nozzle_id
            if (!isset($shiftSaleByNozzleId[$nozzleId])) {
                $shiftSaleByNozzleId[$nozzleId] = [
                    'start_reading' => $sale['start_reading'],
                    'end_reading' => $sale['end_reading']
                ];
            } else {
                // Update end_reading to be the latest end_reading for the nozzle_id
                $shiftSaleByNozzleId[$nozzleId]['end_reading'] = $sale['end_reading'];
            }

            // Keyed by tank_id
            if (!isset($shiftSaleByTankId[$tankId])) {
                $shiftSaleByTankId[$tankId] = [
                    'tank_start_reading' => $sale['tank_start_reading'],
                    'tank_end_reading' => $sale['tank_end_reading']
                ];
            } else {
                // Update tank_end_reading to be the latest end_reading for the tank_id
                $shiftSaleByTankId[$tankId]['tank_end_reading'] = $sale['tank_end_reading'];
            }
        }


        $fuelAdjustment = FuelAdjustment::select('fuel_adjustment.product_id', DB::raw('SUM(fuel_adjustment_data.quantity) as total_quantity'))
            ->leftJoin('fuel_adjustment_data', 'fuel_adjustment_data.fuel_adjustment_id', '=', 'fuel_adjustment.id')
            ->where('fuel_adjustment.client_company_id', $sessionUser['client_company_id'])
            ->whereNotNull('nozzle_id')
            ->whereIn('product_id', $productIds)
            ->whereBetween('fuel_adjustment.date', [$startDate, $endDate])
            ->where('fuel_adjustment.client_company_id', $sessionUser['client_company_id'])
            ->groupBy('fuel_adjustment.product_id')
            ->get()
            ->keyBy('product_id')
            ->toArray();

        $tankRefill = TankRefillTotal::select(DB::raw('SUM(tank_refill.dip_sale) as volume'), 'tank_refill.tank_id')
            ->leftJoin('tank_refill', 'tank_refill.refill_id', '=', 'tank_refill_total.id')
            ->where('tank_refill_total.client_company_id', $sessionUser['client_company_id'])
            ->where('tank_refill_total.date', $date)
            ->get()
            ->keyBy('tank_id')
            ->toArray();

        $payOrder = PayOrderData::select(DB::raw('SUM(pay_order_data.quantity) as quantity'), 'pay_order_data.product_id')
            ->leftJoin('pay_order', 'pay_order.id', '=', 'pay_order_data.pay_order_id')
            ->where('status', FuelMatixStatus::PENDING)
            ->where('pay_order.client_company_id', $sessionUser['client_company_id'])
            ->whereIn('product_id', $productIds)
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id')
            ->toArray();
        foreach ($products as &$product) {
            $totalSale = 0;
            $totalEndReading = 0;
            $totalRefill = 0;
            $totalAmount = 0;
            foreach ($product['tanks'] as &$tank) {
                foreach ($tank['dispensers'] as &$dispenser) {
                    foreach ($dispenser['nozzle'] as &$nozzle) {
                        $nozzle['start_reading'] = $shiftSaleByNozzleId[$nozzle['id']]['start_reading'] ?? 0;
                        $nozzle['end_reading'] = $shiftSaleByNozzleId[$nozzle['id']]['end_reading'] ?? 0;
                        $nozzle['sale'] = $nozzle['end_reading'] - $nozzle['start_reading'];
                        $nozzle['start_reading_format'] = $nozzle['start_reading'] > 0 ?  number_format($nozzle['start_reading'], 2) : '-';
                        $nozzle['end_reading_format'] = $nozzle['end_reading'] > 0 ? number_format($nozzle['end_reading'], 2) : '-';
                        $nozzle['sale_format'] = $nozzle['sale'] > 0 ? number_format($nozzle['sale'], 2) : '-';
                        $totalSale += $nozzle['sale'];
                        $nozzle['unit_price_format'] = number_format($product['selling_price'], 2);
                        $nozzle['amount'] = $nozzle['sale'] * $product['selling_price'];
                        $nozzle['amount_format'] = $nozzle['amount'] > 0 ?number_format($nozzle['amount'], 2) : '-';
                        $totalAmount += $nozzle['amount'];
                    }
                }
                $tank['end_reading'] = $shiftSaleByTankId[$tank['id']]['tank_end_reading'] ?? 0;
                $tank['refill'] = $tankRefill[$tank['id']]['volume'] ?? 0;
                $tank['end_reading_format'] = $tank['end_reading'] > 0 ? number_format($tank['end_reading'], 2) : '-';
                $totalEndReading += $tank['end_reading'];
                $totalRefill += $tank['refill'];
            }
            $product['total'] = $totalSale > 0 ? number_format($totalSale, 2) : '-';
            $product['subtotal_amount'] = $totalAmount > 0 ? number_format($totalAmount, 2) : '-';
            $adjustment = $fuelAdjustment[$product['id']]['total_quantity'] ?? 0;
            $product['adjustment'] = $adjustment > 0 ? number_format($adjustment, 2) : '-' ;
            $product['adjustment_amount'] = $adjustment > 0 ? number_format($adjustment * $product['selling_price'], 2) : '-' ;

            $totalQuantity = $totalSale - $adjustment;
            $product['total_sale'] = $totalQuantity > 0 ? number_format($totalQuantity, 2) : '-';
            $product['total_amount'] = ($totalAmount - ($adjustment * $product['selling_price'])) > 0 ? number_format($totalAmount - ($adjustment * $product['selling_price']), 2) : '-';
            $product['end_reading'] = $totalEndReading > 0 ? number_format($totalEndReading, 2) : '-';
            $product['tank_refill'] = $totalRefill > 0 ? number_format($totalRefill, 2) : '-';
            $totalByProduct = $totalEndReading + $totalRefill;
            $product['total_by_product'] = $totalByProduct > 0 ? number_format($totalByProduct, 2) : '-';
            $payOrderQuantity = $payOrder[$product['id']]['quantity'] ?? 0;
            $product['pay_order'] = $payOrderQuantity > 0 ? number_format($payOrderQuantity, 2) : '-';
            $product['closing_balance'] = ($totalEndReading + $payOrderQuantity) > 0 ? number_format($totalEndReading + $payOrderQuantity, 2) : '-';
            $gainLoss = $totalByProduct != 0 && $totalQuantity != 0 ? ($totalByProduct - $totalQuantity) / $totalQuantity : 0 ;
            $product['gain_loss'] = $gainLoss;
            $product['gain_loss_format'] = $gainLoss > 0 ? number_format(abs($gainLoss), 2) .'%' : '-';
        }
        $accountReceivable = Category::where('client_company_id', $sessionUser['client_company_id'])->where('slug', strtolower( AccountCategory::ACCOUNT_RECEIVABLE))->first();
        $transaction = Transaction::select('transactions.account_id as id',  DB::raw("SUM(transactions.debit_amount) as amount"), 'categories.name', DB::raw('SUM(transactions.quantity) as quantity'), 'c1.name as product_name')
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->leftJoin('transactions as t1', 't1.linked_id', '=', 'transactions.id')
            ->leftJoin('categories as c1', 'c1.id', '=', 't1.account_id')
            ->where('transactions.date', $date)
            ->where('categories.parent_category', $accountReceivable->id)
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->having('amount', '>', 0)
            ->groupBy('transactions.id')
            ->get()
            ->toArray();
        $totalQuantity = 0;
        $totalAmount = 0;
        foreach ($transaction as &$data) {
            $data['amount_format'] = number_format($data['amount'], 2);
            $totalQuantity += $data['quantity'];
            $totalAmount += $data['amount'];
        }
        $expenses = Expense::select('expense.id', 'expense.date', 'c1.name as expense_type', 'c2.name as payment_method', 'expense.amount', 'expense.remarks', 'expense.approve_date', 'u1.name as approve_by', 'u2.name as request_by')
            ->leftJoin('categories as c1', 'c1.id', '=', 'expense.category_id')
            ->leftJoin('categories as c2', 'c2.id', '=', 'expense.payment_id')
            ->leftJoin('users as u1', 'u1.id', '=', 'expense.approve_by')
            ->leftJoin('users as u2', 'u2.id', '=', 'expense.user_id')
            ->whereBetween('date', [$startDate, $endDate])
            ->where('expense.client_company_id', $sessionUser['client_company_id'])
            ->where('status', FuelMatixStatus::APPROVE)
            ->get()
            ->toArray();
        $totalExpense = 0;
        foreach ($expenses as &$expense) {
            $totalExpense += $expense['amount'];
            $expense['amount_format'] = number_format($expense['amount'], 2);
        }
        return [
            'status' => 200,
            'data' => $products,
            'companySales' => $transaction,
            'expenses' => $expenses,
            'total' => [
                'quantity' => number_format($totalQuantity, 2),
                'amount' => number_format($totalAmount, 2),
                'expense' => number_format($totalExpense, 2),
            ]
        ];
    }

    /**
     * @param array $filter
     * @return array
     */
    public static function windfallReport(array $filter): array
    {
        $sessionUser = SessionUser::getUser();
        $startDate = Carbon::parse($filter['start_date'], SessionUser::TIMEZONE)->startOfDay();
        $endDate = Carbon::parse($filter['end_date'], SessionUser::TIMEZONE)->endOfDay();
        // Query for shift sales data
        $shiftSale = ShiftSale::select(
            'shift_total.id',
            DB::raw('DATE(shift_total.start_date) as date'),
            'shift_sale.net_profit',
            'products.name as product_name',
            DB::raw('shift_sale.net_profit_amount as amount'),
            DB::raw("'Shift Sale' as source")
        )
            ->leftJoin('shift_total', 'shift_total.id', '=', 'shift_sale.shift_id')
            ->leftJoin('products', 'products.id', '=', 'shift_total.product_id')
            ->whereBetween('shift_total.start_date', [$startDate, $endDate])
            ->where('shift_sale.net_profit_amount', '!=', 0)
            ->where('shift_total.client_company_id', $sessionUser['client_company_id']);

        // Apply product filter if provided
        if (!empty($productId)) {
            $shiftSale->where('shift_total.product_id', $productId);
        }

        // Query for tank refills data
        $tankRefill = TankRefill::select(
            'tank_refill_total.id',
            'tank_refill_total.date',
            'tank_refill.net_profit',
            'products.name as product_name',
            DB::raw('tank_refill.net_profit_amount as amount'),
            DB::raw("'Tank Refill' as source")
        )
            ->leftJoin('tank_refill_total', 'tank_refill_total.id', '=', 'tank_refill.refill_id')
            ->leftJoin('tank', 'tank.id', '=', 'tank_refill.tank_id')
            ->leftJoin('products', 'products.id', '=', 'tank.product_id')
            ->whereBetween('tank_refill_total.date', [$filter['start_date'], $filter['end_date']])
            ->whereNotNull('tank_refill.net_profit_amount')
            ->where('tank_refill.net_profit_amount', '!=', 0)
            ->where('tank_refill_total.client_company_id', $sessionUser['client_company_id']);

        // Apply product filter if provided
        if (!empty($productId)) {
            $tankRefill->where('products.id', $productId);
        }

        // Combine shift sale and tank refill results and order by date
        $queryResult = $shiftSale->union($tankRefill)
            ->orderBy('date', 'ASC')
            ->get()
            ->toArray();

        // Initialize total amount
        $total = 0;

        // Format results and calculate total amount
        foreach ($queryResult as &$result) {
            $result['status'] = $result['net_profit'] > 0 ? 'Profit' : 'Loss';
            $result['quantity'] = number_format(abs($result['net_profit']), 2);
            $result['date'] = date('d/m/Y', strtotime($result['date']));
            $total += $result['amount'];
            $result['amount'] = number_format(abs($result['amount']), 2);
        }
        return [
            'status' => 200,
            'data' => $queryResult,
            'total' => [
                'status' => $total > 0 ? 'Profit' : 'Loss',
                'amount' => number_format(abs($total), 2)
            ]
        ];
    }
    /**
     * @param array $filter
     * @return array
     */
    public static function vendorReport(array $filter): array
    {
        // Retrieve and aggregate transactions within the specified date range for the given vendor
        $result = Transaction::select('transactions.id','transactions.date', 't1.id as t1id',
            DB::raw('SUM(transactions.credit_amount) as bill'),
            DB::raw('SUM(transactions.debit_amount) as paid'),
            'categories.name as product')
            ->leftJoin('transactions as t1', 't1.id', '=', 'transactions.linked_id')
            ->leftJoin('categories', 'categories.id', '=', 't1.account_id')
            ->whereBetween('transactions.date', [$filter['start_date'], $filter['end_date']])
            ->where('transactions.account_id', $filter['vendor_id'])
            ->groupBy('transactions.id')
            ->orderBy('transactions.id', 'ASC')
            ->get()
            ->toArray();

        // Initialize total counters and balance tracker
        $total['bill'] = 0;
        $total['paid'] = 0;
        $total['balance'] = 0;
        $balance  = 0;

        // Loop through each transaction to calculate balances and format data
        foreach ($result as $key => &$data) {
            $data['product_name'] = '';
            $data['payment_method'] = '';

            // Determine if the transaction is a bill or a payment and label accordingly
            if ($data['bill'] > 0) {
                $data['product_name'] = $data['product'];
            } else if ($data['paid'] > 0) {
                $data['payment_method'] = $data['product'];
            }

            // Update balance with the difference between bill and payment amounts
            $balance =  $balance + $data['bill'] - $data['paid'];
            $data['balance'] = $balance;

            // Accumulate totals for bills, payments, and balances
            $total['bill'] += $data['bill'];
            $total['paid'] += $data['paid'];
            $total['balance'] += $data['balance'];

            // Format date and amounts for display
            $data['date'] = Helpers::formatDate($data['date'], FuelMatixDateTimeFormat::STANDARD_DATE);
            $data['bill'] = number_format($data['bill'], 2);
            $data['paid'] = number_format($data['paid'], 2);
            $data['balance'] = number_format($data['balance'], 2);
        }

        // Format total amounts for display
        $total['bill'] = number_format($total['bill'], 2);
        $total['paid'] = number_format($total['paid'], 2);
        $total['balance'] = number_format($balance, 2);
        return [
            'status' => 200,
            'data' => $result,
            'total' => $total
        ];
    }

    /**
     * @param array $filter
     * @return array
     */
    public static function creditCompanyReport(array $filter): array
    {
        $sessionUser = SessionUser::getUser();
        // Calculate the opening balance for the account category before the start date
        $openingBalance = Transaction::select(DB::raw('SUM(debit_amount - credit_amount) as opening_balance'))
            ->where('date', '<', $filter['start_date'])
            ->where('account_id', $filter['category_id'])
            ->first();

        // Default opening balance to 0 if no data is found
        $openingBalance = $openingBalance['opening_balance'] ?? 0;

        // Query for transactions within the date range, grouped by date
        $result = Transaction::select(
            'date',
            'categories.name as company_name',
            DB::raw('SUM(credit_amount) as paid_amount'),
            DB::raw('SUM(debit_amount) as bill_amount')
        )
            ->whereBetween('date', [$filter['start_date'], $filter['end_date']])
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->where('account_id', $filter['category_id'])
            ->groupBy('date')
            ->get()
            ->toArray();

        // Initialize the due amount with the opening balance
        $dueAmount = $openingBalance;

        // Process and format the results
        foreach ($result as &$item) {
            $dueAmount += $item['bill_amount'] - $item['paid_amount'];
            $item['due_amount'] = $dueAmount;
            $item['bill_amount'] = !empty($item['bill_amount']) ? number_format($item['bill_amount'], 2) : '';
            $item['paid_amount'] = !empty($item['paid_amount']) ? number_format($item['paid_amount'], 2) : '';
            $item['due_amount'] = !empty($item['due_amount']) ? number_format($item['due_amount'], 2) : '';
            $item['date'] = Helpers::formatDate($item['date'], FuelMatixDateTimeFormat::STANDARD_DATE);
        }
        return [
            'status' => 200,
            'data' => $result,
            'opening_balance' => [
                'amount' => $openingBalance,
                'amount_format' => number_format(abs($openingBalance), 2)
            ]
        ];

    }

    /**
     * @param $filter
     * @return array
     */
    public static function driverReport(array $filter): array
    {
        $sessionUser = SessionUser::getUser();
        $result = Transaction::select('transactions.id', 'categories.name as company_name', 'transactions.date', 'car.car_number', 'transactions.voucher_no', DB::raw('SUM(transactions.debit_amount) as bill'), DB::raw('SUM(transactions.quantity) as quantity'))
            ->leftJoin('car', 'car.id', '=', 'transactions.car_id')
            ->leftJoin('transactions as t1', 't1.linked_id', '=', 'transactions.id')
            ->leftJoin('categories', 'categories.id', '=', 't1.account_id')
            ->whereBetween('transactions.date', [$filter['start_date'], $filter['end_date']])
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->whereNotNull('transactions.car_id');

        if (!empty($companyId)) {
            $result->where(function($q) use ($companyId){
                $q->where('transactions.linked_id', $companyId);
            });
        }
        $result = $result->groupBy('transactions.date')
            ->groupBy('transactions.car_id')
            ->orderBy('transactions.date', 'ASC')
            ->get()
            ->toArray();
        $totalBill = 0;
        $totalQuantity = 0;
        foreach ($result as &$item) {
            $totalBill += $item['bill'];
            $totalQuantity += $item['quantity'];
            $item['date'] = date('d/m/Y', strtotime($item['date']));
            $item['bill'] = !empty($item['bill']) ? number_format($item['bill'], 2) : '';
            $item['quantity'] = !empty($item['quantity']) ? number_format($item['quantity'], 2) : '';
        }
        return [
            'status' => 200,
            'data' => $result,
            'total'=> [
                'bill' => number_format($totalBill, 2),
                'quantity' => number_format($totalQuantity, 2),
            ]
        ];
    }

}
