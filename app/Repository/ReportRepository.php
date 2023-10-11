<?php

namespace App\Repository;

use App\Common\AccountCategory;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ShiftSale;
use App\Models\TankLog;
use App\Models\TankRefill;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class ReportRepository
{
    public static function dailyLog($filter)
    {
        $sessionUser = SessionUser::getUser();
        $products = Product::select('id', 'name')->where('client_company_id', $sessionUser['client_company_id'])->get()->toArray();
        $currentShiftSales = self::getShiftSale($filter['date']);

        $lastDate =  date('Y-m-d', strtotime("-1 day", strtotime($filter['date'])));
        $lastShiftSales = self::getShiftSale($lastDate);

        $lastShiftSaleArray = [];
        $shiftSaleArray = [];
        foreach ($currentShiftSales as $shiftSale) {
            $shiftSaleArray[$shiftSale['product_id']][] = $shiftSale;
        }
        foreach ($lastShiftSales as $shiftSale) {
            $lastShiftSaleArray[$shiftSale['product_id']][] = $shiftSale;
        }
        $totalShift = count($shiftSaleArray) ?  count(max($shiftSaleArray)) : 0;
        foreach ($products as &$product) {
            $totalQuantity = 0;
            $totalAmount = 0;
            $lastDateQuantity = 0;
            for ($i = 0; $i < $totalShift; $i++) {
                if (isset($shiftSaleArray[$product['id']])) {
                    $consumption = isset($shiftSaleArray[$product['id']][$i]) ? $shiftSaleArray[$product['id']][$i]['consumption'] : 0;
                    $amount = isset($shiftSaleArray[$product['id']][$i]) ? $shiftSaleArray[$product['id']][$i]['amount'] : 0;

                    $lastDateQuantity +=  isset($lastShiftSaleArray[$product['id']][$i]) ? $lastShiftSaleArray[$product['id']][$i]['consumption'] : 0;

                    $product['value'][] = [
                        'quantity' => $consumption,
                        'amount' => number_format($amount, 2)
                    ];
                    $totalQuantity += $consumption;
                    $totalAmount += $amount;
                } else {
                    $product['value'][] = [
                        'quantity' => 0,
                        'amount' => 0,
                    ];
                }
            }
            $product['total'] = [
                'quantity' => $totalQuantity,
                'amount' => number_format($totalAmount, 2),
                'percent' => $totalQuantity > 0 && $lastDateQuantity > 0 ? number_format((($totalQuantity - $lastDateQuantity) / $lastDateQuantity) * 100, 2) : 0
            ];
        }
        $result['shift_sale'] = [
            'totalShift' => $totalShift,
            'data' => $products
        ];
        $result['tank_refill'] = self::getTankRefill($filter['date']);
        $result['stock'] = self::getStock($filter['date']);
        $result['expense']['salary'] = self::getSalaryExpense($filter['date']);
        $result['expense']['cost_of_good_sold'] = self::getCostOfGoodSoldExpense($filter['date']);
        $result['due_payments'] = self::getDuePayments($filter['date']);
        $result['due_invoice'] = self::getDueInvoice($filter['date']);
        $result['asset_balance']['cash'] = self::getAssetBalance($filter['date'], AccountCategory::CASH_IM_HAND);
        $result['asset_balance']['bank'] = self::getAssetBalance($filter['date'], AccountCategory::BANK);
        return $result;
    }
    public static function getAssetBalance($date, $accountCategoryName)
    {
        $sessionUser = SessionUser::getUser();
        $accountCategory = Category::select('id')->where('client_company_id', $sessionUser['client_company_id'])->where('slug', strtolower($accountCategoryName))->first();
        $category = Category::select('id')->where('parent_category', $accountCategory->id)->get()->pluck('id')->toArray();
        $transaction = Transaction::select(DB::raw('SUM(debit_amount - credit_amount) as amount'), 'categories.category as category_name')
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->leftJoin('categories', 'categories.id', 'transactions.linked_id')
            ->whereIn('transactions.linked_id', $category)
            ->where('transactions.date', $date)
            ->groupBy('transactions.linked_id')
            ->get()
            ->toArray();
        return $transaction;
    }
    public static function getDueInvoice($date)
    {
        $sessionUser = SessionUser::getUser();
        $result = Invoice::select(DB::raw('SUM(amount - paid_amount) as amount'), 'categories.category as category_name')
            ->where('invoices.client_company_id', $sessionUser['client_company_id'])
            ->leftJoin('categories', 'categories.id', 'invoices.category_id')
            ->where('date', $date)
            ->having('amount', '>', 0)
            ->groupBy('invoices.category_id')
            ->get()
            ->toArray();
        return $result;
    }
    public static function getDuePayments($date)
    {
        $sessionUser = SessionUser::getUser();
        $accountPayable = Category::select('id')->where('client_company_id', $sessionUser['client_company_id'])->where('slug', strtolower(AccountCategory::ACCOUNT_PAYABLE))->first();
        $transaction = Transaction::select(DB::raw('SUM(credit_amount) as amount'), 'categories.category as category_name')
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->leftJoin('categories', 'categories.id', 'transactions.linked_id')
            ->where('categories.parent_category', $accountPayable->id)
            ->where('date', $date)
            ->having('amount', '>', 0)
            ->groupBy('transactions.linked_id')
            ->get()
            ->toArray();
        return $transaction;
    }

    public static function getCostOfGoodSoldExpense($date)
    {
        $sessionUser = SessionUser::getUser();
        $costOfGoodSoldCategory = Category::select('id')->where('client_company_id', $sessionUser['client_company_id'])->where('slug', strtolower(AccountCategory::COST_OF_GOOD_SOLD))->first();
        $transaction = Transaction::select(DB::raw('SUM(debit_amount) as amount'), 'categories.category as category_name')
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->leftJoin('categories', 'categories.id', 'transactions.linked_id')
            ->where('categories.parent_category', $costOfGoodSoldCategory->id)
            ->where('date', $date)
            ->having('amount', '>', 0)
            ->groupBy('transactions.linked_id')
            ->get()
            ->toArray();
        return $transaction;
    }
    public static function getSalaryExpense($date)
    {
        $sessionUser = SessionUser::getUser();
        $salaryExpense = Category::select('id')->where('client_company_id', $sessionUser['client_company_id'])->where('slug', strtolower(AccountCategory::SALARY_EXPENSE))->first();
        $transaction = Transaction::where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->leftJoin('categories', 'categories.id', 'transactions.linked_id')
            ->where('categories.parent_category', $salaryExpense->id)
            ->where('date', $date)
            ->sum('debit_amount');
        return $transaction;
    }
    public static function getStock($date)
    {
        $sessionUser = SessionUser::getUser();
        $result = TankLog::select('tank_log.id', 'tank_log.volume', 'products.id', 'products.name as product_name')
            ->leftJoin('tank', 'tank.id', 'tank_log.tank_id')
            ->leftJoin('products', 'products.id', 'tank.product_id')
            ->where('date', $date)
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
                'opening_stock' => $data[0]['volume'],
                'closing_stock' => count($data) > 1 ? $data[count($data) - 1]['volume'] : 0
            ];
        }
        return $resultArray;
    }
    public static function getTankRefill($date)
    {
        $sessionUser = SessionUser::getUser();
        $result = TankRefill::select('tank_refill.id', 'tank_refill.date', 'tank_refill.total_refill_volume as quantity', 'tank_refill.net_profit', 'products.name as product_name')
            ->leftJoin('tank', 'tank.id', 'tank_refill.tank_id')
            ->leftJoin('products', 'products.id', 'tank.product_id')
            ->where('tank_refill.date', $date)
            ->where('tank_refill.client_company_id', $sessionUser['client_company_id'])
            ->get()
            ->toArray();
        foreach ($result as &$data) {
            $data['date'] = date('d/m/Y', strtotime($data['date']));
        }
        return $result;
    }
    public static function getShiftSale($date)
    {
        $sessionUser = SessionUser::getUser();
        $result = ShiftSale::select('consumption', 'amount', 'product_id')
            ->where('date', $date)
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->where('status', 'end')
            ->get()
            ->toArray();
        return $result;
    }
}
