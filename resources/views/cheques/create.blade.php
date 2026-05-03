@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 custom-heading">Registrar Cheque</h1>
@stop

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('cheques.store') }}" method="POST" autocomplete="off">
                    @csrf

                    {{-- Tipo de cheque --}}
                    <div class="row">

                        <x-adminlte-select name="tipo" label="Tipo de Cheque"
                            fgroup-class="col-md-3" label-class="text-info">

                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-info">
                                    <i class="fas fa-exchange-alt"></i>
                                </div>
                            </x-slot>

                            <option value="cobrar">Cheque a Cobrar</option>
                            <option value="pagar">Cheque a Pagar</option>

                        </x-adminlte-select>


                        <x-adminlte-input name="numero_cheque" label="Número de Cheque"
                            placeholder="Número del cheque"
                            fgroup-class="col-md-3">

                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-primary">
                                    <i class="fas fa-money-check"></i>
                                </div>
                            </x-slot>

                        </x-adminlte-input>


                        <x-adminlte-input name="banco" label="Banco"
                            placeholder="Nombre del banco"
                            fgroup-class="col-md-3">

                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-success">
                                    <i class="fas fa-university"></i>
                                </div>
                            </x-slot>

                        </x-adminlte-input>


                        <x-adminlte-input name="titular" label="Titular"
                            placeholder="Nombre del titular"
                            fgroup-class="col-md-3">

                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-warning">
                                    <i class="fas fa-user"></i>
                                </div>
                            </x-slot>

                        </x-adminlte-input>

                    </div>


                    {{-- Monto y fechas --}}
                    <div class="row">

                        <x-adminlte-input name="monto" type="number" step="0.01"
                            label="Monto del Cheque"
                            placeholder="0.00"
                            fgroup-class="col-md-3"
                            label-class="text-success">

                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-success">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                            </x-slot>

                        </x-adminlte-input>


                        <x-adminlte-input name="fecha_emision" type="date"
                            label="Fecha Emisión"
                            fgroup-class="col-md-3">

                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-info">
                                    <i class="fas fa-calendar"></i>
                                </div>
                            </x-slot>

                        </x-adminlte-input>


                        <x-adminlte-input name="fecha_cobro" type="date"
                            label="Fecha de Cobro / Pago"
                            fgroup-class="col-md-3"
                            label-class="text-danger">

                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-danger">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                            </x-slot>

                        </x-adminlte-input>


                        <x-adminlte-select name="estado"
                            label="Estado del Cheque"
                            fgroup-class="col-md-3">

                            <option value="pendiente">Pendiente</option>
                            <option value="cobrado">Cobrado</option>
                            <option value="pagado">Pagado</option>
                            <option value="rechazado">Rechazado</option>

                        </x-adminlte-select>

                    </div>


                    {{-- Observación --}}
                    <div class="row">

                        <x-adminlte-textarea name="observacion"
                            label="Observación"
                            placeholder="Observaciones adicionales"
                            fgroup-class="col-md-12"
                            label-class="text-warning">

                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-warning">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                            </x-slot>

                        </x-adminlte-textarea>

                    </div>


                    {{-- Botones --}}
                    <div class="row">
                        <div class="form-group col-md-12 text-right">

                            <a class="btn btn-danger mx-1"
                                href="{{ route('cheques.index') }}">
                                Cancelar
                            </a>

                            <x-adminlte-button
                                type="submit"
                                label="Registrar"
                                theme="primary"
                                icon="fas fa-lg fa-save"
                            />

                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

@stop


@push('js')

<script>

var successMessage = "{{ session('success') }}";
var errorMessage = "{{ session('error') }}";

if (successMessage) {
    Swal.fire('Éxito', successMessage, 'success');
}
else if (errorMessage) {
    Swal.fire('Error', errorMessage, 'error');
}

</script>

@endpush