@extends('adminlte::page')

@section('title', 'Panel principal')

@section('content_header')
    <div class="dashboard-title-container">
        <div>
            <h1 class="dashboard-title">
                <i class="fas fa-chart-line"></i>
                {{ __('Panel principal') }}
            </h1>

            <p class="dashboard-subtitle">
                {{ __('Resumen general del sistema de ventas') }}
            </p>
        </div>

        <div class="dashboard-date">
            <i class="far fa-calendar-alt"></i>
            {{ now()->format('d/m/Y') }}
        </div>
    </div>

    @if(!empty($esAdminCentral) && \Illuminate\Support\Facades\Route::has('empresas.index'))
        <div class="dashboard-admin-bar">
            <i class="fas fa-building"></i>
            {{ __('Administración central') }} ·
            <a href="{{ route('empresas.index') }}">
                {{ __('Gestionar empresas') }}
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    @endif
@stop

@section('content')

    @php
        /*
        |--------------------------------------------------------------------------
        | Normalización de variables
        |--------------------------------------------------------------------------
        | Esto evita errores cuando el controlador no envía alguna colección.
        */

        $ventasDelDiaValor = (float) ($ventasDelDia ?? 0);
        $ventasDelMesValor = (float) ($ventasDelMes ?? 0);

        $productosMasVendidosLista = collect($productosMasVendidos ?? []);
        $productosBajoStockLista = collect($productosBajoStock ?? []);
        $ultimasVentasLista = collect($ultimasVentas ?? []);
        $cuentasCorrientesLista = collect($cuentasCorrientes ?? []);

        $cantidadProductosBajoStock = $productosBajoStockLista->count();
        $cantidadCuentasCorrientes = $cuentasCorrientesLista->count();
    @endphp

    {{-- MENSAJES DEL SISTEMA --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-1"></i>
            {{ session('success') }}

            <button
                type="button"
                class="close"
                data-dismiss="alert"
                aria-label="{{ __('Cerrar') }}"
            >
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle mr-1"></i>
            {{ session('error') }}

            <button
                type="button"
                class="close"
                data-dismiss="alert"
                aria-label="{{ __('Cerrar') }}"
            >
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <div class="font-weight-bold mb-2">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                {{ __('Se encontraron errores:') }}
            </div>

            <ul class="mb-0 pl-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- TARJETAS PRINCIPALES --}}
    <div class="row">

        {{-- VENTAS DEL DÍA --}}
        <div class="col-xl-3 col-md-6 col-sm-12">
            <div class="dashboard-summary-card card-primary-modern">
                <div class="summary-icon">
                    <i class="fas fa-cash-register"></i>
                </div>

                <div class="summary-content">
                    <span class="summary-label">
                        {{ __('Ventas del día') }}
                    </span>

                    <strong class="summary-value">
                        Gs. {{ number_format($ventasDelDiaValor, 0, ',', '.') }}
                    </strong>

                    @if(\Illuminate\Support\Facades\Route::has('ventas.index'))
                        <a
                            href="{{ route('ventas.index') }}"
                            class="summary-link"
                        >
                            {{ __('Ver ventas') }}
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- VENTAS DEL MES --}}
        <div class="col-xl-3 col-md-6 col-sm-12">
            <div class="dashboard-summary-card card-success-modern">
                <div class="summary-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>

                <div class="summary-content">
                    <span class="summary-label">
                        {{ __('Ventas del mes') }}
                    </span>

                    <strong class="summary-value">
                        Gs. {{ number_format($ventasDelMesValor, 0, ',', '.') }}
                    </strong>

                    @if(\Illuminate\Support\Facades\Route::has('ventas.index'))
                        <a
                            href="{{ route('ventas.index') }}"
                            class="summary-link"
                        >
                            {{ __('Ver detalle') }}
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- PRODUCTOS CON BAJO STOCK --}}
        <div class="col-xl-3 col-md-6 col-sm-12">
            <div class="dashboard-summary-card card-danger-modern">
                <div class="summary-icon">
                    <i class="fas fa-box-open"></i>
                </div>

                <div class="summary-content">
                    <span class="summary-label">
                        {{ __('Productos con bajo stock') }}
                    </span>

                    <strong class="summary-value">
                        {{ number_format($cantidadProductosBajoStock, 0, ',', '.') }}
                    </strong>

                    @if(\Illuminate\Support\Facades\Route::has('productos.index'))
                        <a
                            href="{{ route('productos.index') }}"
                            class="summary-link"
                        >
                            {{ __('Ver productos') }}
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- CUENTAS CORRIENTES --}}
        <div class="col-xl-3 col-md-6 col-sm-12">
            <div class="dashboard-summary-card card-warning-modern">
                <div class="summary-icon">
                    <i class="fas fa-credit-card"></i>
                </div>

                <div class="summary-content">
                    <span class="summary-label">
                        {{ __('Cuentas pendientes') }}
                    </span>

                    <strong class="summary-value">
                        {{ number_format($cantidadCuentasCorrientes, 0, ',', '.') }}
                    </strong>

                    @if(\Illuminate\Support\Facades\Route::has('caja.index'))
                        <a
                            href="{{ route('caja.index') }}"
                            class="summary-link"
                        >
                            {{ __('Ir a cobros') }}
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- GRÁFICOS DEL DASHBOARD --}}
    <div class="row">

        {{-- VENTAS VS COMPRAS POR MES --}}
        <div class="col-lg-8">
            <div class="dashboard-card">
                <div class="card-header-dash">
                    <h3>
                        <i class="fas fa-chart-bar text-modern-primary"></i>
                        {{ __('Ventas vs Compras') }} · {{ $anioActual ?? now()->year }}
                    </h3>
                </div>

                <div class="dashboard-chart-container">
                    <canvas id="chartVentasCompras" height="120"></canvas>
                </div>
            </div>
        </div>

        {{-- PRODUCTOS MÁS VENDIDOS (DISTRIBUCIÓN) --}}
        <div class="col-lg-4">
            <div class="dashboard-card">
                <div class="card-header-dash">
                    <h3>
                        <i class="fas fa-chart-pie text-modern-success"></i>
                        {{ __('Productos más vendidos') }}
                    </h3>
                </div>

                <div class="dashboard-chart-container">
                    @if($productosMasVendidosLista->isEmpty())
                        <div class="empty-dashboard">
                            <i class="fas fa-chart-pie"></i>
                            <span>{{ __('No hay ventas registradas.') }}</span>
                        </div>
                    @else
                        <canvas id="chartProductosVendidos" height="170"></canvas>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- EVOLUCIÓN DE VENTAS (AÑO ACTUAL VS ANTERIOR) --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="dashboard-card">
                <div class="card-header-dash">
                    <h3>
                        <i class="fas fa-chart-line text-modern-warning"></i>
                        {{ __('Evolución de ventas por mes') }} ·
                        {{ $anioAnterior ?? (($anioActual ?? now()->year) - 1) }}
                        vs {{ $anioActual ?? now()->year }}
                    </h3>
                </div>

                <div class="dashboard-chart-container">
                    <canvas id="chartEvolucionVentas" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- PRIMERA FILA DE INFORMACIÓN --}}
    <div class="row">

        {{-- PRODUCTOS MÁS VENDIDOS --}}
        <div class="col-lg-6">
            <div class="dashboard-card">
                <div class="card-header-dash">
                    <h3>
                        <i class="fas fa-trophy text-modern-success"></i>
                        {{ __('Productos más vendidos') }}
                    </h3>

                    @if(\Illuminate\Support\Facades\Route::has('productos.index'))
                        <a href="{{ route('productos.index') }}">
                            {{ __('Ver productos') }}
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    @endif
                </div>

                <div class="dashboard-table-container">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>{{ __('Producto') }}</th>

                                <th class="text-right">
                                    {{ __('Cantidad') }}
                                </th>

                                <th class="text-right">
                                    {{ __('Total') }}
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($productosMasVendidosLista as $producto)
                                @php
                                    $nombreProducto = data_get($producto, 'nombre')
                                        ?? data_get($producto, 'descripcion')
                                        ?? data_get($producto, 'producto')
                                        ?? data_get($producto, 'producto.nombre')
                                        ?? 'Producto sin nombre';

                                    $cantidadVendida = (float) (
                                        data_get($producto, 'cantidad_vendida')
                                        ?? data_get($producto, 'total_vendido')
                                        ?? data_get($producto, 'cantidad')
                                        ?? 0
                                    );

                                    $totalVendido = (float) (
                                        data_get($producto, 'importe_total')
                                        ?? data_get($producto, 'total')
                                        ?? data_get($producto, 'monto_total')
                                        ?? 0
                                    );
                                @endphp

                                <tr>
                                    <td>
                                        <div class="producto-nombre">
                                            <i class="fas fa-box"></i>
                                            {{ $nombreProducto }}
                                        </div>
                                    </td>

                                    <td class="text-right">
                                        {{ number_format($cantidadVendida, 0, ',', '.') }}
                                    </td>

                                    <td class="text-right font-weight-bold">
                                        Gs. {{ number_format($totalVendido, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <div class="empty-dashboard">
                                            <i class="fas fa-chart-pie"></i>

                                            <span>
                                                {{ __('No hay ventas registradas.') }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- PRODUCTOS CON BAJO STOCK --}}
        <div class="col-lg-6">
            <div class="dashboard-card">
                <div class="card-header-dash">
                    <h3>
                        <i class="fas fa-exclamation-triangle text-modern-danger"></i>
                        {{ __('Productos con bajo stock') }}
                    </h3>

                    @if(\Illuminate\Support\Facades\Route::has('productos.index'))
                        <a href="{{ route('productos.index') }}">
                            {{ __('Gestionar stock') }}
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    @endif
                </div>

                <div class="dashboard-table-container">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>{{ __('Producto') }}</th>

                                <th class="text-right">
                                    {{ __('Stock actual') }}
                                </th>

                                <th class="text-right">
                                    {{ __('Stock mínimo') }}
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($productosBajoStockLista as $producto)
                                @php
                                    $nombreProducto = data_get($producto, 'nombre')
                                        ?? data_get($producto, 'descripcion')
                                        ?? data_get($producto, 'producto.nombre')
                                        ?? 'Producto sin nombre';

                                    $stockActual = (float) (
                                        data_get($producto, 'stock_actual')
                                        ?? data_get($producto, 'stock')
                                        ?? data_get($producto, 'cantidad')
                                        ?? 0
                                    );

                                    $stockMinimo = (float) (
                                        data_get($producto, 'stock_minimo')
                                        ?? data_get($producto, 'minimo')
                                        ?? 0
                                    );
                                @endphp

                                <tr>
                                    <td>
                                        <div class="producto-nombre">
                                            <i class="fas fa-box-open"></i>
                                            {{ $nombreProducto }}
                                        </div>
                                    </td>

                                    <td class="text-right">
                                        <span class="badge-stock badge-stock-danger">
                                            {{ number_format($stockActual, 0, ',', '.') }}
                                        </span>
                                    </td>

                                    <td class="text-right">
                                        {{ number_format($stockMinimo, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <div class="empty-dashboard empty-success">
                                            <i class="fas fa-check-circle"></i>

                                            <span>
                                                {{ __('No hay productos con bajo stock.') }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- SEGUNDA FILA DE INFORMACIÓN --}}
    <div class="row">

        {{-- ÚLTIMAS VENTAS --}}
        <div class="col-lg-6">
            <div class="dashboard-card">
                <div class="card-header-dash">
                    <h3>
                        <i class="fas fa-receipt text-modern-primary"></i>
                        {{ __('Últimas ventas') }}
                    </h3>

                    @if(\Illuminate\Support\Facades\Route::has('ventas.index'))
                        <a href="{{ route('ventas.index') }}">
                            {{ __('Ver todas') }}
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    @endif
                </div>

                <div class="dashboard-table-container dashboard-scroll">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>{{ __('Cliente') }}</th>

                                <th class="text-right">
                                    {{ __('Monto') }}
                                </th>

                                <th class="text-right">
                                    {{ __('Fecha') }}
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($ultimasVentasLista as $venta)
                                @php
                                    $clienteVenta = data_get($venta, 'cliente.razonsocial')
                                        ?? data_get($venta, 'cliente.nombre')
                                        ?? data_get($venta, 'cliente.nombre_completo')
                                        ?? data_get($venta, 'razonsocial')
                                        ?? 'Consumidor Final';

                                    $numeroFactura = data_get($venta, 'numero_factura')
                                        ?? data_get($venta, 'factura.numero')
                                        ?? data_get($venta, 'numero_completo');

                                    $montoVenta = (float) (
                                        data_get($venta, 'monto_total')
                                        ?? data_get($venta, 'importe_total')
                                        ?? data_get($venta, 'total')
                                        ?? 0
                                    );

                                    $fechaVentaValor = data_get($venta, 'fecha')
                                        ?? data_get($venta, 'fecha_hora')
                                        ?? data_get($venta, 'created_at');

                                    $fechaVentaTexto = '-';

                                    if ($fechaVentaValor) {
                                        try {
                                            $fechaVentaTexto = \Carbon\Carbon::parse(
                                                $fechaVentaValor
                                            )->format('d/m/Y H:i');
                                        } catch (\Throwable $exception) {
                                            $fechaVentaTexto = '-';
                                        }
                                    }
                                @endphp

                                <tr>
                                    <td>
                                        <div class="venta-cliente-principal">
                                            {{ $clienteVenta }}
                                        </div>

                                        @if(!empty($numeroFactura))
                                            <div class="venta-cliente">
                                                {{ __('Fact.') }}
                                                {{ $numeroFactura }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="text-right font-weight-bold">
                                        Gs. {{ number_format($montoVenta, 0, ',', '.') }}
                                    </td>

                                    <td class="text-right">
                                        {{ $fechaVentaTexto }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <div class="empty-dashboard">
                                            <i class="fas fa-receipt"></i>

                                            <span>
                                                {{ __('No hay ventas registradas.') }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- CUENTAS CORRIENTES --}}
        <div class="col-lg-6">
            <div class="dashboard-card">
                <div class="card-header-dash">
                    <h3>
                        <i class="fas fa-credit-card text-modern-warning"></i>
                        {{ __('Cuentas Corrientes') }}
                    </h3>

                    @if(\Illuminate\Support\Facades\Route::has('caja.index'))
                        <a href="{{ route('caja.index') }}">
                            {{ __('Ir a cobros') }}
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    @endif
                </div>

                <div class="cuentas-container">
                    @forelse($cuentasCorrientesLista as $cc)
                        @php
                            $razonSocial = data_get($cc, 'razonsocial')
                                ?? data_get($cc, 'persona.razonsocial')
                                ?? data_get($cc, 'persona.nombre')
                                ?? data_get($cc, 'persona.nombre_completo')
                                ?? data_get($cc, 'cliente.razonsocial')
                                ?? 'Sin nombre';

                            $saldoPendiente = (float) (
                                data_get($cc, 'saldo_pendiente')
                                ?? data_get($cc, 'saldo_actual')
                                ?? data_get($cc, 'saldo')
                                ?? 0
                            );

                            $vencimientos = collect(
                                data_get($cc, 'vencimientos', [])
                            );

                            $historialPagos = collect(
                                data_get($cc, 'historial_pagos', [])
                            );
                        @endphp

                        <div class="cuenta-item">
                            <div class="cuenta-encabezado">
                                <strong class="cuenta-cliente">
                                    <i class="fas fa-user"></i>
                                    {{ $razonSocial }}
                                </strong>

                                <span class="cuenta-saldo">
                                    Gs. {{ number_format($saldoPendiente, 0, ',', '.') }}
                                </span>
                            </div>

                            {{-- VENCIMIENTOS --}}
                            @if($vencimientos->isNotEmpty())
                                <div class="cuenta-vencimientos">
                                    <div class="cuenta-subtitulo">
                                        <i class="fas fa-calendar-alt"></i>
                                        {{ __('Próximos vencimientos:') }}
                                    </div>

                                    <div class="vencimientos-lista">
                                        @foreach($vencimientos->take(3) as $vencimiento)
                                            @php
                                                $fechaVencimientoValor = data_get(
                                                    $vencimiento,
                                                    'fecha_vencimiento'
                                                );

                                                $fechaVencimientoTexto = __('Sin fecha');
                                                $estaVencido = false;

                                                if ($fechaVencimientoValor) {
                                                    try {
                                                        $fechaVencimiento = \Carbon\Carbon::parse(
                                                            $fechaVencimientoValor
                                                        );

                                                        $fechaVencimientoTexto = $fechaVencimiento
                                                            ->format('d/m/y');

                                                        $estaVencido = $fechaVencimiento
                                                            ->copy()
                                                            ->endOfDay()
                                                            ->isPast();
                                                    } catch (\Throwable $exception) {
                                                        $fechaVencimientoTexto = __('Sin fecha');
                                                        $estaVencido = false;
                                                    }
                                                }

                                                $montoVencimiento = (float) (
                                                    data_get($vencimiento, 'saldo')
                                                    ?? data_get($vencimiento, 'monto')
                                                    ?? data_get($vencimiento, 'importe')
                                                    ?? 0
                                                );
                                            @endphp

                                            <span
                                                class="badge-stock {{ $estaVencido ? 'badge-stock-danger' : 'badge-stock-warning' }}"
                                            >
                                                {{ $fechaVencimientoTexto }}
                                                · Gs.
                                                {{ number_format($montoVencimiento, 0, ',', '.') }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="cuenta-sin-vencimientos">
                                    <i class="fas fa-calendar-check"></i>
                                    {{ __('Sin vencimientos pendientes') }}
                                </div>
                            @endif

                            {{-- HISTORIAL DE PAGOS --}}
                            @if($historialPagos->isNotEmpty())
                                <details class="historial-pagos">
                                    <summary>
                                        <i class="fas fa-history"></i>
                                        {{ __('Historial de pagos') }}
                                    </summary>

                                    <div class="historial-lista">
                                        @foreach($historialPagos->take(5) as $pago)
                                            @php
                                                $descripcionPago = data_get(
                                                    $pago,
                                                    'descripcion'
                                                )
                                                    ?? data_get($pago, 'concepto')
                                                    ?? data_get($pago, 'observacion')
                                                    ?? __('Pago registrado');

                                                $montoPago = (float) (
                                                    data_get($pago, 'monto')
                                                    ?? data_get($pago, 'importe')
                                                    ?? data_get($pago, 'importe_total')
                                                    ?? 0
                                                );

                                                $fechaPagoValor = data_get($pago, 'fecha')
                                                    ?? data_get($pago, 'fecha_pago')
                                                    ?? data_get($pago, 'fecha_movimiento')
                                                    ?? data_get($pago, 'created_at');

                                                $fechaPagoTexto = __('Sin fecha');

                                                if ($fechaPagoValor) {
                                                    try {
                                                        $fechaPagoTexto = \Carbon\Carbon::parse(
                                                            $fechaPagoValor
                                                        )->format('d/m/Y H:i');
                                                    } catch (\Throwable $exception) {
                                                        $fechaPagoTexto = __('Sin fecha');
                                                    }
                                                }
                                            @endphp

                                            <div class="historial-item">
                                                <div>
                                                    <div class="historial-descripcion">
                                                        {{ $descripcionPago }}
                                                    </div>

                                                    <div class="historial-fecha">
                                                        {{ $fechaPagoTexto }}
                                                    </div>
                                                </div>

                                                <div class="historial-monto">
                                                    Gs.
                                                    {{ number_format($montoPago, 0, ',', '.') }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            @endif
                        </div>
                    @empty
                        <div class="empty-dashboard">
                            <i class="fas fa-credit-card"></i>

                            <span>
                                {{ __('No hay cuentas corrientes con saldo pendiente.') }}
                            </span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
@stop

@section('css')
    <style>
        :root {
            --modern-primary: #2563eb;
            --modern-success: #16a34a;
            --modern-danger: #dc2626;
            --modern-warning: #d97706;
            --modern-card-bg: #ffffff;
            --modern-card-border: #e5e7eb;
            --modern-text-primary: #1f2937;
            --modern-text-secondary: #4b5563;
            --modern-text-muted: #6b7280;
            --modern-background: #f3f4f6;
        }

        .content-wrapper {
            background: var(--modern-background);
        }

        .dashboard-title-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .dashboard-title {
            margin: 0;
            color: var(--modern-text-primary);
            font-size: 1.65rem;
            font-weight: 700;
        }

        .dashboard-title i {
            color: var(--modern-primary);
            margin-right: 0.4rem;
        }

        .dashboard-subtitle {
            margin: 0.3rem 0 0;
            color: var(--modern-text-muted);
            font-size: 0.9rem;
        }

        .dashboard-date {
            background: #ffffff;
            border: 1px solid var(--modern-card-border);
            border-radius: 10px;
            padding: 0.55rem 0.85rem;
            color: var(--modern-text-secondary);
            font-size: 0.85rem;
            font-weight: 600;
        }

        .dashboard-date i {
            margin-right: 0.35rem;
            color: var(--modern-primary);
        }

        .dashboard-admin-bar {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin-top: 0.85rem;
            padding: 0.55rem 0.9rem;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            color: var(--modern-text-secondary);
            font-size: 0.85rem;
            font-weight: 600;
        }

        .dashboard-admin-bar i {
            color: var(--modern-primary);
        }

        .dashboard-admin-bar a {
            color: var(--modern-primary);
            font-weight: 700;
        }

        .dashboard-admin-bar a i {
            margin-left: 0.25rem;
            font-size: 0.7rem;
        }

        .dashboard-summary-card {
            min-height: 135px;
            display: flex;
            align-items: center;
            gap: 1rem;
            background: var(--modern-card-bg);
            border: 1px solid var(--modern-card-border);
            border-left: 5px solid var(--modern-primary);
            border-radius: 12px;
            padding: 1.15rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .dashboard-summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.09);
        }

        .card-primary-modern {
            border-left-color: var(--modern-primary);
        }

        .card-success-modern {
            border-left-color: var(--modern-success);
        }

        .card-danger-modern {
            border-left-color: var(--modern-danger);
        }

        .card-warning-modern {
            border-left-color: var(--modern-warning);
        }

        .summary-icon {
            width: 52px;
            height: 52px;
            flex: 0 0 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #eff6ff;
            color: var(--modern-primary);
            font-size: 1.35rem;
        }

        .card-success-modern .summary-icon {
            background: #f0fdf4;
            color: var(--modern-success);
        }

        .card-danger-modern .summary-icon {
            background: #fef2f2;
            color: var(--modern-danger);
        }

        .card-warning-modern .summary-icon {
            background: #fffbeb;
            color: var(--modern-warning);
        }

        .summary-content {
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .summary-label {
            color: var(--modern-text-muted);
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .summary-value {
            color: var(--modern-text-primary);
            font-size: 1.35rem;
            line-height: 1.35;
            margin: 0.2rem 0;
            word-break: break-word;
        }

        .summary-link {
            color: var(--modern-primary);
            font-size: 0.78rem;
            font-weight: 600;
        }

        .summary-link i {
            margin-left: 0.2rem;
            font-size: 0.7rem;
        }

        .dashboard-card {
            height: calc(100% - 1.25rem);
            background: var(--modern-card-bg);
            border: 1px solid var(--modern-card-border);
            border-radius: 12px;
            margin-bottom: 1.25rem;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
        }

        .card-header-dash {
            min-height: 58px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--modern-card-border);
            background: #ffffff;
        }

        .card-header-dash h3 {
            margin: 0;
            color: var(--modern-text-primary);
            font-size: 1rem;
            font-weight: 700;
        }

        .card-header-dash h3 i {
            margin-right: 0.4rem;
        }

        .card-header-dash a {
            white-space: nowrap;
            color: var(--modern-primary);
            font-size: 0.78rem;
            font-weight: 600;
        }

        .card-header-dash a i {
            margin-left: 0.25rem;
        }

        .text-modern-primary {
            color: var(--modern-primary);
        }

        .text-modern-success {
            color: var(--modern-success);
        }

        .text-modern-danger {
            color: var(--modern-danger);
        }

        .text-modern-warning {
            color: var(--modern-warning);
        }

        .dashboard-table-container {
            overflow-x: auto;
        }

        .dashboard-chart-container {
            position: relative;
            padding: 1rem;
        }

        .dashboard-scroll {
            max-height: 400px;
            overflow-y: auto;
        }

        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
        }

        .dashboard-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            padding: 0.75rem 1rem;
            background: #f9fafb;
            border-bottom: 1px solid var(--modern-card-border);
            color: var(--modern-text-muted);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .dashboard-table tbody td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            color: var(--modern-text-secondary);
            font-size: 0.84rem;
            vertical-align: middle;
        }

        .dashboard-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .dashboard-table tbody tr:hover {
            background: #f8fafc;
        }

        .producto-nombre {
            color: var(--modern-text-primary);
            font-weight: 600;
        }

        .producto-nombre i {
            margin-right: 0.35rem;
            color: var(--modern-primary);
        }

        .venta-cliente-principal {
            color: var(--modern-text-primary);
            font-weight: 600;
        }

        .venta-cliente {
            margin-top: 2px;
            color: var(--modern-text-muted);
            font-size: 0.72rem;
        }

        .badge-stock {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            margin: 1px;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-stock-danger {
            background: #fee2e2;
            color: #b91c1c;
        }

        .badge-stock-warning {
            background: #fef3c7;
            color: #b45309;
        }

        .cuentas-container {
            max-height: 400px;
            overflow-y: auto;
        }

        .cuenta-item {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--modern-card-border);
        }

        .cuenta-item:last-child {
            border-bottom: 0;
        }

        .cuenta-encabezado {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 0.35rem;
        }

        .cuenta-cliente {
            color: var(--modern-text-primary);
            font-size: 0.88rem;
        }

        .cuenta-cliente i {
            margin-right: 0.3rem;
            color: var(--modern-primary);
        }

        .cuenta-saldo {
            white-space: nowrap;
            color: var(--modern-danger);
            font-size: 0.92rem;
            font-weight: 700;
        }

        .cuenta-vencimientos {
            color: var(--modern-text-muted);
            font-size: 0.76rem;
        }

        .cuenta-subtitulo {
            margin-bottom: 0.3rem;
        }

        .cuenta-subtitulo i {
            margin-right: 0.25rem;
        }

        .vencimientos-lista {
            display: flex;
            flex-wrap: wrap;
            gap: 2px;
        }

        .cuenta-sin-vencimientos {
            color: var(--modern-success);
            font-size: 0.76rem;
        }

        .cuenta-sin-vencimientos i {
            margin-right: 0.25rem;
        }

        .historial-pagos {
            margin-top: 0.45rem;
            color: var(--modern-text-muted);
            font-size: 0.76rem;
        }

        .historial-pagos summary {
            cursor: pointer;
            color: var(--modern-primary);
            font-weight: 600;
        }

        .historial-pagos summary i {
            margin-right: 0.25rem;
        }

        .historial-lista {
            margin-top: 0.4rem;
            padding-left: 0.4rem;
        }

        .historial-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.4rem 0;
            border-bottom: 1px dashed var(--modern-card-border);
        }

        .historial-item:last-child {
            border-bottom: 0;
        }

        .historial-descripcion {
            color: var(--modern-text-primary);
            font-weight: 600;
        }

        .historial-fecha {
            color: var(--modern-text-muted);
            font-size: 0.7rem;
        }

        .historial-monto {
            white-space: nowrap;
            color: var(--modern-success);
            font-weight: 700;
        }

        .empty-dashboard {
            min-height: 130px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            padding: 1.5rem;
            color: var(--modern-text-muted);
            text-align: center;
        }

        .empty-dashboard i {
            color: #cbd5e1;
            font-size: 2rem;
        }

        .empty-success i {
            color: #86efac;
        }

        @media (max-width: 767.98px) {
            .dashboard-title-container {
                align-items: flex-start;
                flex-direction: column;
            }

            .dashboard-date {
                width: 100%;
            }

            .card-header-dash {
                align-items: flex-start;
                flex-direction: column;
                gap: 0.4rem;
            }

            .cuenta-encabezado {
                align-items: flex-start;
                flex-direction: column;
                gap: 0.25rem;
            }

            .summary-value {
                font-size: 1.15rem;
            }
        }
    </style>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const alerts = document.querySelectorAll('.alert-dismissible');

            alerts.forEach(function (alertElement) {
                window.setTimeout(function () {
                    if (
                        typeof window.jQuery !== 'undefined' &&
                        typeof window.jQuery(alertElement).alert === 'function'
                    ) {
                        window.jQuery(alertElement).alert('close');
                    }
                }, 6000);
            });

            if (typeof window.Chart === 'undefined') {
                return;
            }

            var meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

            var moneda = function (value) {
                return Number(value).toLocaleString('es-PY', {
                    style: 'currency',
                    currency: 'PYG',
                    maximumFractionDigits: 0,
                });
            };

            var formatoNumero = function (value) {
                return Number(value).toLocaleString('es-PY');
            };

            @php
                $ventasActualArr = array_values($ventasEsteAnio ?? array_fill(1, 12, 0));
                $ventasAnteriorArr = array_values($ventasAnhoAnterior ?? array_fill(1, 12, 0));
                $comprasActualArr = array_values($comprasEsteAnio ?? array_fill(1, 12, 0));

                $productosChart = collect($productosMasVendidosLista)->take(7);
                $productosChartLabels = $productosChart->map(function ($p) {
                    return \Illuminate\Support\Str::limit(
                        data_get($p, 'nombre')
                            ?? data_get($p, 'descripcion')
                            ?? data_get($p, 'producto.nombre')
                            ?? 'Producto',
                        22,
                        '…'
                    );
                })->values()->all();

                $productosChartData = $productosChart->map(function ($p) {
                    return (float) (
                        data_get($p, 'cantidad_vendida')
                        ?? data_get($p, 'total_vendido')
                        ?? data_get($p, 'cantidad')
                        ?? 0
                    );
                })->values()->all();
            @endphp

            // Gráfico 1: Ventas vs Compras por mes
            var canvasVentasCompras = document.getElementById('chartVentasCompras');

            if (canvasVentasCompras) {
                new Chart(canvasVentasCompras, {
                    type: 'bar',
                    data: {
                        labels: meses,
                        datasets: [
                            {
                                label: 'Ventas',
                                data: @json($ventasActualArr),
                                backgroundColor: 'rgba(37, 99, 235, 0.75)',
                                borderColor: '#2563eb',
                                borderWidth: 1,
                            },
                            {
                                label: 'Compras',
                                data: @json($comprasActualArr),
                                backgroundColor: 'rgba(234, 88, 12, 0.75)',
                                borderColor: '#ea580c',
                                borderWidth: 1,
                            },
                        ],
                    },
                    options: {
                        maintainAspectRatio: false,
                        legend: { display: true, position: 'top' },
                        scales: {
                            xAxes: [{ gridLines: { display: false } }],
                            yAxes: [{
                                ticks: { beginAtZero: true, callback: formatoNumero },
                                gridLines: { color: 'rgba(15, 23, 42, 0.06)' },
                            }],
                        },
                        tooltips: {
                            callbacks: {
                                label: function (tooltipItem, data) {
                                    var dataset = data.datasets[tooltipItem.datasetIndex];
                                    return dataset.label + ': ' + moneda(dataset.data[tooltipItem.index]);
                                },
                            },
                        },
                    },
                });
            }

            // Gráfico 2: Distribución de productos más vendidos
            var canvasProductos = document.getElementById('chartProductosVendidos');

            if (canvasProductos) {
                new Chart(canvasProductos, {
                    type: 'doughnut',
                    data: {
                        labels: @json($productosChartLabels),
                        datasets: [{
                            data: @json($productosChartData),
                            backgroundColor: [
                                '#2563eb', '#16a34a', '#dc2626', '#d97706',
                                '#7c3aed', '#0891b2', '#db2777',
                            ],
                        }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: { boxWidth: 12, padding: 8, font: { size: 11 } },
                        },
                        tooltips: {
                            callbacks: {
                                label: function (tooltipItem, data) {
                                    var label = data.labels[tooltipItem.index] || '';
                                    var value = data.datasets[0].data[tooltipItem.index] || 0;
                                    return label + ': ' + formatoNumero(value) + ' uds.';
                                },
                            },
                        },
                    },
                });
            }

            // Gráfico 3: Evolución de ventas (año actual vs anterior)
            var canvasEvolucion = document.getElementById('chartEvolucionVentas');

            if (canvasEvolucion) {
                new Chart(canvasEvolucion, {
                    type: 'line',
                    data: {
                        labels: meses,
                        datasets: [
                            {
                                label: @json($anioActual ?? now()->year),
                                data: @json($ventasActualArr),
                                borderColor: '#2563eb',
                                backgroundColor: 'rgba(37, 99, 235, 0.10)',
                                fill: true,
                                tension: 0.35,
                                pointRadius: 3,
                                pointBackgroundColor: '#2563eb',
                            },
                            {
                                label: @json($anioAnterior ?? (($anioActual ?? now()->year) - 1)),
                                data: @json($ventasAnteriorArr),
                                borderColor: '#16a34a',
                                backgroundColor: 'rgba(22, 163, 74, 0.10)',
                                fill: true,
                                tension: 0.35,
                                pointRadius: 3,
                                pointBackgroundColor: '#16a34a',
                            },
                        ],
                    },
                    options: {
                        maintainAspectRatio: false,
                        legend: { display: true, position: 'top' },
                        scales: {
                            xAxes: [{ gridLines: { display: false } }],
                            yAxes: [{
                                ticks: { beginAtZero: true, callback: formatoNumero },
                                gridLines: { color: 'rgba(15, 23, 42, 0.06)' },
                            }],
                        },
                        tooltips: {
                            callbacks: {
                                label: function (tooltipItem, data) {
                                    var dataset = data.datasets[tooltipItem.datasetIndex];
                                    return dataset.label + ': ' + moneda(dataset.data[tooltipItem.index]);
                                },
                            },
                        },
                    },
                });
            }
        });
    </script>
@stop
