@extends('adminlte::page')

@php
    // Helper para traducir mes en inglés a español
    function convertirMesAux($mes) {
        $meses = [
            'January' => 'Enero',
            'February' => 'Febrero',
            'March' => 'Marzo',
            'April' => 'Abril',
            'May' => 'Mayo',
            'June' => 'Junio',
            'July' => 'Julio',
            'August' => 'Agosto',
            'September' => 'Septiembre',
            'October' => 'Octubre',
            'November' => 'Noviembre',
            'December' => 'Diciembre'
        ];
        return $meses[$mes] ?? $mes;
    }

    $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    $totalPorMes = array_fill_keys($meses, 0);
    $totalPorMesAnhoAnterior = $totalPorMes;
    $totalPorMesV = $totalPorMes;
    $totalPorMesAnhoAnteriorV = $totalPorMes;

    // Intentar establecer locale (puede fallar en algunos servidores)
    if (setlocale(LC_TIME, 'es_PY.UTF-8', 'es_PY', 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain.1252') === false) {
        // Fallback: usar formato numérico y mapear manualmente
    }

    foreach ($detalles ?? [] as $detalle) {
        if (!empty($detalle->fechaemision)) {
            $mesIngles = date('F', strtotime($detalle->fechaemision));
            $mes = convertirMesAux($mesIngles);
            if (isset($totalPorMes[$mes])) {
                $totalPorMes[$mes] += (float) ($detalle->cantidad ?? 0);
            }
        }
    }

    foreach ($detallesAnhoAnterior ?? [] as $detalle) {
        if (!empty($detalle->fechaemision)) {
            $mesIngles = date('F', strtotime($detalle->fechaemision));
            $mes = convertirMesAux($mesIngles);
            if (isset($totalPorMesAnhoAnterior[$mes])) {
                $totalPorMesAnhoAnterior[$mes] += (float) ($detalle->cantidad ?? 0);
            }
        }
    }

    foreach ($detallesV ?? [] as $detalle) {
        if (!empty($detalle->fecha)) {
            $mesIngles = date('F', strtotime($detalle->fecha));
            $mes = convertirMesAux($mesIngles);
            if (isset($totalPorMesV[$mes])) {
                $totalPorMesV[$mes] += (float) ($detalle->cantidad ?? 0);
            }
        }
    }

    foreach ($detallesAnhoAnteriorV ?? [] as $detalle) {
        if (!empty($detalle->fecha)) {
            $mesIngles = date('F', strtotime($detalle->fecha));
            $mes = convertirMesAux($mesIngles);
            if (isset($totalPorMesAnhoAnteriorV[$mes])) {
                $totalPorMesAnhoAnteriorV[$mes] += (float) ($detalle->cantidad ?? 0);
            }
        }
    }
@endphp

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const meses = @json($meses);
    const cantidades = @json(array_values($totalPorMes));
    const cantidadesAnhoAnterior = @json(array_values($totalPorMesAnhoAnterior));
    const cantidadesV = @json(array_values($totalPorMesV));
    const cantidadesAnhoAnteriorV = @json(array_values($totalPorMesAnhoAnteriorV));

    // Compras
    const ctxCompras = document.getElementById('chartCompras').getContext('2d');
    new Chart(ctxCompras, {
        type: 'bar',
        data: {
            labels: meses,
            datasets: [{
                label: 'Cantidad de Compra (Año Actual)',
                data: cantidades,
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgb(255, 99, 132)',
                borderWidth: 1
            }, {
                label: 'Cantidad de Compra (Año Anterior)',
                data: cantidadesAnhoAnterior,
                backgroundColor: 'rgba(169, 169, 169, 0.2)',
                borderColor: 'rgb(169, 169, 169)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: { beginAtZero: true },
                x: { stacked: false }
            }
        }
    });

    // Ventas
    const ctxVentas = document.getElementById('chartVentas').getContext('2d');
    new Chart(ctxVentas, {
        type: 'bar',
        data: {
            labels: meses,
            datasets: [{
                label: 'Cantidad de Ventas (Año Actual)',
                data: cantidadesV,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgb(54, 162, 235)',
                borderWidth: 1
            }, {
                label: 'Cantidad de Ventas (Año Anterior)',
                data: cantidadesAnhoAnteriorV,
                backgroundColor: 'rgba(169, 169, 169, 0.2)',
                borderColor: 'rgb(169, 169, 169)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: { beginAtZero: true },
                x: { stacked: false }
            }
        }
    });
});
</script>
@endpush

@section('content')
<div class="row mb-3">
    <div class="col-12 text-center">
        <h5>Mercaderías</h5>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0 text-center">Compras</h6>
            </div>
            <div class="card-body" style="height: 400px;">
                <canvas id="chartCompras"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0 text-center">Ventas</h6>
            </div>
            <div class="card-body" style="height: 400px;">
                <canvas id="chartVentas"></canvas>
            </div>
        </div>
    </div>
</div>
@stop