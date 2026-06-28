@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 custom-heading">{{ __('Reportes de Gestión') }}</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('Generar Reportes de Gestión') }}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h5><i class="fas fa-user-tie"></i> {{ __('Rendimiento de Vendedores') }}</h5>
                        <hr>
                        <p>{{ __('Ventas por usuario/vendedor, comisiones generadas, comparativa de rendimiento.') }}</p>
                        <form method="GET" action="{{ route('reportes.gestion.management.vendedores') }}" target="_blank">
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label>{{ __('FECHA DESDE') }}</label>
                                    <input type="date" class="form-control" name="desde" value="{{ date('Y-m-01') }}" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>{{ __('FECHA HASTA') }}</label>
                                    <input type="date" class="form-control" name="hasta" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-secondary form-control">
                                        <i class="fas fa-file-pdf"></i> {{ __('Generar') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-12">
                        <h5><i class="fas fa-chart-line"></i> {{ __('Análisis de Clientes') }}</h5>
                        <hr>
                        <p>{{ __('Nuevos clientes vs recurrentes, valor promedio de compra, segmentación.') }}</p>
                        <form method="GET" action="{{ route('reportes.gestion.management.clientes') }}" target="_blank">
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label>{{ __('FECHA DESDE') }}</label>
                                    <input type="date" class="form-control" name="desde" value="{{ date('Y-m-01') }}" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>{{ __('FECHA HASTA') }}</label>
                                    <input type="date" class="form-control" name="hasta" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-secondary form-control">
                                        <i class="fas fa-file-pdf"></i> {{ __('Generar') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
