@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 custom-heading">{{ __('Reportes de Ventas') }}</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('Generar Reportes de Ventas') }}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h5><i class="fas fa-calendar-alt"></i> {{ __('Ventas por Período') }}</h5>
                        <hr>
                        <p>{{ __('Resumen diario, semanal, mensual con comparativas y tendencias.') }}</p>
                        <form method="GET" action="{{ route('reportes.gestion.ventas.periodo') }}" target="_blank">
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
                        <h5><i class="fas fa-users"></i> {{ __('Ventas por Cliente') }}</h5>
                        <hr>
                        <p>{{ __('Top clientes por monto, frecuencia de compras e historial.') }}</p>
                        <form method="GET" action="{{ route('reportes.gestion.ventas.cliente') }}" target="_blank">
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label>{{ __('FECHA DESDE') }}</label>
                                    <input type="date" class="form-control" name="desde" value="{{ date('Y-m-01') }}" required>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>{{ __('FECHA HASTA') }}</label>
                                    <input type="date" class="form-control" name="hasta" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>{{ __('Cliente') }}</label>
                                    <select name="idcliente" class="form-control">
                                        <option value="">{{ __('Todos los clientes') }}</option>
                                        @foreach ($clientes as $item)
                                            <option value="{{ $item->id }}">{{ $item->razonsocial }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
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
                        <h5><i class="fas fa-box"></i> {{ __('Ventas por Producto') }}</h5>
                        <hr>
                        <p>{{ __('Productos más vendidos, rentabilidad por producto y margen de ganancia.') }}</p>
                        <form method="GET" action="{{ route('reportes.gestion.ventas.producto') }}" target="_blank">
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label>{{ __('FECHA DESDE') }}</label>
                                    <input type="date" class="form-control" name="desde" value="{{ date('Y-m-01') }}" required>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>{{ __('FECHA HASTA') }}</label>
                                    <input type="date" class="form-control" name="hasta" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>{{ __('Producto') }}</label>
                                    <select name="idproducto" class="form-control">
                                        <option value="">{{ __('Todos los productos') }}</option>
                                        @foreach ($productos as $item)
                                            <option value="{{ $item->id }}">{{ $item->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
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
