@extends('adminlte::page')

@section('content_header')
    <div class="row">
        <div class="col-6">
            <h1 class="m-0 custom-heading">Lista de Gastos</h1>
        </div>
        <div class="col-6">
            <a href="{{ route('gasto.create') }}" class="btn btn-primary" style="float: right;">Nuevo Gasto</a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="table1" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Concepto</th>
                            <th>Monto</th>
                            <th>Fecha</th>
                            <th>Método de Pago</th>
                            <th>Observación</th>
                            <th>Estado</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($gastos as $gasto)
                            <tr>
                                <td>{{ $gasto->id }}</td>
                                <td>{{ $gasto->concepto }}</td>
                                <td>{{ number_format($gasto->monto, 2, '.', ',') }}</td>
                                <td>{{ $gasto->fecha }}</td>
                                <td>{{ ucfirst($gasto->metodo_pago) }}</td>
                                <td>{{ $gasto->observacion }}</td>
                                <td>{{ ucfirst($gasto->estado) }}</td>
                                <td>{{ $gasto->usuario->name }}</td>
                                <td>
                                    <a href="{{ route('gasto.edit', $gasto->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-sm fa-fw fa-pencil-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@push('js')
<script>
    $(document).ready(function() {
        $('#table1').DataTable({
            responsive: true,
            autoWidth: false,
            dom: 'Bfrtip', // Habilita los botones
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            order: [[0, 'desc']] // Ordenar por ID descendente
        });
    });
</script>
@endpush
