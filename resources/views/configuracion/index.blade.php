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

            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@endpush
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('configuracion.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            @php
                                $estadocondv = 0;
                                $estadopagos = 0;
                                $idioma = 'es';
                                $moneda = 'Gs.';
                            @endphp
                            @foreach ($configuraciones as $configuracion)
                                @if ($configuracion->descripcion == 'condicionv')
                                    @php $estadocondv = $configuracion->estado; @endphp
                                @elseif ($configuracion->descripcion == 'ventas')
                                    @php $estadopagos = $configuracion->estado; @endphp
                                @elseif ($configuracion->descripcion == 'idioma')
                                    @php $idioma = $configuracion->observacion; @endphp
                                @elseif ($configuracion->descripcion == 'moneda')
                                    @php $moneda = $configuracion->observacion; @endphp
                                @endif
                            @endforeach

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

                        <hr>
                        <h4><i class="fas fa-file-invoice"></i> {{ __('Facturación Electrónica Paraguay (SIFEN/e-Kuatia)') }}</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ __('Habilitar Facturación Electrónica') }}</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="sifen_habilitado"
                                            name="sifen_habilitado" {{ $sifenConfig->habilitado ? 'checked' : '' }}
                                            onchange="document.getElementById('sifen_fields').style.display = this.checked ? 'block' : 'none'; document.getElementById('sifen_habilitado_label').innerText = this.checked ? '{{ __('Habilitado') }}' : '{{ __('Deshabilitado') }}';">
                                        <label class="custom-control-label" for="sifen_habilitado" id="sifen_habilitado_label">
                                            {{ $sifenConfig->habilitado ? __('Habilitado') : __('Deshabilitado') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="sifen_fields" style="{{ $sifenConfig->habilitado ? '' : 'display:none' }}">
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ __('Ambiente') }}</label>
                                    <select name="sifen_ambiente" class="form-control">
                                        <option value="1" {{ $sifenConfig->ambiente == 1 ? 'selected' : '' }}>{{ __('Testing') }}</option>
                                        <option value="2" {{ $sifenConfig->ambiente == 2 ? 'selected' : '' }}>{{ __('Producción') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ __('RUC Emisor') }}</label>
                                    <input type="text" name="sifen_ruc_emisor" class="form-control"
                                        value="{{ $sifenConfig->ruc_emisor }}" placeholder="Ej: 80000000">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ __('DV') }}</label>
                                    <input type="text" name="sifen_dv" class="form-control"
                                        value="{{ $sifenConfig->dv }}" placeholder="Ej: 1" maxlength="3">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ __('Razón Social') }}</label>
                                    <input type="text" name="sifen_razon_social" class="form-control"
                                        value="{{ $sifenConfig->razon_social }}" placeholder="Ej: MI EMPRESA S.A.">
                                </div>
                            </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('Dirección (completa)') }}</label>
            <input type="text" name="sifen_direccion" class="form-control"
                value="{{ $sifenConfig->direccion }}" placeholder="Ej: Av. Principal 123">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('Calle Principal') }}</label>
            <input type="text" name="sifen_calle_principal" class="form-control"
                value="{{ $sifenConfig->calle_principal }}" placeholder="Ej: Av. Principal">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('Número de Casa') }}</label>
            <input type="text" name="sifen_numero_casa" class="form-control"
                value="{{ $sifenConfig->numero_casa }}" placeholder="Ej: 123">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('Calle Secundaria') }}</label>
            <input type="text" name="sifen_calle_secundaria" class="form-control"
                value="{{ $sifenConfig->calle_secundaria }}" placeholder="Ej: Entre calles">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('Complemento Dirección') }}</label>
            <input type="text" name="sifen_complemento_direccion" class="form-control"
                value="{{ $sifenConfig->complemento_direccion }}" placeholder="Ej: Edificio, piso">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('Teléfono') }}</label>
                                    <input type="text" name="sifen_telefono" class="form-control"
                                        value="{{ $sifenConfig->telefono }}" placeholder="Ej: 021000000">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ __('Email') }}</label>
                                    <input type="email" name="sifen_email" class="form-control"
                                        value="{{ $sifenConfig->email }}" placeholder="Ej: info@empresa.com">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ __('Establecimiento') }}</label>
                                    <input type="text" name="sifen_establecimiento" class="form-control"
                                        value="{{ $sifenConfig->establecimiento }}" maxlength="4">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ __('Punto de Expedición') }}</label>
                                    <input type="text" name="sifen_punto_expedicion" class="form-control"
                                        value="{{ $sifenConfig->punto_expedicion }}" maxlength="4">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>{{ __('Certificado Digital P12') }}</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="sifen_certificado_p12"
                                            name="sifen_certificado_p12" accept=".p12,.pfx">
                                        <label class="custom-file-label" for="sifen_certificado_p12">
                                            {{ $sifenConfig->certificado_p12 ? __('Certificado cargado') : __('Seleccionar archivo .p12') }}
                                        </label>
                                    </div>
                                    @if($sifenConfig->certificado_p12)
                                        <div class="form-check mt-2">
                                            <input type="checkbox" class="form-check-input" id="sifen_limpiar_certificado" name="sifen_limpiar_certificado">
                                            <label class="form-check-label text-danger" for="sifen_limpiar_certificado">{{ __('Eliminar certificado actual') }}</label>
                                        </div>
                                    @endif
                                </div>
                            </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('Contraseña del Certificado') }}</label>
            <input type="password" name="sifen_certificado_password" class="form-control"
                value="{{ $sifenConfig->certificado_password }}" placeholder="********">
        </div>
    </div>
</div>
{{-- Nueva fila: Códigos geográficos y actividad económica --}}
<div class="row mt-3 border-top pt-3">
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ __('Código Departamento') }}</label>
            <input type="number" name="sifen_departamento_codigo" class="form-control"
                value="{{ $sifenConfig->departamento_codigo }}" placeholder="Ej: 1">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ __('Código Distrito') }}</label>
            <input type="number" name="sifen_distrito_codigo" class="form-control"
                value="{{ $sifenConfig->distrito_codigo }}" placeholder="Ej: 1">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ __('Código Ciudad') }}</label>
            <input type="number" name="sifen_ciudad_codigo" class="form-control"
                value="{{ $sifenConfig->ciudad_codigo }}" placeholder="Ej: 1">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ __('Nombre Sucursal') }}</label>
            <input type="text" name="sifen_nombre_sucursal" class="form-control"
                value="{{ $sifenConfig->nombre_sucursal }}" placeholder="Ej: Casa Matriz">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('Tipo Contribuyente') }}</label>
            <input type="number" name="sifen_tipo_contribuyente" class="form-control"
                value="{{ $sifenConfig->tipo_contribuyente }}" placeholder="Ej: 2">
            <small class="text-muted">1=Persona Física, 2=Persona Jurídica</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('Código Actividad Económica') }}</label>
            <input type="number" name="sifen_actividad_economica_codigo" class="form-control"
                value="{{ $sifenConfig->actividad_economica_codigo }}" placeholder="Ej: 620">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('Descripción Actividad Económica') }}</label>
            <input type="text" name="sifen_actividad_economica_descripcion" class="form-control"
                value="{{ $sifenConfig->actividad_economica_descripcion }}"
                placeholder="Ej: ACTIVIDADES DE PROGRAMACIÓN">
        </div>
    </div>
</div>
{{-- Nueva fila: Credenciales CSC --}}
<div class="row border-top pt-3">
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('ID CSC') }}</label>
            <input type="text" name="sifen_csc_id" class="form-control"
                value="{{ $sifenConfig->csc_id }}" placeholder="Ej: 0001">
        </div>
    </div>
    <div class="col-md-8">
        <div class="form-group">
            <label>{{ __('CSC (Código de Seguridad)') }}</label>
            <input type="text" name="sifen_csc_codigo" class="form-control"
                value="{{ $sifenConfig->csc_codigo }}" placeholder="Ej: ABCD0000000000000000000000000000">
            <small class="text-muted">Código de 32 caracteres proporcionado por la SET/DNIT</small>
        </div>
    </div>
</div>

                        </div>
                        <button class="btn btn-primary mt-3" type="submit">{{ __('Guardar') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
