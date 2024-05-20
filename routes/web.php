<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\MascotaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\AutocompleteController;
use App\Http\Controllers\CajaReporteController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\TablaPorcentajeController;
use App\Http\Controllers\VentaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::get('/home', function () {
    return view('home');
})->name('home')->middleware('auth');


//acceden los autenticados
Route::middleware('auth')->group(function () {
    Route::resource('/tablaporc', TablaPorcentajeController::class);
    Route::resource('/cliente', ClienteController::class);
    Route::resource('/proveedor', ProveedorController::class);
    Route::resource('/producto', ProductoController::class);
    Route::resource('/mascota', MascotaController::class);
    Route::resource('/compra', CompraController::class);
    Route::resource('/venta', VentaController::class);
    Route::get('/compra/{id}/detalles', [CompraController::class, 'getDetalles']);
    Route::get('/venta/{id}/detalles', [VentaController::class, 'getDetalles']);
    Route::get('/getroles/{id}', [RolController::class, 'getRoles']);
    Route::get('/venta/{id}/cuotas', [VentaController::class, 'getCuotas']);
    Route::get('/caja/venta/{id}/pagomontos', [VentaController::class, 'getMontos']);
    Route::get('/documentopagopdf/{id}', [VentaController::class, 'generarFactura'])->name('documentopagopdf');
    Route::get('/documentopagomontopdf/{id}', [VentaController::class, 'generarFacturaMonto'])->name('documentopagomontopdf');
    Route::get('/cajareportepdf/{desde}/{hasta}/{idusuario?}', [CajaReporteController::class, 'pdffechasusuario']);
    Route::get('/caja', [VentaController::class, 'indexCaja']);
    Route::get('/caja/cobrado/{fecha?}', [VentaController::class, 'indexCobradosCaja']);
    Route::post('/caja/{id}/{fecha}', [VentaController::class, 'pagarCuota']);
    Route::post('/caja/{id}/{montoabonado}/{descuento}', [VentaController::class, 'pagarMonto']);
    Route::resource('/cajareporte', CajaReporteController::class);
    Route::resource('/rol', RolController::class);

    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');


    Route::post('/cargardetalleventa/{id}', [VentaController::class, 'cargarDet'])->name('cargardetalleventa');
    //
});
Route::get('/sinpermiso', function () {
    return view('sinpermiso.index');
})->name('sinpermiso');




Route::get('/mascota/consulta/{id}', [MascotaController::class, 'consulta'])->name('consulta');
Route::get('/mascota/consulta2/{id}', [MascotaController::class, 'consultatwo'])->name('consulta2');
Route::get('/maestro', [MascotaController::class, 'consultamascota'])->name('consultamascota');
Route::get('/cita/optenerdatos/{id}', [CitaController::class, 'obtenerdatos'])->name('obtenerdatos');
Route::get('/cita/propietario/{id}',  [CitaController::class, 'obtenermascotas'])->name('obtenermascotas');
Route::get('/autocomplete',  [AutocompleteController::class, 'autocomplete'])->name('autocomplete');
Route::get('/autocomplete/proveedor',  [AutocompleteController::class, 'proveedor'])->name('obtenerproveedor');
Route::get('/autocomplete/producto',  [AutocompleteController::class, 'getproducto'])->name('obtenerproducto');
Route::post('/guardar-categoria',  [CategoriaController::class, 'storeCat'])->name('guardar-categoria');
Route::delete('/borrar-categoria/{id}', [CategoriaController::class, 'destroy'])->name('borrar-categoria');
Route::post('/autocomplete/obtenercodprod',  [ProductoController::class, 'verifcod'])->name('obtenercodproducto');
//Route::post('/guardar-categoria', 'CategoriaController@storeCat')->name('guardar-categoria');
Route::get('/create', function () {
    return view('create');
});
//Route::post('/guardar-categoria', [CrearCategoriaComponent::class, 'store'])->name('guardar-categoria');



//Route::get('/mascota', 'MascotaController@getRaza');
