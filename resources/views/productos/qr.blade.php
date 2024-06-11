@extends('adminlte::page')



@section('content_header')
    <h1 class="m-0 custom-heading">Generar Qr del Producto</h1>
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

                        
                        <x-adminlte-select2 name="idproducto" id="idproducto" label="PRODUCTO"
                            data-placeholder="Seleccionar un proveedor..." fgroup-class="col-md-4"
                            onchange="actualizarNumeroDocumento()">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-secondary">
                                    <i class="fas fa-user"></i>
                                </div>
                            </x-slot>
                            @foreach ($productos as $item)
                                <option value={{ $item->id }}>{{ $item->descripcion }}</option>
                            @endforeach
                        </x-adminlte-select2>
                        <button class="btn btn-secondary" style="float: right;" onclick="generarPDF()">Generar</button>



                    </div>
                    <!-- Agrega este elemento para mostrar la suma total -->


                </div>
            </div>
        </div>
    </div>

@stop

@push('js')
    <script src="{{ asset('vendor/jquery-ui-1.13.2/jquery-ui.min.js') }}"></script>
    <script>
        function generarPDF() {
            
            var id = document.getElementById('idproducto').value;

            var url = "{{ asset('qrproducto') }}/" + id;
            window.open(url, '_blank');
        }
        
    </script>
@endpush
