<?php

namespace App\Repository;

use App\Common\AccountCategory;
use App\Common\FuelMatixDateTimeFormat;
use App\Common\FuelMatixStatus;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\BalanceTransfer;
use App\Models\Car;
use App\Models\Category;
use App\Models\Expense;
use App\Models\FuelAdjustment;
use App\Models\Invoice;
use App\Models\PayOrderData;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleData;
use App\Models\ShiftSale;
use App\Models\ShiftTotal;
use App\Models\Tank;
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
        $sessionUser = SessionUser::getUser();
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
        $result['total']['sale'] = number_format($shiftSale['total'] + $posSale['total'], $sessionUser['currency_precision']);

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
            $data['amount'] = number_format($data['amount'], $sessionUser['currency_precision']);
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
            $data['amount'] = number_format($data['amount'], $sessionUser['currency_precision']); // Format the amount
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
            $data['amount'] = number_format($data['amount'], $sessionUser['currency_precision']); // Format the amount
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
            $data['amount'] = number_format($data['amount'], $sessionUser['currency_precision']); // Format the amount
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
            $data['amount'] = number_format($data['amount'], $sessionUser['currency_precision']); // Format the amount
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
            $data['amount'] = number_format($data['amount'], $sessionUser['currency_precision']); // Format the amount
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
                'amount' => number_format($data['amount'], $sessionUser['currency_precision'])
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

        $date = explode('to ', $date);
        $startDate = Carbon::parse($date[0], SessionUser::TIMEZONE)->startOfDay();
        $endDate = isset($date[1]) ? Carbon::parse($date[1], SessionUser::TIMEZONE)->endOfDay() : Carbon::parse($date[0], SessionUser::TIMEZONE)->endOfDay();
        $sessionUser = SessionUser::getUser();
        $products = Product::select('products.id', 'products.name as product_name', 'product_types.tank', 'products.selling_price')
            ->leftJoin('product_types', 'product_types.id', '=', 'products.type_id')
            ->where('products.client_company_id', $sessionUser['client_company_id'])
            ->where('product_types.type', 'fuel')
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

        $shiftId = ShiftTotal::select('id')
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->whereBetween('start_date', [$startDate, $endDate])
            ->get()
            ->pluck('id')
            ->toArray();

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
            ->whereBetween('tank_refill_total.date', [$startDate, $endDate])
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
        $grandTotal = 0;
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
                        $nozzle['start_reading_format'] = $nozzle['start_reading'] > 0 ?  number_format($nozzle['start_reading'], $sessionUser['quantity_precision']) : '-';
                        $nozzle['end_reading_format'] = $nozzle['end_reading'] > 0 ? number_format($nozzle['end_reading'], $sessionUser['quantity_precision']) : '-';
                        $nozzle['sale_format'] = $nozzle['sale'] > 0 ? number_format($nozzle['sale'], $sessionUser['quantity_precision']) : '-';
                        $totalSale += $nozzle['sale'];
                        $nozzle['unit_price_format'] = number_format($product['selling_price'], $sessionUser['currency_precision']);
                        $nozzle['amount'] = $nozzle['sale'] * $product['selling_price'];
                        $nozzle['amount_format'] = $nozzle['amount'] > 0 ?number_format($nozzle['amount'], $sessionUser['currency_precision']) : '-';
                        $totalAmount += $nozzle['amount'];
                    }
                }
                $tank['end_reading'] = $shiftSaleByTankId[$tank['id']]['tank_end_reading'] ?? 0;
                $tank['refill'] = $tankRefill[$tank['id']]['volume'] ?? 0;
                $tank['end_reading_format'] = $tank['end_reading'] > 0 ? number_format($tank['end_reading'], $sessionUser['quantity_precision']) : '-';
                $totalEndReading += $tank['end_reading'];
                $totalRefill += $tank['refill'];
            }
            $grandTotal += $totalAmount;
            $product['total'] = $totalSale > 0 ? number_format($totalSale, $sessionUser['quantity_precision']) : '-';
            $product['subtotal_amount'] = $totalAmount > 0 ? number_format($totalAmount, $sessionUser['currency_precision']) : '-';
            $adjustment = $fuelAdjustment[$product['id']]['total_quantity'] ?? 0;
            $product['adjustment'] = $adjustment > 0 ? number_format($adjustment, $sessionUser['quantity_precision']) : '-' ;
            $product['adjustment_amount'] = $adjustment > 0 ? number_format($adjustment * $product['selling_price'], $sessionUser['currency_precision']) : '-' ;

            $totalQuantity = $totalSale - $adjustment;
            $product['total_sale'] = $totalQuantity > 0 ? number_format($totalQuantity, $sessionUser['quantity_precision']) : '-';
            $product['total_amount'] = ($totalAmount - ($adjustment * $product['selling_price'])) > 0 ? number_format($totalAmount - ($adjustment * $product['selling_price']), $sessionUser['currency_precision']) : '-';
            $product['end_reading'] = $totalEndReading > 0 ? number_format($totalEndReading, $sessionUser['quantity_precision']) : '-';
            $product['tank_refill'] = $totalRefill > 0 ? number_format($totalRefill, $sessionUser['quantity_precision']) : '-';
            $totalByProduct = $totalEndReading + $totalRefill;
            $product['total_by_product'] = $totalByProduct > 0 ? number_format($totalByProduct, $sessionUser['quantity_precision']) : '-';
            $payOrderQuantity = $payOrder[$product['id']]['quantity'] ?? 0;
            $product['pay_order'] = $payOrderQuantity > 0 ? number_format($payOrderQuantity, $sessionUser['quantity_precision']) : '-';
            $product['closing_balance'] = ($totalEndReading + $payOrderQuantity) > 0 ? number_format($totalEndReading + $payOrderQuantity, $sessionUser['quantity_precision']) : '-';
            $gainLoss = $totalByProduct != 0 && $totalQuantity != 0 ? ($totalByProduct - $totalQuantity) / $totalQuantity : 0 ;
            $product['gain_loss'] = $gainLoss;
            $product['gain_loss_format'] = $gainLoss > 0 ? number_format(abs($gainLoss), $sessionUser['quantity_precision']) .'%' : '-';
        }
        $accountReceivable = Category::where('client_company_id', $sessionUser['client_company_id'])
            ->where('slug', strtolower( AccountCategory::ACCOUNT_RECEIVABLE))
            ->first();
        $transaction = Transaction::select('transactions.account_id as id',  DB::raw("SUM(transactions.debit_amount) as amount"),  DB::raw("SUM(transactions.credit_amount) as paid_amount"), 'categories.name', DB::raw('SUM(transactions.quantity) as quantity'), 'c1.name as product_name')
            ->leftJoin('transactions as t1', 't1.id', '=', 'transactions.linked_id')
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->leftJoin('categories as c1', 'c1.id', '=', 't1.account_id')
            ->whereBetween('transactions.date', [$startDate, $endDate])
            ->whereJsonContains('categories.category_ids', $accountReceivable->id)
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->groupBy('transactions.account_id')
            ->groupBy('c1.id')
            ->get()
            ->toArray();
        $totalQuantity = 0;
        $totalAmount = 0;
        $productWiseSale = [];
        $totalPaidAmount = 0;
        $debitAmountArray = [];
        $creditAmountArray = [];
        foreach ($transaction as &$data) {
            $data['amount_format'] = number_format($data['amount'], $sessionUser['currency_precision']);
            $totalQuantity += $data['quantity'];
            $data['quantity'] = number_format($data['quantity'], $sessionUser['quantity_precision']);
            $totalAmount += $data['amount'];
            $totalPaidAmount += $data['paid_amount'];
            $data['paid_amount_format'] = number_format($data['paid_amount'], $sessionUser['currency_precision']);

            if (!empty($data['amount'])) {
                $productWiseSale[$data['product_name']][] = $data;
                $debitAmountArray[] = $data;
            }
            if (!empty($data['paid_amount'])) {
                $creditAmountArray[] = $data;
            }
        }
        $productWiseSaleArray = [];
        foreach ($productWiseSale as $key => $row) {
            $quantity = array_sum(array_column($row, 'quantity'));
            $amount = array_sum(array_column($row, 'amount'));
            $productWiseSaleArray[] = [
                'product_name' => $key,
                'quantity' => number_format($quantity, $sessionUser['quantity_precision']),
                'amount_format' => number_format($amount, $sessionUser['currency_precision']),
            ];
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
            $expense['amount_format'] = number_format($expense['amount'], $sessionUser['currency_precision']);
        }
        $posMachine = Category::where('client_company_id', $sessionUser['client_company_id'])
            ->where('slug', strtolower(AccountCategory::POS_MACHINE))
            ->first();
        $posMachineCategory = Category::where('parent_category', $posMachine->id ?? 0)
            ->get()
            ->pluck('id')
            ->toArray();
        $posSales = SaleData::select(
            DB::raw('SUM(sale_data.quantity) as quantity'),
            DB::raw('SUM(sale_data.subtotal) as amount'),
            'price',
            'categories.name as category_name',
        )
            ->leftJoin('sale', 'sale.id', '=', 'sale_data.sale_id')
            ->leftJoin('products', 'products.id', '=', 'sale_data.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'sale.payment_category_id')
            ->where('sale.client_company_id', $sessionUser['client_company_id'])
            ->whereIn('sale_data.shift_sale_id', $shiftId)
            ->whereIn('sale.payment_category_id', $posMachineCategory)
            ->groupBy('sale.payment_category_id')
            ->get()
            ->toArray();
        $posSaleTotalAmount = 0;
        foreach ($posSales as &$posSale) {
            $posSaleTotalAmount += $posSale['amount'];
            $posSale['amount'] = number_format($posSale['amount'], $sessionUser['currency_precision']);
            $posSale['price'] = number_format($posSale['price'], $sessionUser['currency_precision']);
            $posSale['quantity'] = number_format($posSale['quantity'], $sessionUser['quantity_precision']);
        }
        $assetTransfer = BalanceTransfer::select(
            'balance_transfer.amount',
            'c1.name as from_category',
            'c2.name as to_category',
        )
            ->leftJoin('categories as c1', 'c1.id', '=', 'balance_transfer.from_category_id')
            ->leftJoin('categories as c2', 'c2.id', '=', 'balance_transfer.to_category_id')
            ->whereBetween('balance_transfer.date', [$startDate, $endDate])
            ->where('balance_transfer.client_company_id', $sessionUser['client_company_id'])
            ->get()
            ->toArray();
        $totalTransferAmount = 0;
        foreach ($assetTransfer as &$transfer) {
            $totalTransferAmount += $transfer['amount'];
            $transfer['amount'] = number_format($transfer['amount'], $sessionUser['currency_precision']);
        }
        return [
            'status' => 200,
            'data' => $products,
            'companySales' => $debitAmountArray,
            'companyPaid' => $creditAmountArray,
            'expenses' => $expenses,
            'posSales' => $posSales,
            'assetTransfer' => $assetTransfer,
            'productSales' => $productWiseSaleArray,
            'total' => [
                'quantity' => number_format($totalQuantity, $sessionUser['quantity_precision']),
                'amount' => number_format($totalAmount, $sessionUser['currency_precision']),
                'expense' => number_format($totalExpense, $sessionUser['currency_precision']),
                'posSaleTotalAmount' => number_format($posSaleTotalAmount, $sessionUser['currency_precision']),
                'totalTransferAmount' => number_format($totalTransferAmount, $sessionUser['currency_precision']),
                'paid_amount' => number_format($totalPaidAmount, $sessionUser['currency_precision']),
                'grandTotal' => number_format($grandTotal, $sessionUser['currency_precision'])
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
            $result['quantity'] = number_format(abs($result['net_profit']), $sessionUser['quantity_precision']);
            $result['date'] = date('d/m/Y', strtotime($result['date']));
            $total += $result['amount'];
            $result['amount'] = number_format(abs($result['amount']), $sessionUser['currency_precision']);
        }
        return [
            'status' => 200,
            'data' => $queryResult,
            'total' => [
                'status' => $total > 0 ? 'Profit' : 'Loss',
                'amount' => number_format(abs($total), $sessionUser['currency_precision'])
            ]
        ];
    }
    /**
     * @param array $filter
     * @return array
     */
    public static function vendorReport(array $filter): array
    {
        $sessionUser = SessionUser::getUser();
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
            $data['bill'] = number_format($data['bill'], $sessionUser['currency_precision']);
            $data['paid'] = number_format($data['paid'], $sessionUser['currency_precision']);
            $data['balance'] = number_format($data['balance'], $sessionUser['currency_precision']);
        }

        // Format total amounts for display
        $total['bill'] = number_format($total['bill'], $sessionUser['currency_precision']);
        $total['paid'] = number_format($total['paid'], $sessionUser['currency_precision']);
        $total['balance'] = number_format($balance, $sessionUser['currency_precision']);
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
            $item['bill_amount'] = !empty($item['bill_amount']) ? number_format($item['bill_amount'], $sessionUser['currency_precision']) : '';
            $item['paid_amount'] = !empty($item['paid_amount']) ? number_format($item['paid_amount'], $sessionUser['currency_precision']) : '';
            $item['due_amount'] = !empty($item['due_amount']) ? number_format($item['due_amount'], $sessionUser['currency_precision']) : '';
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
        $result = Transaction::select(
            'transactions.id',
            'categories.name as company_name',
            'transactions.date',
            'car.car_number',
            'transactions.voucher_no',
            'transactions.credit_amount as bill',
            DB::raw('transactions.quantity')
        )
            ->join('car', 'car.id', '=', 'transactions.car_id')
            ->leftJoin('transactions as t1', 't1.linked_id', '=', 'transactions.id')
            ->leftJoin('categories', 'categories.id', '=', 't1.account_id')
            ->whereBetween('transactions.date', [$filter['start_date'], $filter['end_date']])
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->having('credit_amount', '>', 0)
            ->whereNotNull('transactions.car_id');

        if (!empty($filter['company_id'])) {
            $result->where(function($q) use ($filter){
                $q->where('t1.account_id', $filter['company_id']);
            });
        }
        if (!empty($filter['car_number'])) {
            $car = Car::where('id', $filter['car_number'])->first();
            $result->where('car.car_number', $car['car_number']);
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
            $item['bill'] = !empty($item['bill']) ? number_format($item['bill'], $sessionUser['currency_precision']) : '';
            $item['quantity'] = !empty($item['quantity']) ? number_format($item['quantity'], $sessionUser['quantity_precision']) : '';
        }
        return [
            'status' => 200,
            'data' => $result,
            'total'=> [
                'bill' => number_format($totalBill, $sessionUser['currency_precision']),
                'quantity' => number_format($totalQuantity, $sessionUser['quantity_precision']),
            ]
        ];
    }

    /**
     * @param array $filter
     * @return array
     */
    public static function companySummary(array $filter): array
    {
        $sessionUser = SessionUser::getUser();
        $transactions = Transaction::select(
            'transactions.account_id as account_id',
            'categories.name as company_name',
            'c1.id as product_id',
            'c1.name as product_name',
            DB::raw('SUM(transactions.debit_amount) as amount')
        )
            ->leftJoin('transactions as t1', 't1.id', '=', 'transactions.linked_id')
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->leftJoin('categories as c1', 'c1.id', '=', 't1.account_id')
            ->whereJsonContains('categories.category_ids', $filter['company_id'])
            ->whereBetween('transactions.date', [$filter['start_date'], $filter['end_date']])
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->groupBy('transactions.account_id', 'categories.name', 'c1.id', 'c1.name')
            ->get()
            ->toArray();
        $groupedResults = [];

        $products = [];
        $productTotals = [];
        foreach ($transactions as $transaction) {
            $accountId = $transaction['account_id'];
            $companyName = $transaction['company_name'];

            // Check if the account_id and company_name already exist in the array
            if (!isset($groupedResults[$accountId])) {
                $groupedResults[$accountId] = [
                    'account_id' => $accountId,
                    'company_name' => $companyName,
                    'products' => []
                ];
            }

            $groupedResults[$accountId]['products'][] = [
                'id' => $transaction['product_id'],
                'name' => $transaction['product_name'],
                'amount' => number_format($transaction['amount'], $sessionUser['currency_precision'])
            ];
            $products[$transaction['product_id']] = [
                'id' => $transaction['product_id'],
                'name' =>  $transaction['product_name'],
            ];
            if (!isset($productTotals[$transaction['product_id']])) {
                $productTotals[$transaction['product_id']] = 0;
            }

            // Add amount to the product total
            $productTotals[$transaction['product_id']] += $transaction['amount'];
        }
        $finalResults = array_values($groupedResults);
        $products = array_values($products);
        foreach ($productTotals as $productId => $total) {
            $productTotals[$productId] = number_format($total, $sessionUser['currency_precision']);
        }

        // Convert associative array to indexed array for consistent output
        $productTotals = array_values($productTotals);
        return [
            'status' => 200,
            'products' => $products,
            'data' => $finalResults,
            'total' => $productTotals
        ];
    }
    public static function companySummaryDetails($filter)
    {
        $sessionUser = SessionUser::getUser();
        $transactions = Transaction::select(
            'transactions.account_id as account_id',
            'categories.name as company_name',
            'c1.id as product_id',
            'c1.name as product_name',
            'transactions.quantity as quantity',
            'car.car_number',
            DB::raw('SUM(transactions.debit_amount) as amount')
        )
            ->leftJoin('transactions as t1', 't1.id', '=', 'transactions.linked_id')
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->leftJoin('categories as c1', 'c1.id', '=', 't1.account_id')
            ->leftJoin('car', 'car.id', '=', 'transactions.car_id')
            ->where('transactions.account_id', $filter['company_id'])
            ->whereBetween('transactions.date', [$filter['start_date'], $filter['end_date']])
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->groupBy('transactions.car_id')
            ->get()
            ->toArray();
        $groupedResults = [];

        $products = [];
        $productTotals = [];
        foreach ($transactions as $transaction) {
            $accountId = $transaction['account_id'];

            // Check if the account_id and company_name already exist in the array
            if (!isset($groupedResults[$accountId])) {
                $groupedResults[$transaction['car_number']] = [
                    'account_id' => $accountId,
                    'car_number' => $transaction['car_number'],
                    'products' => []
                ];
            }

            $groupedResults[$transaction['car_number']]['products'][] = [
                'id' => $transaction['product_id'],
                'name' => $transaction['product_name'],
                'unit_price' => !empty($transaction['amount']) && !empty($transaction['quantity']) ? number_format($transaction['amount'] / $transaction['quantity'], 2) : '',
                'quantity' => $transaction['quantity'],
                'amount' => number_format($transaction['amount'], 2)
            ];

            $products[$transaction['product_id']] = [
                'id' => $transaction['product_id'],
                'name' =>  $transaction['product_name'],
            ];
            if (!isset($productTotals[$transaction['product_id']])) {
                $productTotals[$transaction['product_id']] = 0;
            }

            // Add amount to the product total
            $productTotals[$transaction['product_id']] += $transaction['amount'];
        }
        $finalResults = array_values($groupedResults);
        $products = array_values($products);
        $grandTotal = 0;
        foreach ($productTotals as $productId => $total) {
            $grandTotal += $total;
            $productTotals[$productId] = number_format($total, 2);
        }

        // Convert associative array to indexed array for consistent output
        $productTotals = array_values($productTotals);
        return [
            'status' => 200,
            'products' => $products,
            'data' => $finalResults,
            'total' => $productTotals,
            'grandTotal' => number_format($grandTotal, 2),
        ];
    }

    /**
     * @param array $filter
     * @param Product $product
     * @return array
     */
    public static function salesReport(array $filter, Product $product): array
    {
        $startDate = Carbon::parse($filter['start_date'], SessionUser::TIMEZONE)->startOfDay();
        $endDate = Carbon::parse($filter['end_date'], SessionUser::TIMEZONE)->endOfDay();
        $sessionUser = SessionUser::getUser();


        $tanks = Tank::select('id', 'tank_name')
            ->with(['dispensers' => function($q) {
                $q->select('id', 'dispenser_name as name', 'tank_id');
            }, 'dispensers.nozzle' => function($q) {
                $q->select('id', 'name', 'dispenser_id');
            }])
            ->where('product_id', $product['id'])
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->get()
            ->toArray();
        $nozzleIds = [];
        $tanksId = array_column($tanks, 'id');
        foreach ($tanks as $tank) {
            foreach ($tank['dispensers'] as $dispenser) {
                foreach ($dispenser['nozzle'] as $nozzle) {
                    $nozzleIds[] = $nozzle['id'];
                }
            }
        }

        // Fetch first and last IDs per tank per day
        $tankReading = ShiftSale::join('shift_total', 'shift_total.id', '=', 'shift_sale.shift_id')
            ->join('shift_summary', 'shift_summary.shift_sale_id', '=', 'shift_sale.id')
            ->select(
                'shift_sale.tank_id',
                'shift_summary.nozzle_id',
                DB::raw('DATE(shift_total.start_date) as reading_date'),
                DB::raw('MIN(CASE WHEN shift_sale.id = min_ids_table.min_id THEN shift_sale.start_reading ELSE NULL END) as start_reading'),
                DB::raw('MAX(CASE WHEN shift_sale.id = max_ids_table.max_id THEN shift_sale.end_reading ELSE NULL END) as end_reading')
            )
            ->leftJoin(DB::raw('(SELECT s.tank_id, DATE(st.start_date) as reading_date, MIN(s.id) as min_id
                 FROM shift_sale s
                 JOIN shift_total st ON st.id = s.shift_id
                 GROUP BY s.tank_id, DATE(st.start_date)
                ) as min_ids_table'),
                function ($join) {
                    $join->on('shift_sale.tank_id', '=', 'min_ids_table.tank_id')
                        ->on(DB::raw('DATE(shift_total.start_date)'), '=', 'min_ids_table.reading_date')
                        ->on('shift_sale.id', '=', 'min_ids_table.min_id');
                }
            )
            ->leftJoin(DB::raw('(SELECT s.tank_id, DATE(st.start_date) as reading_date, MAX(s.id) as max_id
                 FROM shift_sale s
                 JOIN shift_total st ON st.id = s.shift_id
                 GROUP BY s.tank_id, DATE(st.start_date)
                ) as max_ids_table'),
                function ($join) {
                    $join->on('shift_sale.tank_id', '=', 'max_ids_table.tank_id')
                        ->on(DB::raw('DATE(shift_total.start_date)'), '=', 'max_ids_table.reading_date')
                        ->on('shift_sale.id', '=', 'max_ids_table.max_id');
                }
            )
            ->whereBetween('shift_total.start_date', [$startDate, $endDate])
            ->whereIn('shift_sale.tank_id', $tanksId)
            ->groupBy('shift_sale.tank_id', 'shift_summary.nozzle_id', DB::raw('DATE(shift_total.start_date)'))
            ->get();
        $nozzleReadings = DB::table('shift_sale')
            ->join('shift_total', 'shift_total.id', '=', 'shift_sale.shift_id')
            ->join('shift_summary', 'shift_summary.shift_sale_id', '=', 'shift_sale.id')
            ->select(
                'shift_summary.nozzle_id',
                DB::raw('DATE(shift_total.start_date) as reading_date'),
                DB::raw('MIN(CASE WHEN shift_sale.id = min_ids_table.min_id THEN shift_summary.start_reading ELSE NULL END) as start_reading'),
                DB::raw('MAX(CASE WHEN shift_sale.id = max_ids_table.max_id THEN shift_summary.end_reading ELSE NULL END) as end_reading')
            )
            ->leftJoin(DB::raw('(SELECT ss.nozzle_id, DATE(st.start_date) as reading_date, MIN(s.id) as min_id
                         FROM shift_sale s
                         JOIN shift_total st ON st.id = s.shift_id
                         JOIN shift_summary ss ON ss.shift_sale_id = s.id
                         GROUP BY ss.nozzle_id, DATE(st.start_date)
                        ) as min_ids_table'),
                function ($join) {
                    $join->on('shift_summary.nozzle_id', '=', 'min_ids_table.nozzle_id')
                        ->on(DB::raw('DATE(shift_total.start_date)'), '=', 'min_ids_table.reading_date')
                        ->on('shift_sale.id', '=', 'min_ids_table.min_id');
                }
            )
            ->leftJoin(DB::raw('(SELECT ss.nozzle_id, DATE(st.start_date) as reading_date, MAX(s.id) as max_id
                         FROM shift_sale s
                         JOIN shift_total st ON st.id = s.shift_id
                         JOIN shift_summary ss ON ss.shift_sale_id = s.id
                         GROUP BY ss.nozzle_id, DATE(st.start_date)
                        ) as max_ids_table'),
                function ($join) {
                    $join->on('shift_summary.nozzle_id', '=', 'max_ids_table.nozzle_id')
                        ->on(DB::raw('DATE(shift_total.start_date)'), '=', 'max_ids_table.reading_date')
                        ->on('shift_sale.id', '=', 'max_ids_table.max_id');
                }
            )
            ->whereBetween('shift_total.start_date', [$startDate, $endDate])
            ->whereIn('shift_summary.nozzle_id', $nozzleIds)
            ->groupBy('shift_summary.nozzle_id', DB::raw('DATE(shift_total.start_date)'))
            ->get()
            ->toArray();

        $tankRefill = TankRefillTotal::select(DB::raw('SUM(tank_refill.dip_sale) as volume'), 'tank_refill.tank_id', 'tank_refill_total.date')
            ->leftJoin('tank_refill', 'tank_refill.refill_id', '=', 'tank_refill_total.id')
            ->where('tank_refill_total.client_company_id', $sessionUser['client_company_id'])
            ->whereIn('tank_refill.tank_id', $tanksId)
            ->whereBetween('tank_refill_total.date', [$startDate, $endDate])
            ->groupBy('tank_refill.tank_id', 'tank_refill_total.date')
            ->get();

        $result = [];
        while ($startDate->lessThanOrEqualTo($endDate)) {
            $date = Carbon::parse($startDate)->format('Y-m-d');
            foreach ($tanks as &$tank) {
                $tankReadingCollection = collect($tankReading)->where('tank_id', $tank['id'])->where('reading_date', $date)->first();
                $tankRefillCollection = collect($tankRefill)->where('tank_id', $tank['id'])->where('date', $date)->first();
                $tank['start_reading'] = 0;
                $tank['end_reading'] = 0;
                $tank['refill'] = 0;
                $tank['selling_price'] = $product['selling_price'];
                if (!empty($tankReadingCollection)) {
                    $tank['start_reading'] = $tankReadingCollection->start_reading;
                    $tank['end_reading'] = $tankReadingCollection->end_reading;
                }
                if (!empty($tankRefillCollection)) {
                    $tank['refill'] = $tankRefillCollection->volume;
                }
                $totalSale = 0;
                $totalAmount = 0;
                foreach ($tank['dispensers'] as &$dispenser) {
                    foreach ($dispenser['nozzle'] as &$nozzle) {
                        $nozzle['start_reading'] = 0;
                        $nozzle['end_reading'] = 0;
                        $nozzle['sale'] = 0;
                        $nozzleReadingCollection = collect($nozzleReadings)->where('nozzle_id', $nozzle['id'])->where('reading_date', $date)->first();
                        if (!empty($nozzleReadingCollection)) {
                            $nozzle['start_reading'] = $nozzleReadingCollection->start_reading;
                            $nozzle['end_reading'] = $nozzleReadingCollection->end_reading;
                            $nozzle['sale'] = $nozzle['end_reading'] - $nozzle['start_reading'];
                            $nozzle['amount'] = $nozzle['sale'] * $product['selling_price'];
                            $totalSale += $nozzle['sale'];
                            $totalAmount += $nozzle['amount'];
                        }
                        $nozzle['start_reading_format'] = !empty($nozzle['start_reading']) ? number_format($nozzle['start_reading'], $sessionUser['quantity_precision']) : '--';
                        $nozzle['end_reading_format'] = !empty($nozzle['end_reading']) ? number_format($nozzle['end_reading'], $sessionUser['quantity_precision']) : '--';
                        $nozzle['sale_format'] = !empty($nozzle['sale']) ? number_format($nozzle['sale'], $sessionUser['quantity_precision']) : '--';
                        $nozzle['amount_format'] = !empty($nozzle['amount']) ? number_format($nozzle['amount'], $sessionUser['currency_precision']) : '--';
                    }
                }
                $tank['total_sale'] = $totalSale;
                $tank['total_amount'] = $totalAmount;
                $tank['refill_format'] = !empty($tank['refill']) ? number_format($tank['refill'], $sessionUser['quantity_precision']) : '--';
                $tank['start_reading_format'] = !empty($tank['start_reading']) ? number_format($tank['start_reading'], $sessionUser['quantity_precision']) : '--';
                $tank['end_reading_format'] = !empty($tank['end_reading']) ? number_format($tank['end_reading'], $sessionUser['quantity_precision']) : '--';
                $tank['total_sale_format'] = !empty($tank['total_sale']) ? number_format($tank['total_sale'], $sessionUser['quantity_precision']) : '--';
                $tank['total_amount_format'] = !empty($tank['total_amount']) ? number_format($tank['total_amount'], $sessionUser['currency_precision']) : '--';
                $tank['selling_price_format'] = !empty($tank['selling_price']) ? number_format($tank['selling_price'], $sessionUser['currency_precision']) : '--';
            }
            $result[] = [
                'date' => $date,
                'tanks' => $tanks,
            ];
            $startDate->addDays(1);
        }
        return $result;

    }

    /**
     * @param array $filter
     * @return array
     */
    public static function posMachineReport(array $filter): array
    {
        $startDate = Carbon::parse($filter['start_date'])->startOfDay();
        $endDate = Carbon::parse($filter['end_date'])->endOfDay();
        $sessionUser = SessionUser::getUser();
        $posMachineId = Category::where('slug', strtolower(AccountCategory::POS_MACHINE))
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first()->id;
        $posMachineIds = Category::where('parent_category', $posMachineId)
            ->pluck('id')
            ->toArray();
        $result = Sale::select(
            'sale.id',
            'categories.name as category_name',
            'sale.date',
            'sale.card_number',
            'sale.voucher_number',
            'sale.invoice_number',
            'sale_data.quantity',
            'sale_data.price',
            'sale_data.subtotal',
            'products.name as product_name'
        )
            ->join('sale_data', 'sale_data.sale_id', '=', 'sale.id')
            ->join('categories', 'categories.id', '=', 'sale.payment_category_id')
            ->join('products', 'products.id', '=', 'sale_data.product_id')
            ->whereBetween('sale.date', [$startDate, $endDate])
            ->whereIn('payment_category_id', $posMachineIds)
            ->groupBy('sale_data.id')
            ->get()
            ->toArray();
        $total = 0;
        $totalQuantity = 0;
        foreach ($result as &$item) {
            $total += $item['subtotal'];
            $totalQuantity += $item['quantity'];
            $item['date'] =  Helpers::formatDate($item['date'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME);
            $item['quantity'] = number_format($item['quantity'], $sessionUser['quantity_precision']);
            $item['price'] = number_format($item['price'], $sessionUser['currency_precision']);
            $item['subtotal'] = number_format($item['subtotal'], $sessionUser['currency_precision']);
        }
        return [
            'status' => 200,
            'data' => $result,
            'total' => [
                'amount' => number_format($total, $sessionUser['currency_precision']),
                'quantity' => number_format($totalQuantity, $sessionUser['quantity_precision']),
            ],
        ];
    }

}
