<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BillController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\StaffController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::middleware('permission:manage-services')->group(function () {
        Route::post('/services', [ServiceController::class, 'store']);
        Route::put('/services/{service}', [ServiceController::class, 'update']);
        Route::delete('/services/{service}', [ServiceController::class, 'destroy']);
    });
    Route::get('/services', [ServiceController::class, 'index']);

    Route::middleware('permission:manage-deals')->group(function () {
        Route::post('/deals', [DealController::class, 'store']);
        Route::put('/deals/{deal}', [DealController::class, 'update']);
        Route::delete('/deals/{deal}', [DealController::class, 'destroy']);
    });
    Route::get('/deals', [DealController::class, 'index']);

    Route::middleware('permission:manage-staff')->group(function () {
        Route::post('/staff', [StaffController::class, 'store']);
        Route::put('/staff/{staff}', [StaffController::class, 'update']);
        Route::delete('/staff/{staff}', [StaffController::class, 'destroy']);
    });
    Route::get('/staff', [StaffController::class, 'index']);

    Route::middleware('permission:create-bill')->group(function () {
        Route::get('/bills', [BillController::class, 'index']);
        Route::post('/bills', [BillController::class, 'store']);
        Route::post('/bills/sync', [BillController::class, 'sync']);
        Route::get('/bills/{uuid}', [BillController::class, 'show']);
        Route::get('/customers/search', [CustomerController::class, 'search']);
    });

    Route::middleware('permission:add-expense')->group(function () {
        Route::get('/expenses', [ExpenseController::class, 'index']);
        Route::post('/expenses', [ExpenseController::class, 'store']);
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy']);
    });

    Route::middleware('permission:view-reports')->group(function () {
        Route::get('/reports/daily', [ReportController::class, 'dailySummary']);
    });
});
