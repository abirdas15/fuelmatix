<?php

namespace App\Repository;

use App\Common\Module;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Validator;

class SaleRepository
{
    /**
     * @param array $data
     * @return true
     */
    public static function saveAdvancePayment(array $data): bool
    {
        $transactionData['linked_id'] = $data['company_id'];
        $transactionData['transaction'] = [
            ['date' => date('Y-m-d'), 'account_id' => $data['driver_id'], 'debit_amount' => $data['amount'], 'credit_amount' => 0],
        ];
        TransactionController::saveTransaction($transactionData);
        return true;
    }
    /**
     * @param $inputData
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public static function validateAdvancePay($inputData): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($inputData, [
            'payment_method' => 'required',
            'payment_category_id' => 'required_unless:payment_method,cash',
            'voucher_number' => 'required_if:payment_method,company|integer',
            'driver_sale.driver_id' => 'required'
        ],[
            'voucher_number.required_if' => 'The voucher number filed is required.',
            'driver_sale.driver_id.required' => 'The driver field is required.'
        ]);
    }

    /**
     * @param $inputData
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public static function validateSale($inputData): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($inputData, [
            'payment_method' => 'required',
            'products' => 'required|array',
            'payment_category_id' => 'required_unless:payment_method,cash',
            'voucher_number' => 'required_if:payment_method,company|integer',
            'products.*.shift_sale_id' => 'required',
            'products.*.product_id' => 'required',
            'products.*.income_category_id' => 'required',
            'products.*.stock_category_id' => 'required',
            'products.*.expense_category_id' => 'required',
            'products.*.quantity' => 'required',
            'products.*.price' => 'required',
            'products.*.subtotal' => 'required',
        ],[
            'voucher_number.required_if' => 'The voucher number filed is required',
            'products.*.shift_sale_id.required' => 'Shift sale is not started. Please start shift sale first.',
            'products.*.product_id.required' => 'The product field is required.',
            'products.*.income_category_id.required' => 'Product is not a income category. Please update product first.',
            'products.*.stock_category_id.required' => 'Product is not a stock category. Please update product first.',
            'products.*.expense_category_id.required' => 'Product is not a expense category. Please update product first.',
            'products.*.price.required' => 'The price field is required.',
            'products.*.subtotal.required' => 'The subtotal field is required.',
        ]);
    }
}
