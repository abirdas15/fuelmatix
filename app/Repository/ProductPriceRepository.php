<?php

namespace App\Repository;

use App\Models\ProductPrice;

class ProductPriceRepository
{
    /**
     * @param ProductPrice $productPriceModel
     * @param array $data
     * @return ProductPrice|false
     */
    public static function save(ProductPrice $productPriceModel, array $data)
    {
        $productPriceModel->date = $data['date'];
        $productPriceModel->product_id = $data['product_id'];
        $productPriceModel->quantity = $data['quantity'];
        $productPriceModel->stock_quantity = $data['stock_quantity'];
        $productPriceModel->price = $data['price'];
        $productPriceModel->unit_price = $data['unit_price'];
        $productPriceModel->module = $data['module'];
        $productPriceModel->module_id = $data['module_id'];
        $productPriceModel->client_company_id = $data['client_company_id'];
        if (!$productPriceModel->save()) {
            return false;
        }
        return $productPriceModel;
    }
}
