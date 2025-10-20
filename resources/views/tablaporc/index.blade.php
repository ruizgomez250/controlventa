@extends('adminlte::page')

@section('title', 'Tabla de Porcentajes')

@section('content_header')
    <h1 class="m-0">Listado de Porcentajes</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped text-center">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Cuota 1</th>
                        <th>Cuota 2</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tablaporc as $row)
                        <tr>
                            <td>{{ $row->id }}</td>
                            <td>{{ $row->cuota1 }}</td>
                            <td>{{ $row->cuota2 }}</td>
                            <td>
                                <button class="btn btn-sm btn-info"
                                    onclick="cargarModal({{ $row->id }}, '{{ $row->cuota1 }}', '{{ $row->cuota2 }}')">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal de Edición --}}
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">

        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Editar Porcentaje Cuota</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    {{-- 🔹 Form genérico. El action se setea con JS --}}
                    <form id="editForm" method="post" autocomplete="off">
                        @csrf
                        @method('PUT')

                        <input type="hidden" id="id" name="id">

                        <div class="row">
                            <x-adminlte-input name="cuota1" id="cuota1" label="Cuota 1" placeholder="Cuota 1"
                                fgroup-class="col-md-6" label-class="text-info" required>
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-info">
                                        <i class="fas fa-coins"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>

                            <x-adminlte-input name="cuota2" id="cuota2" label="Cuota 2" placeholder="Cuota 2"
                                fgroup-class="col-md-6" label-class="text-info" required>
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-info">
                                        <i class="fas fa-percentage"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar cambios</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@stop

@push('js')
    <script>
        // Función para llenar y abrir el modal
        function cargarModal(id, cuota1, cuota2) {
            // Completar inputs
            document.getElementById('id').value = id;
            document.getElementById('cuota1').value = cuota1;
            document.getElementById('cuota2').value = cuota2;

            // Cambiar action del form al update de Laravel
            let form = document.getElementById('editForm');
            form.action = "/tablaporc/" + id;

            // Abrir modal
            $('#editModal').modal('show');
        }
    </script>
@endpush
