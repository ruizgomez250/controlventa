<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Opcion;
use App\Models\Compra_cab;
use App\Models\Compra_det;
use App\Models\Pagare;
use App\Models\Producto;
use App\Models\Proveedor;
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
    public function index()
    {
        if (!auth()->user()->can('compra leer')) {
            return view('sinpermiso.index');
        }
        $heads = [
            'ID', 'Fecha', 'Nro Factura', 'Timbrado', 'Proveedor', 'Condición de Compra', 'Monto Total', 'Usuario', 'Estado', 'Acción'
        ];
        $cabecera = Compra_cab::with('proveedor', 'usuario')->get();
        return view('compras.index', compact('cabecera', 'heads'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->can('compra crear')) {
            return view('sinpermiso.index');
        }
        $opcion = Opcion::where('id_dominio', 12)->get();
        $proveedor = Proveedor::where('estado', 1)->get();
        return view('compras.create', compact('opcion', 'proveedor'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('compra crear')) {
            return redirect()->route('sinpermiso');
        }
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
            $tipoImpuesto = $request->input('tipo_impuesto');
            $total = 0;

            for ($i = 0; $i < $contador; $i++) {

                $montoTotParc = $request->input('total')[$i];

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

            if ($request->condicion === 'CREDITO' && $request->filled('fechP')) {
                $fechas = $request->input('fechP');
                $cantpago = count($fechas);
                $monto = $total / max($cantpago, 1);

                foreach ($fechas as $fecha) {
                    $pagare = new Pagare();
                    $pagare->fecha_emision  = Carbon::createFromFormat('Y-m-d', $request->fechaemision);
                    $pagare->fecha_vencimiento = Carbon::createFromFormat('Y-m-d', $fecha);
                    $pagare->monto = $monto;
                    $pagare->id_compra = $ultimoId;
                    $pagare->estado = 1;
                    $pagare->save();
                }
            }

            DB::commit();
            return redirect()->route('compra.index')->with('success', 'La compra se ha registrado correctamente.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->route('compra.index')->with('error', 'Ha ocurrido un error al registrar la compra. Por favor, inténtelo de nuevo.');
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
        if (!auth()->user()->can('compra borrar')) {
            return redirect()->route('sinpermiso');
        }
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
    }
    public function getDetalles($id)
    {
        if (!auth()->user()->can('compra leer')) {
            return redirect()->route('sinpermiso');
        }
        $detalles = Compra_det::where('id_compracab', $id)
            ->with(['productos', 'productos.unidaddemedida'])
            ->get();

        return response()->json($detalles);
    }

    public function getCuotas($id)
    {
        if (!auth()->user()->can('caja leer')) {
            return redirect()->route('sinpermiso');
        }

        $ventaycuotas = DB::table('compras_cab as v')
            ->select(
                'v.id as iddeuda',
                DB::raw('p.id as idcuota'),
                DB::raw('(SELECT COUNT(p1.id) FROM pagare p1 WHERE p1.id_compra=v.id) as cantidadpago'),
                'v.id_estado as estado',
                DB::raw('(SELECT COUNT(p2.id) FROM pagare p2 WHERE p2.id_compra=v.id AND p2.estado=2) as pagosrealizados'),
                'v.fecha_emision',
                'v.id_usuario',
                'v.id_proveedor',
                'p.monto as cuota',
                'p.fecha_vencimiento',
                'p.fecha_pago',
                'p.estado',
                'v.total_compra as totaldeuda'
            )
            ->join('pagare as p', 'p.id_compra', '=', 'v.id')
            ->where('v.id', $id)
            ->get();
        return response()->json($ventaycuotas);
    }

    public function getMontos($id)
    {
        if (!auth()->user()->can('caja leer')) {
            return redirect()->route('sinpermiso');
        }

        $idUsuarioLogueado = auth()->user()->id;
        $cajas = Caja::where('id_usuario', $idUsuarioLogueado)
            ->where('id_compra', $id)
            ->get();

        return response()->json($cajas);
    }

    public function pagarCuota($id, $fecha)
    {
        if (!auth()->user()->can('caja crear')) {
            return redirect()->route('sinpermiso');
        }
        try {
            DB::beginTransaction();

            $pagare = Pagare::find($id);
            $pagare->estado = 2;
            $pagare->fecha_pago = now();
            $pagare->save();

            $count = DB::table('pagare')
                ->where('estado', 1)
                ->where('id_compra', $pagare->id_compra)
                ->count();
            $cabecera = Compra_cab::find($pagare->id_compra);
            if ($count == 0) {
                $cabecera->id_estado = 2;
                $cabecera->save();
            } else {
                $cabecera->id_estado = 4;
                $cabecera->save();
            }

            $caja = new Caja();
            $caja->id_usuario = auth()->id();
            $caja->fecha_cobro = now();
            $caja->id_compra = $pagare->id_compra;
            $caja->monto = $pagare->monto;
            $caja->save();

            $pagare->caja = $caja->id;
            $pagare->save();

            $ventaycuotas = DB::table('compras_cab as v')
                ->select(
                    'v.id as iddeuda',
                    DB::raw('p.id as idcuota'),
                    DB::raw('(SELECT COUNT(p1.id) FROM pagare p1 WHERE p1.id_compra=v.id) as cantidadpago'),
                    'v.id_estado as estado',
                    DB::raw('(SELECT COUNT(p2.id) FROM pagare p2 WHERE p2.id_compra=v.id AND p2.estado=2) as pagosrealizados'),
                    'v.fecha_emision',
                    'v.id_usuario',
                    'v.id_proveedor',
                    'p.monto as cuota',
                    'p.fecha_vencimiento',
                    'p.fecha_pago',
                    'p.estado',
                    'v.total_compra as totaldeuda'
                )
                ->join('pagare as p', 'p.id_compra', '=', 'v.id')
                ->where('p.id', $id)
                ->get();

            DB::commit();

            return response()->json(['ventaycuotas' => $ventaycuotas, 'idcaja' => $caja->id, 'success' => 'Cuota pagada exitosamente.'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ha ocurrido un error al procesar la operación.'], 500);
        }
    }

    public function pagarMonto($id, $montoabonado, $descuento)
    {
        if (!auth()->user()->can('caja crear')) {
            return redirect()->route('sinpermiso');
        }
        try {
            DB::beginTransaction();
            $montocondesc = $montoabonado + $descuento;
            $compra = Compra_cab::find($id);

            if ($compra->condicion_de_compra == 'CONTADO') {
                $compra->id_estado = 2;
                $compra->save();
            } else {
                $pagares = Pagare::where('id_compra', $id)
                    ->where('estado', 1)
                    ->get();
                foreach ($pagares as $pagare) {
                    $montoP = floatval($pagare->monto);
                    if ($montoP <= $montocondesc) {
                        $pagare->estado = 2;
                    } else {
                        $pagare->monto = $montoP - $montocondesc;
                    }
                    $pagare->fecha_pago = now();
                    if ($montocondesc > 0) {
                        $pagare->save();
                    }
                    $montocondesc = $montocondesc - $montoP;
                }
            }

            $caja = new Caja();
            $caja->id_usuario = auth()->id();
            $caja->fecha_cobro = now();
            $caja->id_compra = $id;
            $caja->monto = $montoabonado;
            $caja->save();

            DB::commit();

            return response()->json(['caja' => $caja->id, 'success' => 'Pago registrado exitosamente.'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ha ocurrido un error al procesar la operación.'], 500);
        }
    }

    public function indexCaja()
    {
        if (!auth()->user()->can('caja leer')) {
            return view('sinpermiso.index');
        }
        $cabecera = Compra_cab::with('proveedor', 'usuario')
            ->whereIn('id_estado', [1, 4])
            ->get();
        return view('caja.compras_index', compact('cabecera'));
    }
}
