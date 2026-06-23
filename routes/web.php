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
use App\Http\Controllers\ChequeController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\ProductoreporteController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\TablaPorcentajeController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\ImpuestoController;
use App\Http\Controllers\ReporteVentaController;
use App\Http\Controllers\ReporteVentaNuevoController;

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

// Depuración - verificar conexión y usuarios
Route::get('/debug-login', function () {
    $dbName = DB::connection()->getDatabaseName();
    $users = \App\Models\User::all();
    $output = "DB: {$dbName}<br>";
    $output .= "Usuarios (" . $users->count() . "):<br>";
    foreach ($users as $u) {
        $hashPreview = strlen($u->password) . ' chars, starts with: ' . substr($u->password, 0, 10);
        $output .= "- {$u->email} | password: {$hashPreview}<br>";
    }
    return $output;
});

// Redirigir raíz al login
Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas del panel de administración multi-empresa (dominio principal)
Route::resource('empresas', App\Http\Controllers\EmpresaController::class);

// Rutas principales de la aplicación (funcionan en localhost/dominio principal)
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/home', function () {
    return view('home');
})->name('home')->middleware('auth');

//acceden los autenticados
Route::middleware('auth')->group(function () {
    Route::resource('impuestos', ImpuestoController::class);
    Route::resource('/gasto', GastoController::class);
    Route::resource('/tablaporc', TablaPorcentajeController::class);
    Route::resource('/cliente', ClienteController::class);
    Route::resource('/proveedor', ProveedorController::class);
    Route::resource('/producto', ProductoController::class);
    Route::get('/qrproductover', [ProductoController::class, 'createReporte'])->name('qrproductover');
    Route::get('/qrproducto/{id}', [ProductoController::class, 'qrproducto'])->name('qrproducto');
    Route::get('/barcodeproducto/{id}', [ProductoController::class, 'barcodeproducto'])->name('barcodeproducto');
    Route::resource('/compra', CompraController::class);
    Route::resource('/venta', VentaController::class);

    Route::get('/compra/{id}/detalles', [CompraController::class, 'getDetalles']);
    Route::get('/venta/{id}/detalles', [VentaController::class, 'getDetalles']);
    Route::get('/getroles/{id}', [RolController::class, 'getRoles']);
    Route::post('/permisos/crear', [RolController::class, 'storePermission'])->name('permisos.crear');
    Route::get('/rol/crear-usuario', [RolController::class, 'createUser'])->name('rol.createUser');
    Route::post('/rol/crear-usuario', [RolController::class, 'storeUser'])->name('rol.storeUser');
    Route::get('/venta/{id}/cuotas', [VentaController::class, 'getCuotas']);
    Route::get('/compra/{id}/cuotas', [CompraController::class, 'getCuotas']);
    Route::get('/caja/venta/{id}/pagomontos', [VentaController::class, 'getMontos']);
    Route::get('/caja/compra/{id}/pagomontos', [CompraController::class, 'getMontos']);
    Route::get('/documentopagopdf/{id}', [VentaController::class, 'generarFactura'])->name('documentopagopdf');
    Route::get('/documentopagomontopdf/{id}', [VentaController::class, 'generarFacturaMonto'])->name('documentopagomontopdf');
    Route::get('/cajareportepdf/{desde}/{hasta}/{idusuario?}', [CajaReporteController::class, 'pdffechasusuario']);
    Route::get('/caja', [VentaController::class, 'indexCaja']);
    Route::get('/caja/compras', [CompraController::class, 'indexCaja']);
    Route::get('/gananciareportepdf/{desde}/{hasta}/{idproducto?}', [ProductoreporteController::class, 'pdfganancia']);
    Route::get('/ventareportepdf/{desde}/{hasta}/{idusuario?}', [VentaController::class, 'pdffechasusuario']);
    Route::get('/caja/cobrado/{fecha?}', [VentaController::class, 'indexCobradosCaja']);
    Route::post('/caja/{id}/{fecha}', [VentaController::class, 'pagarCuota']);
    Route::post('/caja/{id}/{montoabonado}/{descuento}', [VentaController::class, 'pagarMonto']);
    Route::post('/caja/compra/{id}/{fecha}', [CompraController::class, 'pagarCuota']);
    Route::post('/caja/compra/{id}/{montoabonado}/{descuento}', [CompraController::class, 'pagarMonto']);
    Route::resource('/cajareporte', CajaReporteController::class);
    Route::resource('/rol', RolController::class);
    Route::resource('/configuracion', ConfiguracionController::class);
    Route::get('/reportes/vendidos', [ReporteVentaNuevoController::class, 'index'])->name('reportes.vendidos');
    Route::get('/reporteventasnuevo/{fechadesde}/{fechahasta}/{idusuario?}', [ReporteVentaController::class, 'generarReporte']);

    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    Route::post('/cargardetalleventa/{id}', [VentaController::class, 'cargarDet'])->name('cargardetalleventa');
});
Route::get('/sinpermiso', function () {
    return view('sinpermiso.index');
})->name('sinpermiso');

Route::get('/autocomplete',  [AutocompleteController::class, 'autocomplete'])->name('autocomplete');
Route::get('/autocomplete/proveedor',  [AutocompleteController::class, 'proveedor'])->name('obtenerproveedor');
Route::get('/autocomplete/producto',  [AutocompleteController::class, 'getproducto'])->name('obtenerproducto');
Route::post('/guardar-categoria',  [CategoriaController::class, 'storeCat'])->name('guardar-categoria');
Route::post('/guardar-unidad',  [CategoriaController::class, 'storeCat'])->name('guardar-unidad');
Route::delete('/borrar-categoria/{id}', [CategoriaController::class, 'destroy'])->name('borrar-categoria');
Route::delete('/borrar-unidad/{id}', [CategoriaController::class, 'destroy'])->name('borrar-unidad');
Route::post('/autocomplete/obtenercodprod',  [ProductoController::class, 'verifcod'])->name('obtenercodproducto');
Route::post('/autocomplete/obtenercodtemporal',  [ProductoController::class, 'desdetemporal'])->name('obtenercodtemporal');
Route::get('/create', function () {
    return view('create');
});
Route::resource('cheques', ChequeController::class);
