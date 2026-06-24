@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 custom-heading">{{ __('Editar Gasto') }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">

        <form action="{{ route('gasto.update', $gasto->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <x-adminlte-input
                    name="concepto"
                    label="Concepto"
                    fgroup-class="col-md-8"
                    value="{{ old('concepto', $gasto->concepto) }}"
                />

                <x-adminlte-input
                    name="fecha"
                    type="date"
                    label="Fecha"
                    fgroup-class="col-md-4"
                    value="{{ old('fecha', $gasto->fecha) }}"
                />
            </div>

            <div class="row">
                <x-adminlte-input
                    name="monto"
                    type="number"
                    step="0.01"
                    label="Monto"
                    fgroup-class="col-md-4"
                    value="{{ old('monto', $gasto->monto) }}"
                />

                <x-adminlte-select
                    name="metodo_pago"
                    label="Método de Pago"
                    fgroup-class="col-md-4">
                    <option value="efectivo" {{ $gasto->metodo_pago=='efectivo'?'selected':'' }}>{{ __('Efectivo') }}</option>
                    <option value="transferencia" {{ $gasto->metodo_pago=='transferencia'?'selected':'' }}>{{ __('Transferencia') }}</option>
                    <option value="tarjeta" {{ $gasto->metodo_pago=='tarjeta'?'selected':'' }}>{{ __('Tarjeta') }}</option>
                </x-adminlte-select>

                <x-adminlte-select
                    name="estado"
                    label="Estado"
                    fgroup-class="col-md-4">
                    <option value="pendiente" {{ $gasto->estado=='pendiente'?'selected':'' }}>{{ __('Pendiente') }}</option>
                    <option value="aprobado" {{ $gasto->estado=='aprobado'?'selected':'' }}>{{ __('Aprobado') }}</option>
                    <option value="rechazado" {{ $gasto->estado=='rechazado'?'selected':'' }}>{{ __('Rechazado') }}</option>
                </x-adminlte-select>
            </div>

            <div class="row">
                <x-adminlte-textarea
                    name="observacion"
                    label="Observación"
                    fgroup-class="col-md-12">
                    {{ old('observacion', $gasto->observacion) }}
                </x-adminlte-textarea>
            </div>

            <div class="text-right mt-3">
                <a href="{{ route('gasto.index') }}" class="btn btn-danger">{{ __('Cancelar') }}</a>

                <x-adminlte-button
                    type="submit"
                    theme="primary"
                    icon="fas fa-save"
                    label="Actualizar Gasto"
                />
            </div>
        </form>

    </div>
</div>
@stop
