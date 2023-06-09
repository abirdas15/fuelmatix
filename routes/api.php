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
});
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
});
Route::group(['prefix' => 'expense'], function() {
    Route::post('save', [ExpenseController::class, 'save']);
    Route::post('list', [ExpenseController::class, 'list']);
    Route::post('single', [ExpenseController::class, 'single']);
    Route::post('update', [ExpenseController::class, 'update']);
    Route::post('delete', [ExpenseController::class, 'delete']);
});
Route::group(['prefix' => 'tank'], function() {
    Route::post('save', [TankController::class, 'save']);
    Route::post('list', [TankController::class, 'list']);
    Route::post('single', [TankController::class, 'single']);
    Route::post('update', [TankController::class, 'update']);
    Route::post('delete', [TankController::class, 'delete']);
    Route::group(['prefix' => 'reading'], function() {
        Route::post('save', [TankController::class, 'readingSave']);
        Route::post('list', [TankController::class, 'readingList']);
        Route::post('single', [TankController::class, 'readingSingle']);
        Route::post('update', [TankController::class, 'readingUpdate']);
        Route::post('delete', [TankController::class, 'readingDelete']);
    });
});




