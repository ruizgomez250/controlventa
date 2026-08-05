@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 custom-heading">
        <i class="fas fa-building"></i>{{ __('Empresas') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('empresas.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i>{{ __('Nueva Empresa') }}</a>
                </div>
                <div class="card-body">
                    @if ($empresas->isEmpty())
                        <p class="text-muted text-center mb-0">{{ __('No hay empresas registradas.') }}</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>{{ __('ID') }}</th>
                                        <th>{{ __('Nombre') }}</th>
                                        <th>{{ __('Dominio') }}</th>
                                        <th>{{ __('Email Admin') }}</th>
                                        <th>{{ __('Base de Datos') }}</th>
                                        <th>{{ __('Activo') }}</th>
                                        <th>{{ __('Expiración') }}</th>
                                        <th>{{ __('Acciones') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($empresas as $empresa)
                                        <tr>
                                            <td>{{ $empresa->id }}</td>
                                            <td>{{ $empresa->nombre }}</td>
                                            <td>
                                                <a href="{{ request()->getScheme() }}://{{ $empresa->dominio }}.{{ config('tenancy.base_domain') }}{{ in_array(request()->getPort(), [80, 443], true) ? '' : ':'.request()->getPort() }}/login" target="_blank" rel="noopener">
                                                    {{ $empresa->dominio }}.{{ config('tenancy.base_domain') }}{{ in_array(request()->getPort(), [80, 443], true) ? '' : ':'.request()->getPort() }}
                                                </a>
                                            </td>
                                            <td>{{ $empresa->email_admin }}</td>
                                            <td><code>{{ $empresa->database_name }}</code></td>
                                            <td>
                                                @if ($empresa->activo)
                                                    <span class="badge badge-success">{{ __('Activo') }}</span>
                                                @else
                                                    <span class="badge badge-danger">{{ __('Inactivo') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($empresa->fecha_expiracion)
                                                    {{ $empresa->fecha_expiracion->format('d/m/Y') }}
                                                @else
                                                    <span class="text-muted">{{ __('Sin expiración') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('empresas.edit', $empresa) }}" class="btn btn-sm btn-warning" title="{{ __('Editar') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('empresas.destroy', $empresa) }}" method="POST" style="display:inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="{{ __('Suspender') }}" onclick="return confirm('¿Está seguro de suspender esta empresa? El acceso quedará bloqueado, pero sus datos se conservarán durante el periodo de retención.')">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
