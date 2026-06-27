@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 custom-heading">{{ __('Editar Persona') }}</h1>
@stop

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('persona.update', $persona) }}" method="post">
                        @csrf
                        @method('put')

                        <div class="row">
                            <x-adminlte-input name="nombre" label="Nombre"
                                placeholder="{{ __('Ingresar nombre') }}" fgroup-class="col-md-6"
                                value="{{ $persona->nombre }}" label-class="text-primary" required>
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-primary">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                            <x-adminlte-input name="apellido" label="Apellido"
                                placeholder="{{ __('Ingresar apellido') }}" fgroup-class="col-md-6"
                                value="{{ $persona->apellido }}" label-class="text-info">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-info">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>

                        <div class="row">
                            <x-adminlte-input name="documento" label="Documento"
                                placeholder="{{ __('Ingresar documento') }}" fgroup-class="col-md-4"
                                value="{{ $persona->documento }}">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-secondary">
                                        <i class="fas fa-id-card"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                            <x-adminlte-input name="telefono" label="Teléfono"
                                placeholder="{{ __('Ingresar teléfono') }}" fgroup-class="col-md-4"
                                value="{{ $persona->telefono }}">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-success">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                            <x-adminlte-select name="estado" label="Estado" fgroup-class="col-md-4">
                                <option value="1" {{ $persona->estado == 1 ? 'selected' : '' }}>{{ __('Activo') }}</option>
                                <option value="0" {{ $persona->estado == 0 ? 'selected' : '' }}>{{ __('Inactivo') }}</option>
                            </x-adminlte-select>
                        </div>

                        <div class="row">
                            <x-adminlte-input name="direccion" label="Dirección"
                                placeholder="{{ __('Ingresar dirección') }}" fgroup-class="col-md-12"
                                value="{{ $persona->direccion }}" label-class="text-danger">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-danger">
                                        <i class="fas fa-map-marker"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>

                        <div class="row">
                            <x-adminlte-textarea name="observacion" label="Observación" fgroup-class="col-md-12"
                                label-class="text-warning">{{ $persona->observacion }}
                            </x-adminlte-textarea>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-12">
                                <a class="btn btn-danger" style="float: right;"
                                    href="{{ route('persona.index') }}">{{ __('Cancelar') }}</a>
                                <x-adminlte-button class="btn-group mr-2" style="float: right;" type="submit"
                                    label="Guardar" theme="primary" icon="fas fa-lg fa-save" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
