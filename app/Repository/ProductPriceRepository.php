<?php

namespace App\Repository;

use App\Helpers\SessionUser;
use App\Models\Product;
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

    /**
     * @param int $productId
     * @param float $quantity
     * @return float|int|mixed
     */
    public static function updateAndGetProductBuyingPrice(int $productId, float $quantity)
    {

        $sessionUser = SessionUser::getUser();
        $productPrices = ProductPrice::where('client_company_id', $sessionUser['client_company_id'])->where('product_id', $productId)->where('stock_quantity', '>', 0)->get()->toArray();
        $buyingPrice = 0;
        if (empty($productPrices)) {
            $productModel = Product::where('id', $productId)->first();
            if (!empty($productModel['buying_price'])) {
                $buyingPrice = $productModel['buying_price'] * $quantity;
            }
        }
        foreach ($productPrices as $key => $productPrice) {
            if ($productPrice['stock_quantity'] > $quantity) {
                $productPrice['stock_quantity'] = $productPrice['stock_quantity'] - $quantity;
                $buyingPrice += $productPrice['unit_price'] * $quantity;
                $productPrice->save();
                break;
            } else {
                $quantity = $quantity - $productPrice['stock_quantity'];
                $buyingPrice += $productPrice['unit_price'] * $productPrice['stock_quantity'];
                $productPrice['stock_quantity'] = 0;
                $productPrice->save();
            }
        }
        return $buyingPrice;
    }
}
