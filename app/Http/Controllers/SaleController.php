<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\FuelMatixDateTimeFormat;
use App\Common\FuelMatixStatus;
use App\Common\Module;
use App\Common\PaymentMethod;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Car;
use App\Models\Category;
use App\Models\ClientCompany;
use App\Models\Driver;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Sale;
use App\Models\SaleData;
use App\Models\ShiftTotal;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Voucher;
use App\Repository\DriverRepository;
use App\Repository\ProductPriceRepository;
use App\Repository\SaleRepository;
use App\Repository\TransactionRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SaleController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        $requestData = $request->all();
        if (!empty($requestData['advance_pay'])) {
            $validator = SaleRepository::validateAdvancePayment($requestData);
        } else {
            $validator = SaleRepository::validateSale($requestData);
        }
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }

        $sessionUser = SessionUser::getUser();
        $errorsMessage = [];
        $products = $request->input('products');
        foreach ($products as $key => &$eachProduct) {
            $productModel = Product::where('products.id', $eachProduct['product_id'])
                ->leftJoin('product_types', 'product_types.id', '=', 'products.type_id')
                ->first();
            if ($productModel['inventory'] == 1) {
                if ($eachProduct['quantity'] > $productModel['current_stock']) {
                    $errorsMessage[$eachProduct['name']][] = $eachProduct['name'].' has not enough stock. Available quantity: '.$productModel['current_stock'];
                    return response()->json(['status' => 600, 'errors' => $errorsMessage]);
                }
            }
            if (empty($requestData['advance_pay'])) {
                if ($productModel['shift_sale'] == 1) {
                    $shiftSale = ShiftTotal::select('product_id', 'id')
                        ->where('client_company_id', $sessionUser['client_company_id'])->orderBy('id', 'DESC')
                        ->where('status', 'start')
                        ->where('product_id', $eachProduct['product_id'])
                        ->first();
                    if ($shiftSale instanceof ShiftTotal) {
                        $eachProduct['shift_sale_id'] = $shiftSale->id;
                    } else {
                        $errorsMessage[$eachProduct['name']][] = $eachProduct['name'].' shift sale is not started.';
                        return response()->json(['status' => 600, 'errors' => $errorsMessage]);
                    }
                }
            }
        }
        $request->merge(['products' => $products]);
        $requestData = $request->all();

        $driverId = null;
        $voucher = null;
        $voucherNo = null;
        $driverLiabilityId = null;
        $total_amount = array_sum(array_column($requestData['products'], 'subtotal'));
        $payment_category_id = $requestData['company_id'] ?? '';
        $carId = null;

        if (!$sessionUser instanceof User) {
            return response()->json(['status' => 500, 'message' => 'Cannot find session [user].']);
        }

        if ($requestData['payment_method'] == PaymentMethod::COMPANY) {
            $company = Category::find($requestData['company_id']);
            if (!$company instanceof Category) {
                return response()->json(['status' => 400, 'message' => 'Cannot find [company].']);
            }
            $driver = Driver::find($requestData['driver_sale']['driver_id']);
            if (!$driver instanceof Driver) {
                return response()->json(['status' => 400, 'message' => 'Cannot find [driver].']);
            }
            if (empty($requestData['advance_sale']) && !empty($requestData['voucher_number'])) {
                $companyIds = Category::where('client_company_id', $sessionUser['client_company_id'])
                    ->whereJsonContains('category_ids', $requestData['company_id'])
                    ->pluck('id')
                    ->toArray();
                $voucher = Voucher::whereIn('company_id', $companyIds)
                    ->where('voucher_number', $requestData['voucher_number'])
                    ->first();
                if (!$voucher instanceof Voucher) {
                    return response()->json(['status' => 500, 'errors' => ['voucher_number' => ['The voucher number is not valid.']]]);
                }
                if (Carbon::parse($voucher['validity'])->lessThan(Carbon::now())) {
                    return response()->json(['status' => 500, 'errors' => ['voucher_number' => ['The voucher number date is expired.']]]);
                }
                if ($voucher->status == FuelMatixStatus::DONE && !$requestData['voucher_check']) {
                    return response()->json(['status' => 402, 'message' => 'The voucher number is already used.']);
                }
                $voucherNo = $voucher->voucher_number;

                // Check if prefix exists and concatenate it with the voucher number
                if (!empty($voucher->prefix) && !empty($voucher->suffix)) {
                    $voucherNo = $voucher->prefix . '-' . $voucherNo . '-' . $voucher->suffix;
                } elseif (!empty($voucher->prefix)) {
                    $voucherNo = $voucher->prefix . '-' . $voucherNo;
                } elseif (!empty($voucher->suffix)) {
                    $voucherNo = $voucherNo . '-' . $voucher->suffix;
                }
            }
            if (!empty($request['car_number'])) {
                $car = Car::where('car_number', $request['car_number'])
                    ->where('client_company_id', $sessionUser['client_company_id'])
                    ->first();
                if ($car instanceof Car) {
                    $carId = $car->id;
                }
            }
//            if (empty($requestData['voucher_number'])) {
//                $payment_category_id = $driver['un_authorized_bill_id'];
//            }
            if (!empty($requestData['advance_pay']) || !empty($requestData['advance_sale'])) {
                $driverLiability = Category::find($driver['driver_liability_id']);
                if (!$driverLiability instanceof Category) {
                    return response()->json(['status' => 400, 'message' => 'Cannot find [driver un revenue category].']);
                }
                $driverLiabilityId = $driverLiability['id'];
                if (!empty($requestData['advance_pay'])) {
                    $transactionData = [
                        ['date' => date('Y-m-d'), 'account_id' => $requestData['company_id'], 'debit_amount' => $requestData['advance_amount'], 'credit_amount' => 0, 'module' => Module::ADVANCE_PAYMENT],
                        ['date' => date('Y-m-d'), 'account_id' => $driverLiabilityId, 'debit_amount' => 0, 'credit_amount' => $requestData['advance_amount'], 'module' => Module::ADVANCE_PAYMENT],
                    ];
                    TransactionRepository::saveTransaction($transactionData);
                    $voucher->status = 'done';
                    $voucher->save();
                    return response()->json(['status' => 200, 'message' => 'Successfully saved advance payment.']);
                }
                if (!empty($requestData['advance_sale'])) {
                    $driverAmount = DriverRepository::getDriverAmount($driverLiabilityId);
                    if ($total_amount > $driverAmount) {
                        return response()->json(['status' => 400, 'message' => 'Not enough driver amount.']);
                    }
                }
            }
            if (!empty($requestData['is_driver_sale'])) {
                $driverExpense = Category::find($driver['driver_expense_id']);
                if (!$driverExpense instanceof Category) {
                    return response()->json(['status' => 400, 'message' => 'Cannot find [driver expense category].']);
                }
                $driverId = $driverExpense['id'];
                $category = Category::where('id', $sessionUser['category_id'])->first();
                if (!$category instanceof Category) {
                    return response()->json(['status' => 500, 'message' => 'You are not a cashier user.']);
                }
                $cash_in_hand_category_id = $category['id'];
                $transaction = Transaction::select(DB::raw('SUM(debit_amount) as total_debit_amount'), DB::raw('SUM(credit_amount) as total_credit_amount'))
                                    ->where('linked_id', $cash_in_hand_category_id)
                                    ->first();
                if (!$transaction instanceof Transaction) {
                    return response()->json(['status' => 400, 'message' => $sessionUser['name']. ' has not enough balance.']);
                }
                if (($transaction['total_debit_amount'] - $transaction['total_credit_amount']) < $request['driver_sale']['price']) {
                    return response()->json(['status' => 400, 'message' => $sessionUser['name']. ' has not enough balance. Minimum blalance is '. $request['driver_sale']['price']]);
                }
            } else {
                $driverId = $requestData['driver_sale']['driver_id'] ?? null;
            }
        }
        if ($requestData['payment_method'] == PaymentMethod::CASH) {
            $category = Category::where('id', $sessionUser['category_id'])->first();
            if (!$category instanceof Category) {
                return response()->json(['status' => 500, 'message' => 'You are not a cashier user.']);
            }
            $cash_in_hand_category_id = $category['id'];
            $payment_category_id = $category['id'];
        }
        if ($requestData['payment_method'] == PaymentMethod::CARD) {
            $category = Category::where('id', $requestData['pos_machine_id'])->first();
            if (!$category instanceof Category) {
                return response()->json(['status' => 500, 'message' => 'Pos machine cannot be found.']);
            }
            $payment_category_id = $category['id'];
        }
        $sale = new Sale();
        DB::transaction(function() use ($requestData, $total_amount, $payment_category_id, $carId, $voucherNo, $voucher, $sale, $driverId) {
            $sale->date = Carbon::parse($requestData['date']. date('H:i:s'))->format('Y-m-d H:i:s');
            $sale->invoice_date = Carbon::parse($requestData['invoice_date']. date('H:i:s'))->format('Y-m-d H:i:s');
            $sale->invoice_number = Sale::getInvoiceNumber();
            $sale->total_amount = $total_amount;
            $sale->driver_tip = $requestData['driver_tip'] ?? 0;
            $sale->user_id = $requestData['session_user']['id'];
            $sale->customer_id = $requestData['payment_method'] == PaymentMethod::COMPANY ? $payment_category_id : null;
            $sale->payment_method = $requestData['payment_method'] ?? null;
            $sale->card_number = $requestData['card_number'] ?? null;
            $sale->billed_to = $requestData['billed_to'] ?? null;
            $sale->voucher_number = $voucherNo;
            $sale->car_id = $carId;
            $sale->driver_id = $driverId;
            $sale->payment_category_id = $payment_category_id;
            $sale->client_company_id = $requestData['session_user']['client_company_id'];
            if (!$sale->save()) {
                return response()->json(['status' => 400, 'message' => 'Cannot save sale.']);
            }

            foreach ($requestData['products'] as $product) {
                $productModel = Product::where('id', $product['product_id'])->first();
                if (!$productModel instanceof Product) {
                    return response()->json(['status' => 400, 'message' => 'Cannot find product.']);
                }
                $accountId = $product['stock_category_id'];
                $productType = ProductType::where('id', $productModel['type_id'])->first();
                if (!empty($productType['inventory']) && $productType['inventory'] == 1) {
                    $currentStock = !empty($productModel['current_stock']) ? $productModel['current_stock'] : 0;
                    $productModel->updateQuantity($currentStock - $product['quantity']);
                }
                if ($productType['vendor'] == 1) {
                    $accountId = $productModel['vendor_id'];
                }

                $buyingPrice = $productModel['buying_price'] ?? 0;
                $saleData = new SaleData();
                $saleData->sale_id = $sale->id;
                $saleData->product_id = $product['product_id'];
                $saleData->quantity = $product['quantity'];
                $saleData->price = $product['price'];
                $saleData->subtotal = $product['subtotal'];
                $saleData->shift_sale_id = $product['shift_sale_id'] ?? null;
                $saleData->save();
                if ($productType['inventory'] == 1) {
                    $transactionData = [
                        // Transaction for debiting the payment category
                        [
                            'date' => date('Y-m-d'),
                            'description' => $requestData['car_number'] ?? null,
                            'account_id' => $payment_category_id,
                            'debit_amount' => $product['subtotal'],
                            'credit_amount' => 0,
                            'module' => Module::POS_SALE,
                            'module_id' => $sale->id,
                            'car_id' => $carId,
                            'voucher_no' => $voucherNo,
                            'quantity' => $product['quantity']
                        ],
                        // Transaction for crediting the income category
                        [
                            'date' => date('Y-m-d'),
                            'description' => $requestData['car_number'] ?? null,
                            'account_id' => $product['income_category_id'],
                            'debit_amount' => 0,
                            'credit_amount' => $product['subtotal'],
                            'module' => Module::POS_SALE,
                            'module_id' => $sale->id,
                            'car_id' => $carId,
                            'voucher_no' => $voucherNo,
                            'quantity' => $product['quantity']
                        ],
                        // Transaction for debiting the expense category
                        [
                            'date' => date('Y-m-d'),
                            'account_id' => $product['expense_category_id'],
                            'debit_amount' => $buyingPrice,
                            'credit_amount' => 0,
                            'module' => Module::POS_SALE,
                            'module_id' => $sale->id
                        ],
                        // Transaction for crediting the account
                        [
                            'date' => date('Y-m-d'),
                            'account_id' => $accountId,
                            'debit_amount' => 0,
                            'credit_amount' => $buyingPrice,
                            'module' => Module::POS_SALE,
                            'module_id' => $sale->id
                        ]
                    ];
                    TransactionRepository::saveTransaction($transactionData);
                }
            }
            if ($voucher instanceof Voucher) {
                $voucher->status = 'done';
                $voucher->save();
            }

        });
        return response()->json([
            'status' => 200,
            'message' => 'Successfully saved sale.',
            'data' => $sale['id']
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $filter = [
            'keyword' => $request->input('keyword', ''),
            'start_date' => $request->input('start_date', ''),
            'end_date' => $request->input('end_date', '')
        ];
        $paginateData = [
            'order_by' => $request->input('order_by', 'sale.id'),
            'order_mode' => $request->input('order_mode', 'DESC'),
            'limit' => $request->input('limit', 10)
        ];
        $result = SaleRepository::saleList($filter, $paginateData);
        return response()->json(['status' => 200, 'data' => $result]);
    }
    public function exportPdf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:sale,id'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'message' => $validator->errors()]);
        }
        $sessionUser = SessionUser::getUser();
        $filter = [
            'keyword' => $request->input('keyword', ''),
            'start_date' => $request->input('start_date', ''),
            'end_date' => $request->input('end_date', ''),
            'ids' => $request->input('ids')
        ];
        $paginateData = [
            'order_by' => $request->input('order_by', 'sale.id'),
            'order_mode' => $request->input('order_mode', 'DESC'),
            'limit' => $request->input('limit', 10)
        ];
        $result = SaleRepository::saleList($filter, $paginateData);
        $company = ClientCompany::where('id', $sessionUser['client_company_id'])->first();
        $pdf = Pdf::loadView('pdf.sale-list', ['data' => $result, 'company' => $company]);
        $pdf->setPaper('a4', 'landscape');
        return $pdf->output();
    }
    public function exportExcel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:sale,id'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'message' => $validator->errors()]);
        }
        $filter = [
            'keyword' => $request->input('keyword', ''),
            'start_date' => $request->input('start_date', ''),
            'end_date' => $request->input('end_date', ''),
            'ids' => $request->input('ids')
        ];
        $paginateData = [
            'order_by' => $request->input('order_by', 'sale.id'),
            'order_mode' => $request->input('order_mode', 'DESC'),
            'limit' => $request->input('limit', 10)
        ];
        $result = SaleRepository::saleList($filter, $paginateData);
        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $headers = ['Date', 'Invoice Number', 'Company Name', 'Payment Method', 'Voucher Number', 'Car Number', 'Product Name', 'Quantity', 'Total', 'User'];
        foreach ($headers as $colIndex => $header) {
            $column = chr(65 + $colIndex);
            $activeWorksheet->setCellValue($column . '1', $header);
            $activeWorksheet->getStyle($column . '1',)->getFont()->setBold(true);
            $activeWorksheet->getColumnDimension($column)->setWidth(20);
        }
        $activeWorksheet->getStyle('A1:J1')
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('9BCF41');
        $rowIndex = 2;
        foreach ($result as $item) {
            $activeWorksheet->setCellValue('A' . $rowIndex, $item['date']);
            $activeWorksheet->setCellValue('B' . $rowIndex, $item['invoice_number']);
            $activeWorksheet->setCellValue('C' . $rowIndex, $item['company_name']);
            $activeWorksheet->setCellValue('D' . $rowIndex, $item['payment_method']);
            $activeWorksheet->setCellValue('E' . $rowIndex, $item['voucher_number']);
            $activeWorksheet->setCellValue('F' . $rowIndex, $item['car_number']);
            $activeWorksheet->setCellValue('G' . $rowIndex, $item['product_name']);
            $activeWorksheet->setCellValue('H' . $rowIndex, $item['quantity']);
            $activeWorksheet->setCellValue('I' . $rowIndex, $item['total_amount']);
            $activeWorksheet->setCellValue('J' . $rowIndex, $item['user_name']);
            $rowIndex++;
        }
        // Save and output the file
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function single(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $sessionUser = SessionUser::getUser();
        if (!$sessionUser instanceof User) {
            return response()->json(['status' => 400, 'message' => 'Cannot find user.']);
        }
        $result = Sale::select('sale.*', 'car.car_number', 'categories.name as company_name')
            ->leftJoin('car', 'car.id', '=', 'sale.car_id')
            ->leftJoin('categories', 'categories.id', '=', 'sale.payment_category_id')
            ->find($inputData['id']);
        $result['date_format'] = date(FuelMatixDateTimeFormat::STANDARD_DATE_TIME, strtotime($result['date']));
        $result['customer_name'] = $result['billed_to'] ?? 'Walk in Customer';
        $result['payment_method'] = ucfirst($result['payment_method']);
        if (!empty($result['customer_id'])) {
            $category = Category::where('id', $result['customer_id'])->first();
            if ($category instanceof Category) {
                $result['customer_name'] = $category->category;
            }
        }
        $products = SaleData::select('sale_data.*', 'products.name as product_name', 'product_types.name as type_name')
            ->leftJoin('products', 'products.id', '=', 'sale_data.product_id')
            ->leftJoin('product_types', 'products.type_id', '=', 'product_types.id')
            ->where('sale_data.sale_id', $inputData['id'])
            ->get()
            ->toArray();
        foreach ($products as &$product) {
            $product['price'] = number_format($product['price'], $sessionUser['currency_precision']);
            $product['quantity_format'] = number_format($product['quantity'], $sessionUser['quantity_precision']);
            $product['subtotal_format'] = number_format($product['subtotal'], $sessionUser['currency_precision']);
        }
        $result['products'] = $products;
        $result['total_amount_format'] = number_format($result['total_amount'], $sessionUser['currency_precision']);
        $result['company'] = ClientCompany::select('id', 'name', 'address', 'email', 'phone_number')->find($sessionUser['client_company_id']);
        if ($result['payment_method'] == PaymentMethod::CASH ||  $result['payment_method'] == PaymentMethod::CARD) {
            $result['company_name'] = null;
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required',
            'products' => 'required|array',
            'products.*.product_id' => 'required',
            'products.*.quantity' => 'required',
            'products.*.price' => 'required',
            'products.*.subtotal' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $errorsMessage = [];
        foreach ($inputData['products'] as $product) {
            $saleData = SaleData::where('product_id', $product['product_id'])->where('sale_id', $inputData['id'])->first();
            if (!$saleData instanceof SaleData) {
                $errorsMessage[$product['name']][] = $product.' Sale is not found';
            }
            $shiftTotal =ShiftTotal::where('id', $saleData->shift_sale_id)->where('status', FuelMatixStatus::END)->first();
            if ($shiftTotal instanceof ShiftTotal) {
                $errorsMessage[$product['name']][] = 'Cannot edit this '.$product['name']. ' sale.This shift is close.';
            }
        }
        if (count($errorsMessage) > 0) {
            return response()->json(['status' => 600, 'errors' => $errorsMessage]);
        }
        $sale = Sale::find($inputData['id']);
        if (!$sale instanceof Sale) {
            return response()->json(['status' => 500, 'error' => 'Cannot find sale.']);
        }
        $sessionUser = SessionUser::getUser();
        if (!empty($request['car_number'])) {
            $car = Car::where('car_number', $request['car_number'])
                ->where('client_company_id', $sessionUser['client_company_id'])
                ->first();
            if ($car instanceof Car) {
                $carId = $car->id;
            }
        }
        $payment_category_id = $request['company_id'] ?? '';
        if ($request['payment_method'] == PaymentMethod::CASH) {
            $category = Category::where('id', $sessionUser['category_id'])->first();
            if (!$category instanceof Category) {
                return response()->json(['status' => 500, 'message' => 'You are not a cashier user.']);
            }
            $payment_category_id = $category['id'];
        }
        if ($request['payment_method'] == PaymentMethod::CARD) {
            $category = Category::where('id', $request['pos_machine_id'])->first();
            if (!$category instanceof Category) {
                return response()->json(['status' => 500, 'message' => 'Pos machine cannot be found.']);
            }
            $payment_category_id = $category['id'];
        }
        $voucherNo = null;
        if ($request['payment_method'] == PaymentMethod::COMPANY) {
            $voucher = Voucher::where('company_id', $request['company_id'])
                ->where('voucher_number', $request['voucher_number'])
                ->first();
            if (!$voucher instanceof Voucher) {
                return response()->json(['status' => 500, 'errors' => ['voucher_number' => ['The voucher number is not valid.']]]);
            }
            $voucherNo = $voucher->voucher_number;

            // Check if prefix exists and concatenate it with the voucher number
            if (!empty($voucher->prefix) && !empty($voucher->suffix)) {
                $voucherNo = $voucher->prefix . '-' . $voucherNo . '-' . $voucher->suffix;
            } elseif (!empty($voucher->prefix)) {
                $voucherNo = $voucher->prefix . '-' . $voucherNo;
            } elseif (!empty($voucher->suffix)) {
                $voucherNo = $voucherNo . '-' . $voucher->suffix;
            }
        }
        $driverId = $request['driver_sale']['driver_id'] ?? null;
        $total_amount = array_sum(array_column($inputData['products'], 'subtotal'));
        $sale->total_amount = $total_amount;
        $sale->customer_id = $inputData['customer_id'] ?? null;
        $sale->payment_method = $inputData['payment_method'] ?? null;
        $sale->car_id = $carId ?? null;
        $sale->driver_id = $driverId ?? null;
        $sale->payment_category_id = $payment_category_id;
        $sale->voucher_number = $voucherNo;
        if ($sale->save()) {
            SaleData::where('sale_id', $inputData['id'])->delete();
            foreach ($inputData['products'] as $product) {
                $shiftTotal = ShiftTotal::where('product_id', $product['product_id'])->where('status', FuelMatixStatus::START)->first();
                $saleData = new SaleData();
                $saleData->sale_id = $sale->id;
                $saleData->product_id = $product['product_id'];
                $saleData->quantity = $product['quantity'];
                $saleData->price = $product['price'];
                $saleData->subtotal = $product['subtotal'];
                $saleData->shift_sale_id = $shiftTotal->id;
                $saleData->save();
            }
            return response()->json(['status' => 200, 'message' => 'Successfully updated sale.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot updated sale.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $inputData = $request->all();
        $validator = Validator::make($inputData, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        Sale::where('id', $inputData['id'])->delete();
        SaleData::where('sale_id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted sale.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCompanySale(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $sessionUser = SessionUser::getUser();
        $accountReceivable = Category::where('client_company_id', $sessionUser['client_company_id'])->where('slug', strtolower( AccountCategory::ACCOUNT_RECEIVABLE))->first();
        $limit = $requestData['limit'] ?? 10;
        $orderBy = $requestData['order_by'] ?? 'transactions.id';
        $orderMode = $requestData['order_mode'] ?? 'DESC';
        $keyword = $requestData['keyword'] ?? '';
        $companyId = $requestData['company_id'] ?? '';
        $startDate  = $requestData['start_date'] ?? '';
        $endDate  = $requestData['end_date'] ?? '';
        $result = Transaction::select('transactions.id', 'invoice_item.invoice_id',  DB::raw("SUM(transactions.debit_amount) as amount"), 'transactions.created_at', 'invoice_item.date as invoice_created_at', 'transactions.description', 'car.car_number', 'transactions.voucher_no', 'categories.name', 'transactions.module', 'transactions.module_id', 'transactions.account_id as category_id')
            ->leftJoin('categories', 'categories.id', '=', 'transactions.account_id')
            ->leftJoin('invoice_item', 'invoice_item.transaction_id', 'transactions.id')
            ->leftJoin('car', 'car.id', 'transactions.car_id')
            ->whereJsonContains('categories.category_ids', $accountReceivable->id)
            ->where('transactions.debit_amount', '>', 0)
            ->where('transactions.client_company_id', $sessionUser['client_company_id'])
            ->groupBy(DB::raw('CASE WHEN invoice_item.invoice_id IS NULL THEN transactions.id ELSE invoice_item.invoice_id END, CASE WHEN invoice_item.invoice_id IS NULL THEN transactions.module_id ELSE 0 END'));
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('categories.name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('transactions.voucher_no', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('car.car_number', 'LIKE', '%'.$keyword.'%');
            });
        }
        if (!empty($companyId)) {
            $result->where(function($q) use ($companyId) {
                $q->where('transactions.account_id', $companyId);
            });
        }
        if (!empty($startDate) && !empty($endDate)) {
            $result->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('transactions.date', [$startDate, $endDate]);
            });
        }
        $result = $result->orderBy($orderBy, $orderMode)
            ->paginate($limit);
        foreach ($result as &$data) {
            if (!empty($data['invoice_id'])) {
                $data['created_at'] = !empty($data['invoice_created_at']) ? Helpers::formatDate($data['invoice_created_at'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME) : '';
                $data['car_number'] = '';
                $data['voucher_no'] = '';
            } else {
                $data['created_at'] = !empty($data['created_at']) ? Helpers::formatDate($data['created_at'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME) : '';
            }
            $data['amount_format'] = number_format($data['amount'], $sessionUser['currency_precision']);
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function unauthorizedBill(Request $request): JsonResponse
    {
        $sessionUser = SessionUser::getUser();
        $limit = $request['limit'] ?? 10;
        $keyword = $request['keyword'] ?? '';
        $unauthorizedCategory = Category::where('client_company_id', $sessionUser['client_company_id'])
            ->where('slug', strtolower(AccountCategory::UN_AUTHORIZED_BILL))
            ->first();
        $result = Transaction::select(
            'transactions.id',
            'transactions.created_at',
            'transactions.linked_id as driver_id',
            'transactions.debit_amount as amount',
            'c1.name as driver_name',
            'c2.name as company_name',
            'users.name as user_name'
        )
            ->leftJoin('categories as c1', 'c1.id', '=', 'transactions.account_id')
            ->leftJoin('categories as c2', 'c2.id', '=', 'c1.parent_category')
            ->leftJoin('users', 'users.id', '=', 'transactions.user_id')
            ->whereJsonContains('c1.category_ids', $unauthorizedCategory->id)
            ->where('transactions.client_company_id', $sessionUser['client_company_id']);
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('c1.name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('c2.name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('users.name', 'LIKE', '%'.$keyword.'%');
                $q->orWhere('transactions.amount', 'LIKE', '%'.$keyword.'%');
            });
        }
        $result = $result->orderBy('id', 'DESC')
            ->paginate($limit);
        foreach ($result as &$data) {
            $data['created_at'] = Helpers::formatDate($data['created_at'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME);
            $data['amount'] = number_format($data['amount'], $sessionUser['currency_precision']);
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function unauthorizedBillTransfer(Request $request)
    {
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'id' => 'required',
            'voucher_number' => 'required',
            'driver_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $driver = Category::where('id', $requestData['driver_id'])->first();
        if (!$driver instanceof Category) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [driver].']);
        }
        $voucher = Voucher::where('voucher_number', $requestData['voucher_number'])
            ->where('company_id', $driver['module_id'])
            ->where('status', FuelMatixStatus::PENDING)
            ->first();
        if (!$voucher instanceof Voucher) {
            return response()->json(['status' => 500, 'errors' => ['voucher_number' => ['The voucher number is not valid.']]]);
        }
        $transaction = Transaction::find($requestData['id']);
        if (!$transaction instanceof Transaction) {
            return response()->json(['status' => 400, 'message' => 'Cannot find [transaction].']);
        }
        $transaction->linked_id = $driver['module_id'];
        $transaction->save();
        $transaction = Transaction::where('id', $transaction['parent_id'])->first();
        $transaction->account_id = $driver['module_id'];
        $transaction->save();
        $voucher->status = FuelMatixStatus::COMPLETE;
        $voucher->save();
        return response()->json(['status' => 200, 'message' => 'Successfully saved.']);
    }
}
