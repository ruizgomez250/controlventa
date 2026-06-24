@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 custom-heading">{{ __('Reporte de Ventas por Estado') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Generar Reporte de Ventas') }}</h3>
                    <div class="card-tools">
                        <span class="badge badge-warning">{{ __('Estado 1 = Vendido') }}</span>
                        <span class="badge badge-success ml-2">{{ __('Estado 2 = Cobrado') }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h5>{{ __('Reporte por Usuario') }}</h5>
                            <hr>
                        </div>
                        
                        <div class="form-group col-md-3">
                            <label>{{ __('FECHA DESDE') }}</label>
                            <input type="date" class="form-control" id="desde1" value="{{ date('Y-m-d') }}">
                        </div>
                        
                        <div class="form-group col-md-3">
                            <label>{{ __('FECHA HASTA') }}</label>
                            <input type="date" class="form-control" id="hasta1" value="{{ date('Y-m-d') }}">
                        </div>
                        
                        <div class="form-group col-md-4">
                            <label>{{ __('USUARIO') }}</label>
                            <select class="form-control" id="idusuario">
                                <option value="">{{ __('Seleccionar usuario...') }}</option>
                                @foreach ($usuarios as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group col-md-2">
                            <label>{{ __('&nbsp;') }}</label>
                            <button class="btn btn-secondary form-control" onclick="generarPDF()">
                                <i class="fas fa-file-pdf"></i>{{ __('Generar') }}</button>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-12">
                            <h5>{{ __('Reporte General') }}</h5>
                            <hr>
                        </div>
                        
                        <div class="form-group col-md-4">
                            <label>{{ __('FECHA DESDE') }}</label>
                            <input type="date" class="form-control" id="desde2" value="{{ date('Y-m-d') }}">
                        </div>
                        
                        <div class="form-group col-md-4">
                            <label>{{ __('FECHA HASTA') }}</label>
                            <input type="date" class="form-control" id="hasta2" value="{{ date('Y-m-d') }}">
                        </div>
                        
                        <div class="form-group col-md-4">
                            <label>{{ __('&nbsp;') }}</label>
                            <button class="btn btn-secondary form-control" onclick="generarPDFsinuser()">
                                <i class="fas fa-file-pdf"></i>{{ __('Generar') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
<script>
    function generarPDF() {
        var desde = document.getElementById('desde1').value;
        var hasta = document.getElementById('hasta1').value;
        var idusuario = document.getElementById('idusuario').value;
        
        if (!desde || !hasta) {
            alert('Por favor seleccione las fechas');
            return;
        }
        
        if (!idusuario) {
            alert('Por favor seleccione un usuario');
            return;
        }
        
        var url = `{{ url('/') }}/reporteventasnuevo/${desde}/${hasta}/${idusuario}`;
        window.open(url, '_blank');
    }
    
    function generarPDFsinuser() {
        var desde = document.getElementById('desde2').value;
        var hasta = document.getElementById('hasta2').value;
        
        if (!desde || !hasta) {
            alert('Por favor seleccione las fechas');
            return;
        }
        
        var url = `{{ url('/') }}/reporteventasnuevo/${desde}/${hasta}`;
        window.open(url, '_blank');
    }
</script>
@endpush