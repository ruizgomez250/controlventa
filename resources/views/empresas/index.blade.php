@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 custom-heading">
        <i class="fas fa-building"></i> Empresas
    </h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('empresas.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nueva Empresa
                    </a>
                </div>
                <div class="card-body">
                    @if ($empresas->isEmpty())
                        <p class="text-muted text-center mb-0">No hay empresas registradas.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Dominio</th>
                                        <th>Email Admin</th>
                                        <th>Base de Datos</th>
                                        <th>Activo</th>
                                        <th>Expiración</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($empresas as $empresa)
                                        <tr>
                                            <td>{{ $empresa->id }}</td>
                                            <td>{{ $empresa->nombre }}</td>
                                            <td><code>{{ $empresa->dominio }}</code></td>
                                            <td>{{ $empresa->email_admin }}</td>
                                            <td><code>{{ $empresa->database_name }}</code></td>
                                            <td>
                                                @if ($empresa->activo)
                                                    <span class="badge badge-success">Activo</span>
                                                @else
                                                    <span class="badge badge-danger">Inactivo</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($empresa->fecha_expiracion)
                                                    {{ $empresa->fecha_expiracion->format('d/m/Y') }}
                                                @else
                                                    <span class="text-muted">Sin expiración</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('empresas.edit', $empresa) }}" class="btn btn-sm btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('empresas.destroy', $empresa) }}" method="POST" style="display:inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Está seguro de eliminar esta empresa? Se eliminará también su base de datos.')">
                                                        <i class="fas fa-trash"></i>
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
