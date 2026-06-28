@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 custom-heading">{{ __('Reportes de Gestión de Stock') }}</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('Reportes de Inventario') }}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h5><i class="fas fa-boxes"></i> {{ __('Inventario Actual') }}</h5>
                        <hr>
                        <p>{{ __('Listado completo de productos con stock actual, mínimo, máximo y valorización de stock (costo vs venta).') }}</p>
                        <button class="btn btn-secondary" onclick="window.open('{{ route('reportes.gestion.stock.inventario') }}', '_blank')">
                            <i class="fas fa-file-pdf"></i> {{ __('Generar PDF') }}
                        </button>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-12">
                        <h5><i class="fas fa-exclamation-triangle"></i> {{ __('Productos con Stock Bajo') }}</h5>
                        <hr>
                        <p>{{ __('Productos que están por debajo del stock mínimo o sin stock.') }}</p>
                        <button class="btn btn-secondary" onclick="window.open('{{ route('reportes.gestion.stock.bajo') }}', '_blank')">
                            <i class="fas fa-file-pdf"></i> {{ __('Generar PDF') }}
                        </button>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-12">
                        <h5><i class="fas fa-exchange-alt"></i> {{ __('Movimientos de Stock') }}</h5>
                        <hr>
                        <p>{{ __('Entradas y salidas por período. Detalle por producto.') }}</p>
                        <form method="GET" action="{{ route('reportes.gestion.stock.movimientos') }}" target="_blank">
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label>{{ __('FECHA DESDE') }}</label>
                                    <input type="date" class="form-control" name="desde" value="{{ date('Y-m-d') }}" required>
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

                <hr>

                <div class="row">
                    <div class="col-md-12">
                        <h5><i class="fas fa-sync-alt"></i> {{ __('Rotación de Productos') }}</h5>
                        <hr>
                        <p>{{ __('Productos más vendidos, menor rotación y tiempo promedio de permanencia en stock.') }}</p>
                        <button class="btn btn-secondary" onclick="window.open('{{ route('reportes.gestion.stock.rotacion') }}', '_blank')">
                            <i class="fas fa-file-pdf"></i> {{ __('Generar PDF') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
