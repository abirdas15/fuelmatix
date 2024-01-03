<?php

namespace App\Repository;

use App\Common\AccountCategory;
use App\Common\FuelMatixCategoryType;
use App\Common\FuelMatixDateTimeFormat;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Sale;
use App\Models\ShiftSale;
use App\Models\TankLog;
use App\Models\TankRefill;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class ReportRepository
{
    /**
     * @param array $filter
     * @return array
     */
    public static function dailyLog(array $filter): array
    {
        $shiftSale = self::getShiftSale($filter['date']);
        $posSale =  self::getPosSale($filter['date']);
        $result['shift_sale'] = $shiftSale['data'];
        $result['pos_sale'] = $posSale['data'];
        $result['tank_refill'] = self::getTankRefill($filter['date']);
        $result['stock'] = self::getStock($filter['date']);
        $result['expense'] = self::getAllExpense($filter['date']);
        $result['due_payments'] = self::getDuePayments($filter['date']);
        $result['due_invoice'] = self::getDueInvoice($filter['date']);
        $result['asset_balance']['cash'] = self::getAssetBalance($filter['date'], AccountCategory::CASH_IM_HAND);
        $result['asset_balance']['bank'] = self::getAssetBalance($filter['date'], AccountCategory::BANK);
        $result['total']['sale'] = number_format($shiftSale['total'] + $posSale['total'], 2);
        return $result;
    }
    /**
     * @param string $date
     * @return array
     */
    public static function getPosSale(string $date): array
    {
        $sessionUser = SessionUser::getUser();
        $result =  Sale::select('products.name as product_name', DB::raw('SUM(quantity) as quantity'), DB::raw('SUM(subtotal) as amount'), 'date', 'product_types.unit')
            ->leftJoin('sale_data', 'sale_data.sale_id', '=', 'sale.id')
            ->leftJoin('products', 'products.id', '=', 'sale_data.product_id')
            ->leftJoin('product_types', 'product_types.id', '=', 'products.type_id')
            ->where(DB::raw('DATE(date)'), $date)
            ->where('sale.client_company_id', $sessionUser['client_company_id'])
            ->where('product_types.shift_sale', '0')
            ->groupBy('sale_data.product_id')
            ->get()
            ->toArray();
        $total = 0;
        foreach ($result as &$data) {
            $total += $data['amount'];
            $data['time'] = Helpers::formatDate($data['date'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME);
            $data['amount'] = number_format($data['amount'], 2);
        }
        return [
            'data' => $result,
            'total' => $total
        ];
    }
    /**
     * @param string $date
     * @param string $accountCategoryName
     * @return array
     */
    public static function getAssetBalance(string $date, string $accountCategoryName): array
    {
        $sessionUser = SessionUser::getUser();
        $accountCategory = Category::select('id')->where('client_company_id', $sessionUser['client_company_id'])->where('slug', strtolower($accountCategoryName))->first();
        $category = Category::select('id')->where('parent_category', $accountCategory->id)->get()->pluck('id')->toArray();
        $transaction = Transaction::select(DB::raw('SUM(debit_amount - credit_amount) as amount'), 'categories.name as category_name')
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->leftJoin('categories', 'categories.id', 'transactions.linked_id')
            ->whereIn('transactions.linked_id', $category)
            ->where('transactions.date', '<=',  $date)
            ->groupBy('transactions.linked_id')
            ->get()
            ->toArray();
        foreach ($transaction as &$data) {
            $data['amount'] = number_format($data['amount'], 2);
        }
        return $transaction;
    }
    /**
     * @param string $date
     * @return array
     * */
    public static function getDueInvoice(string $date): array
    {
        $sessionUser = SessionUser::getUser();
        $result = Invoice::select(DB::raw('SUM(amount - paid_amount) as amount'), 'categories.name as category_name')
            ->where('invoices.client_company_id', $sessionUser['client_company_id'])
            ->leftJoin('categories', 'categories.id', 'invoices.category_id')
            ->where('date', $date)
            ->having('amount', '>', 0)
            ->groupBy('invoices.category_id')
            ->get()
            ->toArray();
        foreach ($result as &$data) {
            $data['amount'] = number_format($data['amount'], 2);
        }
        return $result;
    }
    /**
     * @param string $date
     * @return array
     */
    public static function getDuePayments(string $date): array
    {
        $sessionUser = SessionUser::getUser();
        $accountPayable = Category::select('id')->where('client_company_id', $sessionUser['client_company_id'])->where('slug', strtolower(AccountCategory::ACCOUNT_PAYABLE))->first();
        $transaction = Transaction::select(DB::raw('SUM(credit_amount) as amount'), 'categories.name as category_name')
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->leftJoin('categories', 'categories.id', 'transactions.linked_id')
            ->where('categories.parent_category', $accountPayable->id)
            ->where('date', $date)
            ->having('amount', '>', 0)
            ->groupBy('transactions.linked_id')
            ->get()
            ->toArray();
        foreach ($transaction as &$data) {
            $data['amount'] = number_format($data['amount'], 2);
        }
        return $transaction;
    }

    /**
     * @param string $date
     * @return array
     */
    public static function getCostOfGoodSoldExpense(string $date): array
    {
        $sessionUser = SessionUser::getUser();
        $costOfGoodSoldCategory = Category::select('id')->where('client_company_id', $sessionUser['client_company_id'])->where('slug', strtolower(AccountCategory::COST_OF_GOOD_SOLD))->first();
        $transaction = Transaction::select(DB::raw('SUM(debit_amount) as amount'), 'categories.name as category_name')
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->leftJoin('categories', 'categories.id', 'transactions.linked_id')
            ->where('categories.parent_category', $costOfGoodSoldCategory->id)
            ->where('date', $date)
            ->having('amount', '>', 0)
            ->groupBy('transactions.linked_id')
            ->get()
            ->toArray();
        foreach ($transaction as &$data) {
            $data['amount'] = number_format($data['amount'], 2);
        }
        return $transaction;
    }

    /**
     * @param string $date
     * @return array
     */
    public static function getAllExpense(string $date): array
    {
        $sessionUser = SessionUser::getUser();
        $transaction = Transaction::select(DB::raw('SUM(debit_amount) as amount'), 'categories.name as category_name')
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->leftJoin('categories', 'categories.id', 'transactions.linked_id')
            ->where('categories.type', FuelMatixCategoryType::EXPENSE)
            ->where('date', $date)
            ->having('amount', '>', 0)
            ->groupBy('linked_id')
            ->get()
            ->toArray();
        foreach ($transaction as $data) {
            $data['amount'] = number_format($data['amount'], 2);
        }
        return $transaction;
    }
    /**
     * @param string $date
     * @return array
     */
    public static function getStock(string $date): array
    {
        $sessionUser = SessionUser::getUser();
        $result = TankLog::select('tank_log.id', 'tank_log.volume', 'products.id', 'products.name as product_name', 'product_types.unit')
            ->leftJoin('tank', 'tank.id', 'tank_log.tank_id')
            ->leftJoin('products', 'products.id', 'tank.product_id')
            ->leftJoin('product_types', 'product_types.id', 'products.type_id')
            ->where(DB::raw('DATE(tank_log.date)'), $date)
            ->where('tank_log.client_company_id', $sessionUser['client_company_id'])
            ->get()
            ->toArray();
        $dataArray = [];
        foreach ($result as $data) {
            $dataArray[$data['product_name']][] = $data;
        }
        $resultArray = [];
        foreach ($dataArray as $key => $data) {
            $resultArray[] = [
                'name' => $key,
                'unit' =>  $data[0]['unit'],
                'opening_stock' => $data[0]['volume'],
                'closing_stock' => count($data) > 1 ? $data[count($data) - 1]['volume'] : $data[0]['volume']
            ];
        }
        return $resultArray;
    }

    /**
     * @param string $date
     * @return mixed
     */
    public static function getTankRefill(string $date)
    {
        $sessionUser = SessionUser::getUser();
        $result = TankRefill::select('tank_refill.id', 'tank_refill.date', 'tank_refill.time', 'tank_refill.total_refill_volume as quantity', 'tank_refill.net_profit', 'products.name as product_name', 'product_types.unit')
            ->leftJoin('tank', 'tank.id', 'tank_refill.tank_id')
            ->leftJoin('products', 'products.id', 'tank.product_id')
            ->leftJoin('product_types', 'product_types.id', 'products.type_id')
            ->where('tank_refill.date', $date)
            ->where('tank_refill.client_company_id', $sessionUser['client_company_id'])
            ->get()
            ->toArray();
        foreach ($result as &$data) {
            $data['date'] = Helpers::formatDate($data['date']. ' '.$data['time'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME);
        }
        return $result;
    }
    /**
     * @param string $date
     * @return array
     */
    public static function getShiftSale(string $date): array
    {
        $sessionUser = SessionUser::getUser();
        $result = ShiftSale::select('consumption', 'amount', 'product_id', 'start_time', 'end_time', 'products.name as product_name', 'product_types.unit')
            ->leftJoin('products', 'products.id', '=', 'shift_sale.product_id')
            ->leftJoin('product_types', 'product_types.id', '=', 'products.type_id')
            ->where('date', $date)
            ->where('shift_sale.client_company_id', $sessionUser['client_company_id'])
            ->where('status', 'end')
            ->get()
            ->toArray();
        $total = 0;
        $resultArray = [];
        foreach ($result as $data) {
            $total += $data['amount'];
            $resultArray[$data['product_name']][] = [
                'time' => 'Shift('.Helpers::formatDate($data['start_time'], FuelMatixDateTimeFormat::STANDARD_TIME).' - '.Helpers::formatDate($data['end_time'], FuelMatixDateTimeFormat::STANDARD_TIME).')',
                'quantity' => $data['consumption'],
                'unit' => $data['unit'],
                'amount' => number_format($data['amount'], 2)
            ];
        }
        $finalResult = [];
        foreach ($resultArray as $key => $row) {
            $finalResult[] = [
                'product_name' => $key,
                'data' => $row
            ];
        }
        return [
            'data' => $finalResult,
            'total' => $total,
        ];
    }
}
