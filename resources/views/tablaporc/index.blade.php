@extends('adminlte::page')



@section('content_header')
    <div class="row">
        <div class="col-6">
            <h1 class="m-0 custom-heading">Lista de Porcentaje de Cuotas</h1>
        </div>
        <div class="col-6">

            <button type="button" class="btn  btn-primary" data-toggle="modal" data-target="#exampleModal"
                style="float: right;">Registra Nuevo
            </button>
        </div>

    </div>


@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    {{-- Minimal example / fill data using the component slot --}}
                    <x-adminlte-datatable id="table1" :heads="$heads" head-theme="dark" theme="light" striped
                        hoverable with-buttons>
                        @foreach ($tablaporc as $row)
                            <tr>

                                <td>{{ $row->id }}</td>
                                <td>{{ $row->porcentaje }}</td>
                                <td>{{ $row->cuota }}</td>
                                <td class="{{ $row->estado === 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $row->estado === 0 ? 'Inactivo' : 'Activo' }}</td>

                                <td style="float:right;">




                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                        data-estado="{{ $row->estado }}" data-id="{{ $row->id }}"
                                        data-cuota="{{ $row->cuota }}" data-porcentaje="{{ $row->porcentaje }}"
                                        data-toggle="modal" data-target="#editModal" onclick="cargarmodal(this)"><i
                                            class="fa fa-sm fa-fw fa-pen"></i></button>


                                    <form id="delete-form" action="{{ route('tablaporc.destroy', [$row->id]) }}"
                                        method="post" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="delete-button">
                                            <ion-icon name="trash-outline" onclick="borrar()"><i
                                                    class="fa fa-sm fa-fw fa-trash"></i>
                                        </button>
                                    </form>

                                </td>
                            </tr>
                        @endforeach
                    </x-adminlte-datatable>
                </div>
            </div>
        </div>
    </div>

    {{-- Themed --}}
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Agregar Porcentaje Cuota</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('tablaporc.store') }}" method="post" autocomplete="off">
                        @csrf
                        @method('POST')
                        {{-- 'id', 'codigo', 'descripcion', 'detalle', 'id_categoria', 'id_estado','pcosto', 'pventa', 'observacion' --}}

                        <div class="row">
                            <x-adminlte-input name="cuota" label="Cuota" placeholder="Cuota" fgroup-class="col-md-6"
                                style="text-align: center;" label-class="text-info" required>
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-info">
                                        <i class="fas fa-coins"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                            <x-adminlte-input name="porcentaje" label="Porcentaje" placeholder="Porcentaje"
                                fgroup-class="col-md-6" style="text-align: center;" label-class="text-success" required>
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-success">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>

                        </div>
                        <div class="row">
                            <div class="form-group col-md-12">

                                <x-adminlte-button class="btn-group mx-1" style="float: right;" type="submit"
                                    label="Registrar" theme="primary" icon="fas fa-lg fa-save" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- Example button to open modal --}}
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Editar Porcentaje Cuota</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('tablaporc.update', ['tablaporc' => $row->id]) }}" method="post"
                        autocomplete="off">
                        @csrf
                        @method('PUT') {{-- O puedes usar @method('PATCH') --}}

                        {{-- Otros campos del formulario --}}
                        <div class="row">
                            <input type="hidden" id="id" name="id">
                            <x-adminlte-input name="cuota1" id="cuota1" label="Cuota" placeholder="Cuota"
                                fgroup-class="col-md-4" style="text-align: center;" label-class="text-info" required>
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-info">
                                        <i class="fas fa-coins"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                            <x-adminlte-input name="porcentaje1" id="porcentaje1" label="Porcentaje"
                                placeholder="Porcentaje" fgroup-class="col-md-4" style="text-align: center;"
                                label-class="text-success" required>
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-success">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                            <div style="display: flex; flex-direction: column;">
                                <label for="estado">Estado:</label>
                                <select name="estado" id="estado">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-12">

                                <x-adminlte-button class="btn-group mx-1" style="float: right;" type="submit"
                                    label="Registrar" theme="primary" icon="fas fa-lg fa-save" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>




@stop


@push('js')
    <script>
        function cargarmodal(boton) {
            console.log(boton);
            // Obtener los valores de los atributos de datos del botón
            var cuota = boton.getAttribute('data-cuota');
            var porcentaje = boton.getAttribute('data-porcentaje');
            var id = boton.getAttribute('data-id');
            var estado = boton.getAttribute('data-estado');

            // Asignar los valores a los campos del formulario
            console.log(cuota);
            document.getElementById('cuota1').value = cuota;
            document.getElementById('porcentaje1').value = porcentaje;
            document.getElementById('id').value = id;
            document.getElementById('estado').value = estado;
        }
        $('#exampleModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget) // Button that triggered the modal
            var recipient = button.data('whatever')
            //  console.log('hola' + recipient);
            var modal = $(this)

            //  modal.find('.modal-title').text('New message to ' + recipient)
            modal.find('.modal-body .romeo').val(recipient)
        })
        // Agregar un evento clic al botón de eliminación
        function borrar() {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, desactivarlo'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form').submit();

                }
            });
        }

        // Obtener el mensaje de éxito o error de Laravel
        var successMessage = "{{ session('success') }}";
        var errorMessage = "{{ session('error') }}";

        // Mostrar el mensaje de éxito o error con SweetAlert
        if (successMessage) {
            Swal.fire('Éxito', successMessage, 'success');
        } else if (errorMessage) {
            Swal.fire('Error', errorMessage, 'error');
        }
    </script>
@endpush
