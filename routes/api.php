<?php

use App\Http\Controllers\Api\VentaControllerApi;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;
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

Route::get('/productos', [ProductoController::class, 'indexl']);
Route::get('/productos/{id}', [ProductoController::class, 'show']);
Route::post('/clientesa', [ClienteController::class, 'storeA']);
Route::get('/clientesa', [ClienteController::class, 'indexA']);
Route::post('/ventasa', [VentaController::class, 'storeApi']);
Route::post('/ventasasimpli', [VentaController::class, 'storeApiSimplified']);
Route::get('/ventas', [VentaControllerApi::class, 'index']);     // Todas las ventas
Route::get('/ventas/{id}', [VentaControllerApi::class, 'show']); // Venta por ID

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
