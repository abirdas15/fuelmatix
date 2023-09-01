<?php

namespace App\Repository;

use App\Common\AccountCategory;
use App\Common\Module;
use App\Helpers\SessionUser;
use App\Http\Controllers\TransactionController;
use App\Models\Category;

class InvoiceRepository
{
    /**
     * @param array $data
     */
    public static function advancePayment(array $data)
    {
        $sessionUser = SessionUser::getUser();
        $advancePaymentLiabilities =  Category::where('category', AccountCategory::ADVANCE_PAYABLE)->where('client_company_id', $sessionUser['id'])->first();
        if (!$advancePaymentLiabilities instanceof Category) {
            return response()->json(['status' => 500, 'message' => 'Cannot find advance payable category.']);
        }
        $advancePayable = Category::where('module', Module::ADVANCE_PAYABLE)->where('module_id', $data['company_id'])->first();
        if (!$advancePayable instanceof Category) {
            $advancePayableData = [
                'category' => $data['category'],
                'parent_category' => $advancePaymentLiabilities['id'],
                'type' => 'liabilities',
                'module' => Module::ADVANCE_PAYABLE,
                'module_id' => $data['company_id']
            ];
            $advancePayable = CategoryRepository::save($advancePayableData);
            if (!$advancePayable instanceof Category) {
                return response()->json(['status' => 500, 'message' => 'Cannot save advance payable.']);
            }
        }
        $transactionData['linked_id'] = $data['payment_category_id'];
        $transactionData['transaction'] = [
            ['date' => date('Y-m-d'), 'account_id' => $advancePayable['id'], 'debit_amount' => $data['amount'], 'credit_amount' => 0]
        ];
        TransactionController::saveTransaction($transactionData);
    }
}
