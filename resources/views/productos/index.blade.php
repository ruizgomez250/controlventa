@extends('adminlte::page')



@section('content_header')
    <div class="row">
        <div class="col-6">
            <h1 class="m-0 custom-heading">Lista de Productos</h1>
        </div>
        <div class="col-6">
            <a href="{{ route('producto.create') }}" class="btn btn-primary " style="float: right;">Registra Nuevo
                Producto</a>
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
                        @foreach ($producto as $row)
                            <tr>
                               
                                <td>{{ $row->codigo }}</td>
                                <td>{{ $row->descripcion }}</td>
                                <td>{{ $row->categoriaproducto->descripcion }}</td>
                                <td>{{ $row->stock . '  ' . $row->unidaddemedida->descripcion }}</td>
                                <td>{{ $row->pcosto }}</td>
                                <td>{{ $row->pventa }}</td>
                                <td>{{ $row->impuesto }}</td>
                                <td>{{ ($row->estado === 0) ? "Inactivo" : "Activo" }}</td>

                                <td style="float:right;">


                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal"
                                        data-target="#exampleModal"
                                        data-whatever="{{ $row->detalle }}"><i class="fa fa-sm fa-fw fa-eye"></i></button>
                          

                                    <a href="{{ route('producto.edit', $row->id) }}"
                                        class="btn btn-sm btn-outline-secondary"><i class="fa fa-sm fa-fw fa-pen"></i></a>

                                    <form id="delete-form" action="{{ route('producto.destroy', [$row->id]) }}" method="post"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="delete-button">
                                            <ion-icon name="trash-outline" onclick="borrar()"><i class="fa fa-sm fa-fw fa-trash"></i>
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
        $('#exampleModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget) // Button that triggered the modal
            var recipient = button.data('whatever')
          //  console.log('hola' + recipient);
            var modal = $(this)

          //  modal.find('.modal-title').text('New message to ' + recipient)
            modal.find('.modal-body .romeo').val(recipient)
        })
        // Agregar un evento clic al botón de eliminación
    function borrar(){
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminarlo'
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
