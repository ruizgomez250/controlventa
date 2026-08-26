<?php

use App\Http\Controllers\Api\BillingWebhookController;
use App\Http\Controllers\Api\VentaControllerApi;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileSyncController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProductoController;
use Illuminate\Http\Request;
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

Route::post('/billing/webhook/{provider}', BillingWebhookController::class)
    ->middleware(['central', 'throttle:30,1'])
    ->name('billing.webhook');

Route::post('/login', [MobileAuthController::class, 'login'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [MobileAuthController::class, 'logout']);
    Route::get('/productos', [ProductoController::class, 'indexl'])->middleware('can:producto leer');
    Route::get('/productos/{id}', [ProductoController::class, 'showl'])->middleware('can:producto leer');
    Route::post('/clientesa', [ClienteController::class, 'storeA'])->middleware('can:cliente crear');
    Route::get('/clientesa', [ClienteController::class, 'indexA'])->middleware('can:cliente leer');
    Route::get('/ventas', [VentaControllerApi::class, 'index'])->middleware('can:venta leer');
    Route::get('/ventas/{id}', [VentaControllerApi::class, 'show'])->middleware('can:venta leer');
    Route::get('/sync/productos/updated', [MobileSyncController::class, 'updatedProducts'])->middleware('can:producto leer');
    Route::post('/sync/productos/upload', [MobileSyncController::class, 'uploadProduct'])->middleware('can:producto crear');
    Route::post('/sync/productos/upload-photo/{id}', [MobileSyncController::class, 'uploadPhoto'])->middleware('can:producto editar');
    Route::post('/ventas/offline', [MobileSyncController::class, 'uploadSale'])->middleware('can:venta crear');

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
