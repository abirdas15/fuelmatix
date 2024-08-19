<?php

namespace App\Http\Controllers;

use App\Common\FuelMatixCategoryType;
use App\Common\FuelMatixDateTimeFormat;
use App\Common\Module;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\BulkSale;
use App\Models\BulkSaleItem;
use App\Models\Category;
use App\Models\PayOrderData;
use App\Repository\TransactionRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BulkSaleController extends Controller
{
    /**
     * Handles saving a bulk sale and its associated products.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'pay_order_id' => 'required|integer',
            'company_id' => 'required|integer',
            'products' => 'required|array',
            'products.*.id' => 'required|integer',
            'products.*.sale_quantity' => 'nullable|integer',
        ]);

        // If validation fails, return the errors
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->messages(),
            ]);
        }

        // Begin a database transaction
        DB::transaction(function() use ($request) {
            // Retrieve the session user
            $sessionUser = SessionUser::getUser();

            // Create a new BulkSale instance and set its properties
            $bulkSale = new BulkSale();
            $bulkSale->date = Carbon::now(SessionUser::TIMEZONE); // Set the current date with the session user's timezone
            $bulkSale->pay_order_id = $request->input('pay_order_id'); // Set the pay order ID
            $bulkSale->company_id = $request->input('company_id');     // Set the company ID
            $bulkSale->client_company_id = $sessionUser['client_company_id']; // Set the client company ID from the session

            // If unable to save the bulk sale, return an error response
            if (!$bulkSale->save()) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Cannot save bulk sale',
                ]);
            }

            // Iterate over each product in the request
            foreach ($request->input('products') as $product) {
                // If sale_quantity is not empty, proceed to save the bulk sale item
                if (!empty($product['sale_quantity'])) {
                    // Create a new BulkSaleItem instance and set its properties
                    $bulkSaleItem = new BulkSaleItem();
                    $bulkSaleItem->bulk_sale_id = $bulkSale->id; // Associate it with the bulk sale
                    $bulkSaleItem->product_id = $product['product_id']; // Set the product ID
                    $bulkSaleItem->selling_price = $product['selling_price']; // Set the selling price
                    $bulkSaleItem->quantity = $product['sale_quantity']; // Set the quantity sold
                    $bulkSaleItem->save(); // Save the bulk sale item

                    // Update the PayOrderData by reducing the quantity
                    $payOrder = PayOrderData::where('id', $product['id'])->first();
                    $payOrder->quantity = $payOrder->quantity - $product['sale_quantity'];
                    $payOrder->save(); // Save the updated pay order

                    // Calculate the total amount for the transaction
                    $amount = $product['selling_price'] * $product['sale_quantity'];

                    // Retrieve the income category associated with the product
                    $incomeCategory = Category::where('client_company_id', $sessionUser['client_company_id'])
                        ->where('type', FuelMatixCategoryType::INCOME)
                        ->where('module', Module::PRODUCT)
                        ->where('module_id', $product['product_id'])
                        ->first();

                    // If the income category is not found, skip this iteration
                    if (!$incomeCategory instanceof Category) {
                        continue;
                    }

                    // Prepare the transaction data for debit and credit entries
                    $transactionData = [
                        ['date' => date('Y-m-d'), 'account_id' => $request->input('company_id'), 'debit_amount' => $amount, 'credit_amount' => 0, 'module' => Module::BULK_SALE, 'module_id' => $bulkSale->id],
                        ['date' => date('Y-m-d'), 'account_id' => $incomeCategory->id, 'debit_amount' => 0, 'credit_amount' => $amount, 'module' => Module::BULK_SALE, 'module_id' => $bulkSale->id],
                    ];

                    // Save the transaction using the repository
                    TransactionRepository::saveTransaction($transactionData);
                }
            }
        });

        // If everything is successful, return a success message
        return response()->json([
            'status' => 200,
            'message' => 'Successfully saved bulk sale.'
        ]);
    }

    /**
     * Retrieves a paginated list of bulk sales with aggregated data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        // Get the limit from the request or default to 10
        $limit = $request->input('limit', 10);

        // Query to select bulk sale details with aggregation
        $result = BulkSale::select(
            'bulk_sales.id',
            'bulk_sales.date',
            DB::raw('SUM(bulk_sale_items.quantity) as quantity'),
            DB::raw('SUM(bulk_sale_items.quantity * bulk_sale_items.selling_price) as amount'),
            'categories.name as company_name',
            'pay_order.number as pay_order_id'
        )
            ->leftJoin('bulk_sale_items', 'bulk_sale_items.bulk_sale_id', '=', 'bulk_sales.id')
            ->leftJoin('categories', 'categories.id', '=', 'bulk_sales.company_id')
            ->leftJoin('pay_order', 'pay_order.id', '=', 'bulk_sales.pay_order_id')
            ->groupBy('bulk_sales.id', 'categories.name', 'pay_order.number')
            ->paginate($limit); // Paginate the results

        // Format the date for each bulk sale in the result
        foreach ($result as &$data) {
            $data['date'] = Helpers::formatDate($data['date'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME);
        }

        // Return the results as a JSON response
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }

}
