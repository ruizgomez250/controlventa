@extends('adminlte::page')

@section('title', 'Dashboard')

@push('css')
<style>
    .dashboard-card {
        background: var(--modern-card-bg);
        backdrop-filter: blur(var(--modern-glass-blur));
        -webkit-backdrop-filter: blur(var(--modern-glass-blur));
        border: 1px solid var(--modern-card-border);
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
        height: 100%;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .dashboard-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    }
    .dashboard-card .card-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }
    .dashboard-card .card-label {
        color: var(--modern-text-secondary);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }
    .dashboard-card .card-value {
        color: var(--modern-text-primary);
        font-size: 1.8rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .dashboard-card .card-sub {
        color: var(--modern-text-muted);
        font-size: 0.8rem;
    }

    .stock-alert-bar {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(245, 158, 11, 0.1));
        border: 1px solid rgba(239, 68, 68, 0.25);
        border-radius: 12px;
        padding: 0.9rem 1.5rem;
        margin-bottom: 1.5rem;
    }
    .stock-alert-bar .alert-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(239, 68, 68, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--modern-danger);
        font-size: 1.1rem;
    }
    .stock-alert-bar .alert-text {
        color: var(--modern-text-primary);
        font-weight: 600;
        font-size: 0.95rem;
    }
    .stock-alert-bar .alert-link {
        color: var(--modern-danger);
        font-weight: 600;
        text-decoration: none;
    }
    .stock-alert-bar .alert-link:hover {
        text-decoration: underline;
    }

    .dashboard-table {
        width: 100%;
        border-collapse: collapse;
    }
    .dashboard-table th {
        color: var(--modern-text-secondary);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        padding: 0.6rem 0.75rem;
        border-bottom: 1px solid var(--modern-card-border);
        text-align: left;
    }
    .dashboard-table td {
        color: var(--modern-text-primary);
        padding: 0.6rem 0.75rem;
        border-bottom: 1px solid rgba(148, 163, 184, 0.06);
        font-size: 0.88rem;
    }
    .dashboard-table tr:last-child td {
        border-bottom: none;
    }
    .dashboard-table tr:hover td {
        background: rgba(99, 102, 241, 0.05);
    }
    .dashboard-table .rank-num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 6px;
        background: rgba(99, 102, 241, 0.12);
        color: var(--modern-primary);
        font-size: 0.75rem;
        font-weight: 700;
    }

    .badge-stock {
        padding: 0.2rem 0.6rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .badge-stock-danger { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
    .badge-stock-warning { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
    .badge-stock-ok { background: rgba(34, 197, 94, 0.15); color: #22c55e; }

    .section-title {
        color: var(--modern-text-primary);
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .section-title i {
        color: var(--modern-primary);
    }

    .chart-container {
        background: var(--modern-card-bg);
        backdrop-filter: blur(var(--modern-glass-blur));
        -webkit-backdrop-filter: blur(var(--modern-glass-blur));
        border: 1px solid var(--modern-card-border);
        border-radius: 16px;
        padding: 1.5rem;
        height: 100%;
    }
    .chart-container canvas {
        max-height: 280px;
    }

    .text-modern-primary { color: var(--modern-primary); }
    .text-modern-success { color: var(--modern-success); }
    .text-modern-warning { color: var(--modern-warning); }
    .text-modern-danger { color: var(--modern-danger); }
    .text-modern-info { color: var(--modern-info); }

    .venta-cliente {
        color: var(--modern-text-secondary);
        font-size: 0.8rem;
    }
    .venta-monto {
        font-weight: 600;
        color: var(--modern-success);
    }
    .venta-fecha {
        color: var(--modern-text-muted);
        font-size: 0.78rem;
    }

    .card-header-dash {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--modern-card-border);
    }
    .card-header-dash h3 {
        color: var(--modern-text-primary);
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
    }
    .card-header-dash a {
        color: var(--modern-primary);
        font-size: 0.8rem;
        text-decoration: none;
    }
    .card-header-dash a:hover {
        text-decoration: underline;
    }
</style>
@endpush

@section('content')
@php
    $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    $hoy = now();
@endphp

{{-- Alerta de stock bajo --}}
@if($cantidadBajoStock > 0 || $productosSinStock > 0)
<div class="stock-alert-bar d-flex align-items-center gap-3">
    <div class="alert-icon flex-shrink-0">
        <i class="fas fa-exclamation-triangle"></i>
    </div>
    <div class="flex-grow-1">
        <span class="alert-text">
            @if($cantidadBajoStock > 0 && $productosSinStock > 0)
                <strong>{{ $cantidadBajoStock }} productos</strong> {{ __('están por debajo del stock mínimo y') }} <strong>{{ $productosSinStock }} productos</strong> {{ __('están sin stock.') }}
            @elseif($cantidadBajoStock > 0)
                <strong>{{ $cantidadBajoStock }} productos</strong> {{ __('están por debajo del stock mínimo.') }}
            @else
                <strong>{{ $productosSinStock }} productos</strong> {{ __('están sin stock.') }}
            @endif</span>
    </div>
    <div class="flex-shrink-0">
        <a href="{{ route('producto.index') }}" class="alert-link">
            <i class="fas fa-arrow-right"></i>{{ __('Ir a productos') }}</a>
    </div>
</div>
@endif

{{-- Tarjetas de resumen --}}
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="dashboard-card">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="card-icon" style="background: rgba(99,102,241,0.12);">
                    <i class="fas fa-shopping-cart text-modern-primary"></i>
                </div>
                <div>
                    <div class="card-label">{{ __('Ventas del Día') }}</div>
                    <div class="card-value">{{ number_format($ventasDelDia, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="card-sub">
                <i class="fas fa-calendar-day"></i> {{ $hoy->isoFormat('DD [de] MMMM [del] YYYY', 'es') }}
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="dashboard-card">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="card-icon" style="background: rgba(34,197,94,0.12);">
                    <i class="fas fa-chart-line text-modern-success"></i>
                </div>
                <div>
                    <div class="card-label">{{ __('Ventas del Mes') }}</div>
                    <div class="card-value">{{ number_format($ventasDelMes, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="card-sub">
                <i class="fas fa-calendar-alt"></i> {{ $meses[$hoy->month - 1] }} {{ $hoy->year }}
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="dashboard-card">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="card-icon" style="background: rgba(245,158,11,0.12);">
                    <i class="fas fa-hand-holding-usd text-modern-warning"></i>
                </div>
                <div>
                    <div class="card-label">{{ __('Cuentas por Cobrar') }}</div>
                    <div class="card-value">{{ number_format($cuentasPorCobrar, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="card-sub">
                <i class="fas fa-file-invoice"></i> {{ $cantidadCuotasPendientes }} cuotas pendientes
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="dashboard-card">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="card-icon" style="background: rgba(239,68,68,0.12);">
                    <i class="fas fa-boxes text-modern-danger"></i>
                </div>
                <div>
                    <div class="card-label">{{ __('Stock Bajo') }}</div>
                    <div class="card-value">{{ $cantidadBajoStock }}</div>
                </div>
            </div>
            <div class="card-sub">
                <i class="fas fa-exclamation-circle"></i> {{ $productosSinStock }} productos sin stock
            </div>
        </div>
    </div>
</div>

{{-- Productos más vendidos y Últimas ventas --}}
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="dashboard-card">
            <div class="card-header-dash">
                <h3><i class="fas fa-crown text-modern-warning"></i>{{ __('Productos Más Vendidos') }}</h3>
                <a href="{{ route('reportes.vendidos') }}">{{ __('Ver reporte') }}<i class="fas fa-external-link-alt"></i></a>
            </div>
            <div style="overflow-x: auto;">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th style="width: 36px;">{{ __('#') }}</th>
                            <th>{{ __('Producto') }}</th>
                            <th style="text-align:right;">{{ __('Cant.') }}</th>
                            <th style="text-align:right;">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productosMasVendidos as $i => $item)
                        <tr>
                            <td><span class="rank-num">{{ $i + 1 }}</span></td>
                            <td>{{ $item->descripcion }}</td>
                            <td style="text-align:right; font-weight:600;">
                                {{ number_format($item->total_vendido, 0, ',', '.') }}
                            </td>
                            <td style="text-align:right; color:var(--modern-success); font-weight:600;">
                                {{ number_format($item->total_monto, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" style="text-align:center; color:var(--modern-text-muted); padding:2rem;">{{ __('No hay datos de ventas en los últimos 12 meses.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="dashboard-card">
            <div class="card-header-dash">
                <h3><i class="fas fa-clock text-modern-info"></i>{{ __('Últimas Ventas') }}</h3>
                <a href="{{ route('venta.index') }}">{{ __('Ver todas') }}<i class="fas fa-external-link-alt"></i></a>
            </div>
            <div style="overflow-x: auto; max-height: 380px; overflow-y: auto;">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>{{ __('Cliente') }}</th>
                            <th style="text-align:right;">{{ __('Monto') }}</th>
                            <th style="text-align:right;">{{ __('Fecha') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ultimasVentas as $venta)
                        <tr>
                            <td>
                                <div>{{ $venta->cliente->razonsocial ?? 'Consumidor Final' }}</div>
                                @if($venta->numero_factura)
                                    <div class="venta-cliente">{{ __('Fact. ') . $venta->numero_factura }}</div>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <span class="venta-monto">{{ number_format($venta->total, 0, ',', '.') }}</span>
                            </td>
                            <td style="text-align:right;">
                                <span class="venta-fecha">{{ \Carbon\Carbon::parse($venta->fecha_emision)->isoFormat('DD/MM/YYYY') }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" style="text-align:center; color:var(--modern-text-muted); padding:2rem;">{{ __('No hay ventas registradas.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Productos con bajo stock --}}
@if($productosBajoStock->count() > 0)
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="dashboard-card">
            <div class="card-header-dash">
                <h3><i class="fas fa-exclamation-triangle text-modern-danger"></i>{{ __('Productos con Stock Bajo') }}</h3>
                <a href="{{ route('producto.index') }}">{{ __('Ver inventario') }}<i class="fas fa-external-link-alt"></i></a>
            </div>
            <div style="overflow-x: auto;">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>{{ __('Código') }}</th>
                            <th>{{ __('Producto') }}</th>
                            <th style="text-align:right;">{{ __('Stock Actual') }}</th>
                            <th style="text-align:right;">{{ __('Stock Mínimo') }}</th>
                            <th style="text-align:right;">{{ __('Estado') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productosBajoStock as $p)
                        <tr>
                            <td style="color:var(--modern-text-muted); font-family:monospace;">{{ $p->codigo }}</td>
                            <td>{{ $p->descripcion }}</td>
                            <td style="text-align:right; font-weight:600; color:var(--modern-danger);">
                                {{ number_format($p->stock, 2, ',', '.') }}
                            </td>
                            <td style="text-align:right; color:var(--modern-text-muted);">
                                {{ $p->stock_minimo ? number_format($p->stock_minimo, 2, ',', '.') : '5' }}
                            </td>
                            <td style="text-align:right;">
                                @if($p->stock <= 0)
                                    <span class="badge-stock badge-stock-danger">{{ __('Sin stock') }}</span>
                                @elseif($p->stock <= ($p->stock_minimo ?: 5))
                                    <span class="badge-stock badge-stock-warning">{{ __('Bajo') }}</span>
                                @else
                                    <span class="badge-stock badge-stock-ok">{{ __('Regular') }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Auditoría / Historial --}}
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="dashboard-card">
            <div class="card-header-dash">
                <h3><i class="fas fa-history text-modern-info"></i>{{ __('Historial de Actividades') }}</h3>
            </div>
            <div style="overflow-x: auto; max-height: 400px; overflow-y: auto;">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th style="width:36px;"><i class="fas fa-user"></i></th>
                            <th>{{ __('Acción') }}</th>
                            <th style="text-align:right;">{{ __('Fecha') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ultimasAcciones as $accion)
                        <tr>
                            <td style="vertical-align:top;">
                                <span style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:8px;background:rgba(99,102,241,0.12);color:var(--modern-primary);font-size:0.75rem;font-weight:700;">
                                    {{ strtoupper(substr($accion->usuario->name ?? 'S', 0, 2)) }}
                                </span>
                            </td>
                            <td>
                                <div style="color:var(--modern-text-primary);font-size:0.85rem;font-weight:500;">
                                    @if($accion->accion === 'venta_creada')
                                        <span class="badge-stock badge-stock-ok" style="font-size:0.7rem;margin-right:4px;">{{ __('VENTA') }}</span>
                                    @elseif($accion->accion === 'producto_modificado')
                                        <span class="badge-stock badge-stock-warning" style="font-size:0.7rem;margin-right:4px;">{{ __('PROD') }}</span>
                                    @elseif($accion->accion === 'factura_anulada')
                                        <span class="badge-stock badge-stock-danger" style="font-size:0.7rem;margin-right:4px;">{{ __('ANUL') }}</span>
                                    @endif
                                    {{ $accion->descripcion }}
                                </div>
                                <div style="color:var(--modern-text-muted);font-size:0.75rem;">
                                    {{ $accion->usuario->name ?? 'Sistema' }}
                                </div>
                            </td>
                            <td style="text-align:right;vertical-align:top;">
                                <span style="color:var(--modern-text-muted);font-size:0.75rem;white-space:nowrap;">
                                    {{ \Carbon\Carbon::parse($accion->created_at)->diffForHumans() }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" style="text-align:center;color:var(--modern-text-muted);padding:2rem;">{{ __('No hay actividades registradas.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="dashboard-card">
            <div class="card-header-dash">
                <h3><i class="fas fa-credit-card text-modern-warning"></i>{{ __('Cuentas Corrientes') }}</h3>
                <a href="{{ route('caja.index') }}">{{ __('Ir a cobros') }}<i class="fas fa-external-link-alt"></i></a>
            </div>
            <div style="overflow-x: auto; max-height: 400px; overflow-y: auto;">
                @forelse($cuentasCorrientes as $cc)
                <div style="padding:0.75rem;border-bottom:1px solid var(--modern-card-border);">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <strong style="color:var(--modern-text-primary);font-size:0.9rem;">
                            <i class="fas fa-user"></i> {{ $cc->razonsocial }}
                        </strong>
                        <span style="font-weight:700;color:var(--modern-danger);font-size:0.95rem;">{{ $moneda }} {{ number_format($cc->saldo_pendiente, 0, ',', '.') }}</span>
                    </div>
                    @if($cc->vencimientos->count() > 0)
                    <div style="font-size:0.78rem;color:var(--modern-text-muted);margin-bottom:4px;">
                        <i class="fas fa-calendar-alt"></i>{{ __('Próximos vencimientos:') }}
                        @foreach($cc->vencimientos->take(3) as $venc)
                            <span class="badge-stock {{ \Carbon\Carbon::parse($venc->fecha_vencimiento)->isPast() ? 'badge-stock-danger' : 'badge-stock-warning' }}" style="font-size:0.7rem;margin:1px;">
                                {{ \Carbon\Carbon::parse($venc->fecha_vencimiento)->isoFormat('DD/MM/YY') }}
                                {{ $moneda }} {{ number_format($venc->monto, 0, ',', '.') }}
                            </span>
                        @endforeach
                    </div>
                    @endif
                    @if($cc->historial_pagos->count() > 0)
                    <details style="font-size:0.78rem;color:var(--modern-text-muted);margin-top:2px;">
                        <summary style="cursor:pointer;color:var(--modern-primary);">
                            <i class="fas fa-check-circle"></i>{{ __('Últimos pagos') }} ({{ $cc->historial_pagos->count() }})</summary>
                        <div style="margin-top:4px;padding-left:8px;border-left:2px solid var(--modern-card-border);">
                            @foreach($cc->historial_pagos as $pago)
                            <div style="display:flex;justify-content:space-between;padding:2px 0;">
                                <span>{{ \Carbon\Carbon::parse($pago->fecha_pago)->isoFormat('DD/MM/YYYY') }}</span>
                                <span style="color:var(--modern-success);font-weight:600;">{{ $moneda }} {{ number_format($pago->monto, 0, ',', '.') }}</span>
                            </div>
                            @endforeach
                        </div>
                    </details>
                    @endif
                </div>
                @empty
                <div style="text-align:center;color:var(--modern-text-muted);padding:2rem;">
                    <i class="fas fa-check-circle" style="font-size:2rem;color:var(--modern-success);display:block;margin-bottom:0.5rem;"></i>{{ __('No hay clientes con saldo pendiente.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Gráficos anuales --}}
<div class="row g-3">
    <div class="col-md-6">
        <div class="chart-container">
            <div class="section-title">
                <i class="fas fa-shopping-bag"></i>{{ __('Compras') }} {{ $anioActual }} vs {{ $anioAnterior }}</div>
            <canvas id="chartCompras"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="chart-container">
            <div class="section-title">
                <i class="fas fa-cash-register"></i>{{ __('Ventas') }} {{ $anioActual }} vs {{ $anioAnterior }}</div>
            <canvas id="chartVentas"></canvas>
        </div>
    </div>
</div>
@stop

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var meses = @json($meses);
    var comprasEste = @json(array_values($comprasEsteAnio));
    var comprasAntes = @json(array_values($comprasAnhoAnterior));
    var ventasEste = @json(array_values($ventasEsteAnio));
    var ventasAntes = @json(array_values($ventasAnhoAnterior));

    var moneda = @json($moneda);

    function formatNumber(n) {
        return n.toLocaleString('es-PY');
    }

    // Gráfico de Compras
    var ctxC = document.getElementById('chartCompras').getContext('2d');
    new Chart(ctxC, {
        type: 'bar',
        data: {
            labels: meses,
            datasets: [{
                label: 'Compras {{ $anioActual }}',
                data: comprasEste,
                backgroundColor: 'rgba(99, 102, 241, 0.6)',
                borderColor: 'rgba(99, 102, 241, 0.9)',
                borderWidth: 1,
                borderRadius: 4,
            }, {
                label: 'Compras {{ $anioAnterior }}',
                data: comprasAntes,
                backgroundColor: 'rgba(148, 163, 184, 0.25)',
                borderColor: 'rgba(148, 163, 184, 0.5)',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: '#94a3b8', font: { family: 'Inter' } }
                },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ctx.dataset.label + ': ' + moneda + ' ' + formatNumber(ctx.raw);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(148,163,184,0.08)' },
                    ticks: { color: '#64748b', font: { family: 'Inter', size: 11 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#64748b', font: { family: 'Inter', size: 10 } }
                }
            }
        }
    });

    // Gráfico de Ventas
    var ctxV = document.getElementById('chartVentas').getContext('2d');
    new Chart(ctxV, {
        type: 'bar',
        data: {
            labels: meses,
            datasets: [{
                label: 'Ventas {{ $anioActual }}',
                data: ventasEste,
                backgroundColor: 'rgba(34, 197, 94, 0.5)',
                borderColor: 'rgba(34, 197, 94, 0.8)',
                borderWidth: 1,
                borderRadius: 4,
            }, {
                label: 'Ventas {{ $anioAnterior }}',
                data: ventasAntes,
                backgroundColor: 'rgba(148, 163, 184, 0.25)',
                borderColor: 'rgba(148, 163, 184, 0.5)',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: '#94a3b8', font: { family: 'Inter' } }
                },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ctx.dataset.label + ': ' + moneda + ' ' + formatNumber(ctx.raw);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(148,163,184,0.08)' },
                    ticks: { color: '#64748b', font: { family: 'Inter', size: 11 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#64748b', font: { family: 'Inter', size: 10 } }
                }
            }
        }
    });
});
</script>
@endpush
