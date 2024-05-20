<?php

namespace App\Http\Controllers;

use App\Models\Opcion;
use App\Models\Compra_cab;
use App\Models\Compra_det;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Services\PermisoService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $permisoService;

    public function __construct(PermisoService $permisoService)
    {
        $this->permisoService = $permisoService;
    }
    public function index()
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Compra', 'leer');
        if ($tienePermiso) {
            $heads = [
                'ID', 'Fecha', 'Nro Factura', 'Timbrado', 'Proveedor', 'Condición de Compra', 'Monto Total', 'Usuario', 'Estado', 'Acción'
            ];
            $cabecera = Compra_cab::with('proveedor', 'usuario', 'estadocompra')->get();
            return view('compras.index', compact('cabecera', 'heads'));
        } else {
            return view('sinpermiso.index');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Compra', 'crear');
        if ($tienePermiso) {
            $opcion = Opcion::where('id_dominio', 12)->get();
            $proveedor = Proveedor::where('id_estado', 3)->get();
            return view('compras.create', compact('opcion', 'proveedor'));
        } else {
            return view('sinpermiso.index');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Compra', 'guardar');
        if ($tienePermiso) {
            try {
                DB::beginTransaction();

                $cabecera = new Compra_cab();
                $cabecera->fecha_emision = Carbon::createFromFormat('Y-m-d', $request->get('fechaemision'));

                $cabecera->nro_factura = $request->get('nrofactura');
                $cabecera->id_proveedor = $request->get('id_proveedor');
                $cabecera->condicion_de_compra = $request->get('condicion');
                $cabecera->total_compra = 0;
                $cabecera->id_estado = 1;
                $cabecera->timbrado = $request->get('timbrado');
                $cabecera->id_usuario = auth()->id();
                $cabecera->save();
                $ultimoId = $cabecera->id; //retorna el id de compra

                // Datos para el detalle

                $contador = count($request->input('codigo'));
                $cantidad = $request->input('cantidad');
                $descripcion = $request->input('descripcion');
                $idProductos = $request->input('codigo');
                $precioU = $request->input('precio');
                $tipoImpuesto = $request->input('iva');
                $total = 0;

                for ($i = 0; $i < $contador; $i++) {

                    $montoTotParc = 0;
                    switch ($tipoImpuesto[$i]) {
                        case 0:
                            $montoTotParc = $request->input('exenta')[$i];
                            break;
                        case 5:
                            $montoTotParc = $request->input('cinco')[$i];
                            break;
                        case 10:
                            $montoTotParc = $request->input('diez')[$i];
                            break;
                        default:
                    }

                    $total = $total + $montoTotParc;
                    $detalle = new Compra_det();
                    $detalle->id_compracab = $ultimoId;
                    $detalle->cantidad = $cantidad[$i];
                    $detalle->descripcion = $descripcion[$i];
                    $detalle->id_productos = $idProductos[$i];
                    $detalle->precio_u = $precioU[$i];
                    $detalle->monto = $montoTotParc;
                    $detalle->tipo_impuesto = $tipoImpuesto[$i];

                    $detalle->save();
                    $producto = Producto::find($idProductos[$i]);
                    $producto->stock = $producto->stock + $detalle->cantidad;

                    $producto->save();
                }
                $cabecera->total_compra = $total;

                $cabecera->save();
                DB::commit();
                return redirect()->route('compra.index')->with('success', 'La compra se ha registrado correctamente.');
            } catch (Exception $e) {

                DB::rollBack();
                Log::error($e->getMessage());
                return redirect()->route('compra.index')->with('error', 'Ha ocurrido un error al registrar la compra. Por favor, inténtelo de nuevo.');
            }
        } else {
            return redirect()->route('sinpermiso');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tienePermiso = $this->permisoService->verificarPermiso('Compra', 'borrar');
        if ($tienePermiso) {
            try {
                DB::beginTransaction();

                $cabecera = Compra_cab::find($id);
                $cabecera->id_estado = 2;
                $cabecera->save();

                // Datos para el detalle
                $detalles = Compra_det::where('id_compracab', $id)->get();
                foreach ($detalles as $detalle) {
                    // Accede a los atributos de cada detalle de compra
                    $idprod = $detalle->id_productos;
                    $cantidad = $detalle->cantidad;
                    $producto = Producto::find($idprod);
                    $stock = $producto->stock;
                    $producto->stock = $stock - $cantidad;
                    $producto->save();
                }
                DB::commit();
                return redirect()->route('compra.index')->with('success', 'La compra se ha desactivado correctamente.');
            } catch (Exception $e) {

                DB::rollBack();
                Log::error($e->getMessage());
                return redirect()->route('compra.index')->with('error', 'Ha ocurrido un error al registrar la compra. Por favor, inténtelo de nuevo.');
            }
        } else {
            return redirect()->route('sinpermiso');
        }
    }
    public function getDetalles($id)

    {
        $tienePermiso = $this->permisoService->verificarPermiso('Compra', 'leer');
        if ($tienePermiso) {
            $detalles = Compra_det::where('id_compracab', $id)
                ->with(['productos', 'productos.unidaddemedida'])
                ->get();

            return response()->json($detalles);
        } else {
            return redirect()->route('sinpermiso');
        }
    }
}
