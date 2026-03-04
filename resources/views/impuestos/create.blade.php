@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 custom-heading">Registrar Impuesto</h1>
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
                title: 'Operación Exitosa',
                text: '{{ session('success') }}',
            });
        @endif

        @if (session('error'))
            Toast.fire({
                icon: 'error',
                title: 'Error',
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
                <form action="{{ route('impuestos.store') }}" method="post" autocomplete="off">
                    @csrf
                    @method('POST')

                    <div class="row">
                        <x-adminlte-input
                            name="descripcion"
                            label="Descripción"
                            placeholder="Ej: IVA 10%"
                            fgroup-class="col-md-8"
                            value="{{ old('descripcion') }}" />

                        <x-adminlte-input
                            name="valor"
                            label="Valor (%)"
                            placeholder="Ej: 10.00"
                            fgroup-class="col-md-4"
                            value="{{ old('valor') }}" />
                    </div>

                    <div class="row">
                        <div class="form-group col-md-12">
                            <a class="btn btn-danger" style="float: right;"
                               href="{{ route('impuestos.index') }}">
                                Cancelar
                            </a>

                            <x-adminlte-button
                                class="btn-group"
                                style="float: right;"
                                type="submit"
                                label="Registrar"
                                theme="primary"
                                icon="fas fa-lg fa-save" />
                        </div>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>
@stop
