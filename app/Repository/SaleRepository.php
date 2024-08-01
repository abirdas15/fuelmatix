<?php

namespace App\Repository;

use App\Common\Module;
use App\Common\PaymentMethod;
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
     * Validate the advance payment input data.
     *
     * This method validates the given input data against the predefined rules for advance payments.
     *
     * @param array $inputData The input data to be validated.
     * @return \Illuminate\Contracts\Validation\Validator The validator instance containing the validation rules and messages.
     */
    public static function validateAdvancePayment(array $inputData): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($inputData, [
            'date' => 'required|date',
            'advance_amount' => 'required|numeric',
            'company_id' => 'required_unless:payment_method,cash',
            'voucher_number' => 'required_if:payment_method,company|integer',
            'driver_sale.driver_id' => 'required'
        ],[
            'voucher_number.required_if' => 'The voucher number field is required.',
            'driver_sale.driver_id.required' => 'The driver field is required.'
        ]);
    }


    /**
     * Validate the sale input data.
     *
     * This method validates the given input data against the predefined rules for sales.
     *
     * @param array $inputData The input data to be validated.
     * @return \Illuminate\Contracts\Validation\Validator The validator instance containing the validation rules and messages.
     */
    public static function validateSale(array $inputData): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($inputData, [
            'date' => 'required|date',
            'payment_method' => 'required',
            'products' => 'required|array',
            'company_id' => $inputData['payment_method'] == PaymentMethod::COMPANY ? 'required' : 'nullable',
            //'voucher_number' => empty($inputData['advance_sale']) ? 'required_if:payment_method,company|integer' : 'nullable',
            'driver_sale.driver_id' => 'required_if:is_driver_sale,true|integer',
            'driver_sale.price' => 'required_if:is_driver_sale,true|numeric',
            'products.*.shift_sale_id' => 'required_if:shift_sale,1',
            'products.*.product_id' => 'required',
            'products.*.income_category_id' => 'required',
            'products.*.stock_category_id' => 'required',
            'products.*.expense_category_id' => 'required',
            'products.*.quantity' => 'required',
            'products.*.price' => 'required',
            'products.*.subtotal' => 'required',
        ],[
            'voucher_number.required_if' => 'The voucher number field is required.',
            'driver_sale.driver_id.required_if' => 'The driver field is required.',
            'driver_sale.price.required_if' => 'The amount field is required.',
            'products.*.shift_sale_id.required_if' => 'Shift sale is not started. Please start shift sale first.',
            'products.*.product_id.required' => 'The product field is required.',
            'products.*.income_category_id.required' => 'Product is not an income category. Please update the product first.',
            'products.*.stock_category_id.required' => 'Product is not a stock category. Please update the product first.',
            'products.*.expense_category_id.required' => 'Product is not an expense category. Please update the product first.',
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
