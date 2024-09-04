<?php

namespace App\Services;

use App\Common\FuelMatixStatus;
use App\Helpers\SessionUser;
use App\Models\FuelAdjustment;
use App\Models\PayOrderData;
use App\Models\Product;
use App\Models\ShiftTotal;
use App\Models\TankRefillTotal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StockSummaryService
{
    protected $sessionUser;
    protected $startDate;
    protected $endDate;
    protected $date;
    protected $products;
    protected $shiftSaleByNozzleId = [];
    protected $shiftSaleByTankId = [];
    protected $fuelAdjustment;
    protected $tankRefill;
    protected $payOrder;

    public function stockSummary(array $initialData)
    {
        $this->sessionUser = SessionUser::getUser();
        $this->startDate = Carbon::parse($initialData['date'], SessionUser::TIMEZONE)->startOfDay();
        $this->endDate = Carbon::parse($initialData['date'], SessionUser::TIMEZONE)->endOfDay();
        $this->date = $initialData['date'];
        $this->fetchProducts()
            ->fetchShiftSale()
            ->getFuelAdjustment()
            ->getTankRefill()
            ->getPayOrder()
            ->calculateSummary();
        return $this->products;
    }

    protected function fetchProducts()
    {
        $this->products = Product::select('products.id', 'products.name as product_name', 'product_types.tank', 'products.selling_price')
            ->leftJoin('product_types', 'product_types.id', '=', 'products.type_id')
            ->where('products.client_company_id', $this->sessionUser['client_company_id'])
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

        return $this;
    }

    protected function fetchShiftSale()
    {
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
            ->where('shift_total.client_company_id', $this->sessionUser['client_company_id'])
            ->whereBetween('start_date', [$this->startDate, $this->endDate])
            ->where('shift_total.status', FuelMatixStatus::END)
            ->whereNotNull('shift_summary.nozzle_id')
            ->get()
            ->toArray();

        foreach ($shiftSale as $sale) {
            $this->processShiftSaleData($sale);
        }

        return $this;
    }

    protected function processShiftSaleData($sale)
    {
        $tankId = $sale['tank_id'];
        $nozzleId = $sale['nozzle_id'];

        if (!isset($this->shiftSaleByNozzleId[$nozzleId])) {
            $this->shiftSaleByNozzleId[$nozzleId] = [
                'start_reading' => $sale['start_reading'],
                'end_reading' => $sale['end_reading']
            ];
        } else {
            $this->shiftSaleByNozzleId[$nozzleId]['end_reading'] = $sale['end_reading'];
        }

        if (!isset($this->shiftSaleByTankId[$tankId])) {
            $this->shiftSaleByTankId[$tankId] = [
                'tank_start_reading' => $sale['tank_start_reading'],
                'tank_end_reading' => $sale['tank_end_reading']
            ];
        } else {
            $this->shiftSaleByTankId[$tankId]['tank_end_reading'] = $sale['tank_end_reading'];
        }
    }

    protected function getFuelAdjustment()
    {
        $productIds = array_column($this->products, 'id');

        $this->fuelAdjustment = FuelAdjustment::select('fuel_adjustment.product_id', DB::raw('SUM(fuel_adjustment_data.quantity) as total_quantity'))
            ->leftJoin('fuel_adjustment_data', 'fuel_adjustment_data.fuel_adjustment_id', '=', 'fuel_adjustment.id')
            ->where('fuel_adjustment.client_company_id', $this->sessionUser['client_company_id'])
            ->whereNotNull('nozzle_id')
            ->whereIn('product_id', $productIds)
            ->whereBetween('fuel_adjustment.date', [$this->startDate, $this->endDate])
            ->groupBy('fuel_adjustment.product_id')
            ->get()
            ->keyBy('product_id')
            ->toArray();

        return $this;
    }

    protected function getTankRefill()
    {
        $this->tankRefill = TankRefillTotal::select(DB::raw('SUM(tank_refill.dip_sale) as volume'), 'tank_refill.tank_id')
            ->leftJoin('tank_refill', 'tank_refill.refill_id', '=', 'tank_refill_total.id')
            ->where('tank_refill_total.client_company_id', $this->sessionUser['client_company_id'])
            ->where('tank_refill_total.date', $this->date)
            ->get()
            ->keyBy('tank_id')
            ->toArray();

        return $this;
    }

    protected function getPayOrder()
    {
        $productIds = array_column($this->products, 'id');

        $this->payOrder = PayOrderData::select(DB::raw('SUM(pay_order_data.quantity) as quantity'), 'pay_order_data.product_id')
            ->leftJoin('pay_order', 'pay_order.id', '=', 'pay_order_data.pay_order_id')
            ->where('status', FuelMatixStatus::PENDING)
            ->where('pay_order.client_company_id', $this->sessionUser['client_company_id'])
            ->whereIn('product_id', $productIds)
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id')
            ->toArray();

        return $this;
    }

    protected function calculateSummary()
    {
        foreach ($this->products as &$product) {
            $totalSale = 0;
            $totalEndReading = 0;
            $totalRefill = 0;
            $totalAmount = 0;

            foreach ($product['tanks'] as &$tank) {
                foreach ($tank['dispensers'] as &$dispenser) {
                    foreach ($dispenser['nozzle'] as &$nozzle) {
                        // Calculate and format sales data for each nozzle
                        $this->calculateNozzleSales($product, $nozzle, $totalSale, $totalAmount);
                    }
                }
                // Calculate and format tank data
                $this->calculateTankData($tank, $totalEndReading, $totalRefill);
            }

            // Calculate and format product data
            $this->calculateProductData($product, $totalSale, $totalEndReading, $totalRefill, $totalAmount);
        }

        return $this->products;
    }

    protected function calculateNozzleSales(&$product, &$nozzle, &$totalSale, &$totalAmount)
    {
        $nozzle['start_reading'] = $this->shiftSaleByNozzleId[$nozzle['id']]['start_reading'] ?? 0;
        $nozzle['end_reading'] = $this->shiftSaleByNozzleId[$nozzle['id']]['end_reading'] ?? 0;
        $nozzle['sale'] = $nozzle['end_reading'] - $nozzle['start_reading'];
        $nozzle['start_reading_format'] = $nozzle['start_reading'] > 0 ?  number_format($nozzle['start_reading'], 2) : '-';
        $nozzle['end_reading_format'] = $nozzle['end_reading'] > 0 ? number_format($nozzle['end_reading'], 2) : '-';
        $nozzle['sale_format'] = $nozzle['sale'] > 0 ? number_format($nozzle['sale'], 2) : '-';
        $totalSale += $nozzle['sale'];
        $nozzle['unit_price_format'] = number_format($product['selling_price'], 2);
        $nozzle['amount'] = $nozzle['sale'] * $product['selling_price'];
        $nozzle['amount_format'] = $nozzle['amount'] > 0 ? number_format($nozzle['amount'], 2) : '-';
        $totalAmount += $nozzle['amount'];
    }

    protected function calculateTankData(&$tank, &$totalEndReading, &$totalRefill)
    {
        $tank['end_reading'] = $this->shiftSaleByTankId[$tank['id']]['tank_end_reading'] ?? 0;
        $tank['end_reading_format'] = $tank['end_reading'] > 0 ? number_format($tank['end_reading'], 2) : '-';
        $tank['refill_volume'] = $this->tankRefill[$tank['id']]['volume'] ?? 0;
        $tank['refill_volume_format'] = $tank['refill_volume'] > 0 ? number_format($tank['refill_volume'], 2) : '-';
        $totalEndReading += $tank['end_reading'];
        $totalRefill += $tank['refill_volume'];
    }

    protected function calculateProductData(&$product, $totalSale, $totalEndReading, $totalRefill, $totalAmount)
    {
        $product['total'] = $totalSale > 0 ? number_format($totalSale, 2) : '-';
        $product['subtotal_amount'] = $totalAmount > 0 ? number_format($totalAmount, 2) : '-';
        $adjustment = $this->fuelAdjustment[$product['id']]['total_quantity'] ?? 0;
        $product['adjustment'] = $adjustment > 0 ? number_format($adjustment, 2) : '-' ;
        $product['adjustment_amount'] = $adjustment > 0 ? number_format($adjustment * $product['selling_price'], 2) : '-' ;

        $totalQuantity = $totalSale - $adjustment;
        $product['total_sale'] = $totalQuantity > 0 ? number_format($totalQuantity, 2) : '-';
        $product['total_amount'] = ($totalAmount - ($adjustment * $product['selling_price'])) > 0 ? number_format($totalAmount - ($adjustment * $product['selling_price']), 2) : '-';
        $product['end_reading'] = $totalEndReading > 0 ? number_format($totalEndReading, 2) : '-';
        $product['tank_refill'] = $totalRefill > 0 ? number_format($totalRefill, 2) : '-';
        $totalByProduct = $totalEndReading + $totalRefill;
        $product['total_by_product'] = $totalByProduct > 0 ? number_format($totalByProduct, 2) : '-';
        $payOrderQuantity = $this->payOrder[$product['id']]['quantity'] ?? 0;
        $product['pay_order'] = $payOrderQuantity > 0 ? number_format($payOrderQuantity, 2) : '-';
        $product['closing_balance'] = ($totalEndReading + $payOrderQuantity) > 0 ? number_format($totalEndReading + $payOrderQuantity, 2) : '-';
        $gainLoss = $totalByProduct != 0 && $totalQuantity != 0 ? ($totalByProduct - $totalQuantity) / $totalQuantity : 0 ;
        $product['gain_loss'] = $gainLoss;
        $product['gain_loss_format'] = $gainLoss > 0 ? number_format(abs($gainLoss), 2) .'%' : '-';
    }
}
