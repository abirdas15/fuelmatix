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




