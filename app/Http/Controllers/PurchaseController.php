<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\Module;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\ProductPrice;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Repository\ProductPriceRepository;
use App\Repository\PurchaseRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'date' => 'required|date',
            'vendor_id' => 'required|integer',
            'purchase_item.*' => 'required|array',
            'purchase_item.*.product_id' => 'required',
            'purchase_item.*.unit_price' => 'required',
            'purchase_item.*.quantity' => 'required',
            'purchase_item.*.total' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $vendor = Category::where('id', $requestData['vendor_id'])->first();
        if (!$vendor instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find vendor.']);
        }
        $sessionUser = SessionUser::getUser();
        $category = Category::where('slug', strtolower(AccountCategory::STOCK_IN_HAND))->where('client_company_id', $sessionUser['client_company_id'])->first();
        if (!$category instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find stock category.']);
        }
        $purchase = PurchaseRepository::save(new Purchase(), [
            'date' => Carbon::parse($requestData['date'].' '.date('H:i:s'))->format('Y-m-d H:i:s'),
            'vendor_id' => $requestData['vendor_id'],
            'total_amount' => array_sum(array_column($requestData['purchase_item'], 'total')),
            'status' => 'due',
            'bill_id' =>  $requestData['bill_id'] ?? null,
            'client_company_id' => $sessionUser['client_company_id']
        ]);
        if (!$purchase instanceof Purchase) {
            return response()->json(['status' => 400, 'message' => 'Cannot save purchase.']);
        }
        foreach ($requestData['purchase_item'] as $purchase_item) {
            $purchaseItem = PurchaseRepository::saveItem(new PurchaseItem(), [
                'purchase_id' => $purchase['id'],
                'product_id' => $purchase_item['product_id'],
                'unit_price' => $purchase_item['unit_price'],
                'quantity' => $purchase_item['quantity'],
                'total' => $purchase_item['total'],
            ]);
            if (!$purchaseItem instanceof PurchaseItem) {
                return response()->json(['status' => 400, 'message' => 'Cannot save purchase item']);
            }
            $stockCategory = Category::where('parent_category', $category['id'])
                ->where('module', 'product')
                ->where('module_id', $purchase_item['product_id'])
                ->where('client_company_id', $sessionUser['client_company_id'])
                ->first();
            if (!$stockCategory instanceof Category) {
                return response()->json(['status' => 500, 'error' => 'Cannot find account stock category.']);
            }
            $transactionData['linked_id'] = $stockCategory['id'];
            $transactionData['transaction'] = [
                ['date' => $requestData['date'], 'account_id' => $vendor['id'], 'debit_amount' => $purchase_item['total'], 'credit_amount' => 0, 'module' => Module::PURCHASE, 'module_id' => $purchase->id]
            ];
            TransactionController::saveTransaction($transactionData);
            ProductPriceRepository::save(new ProductPrice(), [
                'date' => $requestData['date'],
                'product_id' => $purchase_item['product_id'],
                'quantity' => $purchase_item['quantity'],
                'stock_quantity' => $purchase_item['quantity'],
                'price' => $purchase_item['total'],
                'unit_price' => $purchase_item['unit_price'],
                'module' => Module::PURCHASE,
                'module_id' => $purchase['id'],
                'client_company_id' => $sessionUser['client_company_id']
            ]);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully saved purchase.']);
    }
}
