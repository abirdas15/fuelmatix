<?php

namespace App\Http\Controllers;

use App\Common\FuelMatixDateTimeFormat;
use App\Common\FuelMatixStatus;
use App\Common\Module;
use App\Common\PaymentMethod;
use App\Helpers\SessionUser;
use App\Models\Car;
use App\Models\Category;
use App\Models\ClientCompany;
use App\Models\Driver;
use App\Models\DummySale;
use App\Models\DummySaleData;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Sale;
use App\Models\SaleData;
use App\Models\ShiftTotal;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Voucher;
use App\Repository\DriverRepository;
use App\Repository\SaleRepository;
use App\Repository\TransactionRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DummySaleController extends Controller
{
    public function save(Request $request)
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
        }
        $request->merge(['products' => $products]);
        $requestData = $request->all();

        $driverId = null;
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
            if (!empty($request['car_number'])) {
                $car = Car::where('car_number', $request['car_number'])
                    ->where('client_company_id', $sessionUser['client_company_id'])
                    ->first();
                if ($car instanceof Car) {
                    $carId = $car->id;
                }
            }
            if (empty($requestData['voucher_number'])) {
                $payment_category_id = $driver['un_authorized_bill_id'];
            }
            if ($requestData['payment_method'] == PaymentMethod::CASH) {
                $category = Category::where('id', $sessionUser['category_id'])->first();
                if (!$category instanceof Category) {
                    return response()->json(['status' => 500, 'message' => 'You are not a cashier user.']);
                }
                $payment_category_id = $category['id'];
            }
        }
        if ($requestData['payment_method'] == PaymentMethod::CARD) {
            $category = Category::where('id', $requestData['pos_machine_id'])->first();
            if (!$category instanceof Category) {
                return response()->json(['status' => 500, 'message' => 'Pos machine cannot be found.']);
            }
            $payment_category_id = $category['id'];
        }
        $sale = new DummySale();
        DB::transaction(function() use ($requestData, $total_amount, $payment_category_id, $carId, $sale, $driverId) {
            $sale->date = Carbon::parse($requestData['date']. date('H:i:s'))->format('Y-m-d H:i:s');
            $sale->invoice_number = Sale::getInvoiceNumber();
            $sale->total_amount = $total_amount;
            $sale->driver_tip = $requestData['driver_tip'] ?? 0;
            $sale->user_id = $requestData['session_user']['id'];
            $sale->customer_id = $requestData['payment_method'] == PaymentMethod::COMPANY ? $payment_category_id : null;
            $sale->payment_method = $requestData['payment_method'] ?? null;
            $sale->billed_to = $requestData['billed_to'] ?? null;
            $sale->voucher_number = $requestData['voucher_number'] ?? null;
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
                $saleData = new DummySaleData();
                $saleData->sale_id = $sale->id;
                $saleData->product_id = $product['product_id'];
                $saleData->quantity = $product['quantity'];
                $saleData->price = $product['price'];
                $saleData->subtotal = $product['subtotal'];
                $saleData->shift_sale_id = $product['shift_sale_id'] ?? null;
                $saleData->save();
            }

        });
        return response()->json([
            'status' => 200,
            'message' => 'Successfully saved sale.',
            'data' => $sale['id']
        ]);
    }
    public function single(Request $request)
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
        $result = DummySale::select('dummy_sale.*', 'car.car_number', 'categories.name as company_name')
            ->leftJoin('car', 'car.id', '=', 'dummy_sale.car_id')
            ->leftJoin('categories', 'categories.id', '=', 'dummy_sale.payment_category_id')
            ->find($inputData['id']);
        $result['date'] = date(FuelMatixDateTimeFormat::STANDARD_DATE_TIME, strtotime($result['date']));
        $result['customer_name'] = $result['billed_to'] ?? 'Walk in Customer';
        $result['payment_method'] = ucfirst($result['payment_method']);
        if (!empty($result['customer_id'])) {
            $category = Category::where('id', $result['customer_id'])->first();
            if ($category instanceof Category) {
                $result['customer_name'] = $category->category;
            }
        }
        $products = DummySaleData::select('dummy_sale_data.*', 'products.name as product_name', 'product_types.name as type_name')
            ->leftJoin('products', 'products.id', '=', 'dummy_sale_data.product_id')
            ->leftJoin('product_types', 'products.type_id', '=', 'product_types.id')
            ->where('dummy_sale_data.sale_id', $inputData['id'])
            ->get()
            ->toArray();
        foreach ($products as &$product) {
            $product['price'] = number_format($product['price'], $sessionUser['currency_precision']);
            $product['quantity'] = number_format($product['quantity'], $sessionUser['quantity_precision']);
            $product['subtotal'] = number_format($product['subtotal'], $sessionUser['currency_precision']);
        }
        $result['products'] = $products;
        $result['total_amount'] = number_format($result['total_amount'], $sessionUser['currency_precision']);
        $result['company'] = ClientCompany::select('id', 'name', 'address', 'email', 'phone_number')->find($sessionUser['client_company_id']);
        if ($result['payment_method'] == PaymentMethod::CASH ||  $result['payment_method'] == PaymentMethod::CARD) {
            $result['company_name'] = null;
        }
        return response()->json(['status' => 200, 'data' => $result]);
    }
}
