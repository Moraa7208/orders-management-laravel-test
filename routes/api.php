<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\StockMovementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API versioning prefix
Route::prefix('v1')->group(function () {
    // Warehouses Просмотреть список складов
    Route::apiResource('warehouses', WarehouseController::class)->only(['index']);

    // Products with stock information  Просмотреть список товаров с их остатками по складам
    Route::apiResource('products', ProductController::class)->only(['index']);
    // Orders
    Route::apiResource( 'orders', OrderController::class);

        // Order actions
    Route::patch('orders/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');
    Route::patch('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::patch('orders/{order}/resume', [OrderController::class, 'resume'])->name('orders.resume');

    Route::get('stock-movements', [StockMovementController::class, 'index'])->name('stock-movements.index');
    Route::post('manual-adjustment', [StockMovementController::class, 'manualAdjustment']);
    
}); 
