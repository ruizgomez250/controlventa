@extends('adminlte::page')



@section('content_header')
    <h1 class="m-0 custom-heading">Configuracion</h1>
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
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-body">
                    <form method="POST" action="{{ route('configuracion.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-3">
                                <h5 class="custom-heading">Lector QR Webcam</h5>
                                @foreach ($configuraciones as $configuracion)
                                    @if ($configuracion->descripcion == 'qr')
                                        <label>
                                            <input type="hidden" name="idqr"
                                                value="{{ $configuracion->id }}">
                                            <input type="checkbox" name="qr"
                                                class="configuracion-checkbox"
                                                {{ $configuracion->estado == 1 ? 'checked' : '' }}>
                                            Leer
                                        </label>
                                    @endif
                                @endforeach

                            </div>


                        </div>
                        <button class="btn
                                        btn-primary"
                            type="submit">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
