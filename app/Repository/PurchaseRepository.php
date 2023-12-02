<?php

namespace App\Repository;

use App\Models\Purchase;
use App\Models\PurchaseItem;

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
}
