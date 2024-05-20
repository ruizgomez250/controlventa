@extends('adminlte::page')



@section('content_header')
    <h1 class="m-0 custom-heading">Generar Reportes de Caja</h1>
@stop
@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/jquery-ui-1.13.2/jquery-ui.min.css') }}">
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    {{-- 'id', 'codigo', 'descripcion', 'detalle', 'id_categoria', 'id_estado','pcosto', 'pventa', 'observacion' --}}





                    <div class="row">
                        {{-- With Label --}}

                        <div class="form-group">
                            <label for="fechaemision">FECHA DESDE</label>
                            <input type="date" class="form-control" id="desde1" name="fechaemision"
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="fechaemision">FECHA HASTA</label>
                            <input type="date" class="form-control" id="hasta1" name="fechaemision"
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                        <x-adminlte-select2 name="idusuario" id="idusuario" label="USUARIO DE CAJA"
                            data-placeholder="Seleccionar un proveedor..." fgroup-class="col-md-4"
                            onchange="actualizarNumeroDocumento()">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-secondary">
                                    <i class="fas fa-user"></i>
                                </div>
                            </x-slot>
                            @foreach ($usuarios as $item)
                                <option value={{ $item->id }}>{{ $item->name }}</option>
                            @endforeach
                        </x-adminlte-select2>
                        <button class="btn btn-secondary" style="float: right;" onclick="generarPDF()">Generar</button>



                    </div>

                    <hr>

                    <div class="row">
                        {{-- With Label --}}
                        @php
                            $config1 = ['format' => 'DD-MM-YYYY'];
                        @endphp
                        <div class="form-group">
                            <label for="fechaemision">FECHA DESDE</label>
                            <input type="date" class="form-control" id="desde2" name="fechaemision"
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="fechaemision">FECHA HASTA</label>
                            <input type="date" class="form-control" id="hasta2" name="fechaemision"
                                value="{{ date('Y-m-d') }}" required>
                        </div>

                        <x-adminlte-button class="btn" style="float: right;" label="Generar" theme="secondary" onclick="generarPDFsinuser()"
                            icon="fas fa-lg fa-save" />


                    </div>

                    <!-- Agrega este elemento para mostrar la suma total -->

                    </form>

                </div>
            </div>
        </div>
    </div>

@stop

@push('js')
    <script src="{{ asset('vendor/jquery-ui-1.13.2/jquery-ui.min.js') }}"></script>
    <script>
        function generarPDF() {
            var desde = document.getElementById('desde1').value;
            var hasta = document.getElementById('hasta1').value;
            var idusuario = document.getElementById('idusuario').value;

            var url = `/controlventa/public/cajareportepdf/${desde}/${hasta}/${idusuario}`;
            window.open(url, '_blank');
        }
        function generarPDFsinuser() {
            var desde = document.getElementById('desde2').value;
            var hasta = document.getElementById('hasta2').value;

            var url = `/controlventa/public/cajareportepdf/${desde}/${hasta}`;
            window.open(url, '_blank');
        }
    </script>
@endpush
