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
    public static function validateAdvancePayment($inputData): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($inputData, [
            'advance_amount' => 'required|numeric',
            'company_id' => 'required_unless:payment_method,cash',
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
            'company_id' => 'required_unless:payment_method,cash',
            'voucher_number' => empty($inputData['advance_sale']) ? 'required_if:payment_method,company|integer' : 'nullable',
            'driver_sale.driver_id' => 'required_if:is_driver_sale,true|integer',
            'driver_sale.price' => 'required_if:is_driver_sale,true|numeric',
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
            'driver_sale.driver_id.required_if' => 'The driver filed is required',
            'driver_sale.price.required_if' => 'The amount filed is required',
            'products.*.shift_sale_id.required' => 'Shift sale is not started. Please start shift sale first.',
            'products.*.product_id.required' => 'The product field is required.',
            'products.*.income_category_id.required' => 'Product is not a income category. Please update product first.',
            'products.*.stock_category_id.required' => 'Product is not a stock category. Please update product first.',
            'products.*.expense_category_id.required' => 'Product is not a expense category. Please update product first.',
            'products.*.price.required' => 'The price field is required.',
            'products.*.subtotal.required' => 'The subtotal field is required.',
        ]);
    }
    /**
     * @param array $data
     * */
    public static function advancePayment($data)
    {

    }
}
