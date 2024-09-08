<?php

namespace App\Http\Controllers;

use App\Common\AccountCategory;
use App\Common\FuelMatixDateTimeFormat;
use App\Helpers\Helpers;
use App\Helpers\SessionUser;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\ShiftSale;
use App\Models\ShiftSummary;
use App\Models\ShiftTotal;
use App\Models\User;
use App\Repository\ShiftSaleRepository;
use App\Services\TankReadingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShiftSaleController extends Controller
{

    /**
     * @var TankReadingService
     */
    private $tankReadingService;

    public function __construct(TankReadingService $tankReadingService)
    {
        $this->tankReadingService = $tankReadingService;
    }
    /**
     * Save method to handle the start and end of a shift sale.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        // Retrieve all input data from the request
        $inputData = $request->all();

        // Validate the input data with specific rules
        $validator = Validator::make($inputData, [
            'date' => 'required',
            'tanks' => 'required|array',
            'status' => 'required',
            'product_id' => 'required',
            'tanks.*.start_reading' => $inputData['status'] == 'end' ? 'required' : 'nullable',
            'tanks.*.end_reading' => $inputData['status'] == 'end' ? 'required' : 'nullable',
            'tanks.*.consumption' => $inputData['status'] == 'end' ? 'required' : 'nullable',
            'amount' => $inputData['status'] == 'end' ? 'required' : 'nullable',
            'tanks.*.dispensers' => $inputData['status'] == 'end' ? 'required|array' : 'nullable',
            'categories.*.category_id' => $inputData['status'] == 'end' ? 'required' : 'nullable',
            'categories.*.amount' => $inputData['status'] == 'end' ? 'required' : 'nullable',
        ],[
            'categories.*.category_id.required' => 'The category field is required.',
            'categories.*.amount.required' => 'The category field is required.'
        ]);

        // If validation fails, return a JSON response with validation errors
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }

        // Retrieve the product from the database using product_id from the input data
        $product = Product::where('id', $inputData['product_id'])->first();

        // If the product is not found, return an error response
        if (!$product instanceof Product) {
            return response()->json(['status' => 400, 'message' => 'Cannot find product.']);
        }

        // If the status is 'start', initiate the shift sale
        if ($inputData['status'] == 'start') {
            $shiftSaleRepose = ShiftSaleRepository::startShiftSale($request->all());

            // If starting the shift sale fails, return the error response
            if (!$shiftSaleRepose instanceof ShiftTotal) {
                return response()->json($shiftSaleRepose);
            }

            // Return a success response for starting the shift sale
            return response()->json([
                'status' => 200,
                'message' => 'Successfully started shift sale.'
            ]);
        }

        // Retrieve the shift sale from the database using shift_id from the input data
        $shiftTotal = ShiftTotal::where('id', $inputData['shift_id'])->first();

        if ($request->input('status') ==  'previous') {
            $shiftTotal = new ShiftTotal();
        }

        // If the shift sale is not found, return an error response
        if (!$shiftTotal instanceof ShiftTotal) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot find shift sale.'
            ]);
        }

        // End the shift sale
        $shiftSaleResponse = ShiftSaleRepository::shiftSaleEnd($shiftTotal, $product, $request->all());

        // If ending the shift sale fails, return the error response
        if (!$shiftSaleResponse instanceof ShiftTotal) {
            return response()->json($shiftSaleResponse);
        }

        // Return a success response for ending the shift sale, including the shift sale ID
        return response()->json([
            'status' => 200,
            'message' => 'Successfully ended shift sale.',
            'shift_sale_id' => $shiftSaleResponse->id
        ]);
    }

    /**
     * List ShiftTotals with their related data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        // Retrieve the session user
        $session = SessionUser::getUser();
        if (!$session instanceof User) {
            // Return an error response if the session user cannot be found
            return response()->json([
                'status' => 400,
                'message' => 'Session user cannot be found'
            ]);
        }

        // Retrieve query parameters or set default values
        $limit = $request->input('limit', 20);
        $keyword = $request->input('keyword', '');
        $order_by = $request->input('order_by', 'shift_sale.id');
        $order_mode = $request->input('order_mode', 'DESC');

        // Build the query
        $result = ShiftTotal::select(
            'shift_total.*',  // Select all columns from the shift_total table
            'products.name as product_name',  // Get product name
            'users.name as user_name',  // Get user name
            'product_types.tank',  // Get tank information from product_types
            DB::raw('SUM(shift_sale.consumption) as total_consumption'),  // Aggregate total consumption
            DB::raw('SUM(shift_sale.amount) as total_amount')  // Aggregate total amount
        )
            ->leftJoin('shift_sale', 'shift_sale.shift_id', '=', 'shift_total.id')  // Join with shift_sale table
            ->leftJoin('products', 'products.id', '=', 'shift_total.product_id')  // Join with products table
            ->leftJoin('product_types', 'product_types.id', '=', 'products.type_id')  // Join with product_types table
            ->leftJoin('users', 'users.id', '=', 'shift_total.user_id')  // Join with users table
            ->where('shift_total.client_company_id', $session['client_company_id']);  // Filter by client company ID

        // Apply keyword search if provided
        if (!empty($keyword)) {
            $result->where(function($q) use ($keyword) {
                $q->where('products.name', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('users.name', 'LIKE', '%'.$keyword.'%');
            });
        }

        // Group by shift_total.id to aggregate data correctly
        $result = $result->groupBy('shift_total.id')
            ->orderBy($order_by, $order_mode)  // Order the results
            ->paginate($limit);  // Paginate the results

        // Format the results
        foreach ($result as &$data) {
            // Format amount
            $data['amount'] = !empty($data['amount']) ? number_format($data['amount'], 2) : '';
            // Format consumption
            $data['consumption'] = !empty($data['consumption']) ? number_format($data['consumption'], 2) : '';

            $data['database_date'] = Helpers::formatDate($data['start_date'], FuelMatixDateTimeFormat::ONLY_DATE);
            // Format date
            $data['date'] = Helpers::formatDate($data['start_date'], FuelMatixDateTimeFormat::STANDARD_DATE_TIME);
        }

        // Return the result as a JSON response
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }
    /**
     * Handle the request to fetch a single shift sale.
     *
     * @param Request $request The incoming request object.
     * @return JsonResponse The JSON response containing the shift sale details.
     */
    public function single(Request $request): JsonResponse
    {
        // Validate the incoming request, ensuring the 'id' field is provided
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);

        // If validation fails, return a JSON response with status 500 and validation errors
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }

        // Fetch the shift sale details using the ShiftSaleRepository
        $response = ShiftSaleRepository::getSingleShiftSale($request->input('id'));

        // Return a JSON response with status 200 and the fetched shift sale data
        return response()->json([
            'status' => 200,
            'data' => $response
        ]);
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
            'date' => 'required',
            'product_id' => 'required',
            'start_reading' => 'required',
            'end_reading' => 'required',
            'consumption' => 'required',
            'amount' => 'required',
            'dispensers' => 'required|array',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }
        $shiftSale = ShiftSale::find($inputData['id']);
        if (!$shiftSale instanceof ShiftSale) {
            return response()->json(['status' => 400, 'message' => 'Cannot find shift sale.']);
        }
        $shiftSale->date = $inputData['date'];
        $shiftSale->product_id = $inputData['product_id'];
        $shiftSale->start_reading = $inputData['start_reading'];
        $shiftSale->end_reading = $inputData['end_reading'];
        $shiftSale->consumption = $inputData['consumption'];
        $shiftSale->amount = $inputData['amount'];
        if ($shiftSale->save()) {
            ShiftSummary::where('shift_sale_id', $inputData['id'])->delete();
            foreach ($inputData['dispensers'] as $dispenser) {
                foreach ($dispenser['nozzle'] as $nozzle) {
                    $shiftSaleSummary = new ShiftSummary();
                    $shiftSaleSummary->shift_sale_id = $inputData['id'];
                    $shiftSaleSummary->nozzle_id = $nozzle['id'];
                    $shiftSaleSummary->start_reading = $nozzle['start_reading'];
                    $shiftSaleSummary->end_reading = $nozzle['end_reading'];
                    $shiftSaleSummary->consumption = $nozzle['consumption'];
                    $shiftSaleSummary->amount = $nozzle['amount'];
                    $shiftSaleSummary->save();
                }
            }

            return response()->json(['status' => 200, 'message' => 'Successfully updated shift sale.']);
        }
        return response()->json(['status' => 500, 'error' => 'Cannot updated shift sale.']);
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
        ShiftTotal::where('id', $inputData['id'])->delete();
        ShiftSale::where('shift_id', $inputData['id'])->delete();
        ShiftSummary::where('shift_sale_id', $inputData['id'])->delete();
        return response()->json(['status' => 200, 'message' => 'Successfully deleted shift sale.']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCategory(Request $request): JsonResponse
    {

        $inputData = $request->all();
        $categoryId = Category::select('id')
            ->whereIn('slug', [strtolower(AccountCategory::CASH_IN_HAND), strtolower(AccountCategory::ACCOUNT_RECEIVABLE),  strtolower(AccountCategory::POS_MACHINE)])
            ->where('client_company_id', $inputData['session_user']['client_company_id'])
            ->pluck('id')
            ->toArray();
        $result = Category::select('id', 'name', 'slug')
            ->with('product_price')
            ->where(function ($query) use ($categoryId) {
                foreach ($categoryId as $id) {
                    $query->orWhereJsonContains('category_ids', $id);
                }
            })
            ->orderBy('parent_category', 'ASC')
            ->get()
            ->toArray();
        $resultArray = [];
        foreach ($result as &$data) {
            $data['selected'] = false;
            if ($data['slug'] != strtolower(AccountCategory::ACCOUNT_RECEIVABLE) && $data['slug'] != strtolower(AccountCategory::POS_MACHINE) && $data['slug'] != strtolower(AccountCategory::CASH_IN_HAND)) {
                if ($data['name'] == AccountCategory::CASH) {
                    $data['selected'] = true;
                }
                $resultArray[] = $data;
            }
        }
        return response()->json(['status' => 200, 'data' => $resultArray]);
    }
    /**
     * Retrieves shift information for a given date.
     *
     * @param Request $request The HTTP request instance containing request data.
     * @return JsonResponse The JSON response containing the status and the shift data or validation errors.
     */
    public function getShiftByDate(Request $request): JsonResponse
    {
        // Retrieve all request data
        $requestData = $request->all();

        // Validate the request data to ensure 'date' is present and is a valid date
        $validator = Validator::make($requestData, [
            'date' => 'required|date'
        ]);

        // Return validation errors if the validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'errors' => $validator->errors()
            ]);
        }

        // Get the current session user
        $sessionUser = SessionUser::getUser();

        // Parse the provided date into start and end timestamps for the whole day
        $startDate = Carbon::parse($requestData['date'], SessionUser::TIMEZONE)->startOfDay();
        $endDate = Carbon::parse($requestData['date'], SessionUser::TIMEZONE)->endOfDay();

        // Query the ShiftTotal table for shifts within the specified date range and join with the products table
        $result = ShiftTotal::select('shift_total.id', 'shift_total.start_date', 'shift_total.end_date', 'products.name as product_name')
            ->leftJoin('products', 'products.id', '=', 'shift_total.product_id')
            ->whereBetween('start_date', [$startDate, $endDate])
            ->where('shift_total.client_company_id', $sessionUser['client_company_id'])
            ->get()
            ->toArray();

        // Format the shift data
        foreach ($result as &$data) {
            if (!empty($data['start_date']) && !empty($data['end_date'])) {
                $data['name'] = $data['product_name'] . ' (' . Helpers::formatDate($data['start_date'], FuelMatixDateTimeFormat::STANDARD_TIME) . ' - ' . Helpers::formatDate($data['end_date'], FuelMatixDateTimeFormat::STANDARD_TIME) . ')';
            } else {
                $data['name'] = $data['product_name'] . ' (' . Helpers::formatDate($data['start_date'], FuelMatixDateTimeFormat::STANDARD_TIME) . ' - Running)';
            }
        }

        // Return the formatted shift data as a JSON response
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }
    /**
     * Handles the tank reading request, validating inputs and retrieving necessary data.
     *
     * This method processes a request to obtain tank readings for a specified product. It performs input validation,
     * retrieves the relevant product and product type, and then uses a service to gather and return the tank reading data.
     *
     * @param Request $request - The incoming HTTP request containing parameters for tank reading.
     *
     * @return JsonResponse - Returns a JSON response with either error messages or the requested data.
     */
    public function tankReading(Request $request): JsonResponse
    {
        // Validate required input parameters
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer',
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json(['status' => 500, 'errors' => $validator->errors()]);
        }

        // Retrieve the session user
        $sessionUser = SessionUser::getUser();

        // Retrieve the product by ID and validate its existence within the user's client company
        $product = Product::where('id', $request->input('product_id'))
            ->where('client_company_id', $sessionUser['client_company_id'])
            ->first();

        if (!$product instanceof Product) {
            return response()->json([
                'status' => 500,
                'error' => 'Cannot find product.'
            ]);
        }

        // Retrieve the product type associated with the product
        $productType = ProductType::where('id', $product['type_id'])->first();

        if (!$productType instanceof ProductType) {
            return response()->json([
                'status' => 500,
                'error' => 'Cannot find product type.'
            ]);
        }

        // Call the tank reading service to get the tank reading data
        $response = $this->tankReadingService->tankReading([
            'status' => $request->input('status', ''),
            'shift_id' => $request->input('shift_id', 0),
            'date' => $request->input('date', '')
        ], $product);

        // Return the data in a successful JSON response
        return response()->json([
            'status' => 200,
            'data' => $response
        ]);
    }

}
