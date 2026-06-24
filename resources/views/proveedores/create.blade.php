@extends('adminlte::page')



@section('content_header')
    <h1 class="m-0 custom-heading ">{{ __('Registrar Proveedor') }}</h1>
@stop

@section('content')
@section('plugins.BootstrapSwitch', true)
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
                    text:  '{{ session('success') }}',
                });
            @endif

            @if (session('error'))
                Toast.fire({
                    icon: 'error',
                    title: '<label style="font-size: 1.6rem !important;">Error Inesperado!</label>',
                    text: '{{ session('error') }}',
                });
            @endif

            // Agregar confirmación de eliminación
            $('.delete-button').on('click', function() {
                var form = $(this).closest('.delete-form');
                Swal.fire({
                    title: 'Confirmar eliminación',
                    text: '¿Estás seguro de que deseas eliminar este Proveedor?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('proveedor.store') }}" method="post">
                    @csrf
                    @method('POST')
                    {{-- With label, invalid feedback disabled and form group class --}}

                    <div class="row">
                        <x-adminlte-input name="razonsocial" label="Razón Social"
                            placeholder="{{ __('Ingresar nombre de persona o empresa') }}" fgroup-class="col-md-6"
                            value="{{ old('razonsocial') }}" style="text-align: center;" label-class="text-primary">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-primary">
                                    <i class="fas fa-user "></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>
                        @error('razonsocial')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <x-adminlte-input name="ruc" label="Ruc" placeholder="{{ __('Ingresar ruc') }}"
                            fgroup-class="col-md-4" />
                        @error('ruc')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        @php
                            $config = [
                                'onColor' => 'success',
                                'offColor' => 'gray',
                                'onText' => 'Activo',
                                'offText' => 'Inactivo',
                                'state' => false,
                                'labelText' => '<i class="fas fa-power-off text-muted"></i>',
                            ];
                        @endphp
                        <x-adminlte-select name="estado" label="Estado del Cliente"
                            data-placeholder="{{ __('Seleccionar una opción...') }}" fgroup-class="col-md-3">
                            <option value="1">{{ __('Activo') }}</option>
                            <option value="0">{{ __('Inactivo') }}</option>
                        </x-adminlte-select>
                    </div>

                    <div class="row">

                        <x-adminlte-input name="celular" label="Celular" placeholder="{{ __('Ingresar número de celular') }}"
                            fgroup-class="col-md-3" value="{{ old('celular') }}" style="text-align: center;"
                            label-class="text-success">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-success">
                                    <i class="fas fa-phone "></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>

                        <x-adminlte-input name="correo" type="email" label="Email"
                            placeholder="{{ __('Ingresar dirección de correo electronico') }}" fgroup-class="col-md-3"
                            value="{{ old('correo') }}" style="text-align: center;" label-class="text-info">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-info">
                                    <i class="fas fa-at "></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>
                        <x-adminlte-input name="direccion" label="Dirección"
                            placeholder="{{ __('Ingresar dirección de domicilio') }}" fgroup-class="col-md-6"
                            value="{{ old('direccion') }}" style="text-align: center;" label-class="text-danger">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-danger">
                                    <i class="fas fa-map-marker "></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>

                    <div class="row">
                        {{-- Disabled --}}
                        <x-adminlte-textarea name="observacion" label="Observación" fgroup-class="col-md-12"
                            label-class="text-warning">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-warning">
                                    <i class="fas fa-lg fa-file-alt "></i>
                                </div>
                            </x-slot>
                        </x-adminlte-textarea>
                    </div>


                    <div class="row">
                        <div class="form-group col-md-12">
                            <a class="btn btn-danger mx-1" style="float: right;"
                                href="{{ route('proveedor.index') }}">{{ __('Cancelar') }}</a>
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
