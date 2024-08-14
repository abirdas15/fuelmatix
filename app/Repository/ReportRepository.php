<?php

namespace App\Repository;

use App\Common\AccountCategory;
use App\Common\FuelMatixDateTimeFormat;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Sale;
use App\Models\ShiftTotal;
use App\Models\TankLog;
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

}
