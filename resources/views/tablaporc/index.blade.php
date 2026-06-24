@extends('adminlte::page')

@section('title', 'Tabla de Porcentajes')

@section('content_header')
    <div class="row">
        <div class="col-6">
            <h1 class="m-0">{{ __('Listado de Porcentajes por Cuota') }}</h1>
        </div>
        <div class="col-6 text-right">
            <a href="{{ route('tablaporc.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i>{{ __('Nuevo Porcentaje') }}</a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped text-center">
                <thead class="thead-dark">
                    <tr>
                        <th>{{ __('ID') }}</th>
                        <th>{{ __('Cant. Cuotas') }}</th>
                        <th>{{ __('Porcentaje %') }}</th>
                        <th>{{ __('Estado') }}</th>
                        <th>{{ __('Acciones') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tablaporc as $row)
                        <tr>
                            <td>{{ $row->id }}</td>
                            <td>{{ $row->cuota }}</td>
                            <td>{{ number_format($row->porcentaje, 2) }} %</td>
                            <td>
                                @if ($row->estado)
                                    <span class="badge badge-success">{{ __('Activo') }}</span>
                                @else
                                    <span class="badge badge-danger">{{ __('Inactivo') }}</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info"
                                    onclick="cargarModal({{ $row->id }}, {{ $row->cuota }}, {{ $row->porcentaje }}, {{ $row->estado }})">
                                    <i class="fas fa-edit"></i>{{ __('Editar') }}</button>
                                <button class="btn btn-sm btn-danger" onclick="eliminar({{ $row->id }})">
                                    <i class="fas fa-trash"></i>
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
                    <h5 class="modal-title" id="exampleModalLabel">{{ __('Editar Porcentaje por Cuota') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">{{ __('&times;') }}</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editForm" method="post" autocomplete="off">
                        @csrf
                        @method('PUT')

                        <input type="hidden" id="id" name="id">

                        <div class="row">
                            <x-adminlte-input name="cuota" id="cuota" label="Cant. Cuotas" placeholder="{{ __('Ej: 3') }}"
                                fgroup-class="col-md-6" label-class="text-info" type="number" min="2" required>
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-info">
                                        <i class="fas fa-coins"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>

                            <x-adminlte-input name="porcentaje" id="porcentaje" label="Porcentaje %" placeholder="{{ __('Ej: 5.00') }}"
                                fgroup-class="col-md-6" label-class="text-info" type="number" step="0.01" min="0" required>
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-info">
                                        <i class="fas fa-percentage"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>

                        <div class="form-group">
                            <label>{{ __('Estado') }}</label>
                            <div class="form-check">
                                <input type="hidden" name="estado" value="0">
                                <input class="form-check-input" type="checkbox" id="estado" name="estado" value="1">
                                <label class="form-check-label" for="estado">{{ __('Activo') }}</label>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                            <button type="submit" class="btn btn-primary">{{ __('Guardar cambios') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
    <script>
        function cargarModal(id, cuota, porcentaje, estado) {
            document.getElementById('id').value = id;
            document.getElementById('cuota').value = cuota;
            document.getElementById('porcentaje').value = porcentaje;
            document.getElementById('estado').checked = estado == 1;

            let form = document.getElementById('editForm');
            form.action = "/tablaporc/" + id;

            $('#editModal').modal('show');
        }

        function eliminar(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción desactivará el registro.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, desactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    var token = '{{ csrf_token() }}';
                    $.ajax({
                        url: '/tablaporc/' + id,
                        method: 'DELETE',
                        data: { _token: token },
                        success: function(response) {
                            Swal.fire('Desactivado', 'Registro desactivado con éxito.', 'success');
                            location.reload();
                        },
                        error: function() {
                            Swal.fire('Error', 'Hubo un problema al desactivar el registro.', 'error');
                        }
                    });
                }
            });
        }

        var successMessage = "{{ session('success') }}";
        var errorMessage = "{{ session('error') }}";

        if (successMessage) {
            Swal.fire('Éxito', successMessage, 'success');
        } else if (errorMessage) {
            Swal.fire('Error', errorMessage, 'error');
        }
    </script>
@endpush