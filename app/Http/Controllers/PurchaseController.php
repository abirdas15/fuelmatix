<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\Module;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Repository\ProductPriceRepository;
use App\Repository\PurchaseRepository;
use App\Repository\TransactionRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat\Wizard\Currency;

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
            $product = Product::where('id', $purchase_item['product_id'])->first();
            if (!$product instanceof Product) {
                return response()->json(['status' => 400, 'message' => 'Cannot find product.']);
            }
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
            $transactionData = [
                ['date' => $requestData['date'], 'account_id' => $stockCategory['id'], 'debit_amount' => $purchase_item['total'], 'credit_amount' => 0, 'module' => Module::PURCHASE, 'module_id' => $purchase->id],
                ['date' => $requestData['date'], 'account_id' => $vendor['id'], 'debit_amount' => 0, 'credit_amount' => $purchase_item['total'], 'module' => Module::PURCHASE, 'module_id' => $purchase->id]
            ];
            TransactionRepository::saveTransaction($transactionData);
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
            $currentStock = !empty($product['current_stock']) ? $product['current_stock'] : 0;
            $product->updateQuantity($currentStock + $purchase_item['quantity']);
        }
        return response()->json(['status' => 200, 'message' => 'Successfully saved purchase.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $paginatedFilter = [
            'limit' => $requestData['limit'] ?? 10,
            'page' => $requestData['page'] ?? 1,
            'order_by' => $requestData['order_by'] ?? 'id',
            'order_mode' => $requestData['order_mode'] ?? 'desc',
            'start_date' => $requestData['start_date'] ?? '',
            'end_date' => $requestData['end_date'] ?? '',
            'vendor_id' => $requestData['vendor_id'] ?? ''
        ];
        $response = PurchaseRepository::list($paginatedFilter);
        return response()->json(['status' => 200, 'data' => $response]);
    }
    /**
     * Handles the payment for a purchase.
     *
     * @param Request $request The HTTP request containing payment details.
     * @return JsonResponse The response indicating the success or failure of the payment process.
     */
    public function pay(Request $request): JsonResponse
    {
        // Validate the incoming request data to ensure required fields are present and correctly formatted
        $validator = Validator::make($request->all(), [
            'purchase_id' => 'required|integer',
            'payment_id' => 'required|integer',
            'amount' => 'required|numeric|min:0'
        ]);

        // If validation fails, return a JSON response with error details
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Retrieve the purchase record by ID and check if it exists
        $purchase = Purchase::find($request->input('purchase_id'));
        if (!$purchase) {
            // If the purchase record is not found, return an error response
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find purchase.'
            ]);
        }

        // Retrieve the payment category by ID and check if it exists
        $category = Category::find($request->input('payment_id'));
        if (!$category) {
            // If the payment category is not found, return an error response
            return response()->json([
                'status' => 500,
                'errors' => ['payment_id' => ['Cannot find payment category.']]
            ]);
        }

        // Check if the category has enough available balance for the payment amount
        if (!$category->checkAvailableBalance($request->input('amount'))) {
            // If there is not enough balance, return an error response
            return response()->json([
                'status' => 500,
                'errors' => ['payment_id' => ['Not enough balance in '.$category->name.'.']]
            ]);
        }

        // Update the paid amount and status of the purchase
        $purchase->paid += $request->input('amount');
        $purchase->status = $purchase->paid == 0 ? 'paid' : 'partially paid';

        // Attempt to save the purchase record; if it fails, return an error response
        if (!$purchase->save()) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot pay amount.'
            ]);
        }

        // Prepare the transaction data for recording the payment
        $transactionData = [
            [
                'date' => date('Y-m-d'),
                'account_id' => $purchase->vendor_id,
                'debit_amount' => $request->input('amount'),
                'credit_amount' => 0,
                'module' => Module::PURCHASE_PAYMENT,
                'module_id' => $purchase->id,
            ],
            [
                'date' => date('Y-m-d'),
                'account_id' => $request->input('payment_id'),
                'debit_amount' => 0,
                'credit_amount' => $request->input('amount'),
                'module' => Module::PURCHASE_PAYMENT,
                'module_id' => $purchase->id,
            ]
        ];

        // Save the transaction records to the database
        TransactionRepository::saveTransaction($transactionData);

        // Return a success response indicating the payment was processed successfully
        return response()->json([
            'status' => 200,
            'message' => 'Successfully paid amount.'
        ]);
    }

}
