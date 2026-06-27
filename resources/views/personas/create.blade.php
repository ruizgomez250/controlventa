@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 custom-heading">{{ __('Registrar Persona') }}</h1>
@stop

@section('content')
@section('plugins.Sweetalert2', true)

@push('js')
    <script>
        $(document).ready(function() {
            var Toast = Swal.mixin({
                toast: true,
                position: 'bottom-end',
                color: '#716add',
                showConfirmButton: false,
                timer: 3000
            });

            @if (session('success'))
                Toast.fire({
                    icon: 'success',
                    title: '<label style="font-size: 1.6rem !important;">Operación Exitosa!</label>',
                    text: '{{ session('success') }}',
                });
            @endif

            @if (session('error'))
                Toast.fire({
                    icon: 'error',
                    title: '<label style="font-size: 1.6rem !important;">Error Inesperado!</label>',
                    text: '{{ session('error') }}',
                });
            @endif
        });
    </script>
@endpush
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('persona.store') }}" method="post">
                    @csrf
                    @method('POST')

                    <div class="row">
                        <x-adminlte-input name="nombre" label="Nombre"
                            placeholder="{{ __('Ingresar nombre') }}" fgroup-class="col-md-6"
                            value="{{ old('nombre') }}" label-class="text-primary" required>
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-primary">
                                    <i class="fas fa-user"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>
                        @error('nombre')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <x-adminlte-input name="apellido" label="Apellido"
                            placeholder="{{ __('Ingresar apellido') }}" fgroup-class="col-md-6"
                            value="{{ old('apellido') }}" label-class="text-info">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-info">
                                    <i class="fas fa-user"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>
                        @error('apellido')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <x-adminlte-input name="documento" label="Documento"
                            placeholder="{{ __('Ingresar documento') }}" fgroup-class="col-md-4"
                            value="{{ old('documento') }}">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-secondary">
                                    <i class="fas fa-id-card"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>
                        <x-adminlte-input name="telefono" label="Teléfono"
                            placeholder="{{ __('Ingresar teléfono') }}" fgroup-class="col-md-4"
                            value="{{ old('telefono') }}">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-success">
                                    <i class="fas fa-phone"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>
                        <x-adminlte-select name="estado" label="Estado" fgroup-class="col-md-4">
                            <option value="1">{{ __('Activo') }}</option>
                            <option value="0">{{ __('Inactivo') }}</option>
                        </x-adminlte-select>
                    </div>

                    <div class="row">
                        <x-adminlte-input name="direccion" label="Dirección"
                            placeholder="{{ __('Ingresar dirección') }}" fgroup-class="col-md-12"
                            value="{{ old('direccion') }}" label-class="text-danger">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-danger">
                                    <i class="fas fa-map-marker"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>

                    <div class="row">
                        <x-adminlte-textarea name="observacion" label="Observación" fgroup-class="col-md-12"
                            label-class="text-warning">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-warning">
                                    <i class="fas fa-lg fa-file-alt"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-textarea>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-12">
                            <a class="btn btn-danger mx-1" style="float: right;"
                                href="{{ route('persona.index') }}">{{ __('Cancelar') }}</a>
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
