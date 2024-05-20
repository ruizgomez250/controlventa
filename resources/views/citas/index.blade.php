@extends('adminlte::page')



@section('content_header')
    <div class="row">
        <div class="col-4">
            <h1 class="m-0 text-dark">Agenda</h1>
        </div>
        <div class="col-4">
            <x-adminlte-input label="Fecha" type="date" id="fecha" name="fecha" onchange="cargarDatos()"
                value="{{ now()->format('Y-m-d') }}" fgroup-class="col-md-8" />

        </div>
        <div class="col-4">
            <a href="{{ route('cita.create') }}" class="btn btn-primary" style="float: right;">
                Agendar</a>
        </div>

    </div>


@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    {{-- Minimal example / fill data using the component slot --}}

                    <div class="row">
                        <x-adminlte-datatable id="table1" :heads="$heads" head-theme="dark" theme="light" striped
                            hoverable with-buttons>
                        </x-adminlte-datatable>
                    </div>
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
                    <h5 class="modal-title" id="exampleModalLabel">Detalle</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <textarea style="background-color: blue;color:white;" class="form-control romeo" id="detalle" rows="20" disabled></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Example button to open modal --}}




@stop


@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            //  console.log('entra');
            cargarDatos();
        });


        $('#exampleModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget) // Button that triggered the modal
            var recipient = button.data('whatever')
            //  console.log('hola' + recipient);
            var modal = $(this)

            //  modal.find('.modal-title').text('New message to ' + recipient)
            modal.find('.modal-body .romeo').val(recipient)
        })


        function cargarDatos() {
            var fecha = document.getElementById("fecha").value;
            var url = 'cita/optenerdatos/' + fecha;
            //     console.log(url);
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    var datos = response.datos;
                    var tabla = $('#table1').DataTable();
                    let atendida = 68;
                    let cancelada = 69;
                    let enespera = 67;
                    tabla.clear().order([
                        [2, 'asc']
                    ]).draw();
                    for (var i = 0; i < datos.length; i++) {
                        tabla.row.add([
                            datos[i].id,
                            datos[i].fecha,
                            datos[i].hora,
                            datos[i].estadocita.descripcion,
                            datos[i].tipoconsulta.descripcion,
                            datos[i].mascota.nombre,
                            datos[i].mascota.propietario.razonsocial,
                            '<button data-toggle="tooltip" data-placement="bottom" title="Atendido" type="button" class="btn btn-sm btn-outline-success" onclick="estadoCita(' +
                            datos[i].id + ',' + atendida +
                            ');" ><ion-icon name="trash-outline"><i class="far fa-thumbs-up"></i></button><button data-toggle="tooltip" data-placement="bottom" title="En Espera" type="button" class="btn btn-sm btn-outline-secondary" onclick="estadoCita(' +
                            datos[i].id + ',' + enespera +
                            ');" ><ion-icon name="trash-outline"><i class="fas fas-sm fas-fw fa-coffee"></i></button>' +
                            '<button data-toggle="tooltip" data-placement="bottom" title="Cancelar" type="button" class="btn btn-sm btn-outline-warning" onclick="estadoCita(' +
                            datos[i].id + ',' + cancelada +
                            ');" ><ion-icon name="trash-outline"><i class="far far-sm far-fw fa-thumbs-down"></i></button><button data-toggle="tooltip" data-placement="bottom" title="Eliminar" type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarCita(' +
                            datos[i].id +
                            ');" ><ion-icon name="trash-outline"><i class="fas fas-sm fas-fw fa-trash-alt"></i></button>',
                        ]).draw();
                        // console.log(datos[i].estado);
                    }
                }
            });
        }


        function eliminarCita(id) {

            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    var token = '{{ csrf_token() }}';
                    var url = '{{ route('cita.destroy', ':id') }}';
                    url = url.replace(':id', id);
                    console.log(url);
                    fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log(data.message);
                            Swal.fire(
                        'Eliminado',
                        data.message,
                        'success'
                    );
                            //  window.location.href = '{{ route('cita.index') }}';
                            cargarDatos();
                            // Realiza cualquier acción adicional después de eliminar la entidad
                        })
                        .catch(error => console.error(error));
               
                }
            });
        }

        function estadoCita(id, estado) {
            var token = '{{ csrf_token() }}';
            var url = '{{ route('cita.cambioestado', ':id') }}';
            url = url.replace(':id', id);
            console.log(url);
            fetch(url, {
                    method: 'PUT',
                    body: JSON.stringify({
                        estado: estado
                    }),
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log(data.message);
                    //    window.location.href = '{{ route('cita.index') }}';
                    cargarDatos();
                    // Realiza cualquier acción adicional después de eliminar la entidad
                })
                .catch(error => console.error(error));
        }
    </script>
@endpush
