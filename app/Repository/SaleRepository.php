<?php

namespace App\Repository;

use App\Common\FuelMatixDateTimeFormat;
use App\Common\Module;
use App\Common\PaymentMethod;
use App\Helpers\SessionUser;
use App\Http\Controllers\TransactionController;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class SaleRepository
{
    /**
     * @param array $filter
     * @param array $paginateData
     * @return mixed
     */
    public static function saleList(array $filter, array $paginateData)
    {
        $sessionUser = SessionUser::getUser();
        $result = Sale::select('sale.id', 'sale.invoice_number', 'sale.date', 'sale.total_amount', 'sale.payment_method', 'users.name as user_name', 'sale.voucher_number', 'car.car_number', 'categories.name as company_name')
            ->with(['sale_data' => function($q) {
                $q->select('sale_data.id', 'sale_data.sale_id', 'sale_data.product_id', 'sale_data.quantity', 'products.name as product_name')
                    ->join('products', 'products.id', '=', 'sale_data.product_id');
            }])
            ->leftJoin('car', 'car.id', '=', 'sale.car_id')
            ->leftJoin('categories', 'categories.id', '=', 'sale.payment_category_id')
            ->leftJoin('users', 'users.id', '=', 'sale.user_id')
            ->where('sale.client_company_id', $sessionUser['client_company_id']);
        if (!empty($filter['keyword'])) {
            $result->where(function ($q) use ($filter) {
                $q->where('invoice_number', 'LIKE', '%'.$filter['keyword'].'%');
            });
        }
        if (!empty($filter['start_date']) && !empty($filter['end_date'])) {
            $startDate = Carbon::parse($filter['start_date'])->startOfDay();
            $endDate = Carbon::parse($filter['end_date'])->endOfDay();
            $result->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('sale.date', [$startDate, $endDate]);
            });
        }
        if (!empty($filter['ids'])) {
            $result->where(function($q) use ($filter) {
                $q->whereIn('sale.id', $filter['ids']);
            });
        }
        $result = $result->orderBy($paginateData['order_by'], $paginateData['order_mode'])
            ->paginate($paginateData['limit']);
        foreach ($result as &$data) {
            $data['date'] = date(FuelMatixDateTimeFormat::STANDARD_DATE_TIME, strtotime($data['date']));
            if ($data['payment_method'] == PaymentMethod::CASH || $data['payment_method'] == PaymentMethod::CARD) {
                $data['company_name'] = null;
            }
            $totalQuantity = 0;
            $productArray = [];
            foreach ($data['sale_data'] as $sale_data) {
                $productArray[] = $sale_data['product_name'];
                $totalQuantity += $sale_data['quantity'];
            }
            $data['quantity'] = number_format($totalQuantity, $sessionUser['quantity_precision']);
            $data['product_name'] = implode(', ', $productArray);
            $data['total_amount'] = number_format($data['total_amount'], $sessionUser['currency_precision']);
        }
        return $result;
    }
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
