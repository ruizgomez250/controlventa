@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 custom-heading">{{ __('Registrar Gasto') }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">

        @if(session('success'))
            <x-adminlte-alert theme="success" title="{{ __('Éxito') }}">
                {{ session('success') }}
            </x-adminlte-alert>
        @endif

        @if(session('error'))
            <x-adminlte-alert theme="danger" title="{{ __('Error') }}">
                {{ session('error') }}
            </x-adminlte-alert>
        @endif

        <form action="{{ route('gasto.store') }}" method="POST">
            @csrf

            <div class="row">
                <x-adminlte-input
                    name="concepto"
                    label="Concepto"
                    fgroup-class="col-md-8"
                    value="{{ old('concepto') }}"
                />

                <x-adminlte-input
                    name="fecha"
                    type="date"
                    label="Fecha"
                    fgroup-class="col-md-4"
                    value="{{ old('fecha', now()->format('Y-m-d')) }}"
                />
            </div>

            <div class="row">
                <x-adminlte-input
                    name="monto"
                    type="number"
                    step="0.01"
                    label="Monto"
                    fgroup-class="col-md-4"
                    value="{{ old('monto') }}"
                />

                <x-adminlte-select
                    name="metodo_pago"
                    label="Método de Pago"
                    fgroup-class="col-md-4">
                    <option value="efectivo" selected>{{ __('Efectivo') }}</option>
                    <option value="transferencia">{{ __('Transferencia') }}</option>
                    <option value="tarjeta">{{ __('Tarjeta') }}</option>
                </x-adminlte-select>

                <x-adminlte-select
                    name="estado"
                    label="Estado"
                    fgroup-class="col-md-4">
                    <option value="pendiente" >{{ __('Pendiente') }}</option>
                    <option value="aprobado" selected>{{ __('Aprobado') }}</option>
                    <option value="rechazado" >{{ __('Rechazado') }}</option>
                </x-adminlte-select>
            </div>

            <div class="row">
                <x-adminlte-textarea
                    name="observacion"
                    label="Observación"
                    fgroup-class="col-md-12">
                    {{ old('observacion') }}
                </x-adminlte-textarea>
            </div>

            <div class="text-right mt-3">
                <a href="{{ url()->previous() }}" class="btn btn-danger">{{ __('Cancelar') }}</a>

                <x-adminlte-button
                    type="submit"
                    theme="primary"
                    icon="fas fa-save"
                    label="Registrar Gasto"
                />
            </div>

        </form>
    </div>
</div>
@stop
