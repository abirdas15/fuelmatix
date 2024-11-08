<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\BalanceSheetController;
use App\Http\Controllers\ProfitLossController;
use App\Http\Controllers\IncomeStatementController;
use App\Http\Controllers\PayableController;
use App\Http\Controllers\ReceivableController;
use App\Http\Controllers\TrailBalanceController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DispenserController;
use App\Http\Controllers\NozzleController;
use App\Http\Controllers\ShiftSaleController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\TankController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\PayOrderController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\CreditCompanyController;
use App\Http\Controllers\PosMachineController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BalanceTransferController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\FuelAdjustmentController;
use App\Http\Controllers\CompanyBillController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\BulkSaleController;
use App\Http\Controllers\DummySaleController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'auth'], function() {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('AuthReqCheck');
});
Route::group(['middleware' => 'AuthReqCheck'], function() {
    Route::group(['prefix' => 'category'], function() {
        Route::post('list', [CategoryController::class, 'list']);
        Route::post('parent', [CategoryController::class, 'parent']);
        Route::post('save', [CategoryController::class, 'save']);
        Route::post('single', [CategoryController::class, 'single']);
        Route::post('update', [CategoryController::class, 'update']);
    });
    Route::group(['prefix' => 'transaction'], function() {
        Route::post('save', [TransactionController::class, 'save']);
        Route::post('single', [TransactionController::class, 'single']);
        Route::post('split', [TransactionController::class, 'split']);
    });
    Route::group(['prefix' => 'balance-sheet'], function() {
        Route::post('get', [BalanceSheetController::class, 'get']);
    });
    Route::group(['prefix' => 'profit-and-loss'], function() {
        Route::post('get', [ProfitLossController::class, 'get']);
    });
    Route::group(['prefix' => 'income-statement'], function() {
        Route::post('get', [IncomeStatementController::class, 'get']);
    });
    Route::group(['prefix' => 'payable'], function() {
        Route::post('get', [PayableController::class, 'get']);
    });
    Route::group(['prefix' => 'receivable'], function() {
        Route::post('get', [ReceivableController::class, 'get']);
    });
    Route::group(['prefix' => 'trail-balance'], function() {
        Route::post('get', [TrailBalanceController::class, 'get']);
    });
    Route::group(['prefix' => 'ledger'], function() {
        Route::post('get', [LedgerController::class, 'get']);
    });
    Route::group(['prefix' => 'product/type'], function() {
        Route::post('list', [ProductTypeController::class, 'list']);
    });
    Route::group(['prefix' => 'product'], function() {
        Route::post('save', [ProductController::class, 'save']);
        Route::post('list', [ProductController::class, 'list']);
        Route::post('single', [ProductController::class, 'single']);
        Route::post('update', [ProductController::class, 'update']);
        Route::post('delete', [ProductController::class, 'delete']);
        Route::post('dispenser', [ProductController::class, 'getDispenser']);
        Route::post('get/tank', [ProductController::class, 'getTank']);
    });
    Route::group(['prefix' => 'dispenser'], function() {
        Route::post('save', [DispenserController::class, 'save']);
        Route::post('list', [DispenserController::class, 'list']);
        Route::post('single', [DispenserController::class, 'single']);
        Route::post('update', [DispenserController::class, 'update']);
        Route::post('delete', [DispenserController::class, 'delete']);
        Route::group(['prefix' => 'reading'], function() {
            Route::post('save', [DispenserController::class, 'readingSave']);
            Route::post('list', [DispenserController::class, 'readingList']);
            Route::post('single', [DispenserController::class, 'readingSingle']);
            Route::post('update', [DispenserController::class, 'readingUpdate']);
            Route::post('delete', [DispenserController::class, 'readingDelete']);
        });
    });
    Route::group(['prefix' => 'nozzle'], function() {
        Route::post('save', [NozzleController::class, 'save']);
        Route::post('list', [NozzleController::class, 'list']);
        Route::post('single', [NozzleController::class, 'single']);
        Route::post('update', [NozzleController::class, 'update']);
        Route::post('delete', [NozzleController::class, 'delete']);
        Route::group(['prefix' => 'reading'], function() {
            Route::post('save', [NozzleController::class, 'readingSave']);
            Route::post('list', [NozzleController::class, 'readingList']);
            Route::post('single', [NozzleController::class, 'readingSingle']);
            Route::post('update', [NozzleController::class, 'readingUpdate']);
            Route::post('delete', [NozzleController::class, 'readingDelete']);
        });
    });
    Route::group(['prefix' => 'shift/sale'], function() {
        Route::post('save', [ShiftSaleController::class, 'save']);
        Route::post('list', [ShiftSaleController::class, 'list']);
        Route::post('single', [ShiftSaleController::class, 'single']);
        Route::post('update', [ShiftSaleController::class, 'update']);
        Route::post('delete', [ShiftSaleController::class, 'delete']);
        Route::post('getCategory', [ShiftSaleController::class, 'getCategory']);
        Route::post('getShiftByDate', [ShiftSaleController::class, 'getShiftByDate']);
        Route::post('tankReading', [ShiftSaleController::class, 'tankReading']);
        Route::post('getNozzleLatestReading', [ShiftSaleController::class, 'getNozzleLatestReading']);
        Route::post('shiftName/list', [ShiftSaleController::class, 'shiftNameList']);
    });
    Route::group(['prefix' => 'expense'], function() {
        Route::post('save', [ExpenseController::class, 'save']);
        Route::post('list', [ExpenseController::class, 'list']);
        Route::post('list/export', [ExpenseController::class, 'listExport']);
        Route::post('single', [ExpenseController::class, 'single']);
        Route::post('update', [ExpenseController::class, 'update']);
        Route::post('delete', [ExpenseController::class, 'delete']);
        Route::post('approve', [ExpenseController::class, 'approve']);
        Route::post('report', [ExpenseController::class, 'report']);
        Route::post('export/pdf', [ExpenseController::class, 'exportPdf']);
    });
    Route::group(['prefix' => 'tank'], function() {
        Route::post('save', [TankController::class, 'save']);
        Route::post('list', [TankController::class, 'list']);
        Route::post('single', [TankController::class, 'single']);
        Route::post('update', [TankController::class, 'update']);
//        Route::post('delete', [TankController::class, 'delete']);
        Route::post('get/nozzle', [TankController::class, 'getNozzle']);
        Route::post('byProduct', [TankController::class, 'getTankByProduct']);
        Route::post('getVolume', [TankController::class, 'getVolume']);
        Route::post('getBstiChart', [TankController::class, 'getBstiChart']);
        Route::post('tankWithLatestReading', [TankController::class, 'tankWithLatestReading']);
        Route::group(['prefix' => 'reading'], function() {
            Route::post('save', [TankController::class, 'readingSave']);
            Route::post('list', [TankController::class, 'readingList']);
            Route::post('single', [TankController::class, 'readingSingle']);
            Route::post('update', [TankController::class, 'readingUpdate']);
            Route::post('delete', [TankController::class, 'readingDelete']);
            Route::post('latest', [TankController::class, 'latestReading']);
        });
        Route::group(['prefix' => 'refill'], function() {
            Route::post('save', [TankController::class, 'refillSave']);
            Route::post('list', [TankController::class, 'refillList']);
            Route::post('single', [TankController::class, 'refillSingle']);
            Route::post('update', [TankController::class, 'refillUpdate']);
            Route::post('delete', [TankController::class, 'refillDelete']);
        });
    });
    Route::group(['prefix' => 'bank'], function() {
        Route::post('save', [BankController::class, 'save']);
        Route::post('list', [BankController::class, 'list']);
        Route::post('single', [BankController::class, 'single']);
        Route::post('update', [BankController::class, 'update']);
        Route::post('delete', [BankController::class, 'delete']);
    });
    Route::group(['prefix' => 'vendor'], function() {
        Route::post('save', [VendorController::class, 'save']);
        Route::post('list', [VendorController::class, 'list']);
        Route::post('single', [VendorController::class, 'single']);
        Route::post('update', [VendorController::class, 'update']);
        Route::post('delete', [VendorController::class, 'delete']);
        Route::post('payment', [VendorController::class, 'payment']);
        Route::post('report', [VendorController::class, 'report']);
    });
    Route::group(['prefix' => 'pay/order'], function() {
        Route::post('save', [PayOrderController::class, 'save']);
        Route::post('list', [PayOrderController::class, 'list']);
        Route::post('single', [PayOrderController::class, 'single']);
        Route::post('update', [PayOrderController::class, 'update']);
        Route::post('delete', [PayOrderController::class, 'delete']);
        Route::post('latest', [PayOrderController::class, 'latest']);
        Route::post('quantity', [PayOrderController::class, 'getQuantity']);
        Route::post('product', [PayOrderController::class, 'payOrderProduct']);
    });
    Route::group(['prefix' => 'sale'], function() {
        Route::post('save', [SaleController::class, 'save']);
        Route::post('list', [SaleController::class, 'list']);
        Route::post('single', [SaleController::class, 'single']);
        Route::post('update', [SaleController::class, 'update']);
        Route::post('delete', [SaleController::class, 'delete']);
        Route::post('unauthorizedBill', [SaleController::class, 'unauthorizedBill']);
        Route::post('unauthorizedBill/transfer', [SaleController::class, 'unauthorizedBillTransfer']);
    });
    Route::group(['prefix' => 'dummySale'], function() {
        Route::post('save', [DummySaleController::class, 'save']);
        Route::post('single', [DummySaleController::class, 'single']);
        Route::post('list', [DummySaleController::class, 'list']);
    });
    Route::group(['prefix' => 'creditCompany'], function() {
        Route::post('save', [CreditCompanyController::class, 'save']);
        Route::post('list', [CreditCompanyController::class, 'list']);
        Route::post('single', [CreditCompanyController::class, 'single']);
        Route::post('update', [CreditCompanyController::class, 'update']);
        Route::post('delete', [CreditCompanyController::class, 'delete']);
    });
    Route::group(['prefix' => 'posMachine'], function() {
        Route::post('save', [PosMachineController::class, 'save']);
        Route::post('list', [PosMachineController::class, 'list']);
        Route::post('single', [PosMachineController::class, 'single']);
        Route::post('update', [PosMachineController::class, 'update']);
        Route::post('delete', [PosMachineController::class, 'delete']);
    });
    Route::group(['prefix' => 'employee'], function() {
        Route::post('save', [EmployeeController::class, 'save']);
        Route::post('list', [EmployeeController::class, 'list']);
        Route::post('single', [EmployeeController::class, 'single']);
        Route::post('update', [EmployeeController::class, 'update']);
        Route::post('delete', [EmployeeController::class, 'delete']);
    });
    Route::group(['prefix' => 'salary'], function() {
        Route::post('searchEmployee', [SalaryController::class, 'searchEmployee']);
        Route::post('save', [SalaryController::class, 'save']);
        Route::post('list', [SalaryController::class, 'list']);
        Route::post('single', [SalaryController::class, 'single']);
        Route::post('update', [SalaryController::class, 'update']);
        Route::post('delete', [SalaryController::class, 'delete']);
        Route::post('getCategory', [SalaryController::class, 'getCategory']);
        Route::post('print', [SalaryController::class, 'print']);
    });
    Route::group(['prefix' => 'companySale'], function() {
        Route::post('list', [SaleController::class, 'getCompanySale']);
    });
    Route::group(['prefix' => 'invoice'], function() {
        Route::post('generate', [InvoiceController::class, 'generate']);
        Route::post('list', [InvoiceController::class, 'list']);
        Route::post('payment', [InvoiceController::class, 'payment']);
        Route::post('global/payment', [InvoiceController::class, 'globalPayment']);
        Route::post('delete', [InvoiceController::class, 'delete']);
        Route::post('single', [InvoiceController::class, 'single']);
        Route::post('download/pdf', [InvoiceController::class, 'downloadPdf']);
        Route::post('payment/list', [InvoiceController::class, 'invoicePayment']);
        Route::post('change-number', [InvoiceController::class, 'changeInvoiceNumber']);
    });
    Route::group(['prefix' => 'dashboard'], function() {
        Route::post('get', [DashboardController::class, 'get']);
    });
    Route::group(['prefix' => 'report'], function() {
        Route::group(['prefix' => 'dailyLog'], function() {
            Route::post('/', [ReportController::class, 'dailyLog']);
            Route::post('export/pdf', [ReportController::class, 'dailyLogExportPdf']);
        });
        Route::group(['prefix' => 'sales'], function() {
            Route::post('/', [ReportController::class, 'salesReport']);
            Route::post('export/pdf', [ReportController::class, 'salesReportReportPDF']);
        });
        Route::group(['prefix' => 'windfall'], function() {
            Route::post('/', [ReportController::class, 'windfallReport']);
            Route::post('export/pdf', [ReportController::class, 'windfallReportPDF']);
        });
        Route::group(['prefix' => 'creditCompany'], function() {
            Route::post('/', [ReportController::class, 'creditCompanyReport']);
            Route::post('export/pdf', [ReportController::class, 'creditCompanyReportExportPDF']);
        });
        Route::group(['prefix' => 'driver'], function() {
            Route::post('/', [ReportController::class, 'driverReport']);
            Route::post('export/pdf', [ReportController::class, 'driverReportExportPDF']);
        });
        Route::group(['prefix' => 'stockSummary'], function() {
            Route::post('/', [ReportController::class, 'stockSummary']);
            Route::post('export/pdf', [ReportController::class, 'stockSummaryExportPDF']);
        });
        Route::group(['prefix' => 'vendor'], function() {
            Route::post('export/pdf', [ReportController::class, 'vendorReportExportPDF']);
        });
        Route::group(['prefix' => 'company/summary'], function() {
            Route::post('/', [ReportController::class, 'companySummary']);
            Route::post('export/pdf', [ReportController::class, 'companySummaryExportPDF']);
            Route::group(['prefix' => 'details'], function() {
                Route::post('/', [ReportController::class, 'companySummaryDetails']);
                Route::post('export/pdf', [ReportController::class, 'companySummaryDetailsExportPDF']);
            });
        });
    });
    Route::group(['prefix' => 'user'], function() {
        Route::post('save', [UserController::class, 'save']);
        Route::post('list', [UserController::class, 'list']);
        Route::post('single', [UserController::class, 'single']);
        Route::post('update', [UserController::class, 'update']);
        Route::post('delete', [UserController::class, 'delete']);
    });
    Route::group(['prefix' => 'balanceTransfer'], function() {
        Route::post('save', [BalanceTransferController::class, 'save']);
        Route::post('list', [BalanceTransferController::class, 'list']);
        Route::post('single', [BalanceTransferController::class, 'single']);
        Route::post('update', [BalanceTransferController::class, 'update']);
        Route::post('approve', [BalanceTransferController::class, 'approve']);
        Route::post('delete', [BalanceTransferController::class, 'delete']);
    });
    Route::group(['prefix' => 'company'], function() {
        Route::post('save', [CompanyController::class, 'save']);
        Route::post('single', [CompanyController::class, 'single']);
    });
    Route::group(['prefix' => 'voucher'], function() {
        Route::post('save', [VoucherController::class, 'save']);
        Route::post('list', [VoucherController::class, 'list']);
    });
    Route::group(['prefix' => 'driver'], function() {
        Route::post('save', [DriverController::class, 'save']);
        Route::post('list', [DriverController::class, 'list']);
        Route::post('single', [DriverController::class, 'single']);
        Route::post('update', [DriverController::class, 'update']);
        Route::post('delete', [DriverController::class, 'delete']);
        Route::post('amount', [DriverController::class, 'getAmount']);
    });
    Route::group(['prefix' => 'role'], function() {
        Route::post('list', [RoleController::class, 'list']);
        Route::post('save', [RoleController::class, 'save']);
        Route::post('single', [RoleController::class, 'single']);
        Route::post('update', [RoleController::class, 'update']);
        Route::post('delete', [RoleController::class, 'delete']);
    });
    Route::group(['prefix' => 'permission'], function() {
        Route::post('list', [PermissionController::class, 'getAllPermission']);
    });
    Route::group(['prefix' => 'fuelAdjustment'], function() {
        Route::post('save', [FuelAdjustmentController::class, 'save']);
        Route::post('list', [FuelAdjustmentController::class, 'list']);
        Route::post('single', [FuelAdjustmentController::class, 'single']);
        Route::post('update', [FuelAdjustmentController::class, 'update']);
        Route::post('delete', [FuelAdjustmentController::class, 'delete']);
    });
    Route::group(['prefix' => 'companyBill'], function() {
        Route::post('list', [CompanyBillController::class, 'list']);
        Route::post('download', [CompanyBillController::class, 'download']);
    });
    Route::group(['prefix' => 'car'], function() {
        Route::post('search', [CarController::class, 'search']);
        Route::post('save', [CarController::class, 'save']);
        Route::post('list', [CarController::class, 'list']);
        Route::post('single', [CarController::class, 'single']);
        Route::post('update', [CarController::class, 'update']);
        Route::post('delete', [CarController::class, 'delete']);
    });
    Route::group(['prefix' => 'purchase'], function() {
        Route::post('save', [PurchaseController::class, 'save']);
        Route::post('list', [PurchaseController::class, 'list']);
        Route::post('pay', [PurchaseController::class, 'pay']);
    });
    Route::group(['prefix' => 'bulkSale'], function() {
        Route::post('save', [BulkSaleController::class, 'save']);
        Route::post('list', [BulkSaleController::class, 'list']);
    });
});




