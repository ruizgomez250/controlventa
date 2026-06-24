@extends('adminlte::page')

@section('title', 'Nuevo Porcentaje por Cuota')

@section('content_header')
    <h1 class="m-0">{{ __('Nuevo Porcentaje por Cuota') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('tablaporc.store') }}" method="post" autocomplete="off">
                        @csrf
                        @method('POST')

                        <div class="row">
                            <x-adminlte-input name="cuota" id="cuota" label="Cantidad de Cuotas"
                                placeholder="{{ __('Ej: 3') }}" fgroup-class="col-md-4" label-class="text-info" type="number"
                                min="2" required>
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-info">
                                        <i class="fas fa-coins"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>

                            <x-adminlte-input name="porcentaje" id="porcentaje" label="Porcentaje de Interés %"
                                placeholder="{{ __('Ej: 5.00') }}" fgroup-class="col-md-4" label-class="text-info" type="number"
                                step="0.01" min="0" required>
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-info">
                                        <i class="fas fa-percentage"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>

                        <div class="form-group">
                            <label>{{ __('Estado') }}</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="estado" name="estado" value="1" checked>
                                <label class="form-check-label" for="estado">{{ __('Activo') }}</label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-12">
                                <a class="btn btn-danger mx-1" style="float: right;"
                                    href="{{ route('tablaporc.index') }}">{{ __('Cancelar') }}</a>
                                <x-adminlte-button class="btn-group mx-1" style="float: right;" type="submit"
                                    label="Registrar" theme="primary" icon="fas fa-lg fa-save" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop