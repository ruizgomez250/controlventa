@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 custom-heading">
        <i class="fas fa-plus-circle"></i> Nueva Empresa
    </h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('empresas.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="nombre">Nombre de la Empresa <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}" required>
                            @error('nombre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="dominio">Subdominio <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="dominio" id="dominio" class="form-control @error('dominio') is-invalid @enderror" value="{{ old('dominio') }}" required placeholder="memiempresa">
                                <div class="input-group-append">
                                    <span class="input-group-text">.tudominio.com</span>
                                </div>
                                @error('dominio')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted">Solo letras minúsculas, números y guiones.</small>
                        </div>

                        <div class="form-group">
                            <label for="email_admin">Email del Administrador <span class="text-danger">*</span></label>
                            <input type="email" name="email_admin" id="email_admin" class="form-control @error('email_admin') is-invalid @enderror" value="{{ old('email_admin') }}" required>
                            @error('email_admin')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_admin">Contraseña del Administrador <span class="text-danger">*</span></label>
                            <input type="password" name="password_admin" id="password_admin" class="form-control @error('password_admin') is-invalid @enderror" required>
                            @error('password_admin')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_admin_confirmation">Confirmar Contraseña <span class="text-danger">*</span></label>
                            <input type="password" name="password_admin_confirmation" id="password_admin_confirmation" class="form-control" required>
                        </div>

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Crear Empresa
                            </button>
                            <a href="{{ route('empresas.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
