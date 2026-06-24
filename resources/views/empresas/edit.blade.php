@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 custom-heading">
        <i class="fas fa-edit"></i>{{ __('Editar Empresa: {{ $empresa->nombre }}') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('empresas.update', $empresa) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="nombre">{{ __('Nombre de la Empresa') }}<span class="text-danger">{{ __('*') }}</span></label>
                            <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $empresa->nombre) }}" required>
                            @error('nombre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="dominio">{{ __('Subdominio') }}<span class="text-danger">{{ __('*') }}</span></label>
                            <div class="input-group">
                                <input type="text" name="dominio" id="dominio" class="form-control @error('dominio') is-invalid @enderror" value="{{ old('dominio', $empresa->dominio) }}" required placeholder="{{ __('memiempresa') }}">
                                <div class="input-group-append">
                                    <span class="input-group-text">{{ __('.tudominio.com') }}</span>
                                </div>
                                @error('dominio')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted">{{ __('Solo letras minúsculas, números y guiones.') }}</small>
                        </div>

                        <div class="form-group">
                            <label for="email_admin">{{ __('Email del Administrador') }}</label>
                            <input type="email" id="email_admin" class="form-control" value="{{ $empresa->email_admin }}" disabled>
                            <small class="form-text text-muted">{{ __('El email del administrador no se puede modificar.') }}</small>
                        </div>

                        <div class="form-group">
                            <label for="password_admin">{{ __('Nueva Contraseña del Administrador') }}</label>
                            <input type="password" name="password_admin" id="password_admin" class="form-control @error('password_admin') is-invalid @enderror" placeholder="{{ __('Dejar vacío para no cambiar') }}">
                            @error('password_admin')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_admin_confirmation">{{ __('Confirmar Nueva Contraseña') }}</label>
                            <input type="password" name="password_admin_confirmation" id="password_admin_confirmation" class="form-control" placeholder="{{ __('Repetir nueva contraseña') }}">
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" name="activo" id="activo" class="form-check-input" value="1" {{ old('activo', $empresa->activo) ? 'checked' : '' }}>
                                <label class="form-check-label" for="activo">{{ __('Empresa activa') }}</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="fecha_expiracion">{{ __('Fecha de Expiración') }}</label>
                            <input type="datetime-local" name="fecha_expiracion" id="fecha_expiracion" class="form-control @error('fecha_expiracion') is-invalid @enderror" value="{{ old('fecha_expiracion', $empresa->fecha_expiracion ? $empresa->fecha_expiracion->format('Y-m-d\TH:i') : '') }}">
                            @error('fecha_expiracion')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">{{ __('Dejar vacío para que no tenga expiración.') }}</small>
                        </div>

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i>{{ __('Actualizar Empresa') }}</button>
                            <a href="{{ route('empresas.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i>{{ __('Cancelar') }}</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-database"></i>{{ __('Información de la Base de Datos') }}</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th>{{ __('Base de Datos') }}</th>
                            <td><code>{{ $empresa->database_name }}</code></td>
                        </tr>
                        <tr>
                            <th>{{ __('Host') }}</th>
                            <td><code>{{ $empresa->database_host }}:{{ $empresa->database_port }}</code></td>
                        </tr>
                        <tr>
                            <th>{{ __('Usuario BD') }}</th>
                            <td><code>{{ $empresa->database_username }}</code></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
