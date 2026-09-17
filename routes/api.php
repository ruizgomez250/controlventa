<?php

use App\Http\Controllers\Api\BillingWebhookController;
use App\Http\Controllers\Api\VentaControllerApi;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileSyncController;
use App\Http\Controllers\Api\BaleApiController;
use App\Http\Controllers\Api\MobilePeopleController;
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
    Route::get('/media/{type}/{file}', function (string $type, string $file) {
        abort_unless(in_array($type, ['fardos', 'productos'], true) && preg_match('/^[A-Za-z0-9_-][A-Za-z0-9._-]*\.(jpg|jpeg|png|webp)$/i', $file), 404);
        $path = $type . '/' . $file;
        abort_unless(\Illuminate\Support\Facades\Storage::disk('public')->exists($path), 404);
        return \Illuminate\Support\Facades\Storage::disk('public')->response($path);
    })->middleware('throttle:120,1');
    Route::post('/logout', [MobileAuthController::class, 'logout']);
    Route::get('/productos', [ProductoController::class, 'indexl'])->middleware('can:producto leer');
    Route::get('/productos/{id}', [ProductoController::class, 'showl'])->middleware('can:producto leer');
    Route::post('/clientesa', [ClienteController::class, 'storeA'])->middleware('can:cliente crear');
    Route::get('/clientesa', [ClienteController::class, 'indexA'])->middleware('can:cliente leer');
    Route::post('/mobile/clientes', [MobilePeopleController::class, 'createClient'])->middleware('can:cliente crear');
    Route::put('/mobile/clientes/{cliente}', [MobilePeopleController::class, 'updateClient'])->middleware('can:cliente editar');
    Route::put('/mobile/proveedores/{proveedor}', [MobilePeopleController::class, 'updateSupplier'])->middleware('can:proveedor editar');
    Route::get('/ventas', [VentaControllerApi::class, 'index'])->middleware('can:venta leer');
    Route::get('/ventas/{id}', [VentaControllerApi::class, 'show'])->middleware('can:venta leer');
    Route::get('/sync/productos/updated', [MobileSyncController::class, 'updatedProducts'])->middleware('can:producto leer');
    Route::get('/catalogos/ropa', [MobileSyncController::class, 'clothingCatalog']);
    Route::get('/fardos', [BaleApiController::class, 'index']);
    Route::get('/fardos/{bale}', [BaleApiController::class, 'show']);
    Route::get('/fardos/{bale}/productos', [BaleApiController::class, 'products']);
    Route::post('/fardos/{bale}/productos/{producto}/sumar', [BaleApiController::class, 'addExisting']);
    Route::patch('/fardos/{bale}', [BaleApiController::class, 'update']);
    Route::post('/fardos', [BaleApiController::class, 'store']);
    Route::post('/fardos/{bale}/iniciar', [BaleApiController::class, 'start']);
    Route::post('/fardos/{bale}/finalizar', [BaleApiController::class, 'finalize']);
    Route::post('/fardos/{bale}/foto', [BaleApiController::class, 'uploadPhoto']);
    Route::get('/fardos-proveedores', [BaleApiController::class, 'suppliers']);
    Route::post('/fardos-proveedores', [BaleApiController::class, 'storeSupplier']);
    Route::post('/sync/productos/upload', [MobileSyncController::class, 'uploadProduct'])->middleware('can:producto crear');
    Route::post('/sync/productos/upload-photo/{id}', [MobileSyncController::class, 'uploadPhoto'])->middleware('can:producto editar');
    Route::post('/ventas/offline', [MobileSyncController::class, 'uploadSale'])->middleware('can:venta crear');

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
