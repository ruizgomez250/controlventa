@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 custom-heading">{{ __('Reportes Financieros') }}</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('Generar Reportes Financieros') }}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h5><i class="fas fa-hand-holding-usd"></i> {{ __('Cuentas por Cobrar') }}</h5>
                        <hr>
                        <p>{{ __('Ventas a crédito pendientes, antigüedad de deudas y próximos vencimientos.') }}</p>
                        <button class="btn btn-secondary" onclick="window.open('{{ route('reportes.gestion.financiero.cobrar') }}', '_blank')">
                            <i class="fas fa-file-pdf"></i> {{ __('Generar PDF') }}
                        </button>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-12">
                        <h5><i class="fas fa-credit-card"></i> {{ __('Cuentas por Pagar') }}</h5>
                        <hr>
                        <p>{{ __('Compras pendientes de pago, cheques emitidos y por cobrar, proveedores con saldos.') }}</p>
                        <button class="btn btn-secondary" onclick="window.open('{{ route('reportes.gestion.financiero.pagar') }}', '_blank')">
                            <i class="fas fa-file-pdf"></i> {{ __('Generar PDF') }}
                        </button>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-12">
                        <h5><i class="fas fa-chart-pie"></i> {{ __('Margen de Ganancia') }}</h5>
                        <hr>
                        <p>{{ __('Por producto, por categoría y general de la empresa.') }}</p>
                        <form method="GET" action="{{ route('reportes.gestion.financiero.margen') }}" target="_blank">
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
                                    <label>{{ __('Tipo') }}</label>
                                    <select name="tipo" class="form-control">
                                        <option value="general">{{ __('General de la Empresa') }}</option>
                                        <option value="categoria">{{ __('Por Categoría') }}</option>
                                        <option value="producto">{{ __('Por Producto') }}</option>
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
