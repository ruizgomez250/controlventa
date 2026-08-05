<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Compra_cab;
use App\Models\Pagare;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (in_array(request()->getHost(), config('tenancy.central_hosts'), true)) {
            abort_unless(auth()->user()->empresa_id === null, 403);

            return redirect()->route('empresas.index');
        }

        $hoy = now()->toDateString();
        $anioActual = now()->year;
        $anioAnterior = $anioActual - 1;

        // Ventas del día
        $ventasDelDia = Venta::whereDate('fecha_emision', $hoy)->sum('total');

        // Ventas del mes
        $ventasDelMes = Venta::whereYear('fecha_emision', $anioActual)
            ->whereMonth('fecha_emision', now()->month)
            ->sum('total');

        // Productos más vendidos (últimos 12 meses)
        $productosMasVendidos = DB::table('ventas_detalles')
            ->join('productos', 'ventas_detalles.id_producto', '=', 'productos.id')
            ->join('ventas', 'ventas_detalles.id_venta', '=', 'ventas.id')
            ->select(
                'productos.id',
                'productos.descripcion',
                DB::raw('SUM(ventas_detalles.cantidad) as total_vendido'),
                DB::raw('SUM(ventas_detalles.monto) as total_monto')
            )
            ->where('ventas.fecha_emision', '>=', now()->subMonths(12))
            ->groupBy('productos.id', 'productos.descripcion')
            ->orderByDesc('total_vendido')
            ->limit(10)
            ->get();

        // Productos con bajo stock
        $productosBajoStock = Producto::where('estado', 1)
            ->where(function ($q) {
                $q->whereColumn('stock', '<=', 'stock_minimo')
                    ->orWhere(function ($q2) {
                        $q2->whereNull('stock_minimo')
                            ->orWhere('stock_minimo', 0)
                            ->where('stock', '<=', 5);
                    });
            })
            ->where('stock', '>', 0)
            ->orderBy('stock')
            ->limit(20)
            ->get();

        $cantidadBajoStock = $productosBajoStock->count();

        $productosSinStock = Producto::where('estado', 1)
            ->where('stock', '<=', 0)
            ->count();

        // Cuentas por cobrar (pagares pendientes - estado 1 = pendiente)
        $cuentasPorCobrar = Pagare::where('estado', 1)->sum('monto');
        $cantidadCuotasPendientes = Pagare::where('estado', 1)->count();

        // Últimas ventas
        $ultimasVentas = Venta::with('cliente', 'usuario')
            ->orderByDesc('fecha_emision')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // --- Auditoría / Historial ---
        $ultimasAcciones = Auditoria::with('usuario')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        // --- Cuentas Corrientes (clientes con crédito pendiente) ---
        $clientesConDeuda = DB::table('ventas')
            ->join('pagare', 'ventas.id', '=', 'pagare.id_venta')
            ->join('clientes', 'ventas.id_cliente', '=', 'clientes.id')
            ->where('pagare.estado', 1)
            ->whereIn('ventas.estado', [1, 4])
            ->select(
                'clientes.id',
                'clientes.razonsocial',
                DB::raw('SUM(pagare.monto) as saldo_pendiente')
            )
            ->groupBy('clientes.id', 'clientes.razonsocial')
            ->having('saldo_pendiente', '>', 0)
            ->orderByDesc('saldo_pendiente')
            ->get();

        $cuentasCorrientes = $clientesConDeuda->map(function ($item) {
            // Vencimientos pendientes para este cliente
            $vencimientos = DB::table('pagare')
                ->join('ventas', 'pagare.id_venta', '=', 'ventas.id')
                ->where('ventas.id_cliente', $item->id)
                ->where('pagare.estado', 1)
                ->select('pagare.fecha_vencimiento', 'pagare.monto')
                ->orderBy('pagare.fecha_vencimiento')
                ->get();

            // Pagos realizados (historial)
            $historialPagos = DB::table('pagare')
                ->join('ventas', 'pagare.id_venta', '=', 'ventas.id')
                ->where('ventas.id_cliente', $item->id)
                ->where('pagare.estado', 2)
                ->select('pagare.fecha_pago', 'pagare.monto')
                ->orderByDesc('pagare.fecha_pago')
                ->limit(5)
                ->get();

            return (object) [
                'id' => $item->id,
                'razonsocial' => $item->razonsocial,
                'saldo_pendiente' => (float) $item->saldo_pendiente,
                'vencimientos' => $vencimientos,
                'historial_pagos' => $historialPagos,
            ];
        });

        // --- Datos para gráficos anuales ---
        $comprasAnual = Compra_cab::select(
            DB::raw('YEAR(fecha_emision) as anio'),
            DB::raw('MONTH(fecha_emision) as mes'),
            DB::raw('SUM(total_compra) as total')
        )
            ->whereYear('fecha_emision', '>=', $anioAnterior)
            ->groupBy('anio', 'mes')
            ->orderBy('anio')
            ->orderBy('mes')
            ->get();

        $comprasEsteAnio = array_fill(1, 12, 0);
        $comprasAnhoAnterior = array_fill(1, 12, 0);
        foreach ($comprasAnual as $item) {
            if ((int) $item->anio === $anioActual) {
                $comprasEsteAnio[(int) $item->mes] = (float) $item->total;
            } else {
                $comprasAnhoAnterior[(int) $item->mes] = (float) $item->total;
            }
        }

        $ventasAnual = Venta::select(
            DB::raw('YEAR(fecha_emision) as anio'),
            DB::raw('MONTH(fecha_emision) as mes'),
            DB::raw('SUM(total) as total')
        )
            ->whereYear('fecha_emision', '>=', $anioAnterior)
            ->groupBy('anio', 'mes')
            ->orderBy('anio')
            ->orderBy('mes')
            ->get();

        $ventasEsteAnio = array_fill(1, 12, 0);
        $ventasAnhoAnterior = array_fill(1, 12, 0);
        foreach ($ventasAnual as $item) {
            if ((int) $item->anio === $anioActual) {
                $ventasEsteAnio[(int) $item->mes] = (float) $item->total;
            } else {
                $ventasAnhoAnterior[(int) $item->mes] = (float) $item->total;
            }
        }

        return view('home', compact(
            'ventasDelDia',
            'ventasDelMes',
            'productosMasVendidos',
            'productosBajoStock',
            'cantidadBajoStock',
            'productosSinStock',
            'cuentasPorCobrar',
            'cantidadCuotasPendientes',
            'ultimasVentas',
            'ultimasAcciones',
            'cuentasCorrientes',
            'comprasEsteAnio',
            'comprasAnhoAnterior',
            'ventasEsteAnio',
            'ventasAnhoAnterior',
            'anioActual',
            'anioAnterior'
        ));
    }
}
