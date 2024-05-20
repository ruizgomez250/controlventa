@extends('adminlte::page')



@section('content_header')
<h1 class="m-0 custom-heading">Registrar Cliente</h1>
@stop
@section('plugins.Sweetalert2', true)

@push('js')
    <script>
        $(document).ready(function() {
            var Toast = Swal.mixin({
                toast: true,
                position: 'bottom-end',
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
        });
    </script>
@endpush
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-body">
                    <form action="{{ route('cliente.store') }}" method="post" autocomplete="off">
                        @csrf
                        @method('POST')
                        <div class="row">
                            <x-adminlte-input name="razonsocial" label="Razón Social"
                                placeholder="Ingresar nombre de persona o empresa" fgroup-class="col-md-8"
                                value="{{ old('razonsocial') }}" />
                            <x-adminlte-input name="ruc" label="Ruc" placeholder="Ingresar ruc"
                                fgroup-class="col-md-4" value="{{ old('ruc') }}" />
                        </div>

                        <div class="row">
                            <x-adminlte-input name="celular" label="Celular" placeholder="Ingresar número de celular"
                                fgroup-class="col-md-3" value="{{ old('celular') }}" />
                            <x-adminlte-input name="correo" type="email" label="Email"
                                placeholder="Ingresar dirección de correo electronico" fgroup-class="col-md-3"
                                value="{{ old('correo') }}" />
                            <x-adminlte-input name="direccion" label="Dirección"
                                placeholder="Ingresar dirección de domicilio" fgroup-class="col-md-6"
                                value="{{ old('direccion') }}" />
                        </div>

                        <div class="row">
                            {{-- Disabled --}}
                            <x-adminlte-textarea name="observacion" label="Observación" fgroup-class="col-md-12">
                                {{ old('observacion') }}
                            </x-adminlte-textarea>
                        </div>

                        <div class="row">
                            <x-adminlte-select name="estado" label="Estado del Cliente"
                                data-placeholder="Seleccionar una opción..." fgroup-class="col-md-3">                               
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                            </x-adminlte-select>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-12">
                                <a class="btn btn-danger" style="float: right;" href="{{route('cliente.index')}}">Cancelar</a>
                                <x-adminlte-button class="btn-group" style="float: right;" type="submit" label="Registrar"
                                    theme="primary" icon="fas fa-lg fa-save" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
