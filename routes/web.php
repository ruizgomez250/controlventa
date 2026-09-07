<?php

use App\Http\Controllers\AutocompleteController;
use App\Http\Controllers\CajaReporteController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ChequeController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\EntregaInsumoController;
use App\Http\Controllers\SifenController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\ImpuestoController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProductoreporteController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReporteVentaController;
use App\Http\Controllers\ReporteVentaNuevoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\TablaPorcentajeController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\BaleController;

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
// Redirigir raíz al login
Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas del panel de administración multi-empresa (dominio principal)
// Rutas principales de la aplicación (funcionan en localhost/dominio principal)
Auth::routes(['register' => false]);

Route::middleware(['central', 'throttle:10,1'])->group(function () {
    Route::get('/suscribirme', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/suscribirme', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/activar/{token}', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/activar/{token}', [OnboardingController::class, 'store'])->name('onboarding.store');
});

Route::middleware(['auth', 'central.admin'])->group(function () {
    Route::resource('empresas', App\Http\Controllers\EmpresaController::class)->except('show');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home')->middleware('auth');

//acceden los autenticados
Route::middleware('auth')->group(function () {
    Route::resource('impuestos', ImpuestoController::class);
    Route::resource('/gasto', GastoController::class);
    Route::resource('/tablaporc', TablaPorcentajeController::class);
    Route::resource('/cliente', ClienteController::class);
    Route::resource('/proveedor', ProveedorController::class);
    Route::resource('/producto', ProductoController::class);
    Route::resource('/bales', BaleController::class)->except(['destroy']);
    Route::post('/bales/{bale}/finalize', [BaleController::class, 'finalize'])->name('bales.finalize');
    Route::get('/qrproductover', [ProductoController::class, 'createReporte'])->name('qrproductover');
    Route::get('/qrproducto/{id}', [ProductoController::class, 'qrproducto'])->name('qrproducto');
    Route::get('/barcodeproducto/{id}', [ProductoController::class, 'barcodeproducto'])->name('barcodeproducto');
    Route::resource('/compra', CompraController::class);
    Route::get('/venta/emitidas', [VentaController::class, 'indexEmitidas'])->name('venta.emitidas');
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
    Route::get('/caja', [VentaController::class, 'indexCaja'])->name('caja.index');
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

    Route::get('/sifen/estado', [SifenController::class, 'estado'])->name('sifen.estado');
    Route::get('/sifen/consultar-cdc/{id}', [SifenController::class, 'consultarCDC'])->name('sifen.consultarCDC');
    Route::get('/sifen/consultar-ruc', [SifenController::class, 'consultarRUC'])->name('sifen.consultarRUC');
    Route::post('/sifen/reemitir/{venta}', [SifenController::class, 'reemitir'])->name('sifen.reemitir');
    Route::post('/sifen/inutilizar', [SifenController::class, 'inutilizar'])->name('sifen.inutilizar');
    Route::post('/sifen/validar-ruc', [SifenController::class, 'validarRUC'])->name('sifen.validarRUC');

    Route::prefix('geografico')->name('geografico.')->group(function () {
        Route::get('departamentos', [\App\Http\Controllers\GeograficoController::class, 'departamentos'])->name('departamentos');
        Route::get('distritos', [\App\Http\Controllers\GeograficoController::class, 'distritos'])->name('distritos');
        Route::get('ciudades', [\App\Http\Controllers\GeograficoController::class, 'ciudades'])->name('ciudades');
    });

    Route::resource('/persona', PersonaController::class);
    Route::resource('/entrega_insumo', EntregaInsumoController::class);
    Route::get('/entrega_insumo/{id}/detalles', [EntregaInsumoController::class, 'getDetalles']);
    Route::get('/entrega_insumo/{id}/comprobante', [EntregaInsumoController::class, 'generarComprobante'])->name('entrega_insumo.comprobante');
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
Route::get('/autocomplete/productoventa',  [AutocompleteController::class, 'getproducto'])->name('obtenerproductoventa');
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

Route::get('/idioma/{locale}', function ($locale) {
    if (in_array($locale, ['es', 'en'])) {
        session(['app_locale' => $locale]);
        app()->setLocale($locale);
    }

    return redirect()->back();
})->name('idioma');
