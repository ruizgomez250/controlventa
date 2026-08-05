@extends('adminlte::page')

@section('title', 'Nueva Empresa')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 custom-heading">
            <i class="fas fa-plus-circle mr-2"></i>
            Nueva Empresa
        </h1>

        <a href="{{ route('empresas.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i>
            Volver
        </a>
    </div>
@stop

@section('content')
    @php
        /*
        |--------------------------------------------------------------------------
        | Valores anteriores seguros
        |--------------------------------------------------------------------------
        | Evita el error:
        | htmlspecialchars(): Argument #1 must be of type string, array given
        */

        $nombreAnterior = old('nombre');
        $dominioAnterior = old('dominio');
        $emailAnterior = old('email_admin');

        $nombreAnterior = is_scalar($nombreAnterior)
            ? (string) $nombreAnterior
            : '';

        $dominioAnterior = is_scalar($dominioAnterior)
            ? (string) $dominioAnterior
            : '';

        $emailAnterior = is_scalar($emailAnterior)
            ? (string) $emailAnterior
            : '';
    @endphp

    <div class="row">
        <div class="col-md-8 offset-md-2">

            {{-- Mensaje de éxito --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle mr-1"></i>

                    {{ is_scalar(session('success'))
                        ? session('success')
                        : 'La operación se realizó correctamente.' }}

                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                        aria-label="Cerrar"
                    >
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            {{-- Mensaje de error --}}
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle mr-1"></i>

                    {{ is_scalar(session('error'))
                        ? session('error')
                        : 'Ocurrió un error al procesar la solicitud.' }}

                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                        aria-label="Cerrar"
                    >
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            {{-- Lista general de errores --}}
            @if($errors->any())
                <div class="alert alert-danger">
                    <div class="font-weight-bold mb-2">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Verificá los siguientes datos:
                    </div>

                    <ul class="mb-0 pl-4">
                        @foreach($errors->all() as $error)
                            <li>
                                {{ is_scalar($error)
                                    ? $error
                                    : 'Error de validación.' }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card card-primary card-outline shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-building mr-1"></i>
                        Datos de la empresa
                    </h3>
                </div>

                <form
                    id="form-empresa"
                    action="{{ route('empresas.store') }}"
                    method="POST"
                    autocomplete="off"
                >
                    @csrf

                    <div class="card-body">

                        {{-- Nombre de la empresa --}}
                        <div class="form-group">
                            <label for="nombre">
                                Nombre de la empresa
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                name="nombre"
                                id="nombre"
                                class="form-control @error('nombre') is-invalid @enderror"
                                value="{{ $nombreAnterior }}"
                                maxlength="255"
                                placeholder="Ejemplo: Mi Empresa"
                                autocomplete="organization"
                                required
                                autofocus
                            >

                            @error('nombre')
                                <span
                                    class="invalid-feedback"
                                    role="alert"
                                >
                                    {{ is_scalar($message)
                                        ? $message
                                        : 'El nombre ingresado no es válido.' }}
                                </span>
                            @enderror
                        </div>

                        {{-- Subdominio --}}
                        <div class="form-group">
                            <label for="dominio">
                                Subdominio
                                <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">
                                <input
                                    type="text"
                                    name="dominio"
                                    id="dominio"
                                    class="form-control @error('dominio') is-invalid @enderror"
                                    value="{{ $dominioAnterior }}"
                                    maxlength="100"
                                    placeholder="miempresa"
                                    autocomplete="off"
                                    spellcheck="false"
                                    required
                                >

                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        .tudominio.com
                                    </span>
                                </div>
                            </div>

                            @error('dominio')
                                <span
                                    class="invalid-feedback d-block"
                                    role="alert"
                                >
                                    {{ is_scalar($message)
                                        ? $message
                                        : 'El subdominio ingresado no es válido.' }}
                                </span>
                            @enderror

                            <small class="form-text text-muted">
                                Podés escribir normalmente. Al salir del campo
                                se convertirán los espacios en guiones y se
                                eliminarán los caracteres no permitidos.
                            </small>

                            <div
                                id="vista-previa-dominio"
                                class="mt-2"
                                style="display: none;"
                            >
                                <small class="text-muted">
                                    Dirección resultante:
                                </small>

                                <strong
                                    id="dominio-completo"
                                    class="text-primary"
                                ></strong>
                            </div>
                        </div>

                        {{-- Email del administrador --}}
                        <div class="form-group">
                            <label for="email_admin">
                                Email del administrador
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="email"
                                name="email_admin"
                                id="email_admin"
                                class="form-control @error('email_admin') is-invalid @enderror"
                                value="{{ $emailAnterior }}"
                                maxlength="255"
                                placeholder="administrador@empresa.com"
                                autocomplete="email"
                                required
                            >

                            @error('email_admin')
                                <span
                                    class="invalid-feedback"
                                    role="alert"
                                >
                                    {{ is_scalar($message)
                                        ? $message
                                        : 'El email ingresado no es válido.' }}
                                </span>
                            @enderror
                        </div>

                        {{-- Contraseña del administrador --}}
                        <div class="form-group">
                            <label for="password_admin">
                                Contraseña del administrador
                                <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">
                                <input
                                    type="password"
                                    name="password_admin"
                                    id="password_admin"
                                    class="form-control @error('password_admin') is-invalid @enderror"
                                    autocomplete="new-password"
                                    required
                                >

                                <div class="input-group-append">
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        id="btn-mostrar-password"
                                        title="Mostrar u ocultar contraseña"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            @error('password_admin')
                                <span
                                    class="invalid-feedback d-block"
                                    role="alert"
                                >
                                    {{ is_scalar($message)
                                        ? $message
                                        : 'La contraseña ingresada no es válida.' }}
                                </span>
                            @enderror
                        </div>

                        {{-- Confirmación de contraseña --}}
                        <div class="form-group">
                            <label for="password_admin_confirmation">
                                Confirmar contraseña
                                <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">
                                <input
                                    type="password"
                                    name="password_admin_confirmation"
                                    id="password_admin_confirmation"
                                    class="form-control"
                                    autocomplete="new-password"
                                    required
                                >

                                <div class="input-group-append">
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        id="btn-mostrar-confirmacion"
                                        title="Mostrar u ocultar contraseña"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a
                            href="{{ route('empresas.index') }}"
                            class="btn btn-secondary"
                        >
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </a>

                        <button
                            type="submit"
                            class="btn btn-primary"
                            id="btn-guardar"
                        >
                            <i class="fas fa-save mr-1"></i>
                            Crear empresa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .custom-heading {
            color: #343a40;
            font-size: 1.65rem;
            font-weight: 600;
        }

        .card {
            border-radius: 10px;
        }

        .card-header {
            background-color: #ffffff;
        }

        .form-group label {
            color: #343a40;
            font-weight: 600;
        }

        .input-group-text {
            background-color: #f4f6f9;
            color: #495057;
            font-weight: 600;
        }

        #dominio {
            position: relative;
            z-index: 2;
            pointer-events: auto;
            user-select: text;
        }

        #dominio-completo {
            word-break: break-all;
        }

        @media (max-width: 576px) {
            .content-header .d-flex {
                align-items: flex-start !important;
                flex-direction: column;
                gap: 10px;
            }

            .card-footer {
                flex-direction: column-reverse;
                gap: 10px;
            }

            .card-footer .btn {
                width: 100%;
            }
        }
    </style>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const formulario = document.getElementById('form-empresa');
            const dominioInput = document.getElementById('dominio');
            const vistaPrevia = document.getElementById(
                'vista-previa-dominio'
            );
            const dominioCompleto = document.getElementById(
                'dominio-completo'
            );

            const passwordInput = document.getElementById(
                'password_admin'
            );
            const confirmacionInput = document.getElementById(
                'password_admin_confirmation'
            );

            const botonMostrarPassword = document.getElementById(
                'btn-mostrar-password'
            );
            const botonMostrarConfirmacion = document.getElementById(
                'btn-mostrar-confirmacion'
            );
            const botonGuardar = document.getElementById('btn-guardar');

            /*
             * Esta función NO modifica el campo mientras se escribe.
             * Solamente actualiza la vista previa.
             */
            function actualizarVistaPrevia() {
                if (
                    !dominioInput ||
                    !vistaPrevia ||
                    !dominioCompleto
                ) {
                    return;
                }

                const valorEscrito = dominioInput.value.trim();

                if (valorEscrito.length > 0) {
                    dominioCompleto.textContent =
                        valorEscrito + '.tudominio.com';

                    vistaPrevia.style.display = 'block';
                } else {
                    dominioCompleto.textContent = '';
                    vistaPrevia.style.display = 'none';
                }
            }

            /*
             * Esta función normaliza el dominio solamente al salir
             * del campo o al enviar el formulario.
             */
            function normalizarDominio(valor) {
                return valor
                    .trim()
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/[^a-z0-9-]/g, '')
                    .replace(/-+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }

            function normalizarCampoDominio() {
                if (!dominioInput) {
                    return;
                }

                dominioInput.value = normalizarDominio(
                    dominioInput.value
                );

                actualizarVistaPrevia();
            }

            function alternarVisibilidad(input, boton) {
                if (!input || !boton) {
                    return;
                }

                const icono = boton.querySelector('i');
                const estaOculta = input.type === 'password';

                input.type = estaOculta ? 'text' : 'password';

                if (icono) {
                    icono.classList.toggle(
                        'fa-eye',
                        !estaOculta
                    );

                    icono.classList.toggle(
                        'fa-eye-slash',
                        estaOculta
                    );
                }
            }

            if (dominioInput) {
                /*
                 * Permite escribir normalmente.
                 * No reemplaza ni elimina caracteres en cada tecla.
                 */
                dominioInput.addEventListener(
                    'input',
                    actualizarVistaPrevia
                );

                dominioInput.addEventListener(
                    'blur',
                    normalizarCampoDominio
                );

                actualizarVistaPrevia();

                window.setTimeout(function () {
                    dominioInput.readOnly = false;
                    dominioInput.disabled = false;
                }, 100);
            }

            if (botonMostrarPassword) {
                botonMostrarPassword.addEventListener(
                    'click',
                    function () {
                        alternarVisibilidad(
                            passwordInput,
                            botonMostrarPassword
                        );
                    }
                );
            }

            if (botonMostrarConfirmacion) {
                botonMostrarConfirmacion.addEventListener(
                    'click',
                    function () {
                        alternarVisibilidad(
                            confirmacionInput,
                            botonMostrarConfirmacion
                        );
                    }
                );
            }

            if (formulario) {
                formulario.addEventListener(
                    'submit',
                    function (event) {
                        normalizarCampoDominio();

                        if (
                            dominioInput &&
                            dominioInput.value.length === 0
                        ) {
                            event.preventDefault();

                            dominioInput.focus();

                            alert(
                                'Ingresá un subdominio válido.'
                            );

                            return;
                        }

                        if (botonGuardar) {
                            botonGuardar.disabled = true;

                            botonGuardar.innerHTML =
                                '<i class="fas fa-spinner ' +
                                'fa-spin mr-1"></i> ' +
                                'Creando empresa...';
                        }
                    }
                );
            }
        });
    </script>
@stop
