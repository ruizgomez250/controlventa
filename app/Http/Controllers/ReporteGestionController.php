<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Compra_cab;
use App\Models\Compra_det;
use App\Models\Cliente;
use App\Models\Proveedor;
use App\Models\User;
use App\Models\Caja;
use App\Models\Pagare;
use App\Models\Cheque;
use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use TCPDF;
use DateTime;

class ReporteGestionController extends Controller
{
    private function getMoneda()
    {
        return Configuracion::where('descripcion', 'moneda')->value('observacion') ?? 'Gs.';
    }

    private function setupPdf($title, $orientation = 'P')
    {
        $pdf = new TCPDF($orientation, 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();
        $pdf->SetCreator('easyStock');
        $pdf->SetTitle($title);
        $pdf->SetY(15);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, $title, 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Ln(3);
        return $pdf;
    }

    private function tableHeader($pdf, $headers, $colWidths)
    {
        $pdf->SetFillColor(1, 0, 0);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 8);
        foreach ($headers as $i => $header) {
            $pdf->Cell($colWidths[$i], 8, $header, 1, 0, 'C', true);
        }
        $pdf->Ln();
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 8);
    }

    // ===================== STOCK REPORTS =====================

    public function stockIndex()
    {
        if (!auth()->user()->can('reporte_stock leer')) {
            return view('sinpermiso.index');
        }
        $productos = Producto::where('estado', 1)->orderBy('descripcion')->get();
        return view('reportes.gestion.stock', compact('productos'));
    }

    public function pdfInventarioActual()
    {
        if (!auth()->user()->can('reporte_stock leer')) {
            return redirect()->route('sinpermiso');
        }
        $productos = Producto::with('categoriaproducto')->where('estado', 1)->orderBy('descripcion')->get();
        $moneda = $this->getMoneda();
        $pdf = $this->setupPdf(__('Inventario Actual'));
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, __('Generado') . ': ' . date('d/m/Y H:i'), 0, 1, 'R');
        $pdf->Ln(2);

        $headers = [__('Código'), __('Producto'), __('Stock Actual'), __('Stock Mín.'), __('Stock Máx.'), __('Costo') . " ($moneda)", __('Venta') . " ($moneda)", __('Valorización')];
        $widths = [22, 50, 18, 18, 18, 25, 25, 28];
        $this->tableHeader($pdf, $headers, $widths);

        $totalValorizacion = 0;
        foreach ($productos as $p) {
            $valorizacion = $p->pcosto * $p->stock;
            $totalValorizacion += $valorizacion;
            $pdf->Cell($widths[0], 7, $p->codigo, 1, 0, 'C');
            $pdf->Cell($widths[1], 7, substr($p->descripcion, 0, 35), 1, 0, 'L');
            $pdf->Cell($widths[2], 7, number_format($p->stock, 2, ',', '.'), 1, 0, 'C');
            $pdf->Cell($widths[3], 7, number_format($p->stock_minimo ?? 0, 2, ',', '.'), 1, 0, 'C');
            $pdf->Cell($widths[4], 7, number_format($p->stock_maximo ?? 0, 2, ',', '.'), 1, 0, 'C');
            $pdf->Cell($widths[5], 7, number_format($p->pcosto ?? 0, 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell($widths[6], 7, number_format($p->pventa ?? 0, 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell($widths[7], 7, number_format($valorizacion, 0, ',', '.'), 1, 1, 'R');
        }
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(array_sum(array_slice($widths, 0, 7)), 8, __('Valorización Total') . ':', 1, 0, 'R');
        $pdf->Cell($widths[7], 8, number_format($totalValorizacion, 0, ',', '.') . " $moneda", 1, 1, 'R');

        $pdf->Output('inventario_actual.pdf', 'I');
        exit;
    }

    public function pdfStockBajo()
    {
        if (!auth()->user()->can('reporte_stock leer')) {
            return redirect()->route('sinpermiso');
        }
        $productos = Producto::where('estado', 1)
            ->where(function ($q) {
                $q->whereColumn('stock', '<=', 'stock_minimo')
                    ->orWhere(function ($q2) {
                        $q2->whereNull('stock_minimo')->where('stock', '<=', 5);
                    })
                    ->orWhere('stock', '<=', 0);
            })
            ->orderBy('stock')
            ->get();
        $moneda = $this->getMoneda();
        $pdf = $this->setupPdf(__('Productos con Stock Bajo'));
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, __('Generado') . ': ' . date('d/m/Y H:i'), 0, 1, 'R');
        $pdf->Ln(2);

        $headers = [__('Código'), __('Producto'), __('Stock Actual'), __('Stock Mín.'), __('Costo'), __('Estado')];
        $widths = [25, 55, 25, 25, 30, 30];
        $this->tableHeader($pdf, $headers, $widths);

        foreach ($productos as $p) {
            $estado = $p->stock <= 0 ? __('Sin stock') : __('Bajo');
            $pdf->Cell($widths[0], 7, $p->codigo, 1, 0, 'C');
            $pdf->Cell($widths[1], 7, substr($p->descripcion, 0, 40), 1, 0, 'L');
            $pdf->Cell($widths[2], 7, number_format($p->stock, 2, ',', '.'), 1, 0, 'C');
            $pdf->Cell($widths[3], 7, number_format($p->stock_minimo ?? 5, 2, ',', '.'), 1, 0, 'C');
            $pdf->Cell($widths[4], 7, number_format($p->pcosto ?? 0, 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell($widths[5], 7, $estado, 1, 1, 'C');
        }

        $pdf->Output('stock_bajo.pdf', 'I');
        exit;
    }

    public function pdfMovimientosStock(Request $request)
    {
        if (!auth()->user()->can('reporte_stock leer')) {
            return redirect()->route('sinpermiso');
        }
        $desde = $request->get('desde', date('Y-m-d'));
        $hasta = $request->get('hasta', date('Y-m-d'));
        $idproducto = $request->get('idproducto');

        $moneda = $this->getMoneda();
        $pdf = $this->setupPdf(__('Movimientos de Stock'));
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, __('Periodo') . ': ' . date('d/m/Y', strtotime($desde)) . ' ' . __('a') . ' ' . date('d/m/Y', strtotime($hasta)), 0, 1, 'R');
        $pdf->Ln(2);

        $headers = [__('Fecha'), __('Producto'), __('Tipo'), __('Cantidad'), __('Precio Unit.'), __('Total')];
        $widths = [22, 50, 20, 20, 30, 38];
        $this->tableHeader($pdf, $headers, $widths);

        // Ventas (salidas)
        $ventasQuery = VentaDetalle::join('ventas', 'ventas_detalles.id_venta', '=', 'ventas.id')
            ->join('productos', 'ventas_detalles.id_producto', '=', 'productos.id')
            ->whereBetween('ventas.fecha_emision', [$desde, $hasta])
            ->where('ventas.estado', '!=', 0);
        if ($idproducto) {
            $ventasQuery->where('ventas_detalles.id_producto', $idproducto);
        }
        $salidas = $ventasQuery->select(
            'ventas.fecha_emision',
            'productos.descripcion',
            'ventas_detalles.cantidad',
            'ventas_detalles.precio_u',
            DB::raw('ventas_detalles.cantidad * ventas_detalles.precio_u as total')
        )->orderBy('ventas.fecha_emision')->get();

        // Compras (entradas)
        $comprasQuery = Compra_det::join('compras_cab', 'compras_det.id_compracab', '=', 'compras_cab.id')
            ->join('productos', 'compras_det.id_productos', '=', 'productos.id')
            ->whereBetween('compras_cab.fecha_emision', [$desde, $hasta]);
        if ($idproducto) {
            $comprasQuery->where('compras_det.id_productos', $idproducto);
        }
        $entradas = $comprasQuery->select(
            'compras_cab.fecha_emision',
            'productos.descripcion as descripcion',
            'compras_det.cantidad',
            'compras_det.precio_u',
            DB::raw('compras_det.cantidad * compras_det.precio_u as total')
        )->orderBy('compras_cab.fecha_emision')->get();

        // Combine and sort by date
        $movimientos = [];
        foreach ($entradas as $e) {
            $movimientos[] = ['fecha' => $e->fecha_emision, 'producto' => $e->descripcion, 'tipo' => __('Entrada'), 'cantidad' => $e->cantidad, 'precio' => $e->precio_u, 'total' => $e->total];
        }
        foreach ($salidas as $s) {
            $movimientos[] = ['fecha' => $s->fecha_emision, 'producto' => $s->descripcion, 'tipo' => __('Salida'), 'cantidad' => $s->cantidad, 'precio' => $s->precio_u, 'total' => $s->total];
        }
        usort($movimientos, function ($a, $b) {
            return strcmp($a['fecha'], $b['fecha']);
        });

        if (empty($movimientos)) {
            $pdf->Cell(0, 10, __('No hay datos disponibles'), 1, 1, 'C');
        } else {
            foreach ($movimientos as $m) {
                $fecha = new DateTime($m['fecha']);
                $pdf->Cell($widths[0], 7, $fecha->format('d/m/Y'), 1, 0, 'C');
                $pdf->Cell($widths[1], 7, substr($m['producto'], 0, 35), 1, 0, 'L');
                $pdf->Cell($widths[2], 7, $m['tipo'], 1, 0, 'C');
                $pdf->Cell($widths[3], 7, number_format($m['cantidad'], 2, ',', '.'), 1, 0, 'C');
                $pdf->Cell($widths[4], 7, number_format($m['precio'], 0, ',', '.'), 1, 0, 'R');
                $pdf->Cell($widths[5], 7, number_format($m['total'], 0, ',', '.'), 1, 1, 'R');
            }
        }

        $pdf->Output('movimientos_stock.pdf', 'I');
        exit;
    }

    public function pdfRotacionProductos()
    {
        if (!auth()->user()->can('reporte_stock leer')) {
            return redirect()->route('sinpermiso');
        }
        $moneda = $this->getMoneda();
        $pdf = $this->setupPdf(__('Rotación de Productos'));
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, __('Últimos 12 meses'), 0, 1, 'R');
        $pdf->Ln(2);

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 8, __('Productos Más Vendidos'), 0, 1, 'L');
        $headers = ['#', __('Producto'), __('Cant. Vendida'), __('Monto Total'), __('Participación')];
        $widths = [8, 65, 30, 40, 37];
        $this->tableHeader($pdf, $headers, $widths);

        $topVendidos = DB::table('ventas_detalles')
            ->join('productos', 'ventas_detalles.id_producto', '=', 'productos.id')
            ->join('ventas', 'ventas_detalles.id_venta', '=', 'ventas.id')
            ->where('ventas.fecha_emision', '>=', now()->subMonths(12))
            ->where('ventas.estado', '!=', 0)
            ->select(
                'productos.descripcion',
                DB::raw('SUM(ventas_detalles.cantidad) as total_cant'),
                DB::raw('SUM(ventas_detalles.monto) as total_monto')
            )
            ->groupBy('productos.descripcion')
            ->orderByDesc('total_cant')
            ->limit(20)
            ->get();

        $totalGeneral = $topVendidos->sum('total_monto');
        $i = 0;
        foreach ($topVendidos as $item) {
            $i++;
            $participacion = $totalGeneral > 0 ? ($item->total_monto / $totalGeneral) * 100 : 0;
            $pdf->Cell($widths[0], 7, $i, 1, 0, 'C');
            $pdf->Cell($widths[1], 7, substr($item->descripcion, 0, 45), 1, 0, 'L');
            $pdf->Cell($widths[2], 7, number_format($item->total_cant, 2, ',', '.'), 1, 0, 'C');
            $pdf->Cell($widths[3], 7, number_format($item->total_monto, 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell($widths[4], 7, number_format($participacion, 1) . '%', 1, 1, 'C');
        }

        $pdf->Ln(8);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 8, __('Productos con Menor Rotación'), 0, 1, 'L');
        $widths2 = [8, 65, 25, 25, 57];
        $this->tableHeader($pdf, [__('#'), __('Producto'), __('Stock Actual'), __('Veces Vendido'), __('Tiempo Promedio en Stock')], $widths2);

        $bajaRotacion = DB::table('productos')
            ->leftJoin('ventas_detalles', 'productos.id', '=', 'ventas_detalles.id_producto')
            ->leftJoin('ventas', 'ventas_detalles.id_venta', '=', 'ventas.id')
            ->where('productos.estado', 1)
            ->select(
                'productos.descripcion',
                'productos.stock',
                DB::raw('COALESCE(SUM(CASE WHEN ventas.estado != 0 AND ventas.fecha_emision >= "' . now()->subMonths(12)->format('Y-m-d') . '" THEN ventas_detalles.cantidad ELSE 0 END), 0) as veces_vendido'),
                DB::raw('DATEDIFF(NOW(), COALESCE(MAX(ventas.fecha_emision), "1900-01-01")) as dias_ultima_venta')
            )
            ->groupBy('productos.id', 'productos.descripcion', 'productos.stock')
            ->having('veces_vendido', '<', 5)
            ->orderBy('veces_vendido')
            ->limit(20)
            ->get();

        $j = 0;
        foreach ($bajaRotacion as $item) {
            if ($item->veces_vendido == 0) continue;
            $j++;
            $tiempo = $item->dias_ultima_venta > 0 ? $item->dias_ultima_venta . ' ' . __('días') : __('Sin ventas');
            $pdf->Cell($widths2[0], 7, $j, 1, 0, 'C');
            $pdf->Cell($widths2[1], 7, substr($item->descripcion, 0, 45), 1, 0, 'L');
            $pdf->Cell($widths2[2], 7, number_format($item->stock, 2, ',', '.'), 1, 0, 'C');
            $pdf->Cell($widths2[3], 7, number_format($item->veces_vendido, 0), 1, 0, 'C');
            $pdf->Cell($widths2[4], 7, $tiempo, 1, 1, 'C');
        }

        $pdf->Output('rotacion_productos.pdf', 'I');
        exit;
    }

    // ===================== SALES REPORTS =====================

    public function ventasIndex()
    {
        if (!auth()->user()->can('reporte_venta leer')) {
            return view('sinpermiso.index');
        }
        $usuarios = User::all();
        $clientes = Cliente::all();
        $productos = Producto::where('estado', 1)->get();
        return view('reportes.gestion.ventas', compact('usuarios', 'clientes', 'productos'));
    }

    public function pdfVentasPeriodo(Request $request)
    {
        if (!auth()->user()->can('reporte_venta leer')) {
            return redirect()->route('sinpermiso');
        }
        $desde = $request->get('desde', date('Y-m-d'));
        $hasta = $request->get('hasta', date('Y-m-d'));
        $moneda = $this->getMoneda();

        $pdf = $this->setupPdf(__('Ventas por Período'), 'L');
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, __('Periodo') . ': ' . date('d/m/Y', strtotime($desde)) . ' ' . __('a') . ' ' . date('d/m/Y', strtotime($hasta)), 0, 1, 'R');
        $pdf->Ln(2);

        // Daily breakdown
        $ventas = Venta::whereBetween('fecha_emision', [$desde, $hasta])
            ->where('estado', '!=', 0)
            ->orderBy('fecha_emision')
            ->get();

        $totalPeriodo = $ventas->sum('total');
        $cantVentas = $ventas->count();

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 8, __('Resumen del Período'), 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(60, 7, __('Total Ventas') . ':', 0, 0);
        $pdf->Cell(60, 7, number_format($totalPeriodo, 0, ',', '.') . " $moneda", 0, 1);
        $pdf->Cell(60, 7, __('Cantidad de Ventas') . ':', 0, 0);
        $pdf->Cell(60, 7, $cantVentas, 0, 1);
        $pdf->Cell(60, 7, __('Promedio por Venta') . ':', 0, 0);
        $pdf->Cell(60, 7, ($cantVentas > 0 ? number_format($totalPeriodo / $cantVentas, 0, ',', '.') : '0') . " $moneda", 0, 1);
        $pdf->Ln(4);

        // Daily detail
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(0, 8, __('Ventas Diarias'), 0, 1, 'L');
        $headers = [__('Fecha'), __('Cant. Ventas'), __('Total'), __('Promedio')];
        $widths = [30, 40, 50, 50];
        $this->tableHeader($pdf, $headers, $widths);

        $daily = $ventas->groupBy('fecha_emision');
        foreach ($daily as $fecha => $items) {
            $f = new DateTime($fecha);
            $totalDia = $items->sum('total');
            $cantDia = count($items);
            $pdf->Cell($widths[0], 7, $f->format('d/m/Y'), 1, 0, 'C');
            $pdf->Cell($widths[1], 7, $cantDia, 1, 0, 'C');
            $pdf->Cell($widths[2], 7, number_format($totalDia, 0, ',', '.') . " $moneda", 1, 0, 'R');
            $pdf->Cell($widths[3], 7, number_format($totalDia / $cantDia, 0, ',', '.') . " $moneda", 1, 1, 'R');
        }

        $pdf->Output('ventas_periodo.pdf', 'I');
        exit;
    }

    public function pdfVentasCliente(Request $request)
    {
        if (!auth()->user()->can('reporte_venta leer')) {
            return redirect()->route('sinpermiso');
        }
        $desde = $request->get('desde', date('Y-m-01'));
        $hasta = $request->get('hasta', date('Y-m-d'));
        $idcliente = $request->get('idcliente');
        $moneda = $this->getMoneda();

        $pdf = $this->setupPdf(__('Ventas por Cliente'));
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, __('Periodo') . ': ' . date('d/m/Y', strtotime($desde)) . ' ' . __('a') . ' ' . date('d/m/Y', strtotime($hasta)), 0, 1, 'R');
        $pdf->Ln(2);

        $query = Venta::whereBetween('fecha_emision', [$desde, $hasta])
            ->where('estado', '!=', 0);
        if ($idcliente) {
            $query->where('id_cliente', $idcliente);
        }
        $ventas = $query->get();

        $grouped = $ventas->groupBy('id_cliente');
        $topClientes = [];
        foreach ($grouped as $idCli => $items) {
            $cliente = Cliente::find($idCli);
            $topClientes[] = (object) [
                'nombre' => $cliente->razonsocial ?? __('Consumidor Final'),
                'total' => $items->sum('total'),
                'cantidad' => count($items),
                'frecuencia' => count($items),
            ];
        }
        usort($topClientes, function ($a, $b) {
            return $b->total <=> $a->total;
        });

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 8, __('Top Clientes por Monto'), 0, 1, 'L');
        $headers = ['#', __('Cliente'), __('Total'), __('Compras'), __('Frecuencia')];
        $widths = [8, 60, 40, 30, 32];
        $this->tableHeader($pdf, $headers, $widths);

        $i = 0;
        foreach (array_slice($topClientes, 0, 20) as $c) {
            $i++;
            $pdf->Cell($widths[0], 7, $i, 1, 0, 'C');
            $pdf->Cell($widths[1], 7, substr($c->nombre, 0, 40), 1, 0, 'L');
            $pdf->Cell($widths[2], 7, number_format($c->total, 0, ',', '.') . " $moneda", 1, 0, 'R');
            $pdf->Cell($widths[3], 7, $c->cantidad, 1, 0, 'C');
            $pdf->Cell($widths[4], 7, $c->frecuencia . ' ' . __('ventas'), 1, 1, 'C');
        }

        if ($idcliente) {
            $pdf->Ln(8);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 8, __('Historial de Compras'), 0, 1, 'L');
            $headers2 = [__('Fecha'), __('Nro Factura'), __('Total'), __('Estado')];
            $widths2 = [30, 40, 50, 50];
            $this->tableHeader($pdf, $headers2, $widths2);
            foreach ($ventas as $v) {
                $f = new DateTime($v->fecha_emision);
                $estado = $v->estado == 2 ? __('Cobrado') : ($v->estado == 1 ? __('Pendiente') : __('Anulado'));
                $pdf->Cell($widths2[0], 7, $f->format('d/m/Y'), 1, 0, 'C');
                $pdf->Cell($widths2[1], 7, $v->numero_factura ?? 'N/A', 1, 0, 'C');
                $pdf->Cell($widths2[2], 7, number_format($v->total, 0, ',', '.') . " $moneda", 1, 0, 'R');
                $pdf->Cell($widths2[3], 7, $estado, 1, 1, 'C');
            }
        }

        $pdf->Output('ventas_cliente.pdf', 'I');
        exit;
    }

    public function pdfVentasProducto(Request $request)
    {
        if (!auth()->user()->can('reporte_venta leer')) {
            return redirect()->route('sinpermiso');
        }
        $desde = $request->get('desde', date('Y-m-01'));
        $hasta = $request->get('hasta', date('Y-m-d'));
        $idproducto = $request->get('idproducto');
        $moneda = $this->getMoneda();

        $pdf = $this->setupPdf(__('Ventas por Producto'));
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, __('Periodo') . ': ' . date('d/m/Y', strtotime($desde)) . ' ' . __('a') . ' ' . date('d/m/Y', strtotime($hasta)), 0, 1, 'R');
        $pdf->Ln(2);

        $query = VentaDetalle::join('ventas', 'ventas_detalles.id_venta', '=', 'ventas.id')
            ->join('productos', 'ventas_detalles.id_producto', '=', 'productos.id')
            ->whereBetween('ventas.fecha_emision', [$desde, $hasta])
            ->where('ventas.estado', '!=', 0);
        if ($idproducto) {
            $query->where('ventas_detalles.id_producto', $idproducto);
        }
        $items = $query->select(
            'productos.descripcion',
            'productos.pcosto',
            'ventas_detalles.id_producto',
            DB::raw('SUM(ventas_detalles.cantidad) as total_cant'),
            DB::raw('SUM(ventas_detalles.monto) as total_monto'),
            DB::raw('SUM(ventas_detalles.cantidad * ventas_detalles.precio_u) as total_ingreso'),
            DB::raw('SUM(ventas_detalles.cantidad * productos.pcosto) as total_costo')
        )
            ->groupBy('productos.descripcion', 'productos.pcosto', 'ventas_detalles.id_producto')
            ->orderByDesc('total_monto')
            ->get();

        $headers = [__('Producto'), __('Cant.'), __('Ingresos'), __('Costo'), __('Ganancia'), __('Margen')];
        $widths = [50, 18, 30, 30, 30, 22];
        $this->tableHeader($pdf, $headers, $widths);

        foreach ($items as $item) {
            $ganancia = $item->total_ingreso - $item->total_costo;
            $margen = $item->total_ingreso > 0 ? ($ganancia / $item->total_ingreso) * 100 : 0;
            $pdf->Cell($widths[0], 7, substr($item->descripcion, 0, 35), 1, 0, 'L');
            $pdf->Cell($widths[1], 7, number_format($item->total_cant, 2, ',', '.'), 1, 0, 'C');
            $pdf->Cell($widths[2], 7, number_format($item->total_ingreso, 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell($widths[3], 7, number_format($item->total_costo, 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell($widths[4], 7, number_format($ganancia, 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell($widths[5], 7, number_format($margen, 1) . '%', 1, 1, 'C');
        }

        $pdf->Output('ventas_producto.pdf', 'I');
        exit;
    }

    // ===================== FINANCIAL REPORTS =====================

    public function financieroIndex()
    {
        if (!auth()->user()->can('reporte_financiero leer')) {
            return view('sinpermiso.index');
        }
        $proveedores = Proveedor::all();
        return view('reportes.gestion.financiero', compact('proveedores'));
    }

    public function pdfCuentasCobrar()
    {
        if (!auth()->user()->can('reporte_financiero leer')) {
            return redirect()->route('sinpermiso');
        }
        $moneda = $this->getMoneda();
        $pdf = $this->setupPdf(__('Cuentas por Cobrar'));

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 8, __('Ventas a Crédito Pendientes'), 0, 1, 'L');

        $pendientes = Pagare::where('estado', 1)
            ->with('venta.cliente')
            ->orderBy('fecha_vencimiento')
            ->get();

        $headers = [__('Cliente'), __('Monto'), __('Vencimiento'), __('Antigüedad'), __('Estado')];
        $widths = [45, 30, 25, 25, 35];
        $this->tableHeader($pdf, $headers, $widths);

        $hoy = new DateTime();
        $totalPendiente = 0;
        foreach ($pendientes as $p) {
            $cliente = $p->venta->cliente->razonsocial ?? __('Consumidor Final');
            $venc = new DateTime($p->fecha_vencimiento);
            $diasDiff = $hoy->diff($venc)->days;
            $vencida = $venc < $hoy;
            $antiguedad = $vencida ? ($diasDiff . ' ' . __('días vencido')) : __('Por vencer');
            $estado = $vencida ? __('Vencido') : __('Pendiente');
            $totalPendiente += $p->monto;

            $pdf->Cell($widths[0], 7, substr($cliente, 0, 30), 1, 0, 'L');
            $pdf->Cell($widths[1], 7, number_format($p->monto, 0, ',', '.') . " $moneda", 1, 0, 'R');
            $pdf->Cell($widths[2], 7, $venc->format('d/m/Y'), 1, 0, 'C');
            $pdf->Cell($widths[3], 7, $antiguedad, 1, 0, 'C');
            $pdf->Cell($widths[4], 7, $estado, 1, 1, 'C');
        }
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(array_sum(array_slice($widths, 0, 4)), 8, __('Total Pendiente') . ':', 1, 0, 'R');
        $pdf->Cell($widths[4], 8, number_format($totalPendiente, 0, ',', '.') . " $moneda", 1, 1, 'R');

        $pdf->Ln(8);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 8, __('Próximos Vencimientos (30 días)'), 0, 1, 'L');
        $proximos = Pagare::where('estado', 1)
            ->whereBetween('fecha_vencimiento', [date('Y-m-d'), date('Y-m-d', strtotime('+30 days'))])
            ->with('venta.cliente')
            ->orderBy('fecha_vencimiento')
            ->get();
        $headers2 = [__('Cliente'), __('Monto'), __('Vencimiento'), __('Días Restantes')];
        $widths2 = [50, 40, 40, 40];
        $this->tableHeader($pdf, $headers2, $widths2);
        foreach ($proximos as $p) {
            $cliente = $p->venta->cliente->razonsocial ?? __('Consumidor Final');
            $venc = new DateTime($p->fecha_vencimiento);
            $diasRest = $hoy->diff($venc)->days;
            $pdf->Cell($widths2[0], 7, substr($cliente, 0, 30), 1, 0, 'L');
            $pdf->Cell($widths2[1], 7, number_format($p->monto, 0, ',', '.') . " $moneda", 1, 0, 'R');
            $pdf->Cell($widths2[2], 7, $venc->format('d/m/Y'), 1, 0, 'C');
            $pdf->Cell($widths2[3], 7, $diasRest . ' ' . __('días'), 1, 1, 'C');
        }

        $pdf->Output('cuentas_cobrar.pdf', 'I');
        exit;
    }

    public function pdfCuentasPagar()
    {
        if (!auth()->user()->can('reporte_financiero leer')) {
            return redirect()->route('sinpermiso');
        }
        $moneda = $this->getMoneda();
        $pdf = $this->setupPdf(__('Cuentas por Pagar'));

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 8, __('Compras Pendientes de Pago'), 0, 1, 'L');

        // Pagares de compras pendientes
        $pendientes = Pagare::where('estado', 1)
            ->whereNotNull('id_compra')
            ->with('compra.proveedor')
            ->orderBy('fecha_vencimiento')
            ->get();

        $headers = [__('Proveedor'), __('Monto'), __('Vencimiento'), __('Estado')];
        $widths = [50, 35, 30, 45];
        $this->tableHeader($pdf, $headers, $widths);

        $totalPagar = 0;
        $hoy = new DateTime();
        foreach ($pendientes as $p) {
            $proveedor = $p->compra->proveedor->razonsocial ?? 'N/A';
            $venc = new DateTime($p->fecha_vencimiento);
            $vencida = $venc < $hoy;
            $estado = $vencida ? __('Vencido') : __('Pendiente');
            $totalPagar += $p->monto;
            $pdf->Cell($widths[0], 7, substr($proveedor, 0, 30), 1, 0, 'L');
            $pdf->Cell($widths[1], 7, number_format($p->monto, 0, ',', '.') . " $moneda", 1, 0, 'R');
            $pdf->Cell($widths[2], 7, $venc->format('d/m/Y'), 1, 0, 'C');
            $pdf->Cell($widths[3], 7, $estado, 1, 1, 'C');
        }
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(115, 8, __('Total Pendiente') . ':', 1, 0, 'R');
        $pdf->Cell(45, 8, number_format($totalPagar, 0, ',', '.') . " $moneda", 1, 1, 'R');

        $pdf->Ln(8);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 8, __('Cheques Emitidos y por Cobrar'), 0, 1, 'L');
        $cheques = Cheque::orderBy('fecha_cobro')->get();
        $headers2 = [__('Tipo'), __('Número'), __('Banco'), __('Monto'), __('Fecha Cobro'), __('Estado')];
        $widths2 = [20, 30, 30, 30, 25, 25];
        $this->tableHeader($pdf, $headers2, $widths2);
        foreach ($cheques as $ch) {
            $pdf->Cell($widths2[0], 7, $ch->tipo == 'cobrar' ? __('Cobrar') : __('Pagar'), 1, 0, 'C');
            $pdf->Cell($widths2[1], 7, $ch->numero_cheque ?? 'N/A', 1, 0, 'C');
            $pdf->Cell($widths2[2], 7, $ch->banco ?? 'N/A', 1, 0, 'C');
            $pdf->Cell($widths2[3], 7, number_format($ch->monto, 0, ',', '.') . " $moneda", 1, 0, 'R');
            $pdf->Cell($widths2[4], 7, (new DateTime($ch->fecha_cobro))->format('d/m/Y'), 1, 0, 'C');
            $pdf->Cell($widths2[5], 7, __(ucfirst($ch->estado)), 1, 1, 'C');
        }

        $pdf->Ln(8);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 8, __('Proveedores con Saldos'), 0, 1, 'L');

        $saldosProv = DB::table('compras_cab')
            ->join('proveedores', 'compras_cab.id_proveedor', '=', 'proveedores.id')
            ->join('pagare', 'pagare.id_compra', '=', 'compras_cab.id')
            ->where('pagare.estado', 1)
            ->select('proveedores.razonsocial', DB::raw('SUM(pagare.monto) as saldo'))
            ->groupBy('proveedores.razonsocial')
            ->get();

        $headers3 = [__('Proveedor'), __('Saldo Pendiente')];
        $widths3 = [80, 80];
        $this->tableHeader($pdf, $headers3, $widths3);
        foreach ($saldosProv as $sp) {
            $pdf->Cell($widths3[0], 7, substr($sp->razonsocial, 0, 40), 1, 0, 'L');
            $pdf->Cell($widths3[1], 7, number_format($sp->saldo, 0, ',', '.') . " $moneda", 1, 1, 'R');
        }

        $pdf->Output('cuentas_pagar.pdf', 'I');
        exit;
    }

    public function pdfMargenGanancia(Request $request)
    {
        if (!auth()->user()->can('reporte_financiero leer')) {
            return redirect()->route('sinpermiso');
        }
        $desde = $request->get('desde', date('Y-m-01'));
        $hasta = $request->get('hasta', date('Y-m-d'));
        $tipo = $request->get('tipo', 'general');
        $moneda = $this->getMoneda();

        $pdf = $this->setupPdf(__('Margen de Ganancia'));
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, __('Periodo') . ': ' . date('d/m/Y', strtotime($desde)) . ' ' . __('a') . ' ' . date('d/m/Y', strtotime($hasta)), 0, 1, 'R');
        $pdf->Ln(2);

        if ($tipo == 'general' || $tipo == 'categoria') {
            $title = $tipo == 'general' ? __('General de la Empresa') : __('Por Categoría');
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 8, $title, 0, 1, 'L');

            $query = VentaDetalle::join('ventas', 'ventas_detalles.id_venta', '=', 'ventas.id')
                ->join('productos', 'ventas_detalles.id_producto', '=', 'productos.id')
                ->whereBetween('ventas.fecha_emision', [$desde, $hasta])
                ->where('ventas.estado', '!=', 0);

            if ($tipo == 'categoria') {
                $query->join('opciones as cat', 'productos.id_categoria', '=', 'cat.id')
                    ->select(
                        'cat.descripcion as grupo',
                        DB::raw('SUM(ventas_detalles.cantidad * ventas_detalles.precio_u) as ingresos'),
                        DB::raw('SUM(ventas_detalles.cantidad * productos.pcosto) as costos')
                    )
                    ->groupBy('cat.descripcion');
            } else {
                $query->select(
                    DB::raw('"General" as grupo'),
                    DB::raw('SUM(ventas_detalles.cantidad * ventas_detalles.precio_u) as ingresos'),
                    DB::raw('SUM(ventas_detalles.cantidad * productos.pcosto) as costos')
                );
            }

            $data = $query->get();

            $headers = [__('Concepto'), __('Ingresos'), __('Costos'), __('Ganancia'), __('Margen')];
            $widths = [50, 30, 30, 30, 30];
            $this->tableHeader($pdf, $headers, $widths);

            foreach ($data as $d) {
                $ganancia = $d->ingresos - $d->costos;
                $margen = $d->ingresos > 0 ? ($ganancia / $d->ingresos) * 100 : 0;
                $pdf->Cell($widths[0], 7, $d->grupo, 1, 0, 'L');
                $pdf->Cell($widths[1], 7, number_format($d->ingresos, 0, ',', '.') . " $moneda", 1, 0, 'R');
                $pdf->Cell($widths[2], 7, number_format($d->costos, 0, ',', '.'), 1, 0, 'R');
                $pdf->Cell($widths[3], 7, number_format($ganancia, 0, ',', '.'), 1, 0, 'R');
                $pdf->Cell($widths[4], 7, number_format($margen, 1) . '%', 1, 1, 'C');
            }
        }

        if ($tipo == 'general' || $tipo == 'producto') {
            if ($tipo == 'general') $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 8, __('Por Producto'), 0, 1, 'L');

            $prodQuery = VentaDetalle::join('ventas', 'ventas_detalles.id_venta', '=', 'ventas.id')
                ->join('productos', 'ventas_detalles.id_producto', '=', 'productos.id')
                ->whereBetween('ventas.fecha_emision', [$desde, $hasta])
                ->where('ventas.estado', '!=', 0)
                ->select(
                    'productos.descripcion',
                    DB::raw('SUM(ventas_detalles.cantidad * ventas_detalles.precio_u) as ingresos'),
                    DB::raw('SUM(ventas_detalles.cantidad * productos.pcosto) as costos')
                )
                ->groupBy('productos.descripcion')
                ->orderByDesc('ingresos')
                ->limit(30)
                ->get();

            $headers2 = [__('Producto'), __('Ingresos'), __('Costos'), __('Ganancia'), __('Margen')];
            $widths2 = [50, 30, 30, 30, 30];
            $this->tableHeader($pdf, $headers2, $widths2);
            foreach ($prodQuery as $p) {
                $ganancia = $p->ingresos - $p->costos;
                $margen = $p->ingresos > 0 ? ($ganancia / $p->ingresos) * 100 : 0;
                $pdf->Cell($widths2[0], 7, substr($p->descripcion, 0, 35), 1, 0, 'L');
                $pdf->Cell($widths2[1], 7, number_format($p->ingresos, 0, ',', '.') . " $moneda", 1, 0, 'R');
                $pdf->Cell($widths2[2], 7, number_format($p->costos, 0, ',', '.'), 1, 0, 'R');
                $pdf->Cell($widths2[3], 7, number_format($ganancia, 0, ',', '.'), 1, 0, 'R');
                $pdf->Cell($widths2[4], 7, number_format($margen, 1) . '%', 1, 1, 'C');
            }
        }

        $pdf->Output('margen_ganancia.pdf', 'I');
        exit;
    }

    // ===================== MANAGEMENT REPORTS =====================

    public function managementIndex()
    {
        if (!auth()->user()->can('reporte_management leer')) {
            return view('sinpermiso.index');
        }
        $usuarios = User::all();
        return view('reportes.gestion.management', compact('usuarios'));
    }

    public function pdfRendimientoVendedores(Request $request)
    {
        if (!auth()->user()->can('reporte_management leer')) {
            return redirect()->route('sinpermiso');
        }
        $desde = $request->get('desde', date('Y-m-01'));
        $hasta = $request->get('hasta', date('Y-m-d'));
        $moneda = $this->getMoneda();

        $pdf = $this->setupPdf(__('Rendimiento de Vendedores'));
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, __('Periodo') . ': ' . date('d/m/Y', strtotime($desde)) . ' ' . __('a') . ' ' . date('d/m/Y', strtotime($hasta)), 0, 1, 'R');
        $pdf->Ln(2);

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 8, __('Ventas por Usuario'), 0, 1, 'L');

        $ventasPorUser = Venta::whereBetween('fecha_emision', [$desde, $hasta])
            ->where('estado', '!=', 0)
            ->get()
            ->groupBy('id_usuario');

        $headers = [__('Vendedor'), __('Cant. Ventas'), __('Total Vendido'), __('Promedio'), __('Participación')];
        $widths = [40, 25, 35, 35, 35];
        $this->tableHeader($pdf, $headers, $widths);

        $totalGeneral = 0;
        $dataUsers = [];
        foreach ($ventasPorUser as $idUser => $items) {
            $user = User::find($idUser);
            $nombre = $user->name ?? 'N/A';
            $total = $items->sum('total');
            $cant = count($items);
            $totalGeneral += $total;
            $dataUsers[] = (object)[
                'nombre' => $nombre,
                'cantidad' => $cant,
                'total' => $total,
                'promedio' => $cant > 0 ? $total / $cant : 0,
            ];
        }
        usort($dataUsers, function ($a, $b) {
            return $b->total <=> $a->total;
        });

        foreach ($dataUsers as $du) {
            $participacion = $totalGeneral > 0 ? ($du->total / $totalGeneral) * 100 : 0;
            $pdf->Cell($widths[0], 7, $du->nombre, 1, 0, 'L');
            $pdf->Cell($widths[1], 7, $du->cantidad, 1, 0, 'C');
            $pdf->Cell($widths[2], 7, number_format($du->total, 0, ',', '.') . " $moneda", 1, 0, 'R');
            $pdf->Cell($widths[3], 7, number_format($du->promedio, 0, ',', '.') . " $moneda", 1, 0, 'R');
            $pdf->Cell($widths[4], 7, number_format($participacion, 1) . '%', 1, 1, 'C');
        }

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(65, 8, __('TOTAL GENERAL'), 1, 0, 'R');
        $pdf->Cell(35, 8, number_format($totalGeneral, 0, ',', '.') . " $moneda", 1, 1, 'R');

        $pdf->Output('rendimiento_vendedores.pdf', 'I');
        exit;
    }

    public function pdfAnalisisClientes(Request $request)
    {
        if (!auth()->user()->can('reporte_management leer')) {
            return redirect()->route('sinpermiso');
        }
        $desde = $request->get('desde', date('Y-m-01'));
        $hasta = $request->get('hasta', date('Y-m-d'));
        $moneda = $this->getMoneda();

        $pdf = $this->setupPdf(__('Análisis de Clientes'));
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, __('Periodo') . ': ' . date('d/m/Y', strtotime($desde)) . ' ' . __('a') . ' ' . date('d/m/Y', strtotime($hasta)), 0, 1, 'R');
        $pdf->Ln(2);

        $todosClientes = Cliente::where('estado', 1)->count();
        $clientesConVentas = Venta::whereBetween('fecha_emision', [$desde, $hasta])
            ->where('estado', '!=', 0)
            ->distinct('id_cliente')
            ->count('id_cliente');
        $nuevos = Cliente::whereBetween('created_at', [$desde, $hasta])->count();
        $recurrentes = $clientesConVentas - $nuevos;
        if ($recurrentes < 0) $recurrentes = 0;

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 8, __('Nuevos vs Recurrentes'), 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(80, 7, __('Total Clientes Activos') . ':', 0, 0);
        $pdf->Cell(40, 7, $todosClientes, 0, 1);
        $pdf->Cell(80, 7, __('Clientes Nuevos (período)') . ':', 0, 0);
        $pdf->Cell(40, 7, $nuevos, 0, 1);
        $pdf->Cell(80, 7, __('Clientes Recurrentes') . ':', 0, 0);
        $pdf->Cell(40, 7, $recurrentes, 0, 1);
        $pdf->Ln(4);

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 8, __('Valor Promedio de Compra'), 0, 1, 'L');
        $ventas = Venta::whereBetween('fecha_emision', [$desde, $hasta])
            ->where('estado', '!=', 0)
            ->get();
        $totalVentas = $ventas->sum('total');
        $cantVentas = $ventas->count();
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(80, 7, __('Total Ventas') . ':', 0, 0);
        $pdf->Cell(40, 7, number_format($totalVentas, 0, ',', '.') . " $moneda", 0, 1);
        $pdf->Cell(80, 7, __('Promedio por Venta') . ':', 0, 0);
        $pdf->Cell(40, 7, ($cantVentas > 0 ? number_format($totalVentas / $cantVentas, 0, ',', '.') : '0') . " $moneda", 0, 1);
        $pdf->Cell(80, 7, __('Promedio por Cliente') . ':', 0, 0);
        $pdf->Cell(40, 7, ($clientesConVentas > 0 ? number_format($totalVentas / $clientesConVentas, 0, ',', '.') : '0') . " $moneda", 0, 1);
        $pdf->Ln(4);

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 8, __('Top Clientes por Valor'), 0, 1, 'L');
        $topClientes = Venta::whereBetween('fecha_emision', [$desde, $hasta])
            ->where('estado', '!=', 0)
            ->get()
            ->groupBy('id_cliente')
            ->map(function ($items, $idCli) {
                $cliente = Cliente::find($idCli);
                return (object)[
                    'nombre' => $cliente->razonsocial ?? __('Consumidor Final'),
                    'total' => $items->sum('total'),
                    'cantidad' => count($items),
                ];
            })
            ->sortByDesc('total')
            ->take(20);

        $headers = ['#', __('Cliente'), __('Total'), __('Compras'), __('Valor Promedio')];
        $widths = [8, 55, 35, 20, 42];
        $this->tableHeader($pdf, $headers, $widths);
        $i = 0;
        foreach ($topClientes as $tc) {
            $i++;
            $prom = $tc->cantidad > 0 ? $tc->total / $tc->cantidad : 0;
            $pdf->Cell($widths[0], 7, $i, 1, 0, 'C');
            $pdf->Cell($widths[1], 7, substr($tc->nombre, 0, 35), 1, 0, 'L');
            $pdf->Cell($widths[2], 7, number_format($tc->total, 0, ',', '.') . " $moneda", 1, 0, 'R');
            $pdf->Cell($widths[3], 7, $tc->cantidad, 1, 0, 'C');
            $pdf->Cell($widths[4], 7, number_format($prom, 0, ',', '.') . " $moneda", 1, 1, 'R');
        }

        $pdf->Output('analisis_clientes.pdf', 'I');
        exit;
    }
}
