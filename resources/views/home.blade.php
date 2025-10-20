@extends('adminlte::page')
@php
    function convertirMes($mes)
    {
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

    // Inicializar variables para almacenar los totales por mes
    $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

    $totalPorMes = array_fill_keys($meses, 0);
    $totalPorMesAnhoAnterior = $totalPorMes;
    $totalPorMesV = $totalPorMes;
    $totalPorMesAnhoAnteriorV = $totalPorMes;

    setlocale(LC_TIME, 'es_PY.UTF-8');

    // Iterar sobre los detalles obtenidos de la consulta de compras
    foreach ($detalles as $detalle) {
        $mes = strftime('%B', strtotime($detalle->fechaemision));
        $mes = convertirMes($mes);
        $totalPorMes[$mes] += $detalle->cantidad;
    }

    foreach ($detallesAnhoAnterior as $detalle) {
        $mes = strftime('%B', strtotime($detalle->fechaemision));
        $mes = convertirMes($mes);
        $totalPorMesAnhoAnterior[$mes] += $detalle->cantidad;
    }

    // Iterar sobre los detalles obtenidos de la consulta de ventas
    foreach ($detallesV as $detalle) {
        $mes = strftime('%B', strtotime($detalle->fecha));
        $mes = convertirMes($mes);
        $totalPorMesV[$mes] += $detalle->cantidad;
    }

    foreach ($detallesAnhoAnteriorV as $detalle) {
        $mes = strftime('%B', strtotime($detalle->fecha));
        $mes = convertirMes($mes);
        $totalPorMesAnhoAnteriorV[$mes] += $detalle->cantidad;
    }
@endphp

@push('js')
    <script src="vendor/laravel-admin-ext/chartjs/Chart.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const meses = @json($meses);
            const cantidades = @json(array_values($totalPorMes));
            const cantidadesAnhoAnterior = @json(array_values($totalPorMesAnhoAnterior));
            const cantidadesV = @json(array_values($totalPorMesV));
            const cantidadesAnhoAnteriorV = @json(array_values($totalPorMesAnhoAnteriorV));

            const dataCompras = {
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
            };

            const dataVentas = {
                labels: meses,
                datasets: [{
                    label: 'Cantidad de Ventas (Año Actual)',
                    data: cantidadesV,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgb(255, 99, 132)',
                    borderWidth: 1
                }, {
                    label: 'Cantidad de Ventas (Año Anterior)',
                    data: cantidadesAnhoAnteriorV,
                    backgroundColor: 'rgba(169, 169, 169, 0.2)',
                    borderColor: 'rgb(169, 169, 169)',
                    borderWidth: 1
                }]
            };

            const configCompras = {
                type: 'bar',
                data: dataCompras,
                options: {
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            align: 'start',
                            labels: {
                                boxWidth: 20,
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        },
                        x: {
                            stacked: true
                        }
                    }
                }
            };

            const configVentas = {
                type: 'bar',
                data: dataVentas,
                options: {
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            align: 'start',
                            labels: {
                                boxWidth: 20,
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        },
                        x: {
                            stacked: true
                        }
                    }
                }
            };

            const ctxCompras = document.getElementById('chartCompras').getContext('2d');
            new Chart(ctxCompras, configCompras);

            const ctxVentas = document.getElementById('chartVentas').getContext('2d');
            new Chart(ctxVentas, configVentas);
        });
    </script>
@endpush

@section('content')
<div class="row">
    <div class="col-12 text-center">
        <h5>Mercaderias</h5>
    </div>
</div>
    <div class="row">
        <div class="col-6">
            <div class="card">
                <h6 class="text-center">Compras</h6>
                <div class="card-body">
                    <div style="width: 100%; height: 400px;">
                        <canvas id="chartCompras" width="100" height="70"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card">
                <h6 class="text-center">Ventas</h6>
                <div class="card-body">
                    <div style="width: 100%; height: 400px;">
                        <canvas id="chartVentas" width="100" height="70"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
