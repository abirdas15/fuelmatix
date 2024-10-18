<?php

namespace App\Repository;

use App\Common\FuelMatixDateTimeFormat;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;

class PurchaseRepository
{
    /**
     * @param Purchase $purchaseModel
     * @param array $purchaseData
     * @return Purchase|false
     */
    public static function save(Purchase $purchaseModel, array $purchaseData)
    {
        $purchaseModel->date = $purchaseData['date'];
        $purchaseModel->vendor_id = $purchaseData['vendor_id'];
        $purchaseModel->total_amount = $purchaseData['total_amount'];
        $purchaseModel->status = $purchaseData['status'];
        $purchaseModel->bill_id = $purchaseData['bill_id'];
        $purchaseModel->client_company_id = $purchaseData['client_company_id'];
        if (!$purchaseModel->save()) {
            return false;
        }
        return $purchaseModel;
    }

    /**
     * @param PurchaseItem $purchaseItemModel
     * @param array $purchaseItemData
     * @return PurchaseItem|false
     */
    public static function saveItem(PurchaseItem $purchaseItemModel, array $purchaseItemData)
    {
        $purchaseItemModel->purchase_id = $purchaseItemData['purchase_id'];
        $purchaseItemModel->product_id = $purchaseItemData['product_id'];
        $purchaseItemModel->unit_price = $purchaseItemData['unit_price'];
        $purchaseItemModel->quantity = $purchaseItemData['quantity'];
        $purchaseItemModel->total = $purchaseItemData['total'];
        if (!$purchaseItemModel->save()) {
            return false;
        }
        return $purchaseItemModel;
    }

    /**
     * @param array $paginatedFilter
     * @return mixed
     */
    public static function list(array $paginatedFilter)
    {
        $sessionUser = SessionUser::getUser();
        $queryResult = Purchase::select('purchase.id', 'purchase.date', 'purchase.bill_id', 'categories.name as vendor_name', 'purchase.total_amount', 'purchase.paid', DB::raw('(purchase.total_amount - purchase.paid) as due'))
            ->leftJoin('categories', 'categories.id', '=', 'purchase.vendor_id')
            ->with('purchase_item')
            ->where('purchase.client_company_id', $sessionUser['client_company_id']);
        if (!empty($paginatedFilter['start_date']) && !empty($paginatedFilter['end_date'])) {
            $queryResult->where(function($q) use ($paginatedFilter) {
                $q->where(DB::raw('date'), '>=', $paginatedFilter['start_date']);
                $q->where(DB::raw('date'), '<=', $paginatedFilter['end_date']);
            });
        }
        if (!empty($paginatedFilter['vendor_id'])) {
            $queryResult->where(function($q) use ($paginatedFilter) {
                $q->where('vendor_id', $paginatedFilter['vendor_id']);
            });
        }
        $queryResult = $queryResult->orderBy($paginatedFilter['order_by'], $paginatedFilter['order_mode'])
            ->paginate($paginatedFilter['limit']);
        foreach ($queryResult as &$data) {
            $data['total_amount'] = number_format($data['total_amount'], $sessionUser['currency_precision']);
            $data['paid'] = number_format($data['paid'], $sessionUser['currency_precision']);
            $data['due'] = number_format($data['due'], $sessionUser['currency_precision']);
            foreach ($data['purchase_item'] as &$purchase_item) {
                $purchase_item['unit_price'] = number_format($purchase_item['unit_price'], $sessionUser['currency_precision']);
                $purchase_item['quantity'] = number_format($purchase_item['quantity'], $sessionUser['quantity_precision']);
                $purchase_item['total'] = number_format($purchase_item['total'], $sessionUser['currency_precision']);
            }
            $data['date'] = Helpers::formatDate($data['date'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME);
        }
        return$queryResult;
    }
}
