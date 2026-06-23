@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 custom-heading">
        <i class="fas fa-user-plus"></i> Crear Nuevo Usuario
    </h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12 col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('rol.storeUser') }}" method="post">
                        @csrf

                        <x-adminlte-input name="name" label="Nombre Completo" placeholder="Nombre del usuario"
                            value="{{ old('name') }}" required>
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-info">
                                    <i class="fas fa-user"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>

                        <x-adminlte-input name="email" type="email" label="Correo Electrónico"
                            placeholder="correo@ejemplo.com" value="{{ old('email') }}" required>
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-info">
                                    <i class="fas fa-envelope"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>

                        <x-adminlte-input name="password" type="password" label="Contraseña"
                            placeholder="Mínimo 8 caracteres" required>
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-info">
                                    <i class="fas fa-lock"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>

                        <x-adminlte-input name="password_confirmation" type="password" label="Confirmar Contraseña"
                            placeholder="Repita la contraseña" required>
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-info">
                                    <i class="fas fa-lock"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>

                        <div class="text-center mt-4">
                            <a href="{{ route('rol.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Crear Usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
