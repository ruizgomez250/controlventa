<?php

namespace App\Http\Controllers;

use App\Helpers\NumberToWords;
use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Pagare;
use App\Models\Producto;
use App\Models\TablaPorcentaje;
use App\Models\TemporalVentaDetalle;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use TCPDF;

class VentaController extends Controller
{
    public function index()
    {
        if (! auth()->user()->can('venta leer')) {
            return view('sinpermiso.index');
        }
        $heads = [
            'ID',
            'Fecha',
            'Nro Factura',
            'Timbrado',
            'Cliente',
            'Condición de Compra',
            'Monto Total',
            'Usuario',
            'Estado',
            'Acción',
        ];
        $cabecera = Venta::with('cliente', 'usuario')->get();

        return view('ventas.index', compact('cabecera', 'heads'));
    }

    public function create()
    {
        if (! auth()->user()->can('venta crear')) {
            return view('sinpermiso.index');
        }
        $clientes = Cliente::where('estado', 1)->get();
        $configuracionQR = Configuracion::where('descripcion', 'qr')->first();
        $porcentajes = TablaPorcentaje::where('estado', 1)->get()->keyBy('cuota');

        return view('ventas.create', compact('clientes', 'configuracionQR', 'porcentajes'));
    }

    public function store(Request $request)
    {
        if (! auth()->user()->can('venta crear')) {
            return redirect()->route('sinpermiso');
        }

        $validated = $request->validate([
            'fechaemision' => ['required', 'date_format:Y-m-d'],
            'nrofactura' => ['nullable', 'string', 'max:50'],
            'id_proveedor' => ['required', 'integer', 'exists:clientes,id'],
            'condicion' => ['required', 'in:CONTADO,CREDITO'],
            'timbrado' => ['nullable', 'string', 'max:50'],
            'codigo' => ['required', 'array', 'min:1'],
            'codigo.*' => ['required', 'integer', 'distinct', 'exists:productos,id'],
            'cantidad' => ['required', 'array'],
            'cantidad.*' => ['required', 'numeric', 'gt:0'],
            'precio' => ['required', 'array'],
            'precio.*' => ['required', 'numeric', 'gte:0'],
            'fechP' => ['nullable', 'array'],
            'fechP.*' => ['date_format:Y-m-d'],
        ]);

        if (
            count($validated['codigo']) !== count($validated['cantidad'])
            || count($validated['codigo']) !== count($validated['precio'])
        ) {
            return back()->withInput()->with('error', 'El detalle de la venta está incompleto.');
        }

        try {
            DB::beginTransaction();

            // ✅ VALIDACIÓN MÍNIMA
            if (! $request->filled('codigo')) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'codigo' => 'No se cargaron productos.',
                ]);
            }

            // ✅ CABECERA
            $cabecera = new Venta();
            $cabecera->fecha_emision = Carbon::createFromFormat('Y-m-d', $request->fechaemision);
            $cabecera->fecha_vencimiento = Carbon::createFromFormat('Y-m-d', $request->fechaemision);
            $cabecera->numero_factura = $request->nrofactura;
            $cabecera->id_cliente = $request->id_proveedor;
            $cabecera->tipo_comprobante = $request->condicion;
            $cabecera->total = 0;
            $cabecera->estado = 1;
            $cabecera->timbrado_factura = $request->timbrado;
            $cabecera->id_usuario = auth()->id();
            $cabecera->save();

            $ultimoId = $cabecera->id;

            // ✅ ARRAYS SEGUROS
            $cantidad = $validated['cantidad'];
            $idProductos = $validated['codigo'];
            $precioU = $validated['precio'];

            $contador = count($idProductos);
            $total = 0;

            // ✅ DETALLE DE VENTA
            for ($i = 0; $i < $contador; $i++) {

                $producto = Producto::query()->lockForUpdate()->findOrFail($idProductos[$i]);
                $cantidadVendida = (float) $cantidad[$i];
                $precioUnitario = (float) $precioU[$i];

                if ((float) $producto->stock < $cantidadVendida) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'cantidad.'.$i => "Stock insuficiente para {$producto->descripcion}.",
                    ]);
                }

                $subtotal = $cantidadVendida * $precioUnitario;
                $porcentajeImpuesto = (float) $producto->impuesto;
                $montoTotParc = round($subtotal * (1 + ($porcentajeImpuesto / 100)), 2);
                $total += $montoTotParc;

                $detalle = new VentaDetalle();
                $detalle->id_venta = $ultimoId;
                $detalle->cantidad = $cantidadVendida;
                $detalle->descripcion = $producto->descripcion;
                $detalle->id_producto = $idProductos[$i];
                $detalle->precio_u = $precioUnitario;
                $detalle->monto = $montoTotParc;
                $detalle->tipo_impuesto = $porcentajeImpuesto;
                $detalle->save();

                // ✅ DESCUENTA STOCK
                $producto->decrement('stock', $cantidadVendida);
            }

            // ✅ ACTUALIZA TOTAL CABECERA
            $cabecera->total = $total;
            $cabecera->save();

            // ✅ PAGARÉ SOLO SI ES CRÉDITO
            if ($request->condicion === 'CREDITO' && $request->filled('fechP')) {

                $fechas = $request->input('fechP');
                $cantpago = count($fechas);
                $monto = $total / max($cantpago, 1);

                foreach ($fechas as $fecha) {
                    $pagare = new Pagare();
                    $pagare->fecha_emision = Carbon::createFromFormat('Y-m-d', $request->fechaemision);
                    $pagare->fecha_vencimiento = Carbon::createFromFormat('Y-m-d', $fecha);
                    $pagare->monto = $monto;
                    $pagare->id_venta = $ultimoId;
                    $pagare->estado = 1;
                    $pagare->save();
                }
            }

            // ✅ CONFIGURACIÓN CORREGIDA (ACÁ TENÍAS UN BUG GRAVE)
            $configuracionvs = Configuracion::where('descripcion', 'ventas')->first();
            $estadov = $configuracionvs ? $configuracionvs->estado : 0;

            DB::commit();

            return redirect()->route('venta.create')->with([
                'success' => 'La venta se ha registrado correctamente.',
                'ultimoId' => $ultimoId,
                'estadov' => $estadov,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al registrar venta.', ['exception' => $e]);

            return redirect()->route('venta.create')->with('error', 'Ha ocurrido un error al registrar la compra. Por favor, inténtelo de nuevo.');
        }
    }

    public function destroy(string $id)
    {
        if (! auth()->user()->can('venta borrar')) {
            return redirect()->route('sinpermiso');
        }
        try {
            DB::beginTransaction();

            $cabecera = Venta::find($id);
            $cabecera->estado = 0;
            $cabecera->save();

            // Datos para el detalle
            $detalles = VentaDetalle::where('id_venta', $id)->get();
            foreach ($detalles as $detalle) {
                // Accede a los atributos de cada detalle de compra
                $idprod = $detalle->id_producto;
                $cantidad = $detalle->cantidad;
                $producto = Producto::find($idprod);
                $stock = $producto->stock;
                $producto->stock = $stock + $cantidad;
                $producto->save();
            }

            if ($cabecera->tipo_comprobante == 'CREDITO') {

                $pagare = Pagare::where('id_venta', $id)->get();
                $contador = count($pagare);

                for ($i = 0; $i < $contador; $i++) {
                    $pagare[$i]->estado = 0;
                    $pagare[$i]->save();
                }
            }
            DB::commit();

            return redirect()->route('venta.index')->with('success', 'La compra se ha desactivado correctamente.');
        } catch (Exception $e) {

            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()->route('venta.index')->with('error', 'Ha ocurrido un error al registrar la compra. Por favor, inténtelo de nuevo.');
        }
    }

    public function getDetalles($id)
    {
        if (! auth()->user()->can('venta leer') && ! auth()->user()->can('caja leer')) {
            return redirect()->route('sinpermiso');
        }
        $detalles = VentaDetalle::where('id_venta', $id)
            ->with(['producto', 'producto.unidaddemedida'])
            ->get();
        $sumaMontos = Caja::where('id_venta', $id)->sum('monto');

        return response()->json([
            'detalles' => $detalles,
            'sumaMontos' => $sumaMontos,
        ]);
    }

    public function pagarCuota(string $id, string $fecha)
    {
        if (! auth()->user()->can('caja crear')) {
            return redirect()->route('sinpermiso');
        }
        try {
            DB::beginTransaction();

            $pagare = Pagare::query()->lockForUpdate()->findOrFail($id);
            if ((int) $pagare->estado === 2) {
                DB::rollBack();

                return response()->json(['error' => 'La cuota ya fue pagada.'], 409);
            }
            $pagare->estado = 2;
            $pagare->fecha_pago = now();
            $pagare->save();

            $count = DB::table('pagare')
                ->where('estado', 1)
                ->where('id_venta', $pagare->id_venta)
                ->count();
            $cabecera = Venta::find($pagare->id_venta);
            if ($count == 0) {
                $cabecera->estado = 2;
                $cabecera->save();
            } else {
                $cabecera->estado = 4;
                $cabecera->save();
            }

            $caja = new Caja();
            $caja->id_usuario = auth()->id();
            $caja->fecha_cobro = now();
            $caja->id_venta = $pagare->id_venta;
            $caja->monto = $pagare->monto;
            $caja->save();

            $pagare->caja = $caja->id;
            $pagare->save();

            $ventaycuotas = DB::table('ventas as v')
                ->select(
                    'v.id as iddeuda',
                    DB::raw('p.id as idcuota'),
                    DB::raw('(SELECT COUNT(p1.id) FROM pagare p1 WHERE p1.id_venta=v.id) as cantidadpago'),
                    'v.estado',
                    DB::raw('(SELECT COUNT(p2.id) FROM pagare p2 WHERE p2.id_venta=v.id AND p2.estado=2) as pagosrealizados'),
                    'v.fecha_emision',
                    'v.id_usuario',
                    'v.id_cliente',
                    'p.monto as cuota',
                    'p.fecha_vencimiento',
                    'p.fecha_pago',
                    'p.estado',
                    'v.total as totaldeuda'
                )
                ->join('pagare as p', 'p.id_venta', '=', 'v.id')
                ->where('p.id', $id)
                ->get();

            DB::commit();

            return response()->json(['ventaycuotas' => $ventaycuotas, 'idcaja' => $caja->id, 'success' => 'Cuota pagada en forma exitosa.'], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'Ha ocurrido un error al procesar la operación.'], 500);
        }
    }

    public function pagarMonto(string $id, float $montoabonado, float $descuento)
    {
        if (! auth()->user()->can('caja crear')) {
            return redirect()->route('sinpermiso');
        }
        try {
            if ($montoabonado <= 0 || $descuento < 0) {
                return response()->json(['error' => 'Los importes no son válidos.'], 422);
            }

            DB::beginTransaction();
            $montocondesc = $montoabonado + $descuento;
            $venta = Venta::query()->lockForUpdate()->findOrFail($id);
            //dd($venta);
            if ($venta->tipo_comprobante == 'CONTADO') {
                $venta->estado = 2;
                $venta->save();
            } else {
                $pagares = Pagare::where('id_venta', $id)
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
            $caja->id_venta = $id;
            $caja->monto = $montoabonado;
            $caja->save();

            DB::commit();

            return response()->json(['caja' => $caja->id, 'success' => 'Cuota pagada en forma exitosa.'], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'Ha ocurrido un error al procesar la operación.'], 500);
        }
    }

    public function getCuotas($id)
    {
        if (! auth()->user()->can('caja leer')) {
            return redirect()->route('sinpermiso');
        }
        $idVenta = $id;

        $ventaycuotas = DB::table('ventas as v')
            ->select(
                'v.id as iddeuda',
                DB::raw('p.id as idcuota'),
                DB::raw('(SELECT COUNT(p1.id) FROM pagare p1 WHERE p1.id_venta=v.id) as cantidadpago'),
                'v.estado',
                DB::raw('(SELECT COUNT(p2.id) FROM pagare p2 WHERE p2.id_venta=v.id AND p2.estado=2) as pagosrealizados'),
                'v.fecha_emision',
                'v.id_usuario',
                'v.id_cliente',
                'p.monto as cuota',
                'p.fecha_vencimiento',
                'p.fecha_pago',
                'p.estado',
                'v.total as totaldeuda'
            )
            ->join('pagare as p', 'p.id_venta', '=', 'v.id')
            ->where('v.id', $idVenta)
            ->get();

        return response()->json($ventaycuotas);
    }

    public function getMontos($id)
    {
        if (! auth()->user()->can('caja leer')) {
            return redirect()->route('sinpermiso');
        }
        $idUsuarioLogueado = auth()->user()->id;

        // Obtener todas las cajas del usuario logueado
        $cajas = Caja::where('id_usuario', $idUsuarioLogueado)
            ->where('id_venta', $id)
            ->get();

        return response()->json($cajas);
    }

    public function indexCaja()
    {
        if (! auth()->user()->can('caja leer')) {
            return view('sinpermiso.index');
        }
        $heads = [
            'ID',
            'Fecha',
            'Nro Factura',
            'Timbrado',
            'Cliente',
            'Condición de Compra',
            'Monto Total',
            'Usuario',
            'Estado',
            'Acción',
        ];
        $cabecera = Venta::with('cliente', 'usuario')
            ->whereIn('estado', [1, 4])
            ->get();

        return view('caja.index', compact('cabecera'));
    }

    public function indexCobradosCaja(Request $request)
    {
        if (! auth()->user()->can('caja leer')) {
            return view('sinpermiso.index');
        }
        $fecha = $request->input('fecha');
        if ($fecha == null) {
            // Aquí puedes agregar lógica adicional según sea necesario para manejar el filtro de fecha
            $fecha = Carbon::now()->toDateString();
        }
        $heads = [
            'ID',
            'Fecha',
            'Nro Factura',
            'Timbrado',
            'Proveedor',
            'Condición de Compra',
            'Monto Total',
            'Usuario',
            'Estado',
            'Acción',
        ];
        $idUsuarioLogueado = auth()->user()->id;

        $cabecera = Venta::select('ventas.*')
            ->join('cajas', 'ventas.id', '=', 'cajas.id_venta')
            ->where('cajas.id_usuario', $idUsuarioLogueado)
            ->whereDate('cajas.fecha_cobro', $fecha)
            ->get();

        return view('caja.cobrado', compact('cabecera', 'fecha'));
    }

    public function generarFactura($id)
    {
        if (! auth()->user()->can('caja leer')) {
            return redirect()->route('sinpermiso');
        }
        $pagare = Pagare::find($id);
        $cantPagosRealizados = Pagare::where('id', '<=', $id)
            ->where('id_venta', $pagare->id_venta)
            ->count();
        $totalPagos = Pagare::where('id_venta', $pagare->id_venta)
            ->count();
        if (! $pagare) {
            abort(404, 'Pagare no encontrado');
        }
        $ancho = 88.9;
        $pdf = new TCPDF('P', 'mm', [$ancho, 139.7], true, 'UTF-8', false);
        //$pdf = new TCPDF('P', 'mm', array(215.9, 355.6), true, 'UTF-8', false);

        $pdf->SetCreator('Your Creator');
        $pdf->SetTitle('Cobranza Ticket Cod.: '.$pagare->caja);
        $pdf->SetMargins(1, 10, 1);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->AddPage();

        $pdf->Cell(0, 10, 'Ticket Cod.: '.$pagare->caja, 0, 1, 'C');
        $pdf->Cell(0, 10, '---------------------------------------------', 0, 1, 'C');
        // Definir el ancho de la celda como el 20% del ancho de la página
        // Establecer la fuente en negrita

        // Imprimir las celdas del título sin relleno y sin bordes visibles
        $pdf->Cell(0.1 * $ancho, 10, 'CANT.', 'B', 0, 'C'); // Sin relleno, sin bordes visibles
        $pdf->Cell(0.47 * $ancho, 10, 'ARTICULO', 'B', 0, 'C');
        $pdf->Cell(0.2 * $ancho, 10, 'PRECIO', 'B', 0, 'C');
        $pdf->Cell(0.2 * $ancho, 10, 'TOTAL', 'B', 1, 'C'); // Última celda, con salto de línea al final

        // Dibujar la línea horizontal

        $pdf->SetFont('helvetica', '', 9);

        $pdf->Cell(0.1 * $ancho, 10, $cantPagosRealizados.'/'.$totalPagos, 'B', 0, 'C');
        $pdf->Cell(0.47 * $ancho, 10, 'CUOTA', 'B', 0, 'C');
        $pdf->Cell(0.2 * $ancho, 10, number_format($pagare->monto, 0, '', '.'), 'B', 0, 'C');
        $pdf->Cell(0.2 * $ancho, 10, number_format($pagare->monto, 0, '', '.'), 'B', 1, 'C');
        $pdf->SetFont('helvetica', 'B', 9);
        $formatter = new NumberToWords();
        $words = $formatter->toWords($pagare->monto, 0);
        $pdf->Cell(0, 10, ' TOTAL: Gs. '.number_format($pagare->monto, 0, '', '.'), 0, 1, 'R');
        $pdf->Cell(0, 10, $words.' Gs.', 0, 1, 'R');
        $pdf->Cell(0, 10, 'GRACIAS POR TU PAGO!!!', 0, 1, 'C');
        $pdf->Output('ordendecompra.pdf', 'I');
        exit;
    }

    public function generarFacturaMonto($id)
    {
        if (! auth()->user()->can('caja leer')) {
            return redirect()->route('sinpermiso');
        }
        $caja = Caja::find($id);
        if (! $caja) {
            abort(404, 'Pago no encontrado');
        }
        $ancho = 88.9;
        $pdf = new TCPDF('P', 'mm', [$ancho, 139.7], true, 'UTF-8', false);
        //$pdf = new TCPDF('P', 'mm', array(215.9, 355.6), true, 'UTF-8', false);

        $pdf->SetCreator('Your Creator');
        $pdf->SetTitle('Cobranza Ticket Cod.: '.$caja->id);
        $pdf->SetMargins(1, 10, 1);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->AddPage();

        $pdf->Cell(0, 10, 'Ticket Cod.: '.$caja->id, 0, 1, 'C');
        $pdf->Cell(0, 10, '---------------------------------------------', 0, 1, 'C');
        // Definir el ancho de la celda como el 20% del ancho de la página
        // Establecer la fuente en negrita

        // Imprimir las celdas del título sin relleno y sin bordes visibles
        $pdf->Cell(0.1 * $ancho, 10, 'CANT.', 'B', 0, 'C'); // Sin relleno, sin bordes visibles
        $pdf->Cell(0.47 * $ancho, 10, 'ARTICULO', 'B', 0, 'C');
        $pdf->Cell(0.2 * $ancho, 10, 'PRECIO', 'B', 0, 'C');
        $pdf->Cell(0.2 * $ancho, 10, 'TOTAL', 'B', 1, 'C'); // Última celda, con salto de línea al final

        // Dibujar la línea horizontal

        $pdf->SetFont('helvetica', '', 9);

        $pdf->Cell(0.1 * $ancho, 10, 1, 'B', 0, 'C');
        $pdf->Cell(0.47 * $ancho, 10, 'PAGO', 'B', 0, 'C');
        $pdf->Cell(0.2 * $ancho, 10, number_format($caja->monto, 0, '', '.'), 'B', 0, 'C');
        $pdf->Cell(0.2 * $ancho, 10, number_format($caja->monto, 0, '', '.'), 'B', 1, 'C');
        $pdf->SetFont('helvetica', 'B', 9);
        $formatter = new NumberToWords();
        $words = $formatter->toWords($caja->monto, 0);
        $pdf->Cell(0, 10, ' TOTAL: Gs. '.number_format($caja->monto, 0, '', '.'), 0, 1, 'R');
        $pdf->Cell(0, 10, $words.' Gs.', 0, 1, 'R');
        $pdf->Cell(0, 10, 'GRACIAS POR TU PAGO!!!', 0, 1, 'C');
        $pdf->Output('ordendecompra.pdf', 'I');
        exit;
    }

    public function cargarDet(Request $request, $id)
    {
        $producto = Producto::where('codigo', $id)
            ->where('stock', '>', 0)
            ->where('tipo', 'venta')
            ->first();

        // Si no se encuentra el producto, retorna un error
        if (! $producto) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        } else {
            $user_id = FacadesAuth::id();

            // Crea un nuevo registro en la tabla temporal_detalle_venta
            $temporalVentaDetalle = TemporalVentaDetalle::create([
                'producto_id' => $producto->id,
                'user_id' => $user_id,  // Usamos el ID del usuario autenticado
            ]);

            // Retorna una respuesta o redirecciona
            return response()->json(['success' => true, 'data' => $temporalVentaDetalle], 201);
        }
        // Obtener el ID del usuario autenticado

    }
}
