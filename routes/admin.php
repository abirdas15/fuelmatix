<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;
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

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
});
Route::group(['prefix' => 'profile'], function () {
    Route::post('me', [ProfileController::class, 'profile']);
});
Route::group(['prefix' => 'company'], function () {
    Route::post('get', [CompanyController::class, 'getCompany']);
    Route::post('save', [CompanyController::class, 'saveCompany']);
    Route::post('single', [CompanyController::class, 'singleCompany']);
    Route::post('update', [CompanyController::class, 'updateCompany']);
});
Route::group(['prefix' => 'user'], function () {
    Route::post('get', [UserController::class, 'getUser']);
    Route::post('single', [UserController::class, 'singleUser']);
    Route::post('update', [UserController::class, 'updateUser']);
    Route::post('change/password', [UserController::class, 'changePassword']);
});
Route::group(['prefix' => 'dashboard'], function () {
    Route::post('summary', [DashboardController::class, 'summary']);
});



