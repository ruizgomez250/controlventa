@extends('adminlte::page')



@section('content_header')
    <h1 class="m-0 custom-heading">{{ __('Configuracion') }}</h1>
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
                            
                            {{-- <div class="col-3">
                                <div class="card" style="width: 14rem;margin-top: -18px">
                                    <div class="card-body">
                                        <label class="custom-heading">Lector QR Webcam</label>--}}
                                        @php
                                            $estadocondv = 0;
                                            $estadopagos = 0;
                                            $idioma = 'es';
                                            $moneda = 'Gs.';
                                        @endphp
                                            @foreach ($configuraciones as $configuracion)
                                                @if ($configuracion->descripcion == 'condicionv')
                                                    @php
                                                        $estadocondv = $configuracion->estado;
                                                    @endphp
                                                @elseif ($configuracion->descripcion == 'ventas')
                                                    @php
                                                        $estadopagos = $configuracion->estado;
                                                    @endphp
                                                @elseif ($configuracion->descripcion == 'idioma')
                                                    @php
                                                        $idioma = $configuracion->observacion;
                                                    @endphp
                                                @elseif ($configuracion->descripcion == 'moneda')
                                                    @php
                                                        $moneda = $configuracion->observacion;
                                                    @endphp
                                                @endif
                                            @endforeach
                                   {{-- </div>
                                </div>
                            </div> --}}
                            <div class="col-3">
                                <div class="card" style="width: 14rem;margin-top: -18px">
                                    <div class="card-body">
                                        <label for="">{{ __('Venta Mayorista') }}</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="condicion"
                                                id="inlineRadio1" value="apartir" {{ $estadocondv == 0 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="inlineRadio1">{{ __('A Partir') }}</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="condicion"
                                                id="inlineRadio2" value="cadavez" {{ $estadocondv == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="inlineRadio2">{{ __('Cada Vez') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="card" style="width: 14rem;margin-top: -18px">
                                    <div class="card-body">
                                        <label class="custom-heading">{{ __('Pago Simplificado') }}</label>

                                        <label>
                                            <input type="checkbox" name="pagos" class="configuracion-checkbox"
                                                {{ $estadopagos == 1 ? 'checked' : '' }}>
                                        </label>

                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="card" style="width: 14rem;margin-top: -18px">
                                    <div class="card-body">
                                        <label for="">{{ __('Idioma / Language') }}</label>
                                        <select name="idioma" class="form-control">
                                            <option value="es" {{ $idioma == 'es' ? 'selected' : '' }}>{{ __('Español') }}</option>
                                            <option value="en" {{ $idioma == 'en' ? 'selected' : '' }}>{{ __('English') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="card" style="width: 14rem;margin-top: -18px">
                                    <div class="card-body">
                                        <label for="">{{ __('Símbolo de Moneda') }}</label>
                                        <input type="text" name="moneda" class="form-control"
                                            value="{{ $moneda }}" maxlength="10"
                                            placeholder="{{ __('Ej: Gs., $, €') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button class="btn
                                        btn-primary"
                            type="submit">{{ __('Guardar') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
