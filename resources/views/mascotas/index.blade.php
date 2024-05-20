@extends('adminlte::page')



@section('content_header')
    <div class="row">
        <div class="col-6">
            <h1 class="m-0 text-dark">Lista de Mascotas</h1>
        </div>
        <div class="col-6">
            <a href="{{ route('mascota.create') }}" class="btn btn-primary" style="float: right;">Registra
                Mascota</a>
        </div>

    </div>


@stop

@section('content')


    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-body">

                    {{-- composer require realrashid/sweet-alert --}}
                    <x-adminlte-datatable id="table1" :heads="$heads" head-theme="dark" theme="light" striped
                        hoverable with-buttons>
                        @foreach ($mascota as $row)
                            <tr>

                                <td>{{ $row->id }}</td>
                                <td>{{ $row->nombre }}</td>
                                <td>{{ $row->razaanimal->nombre }}</td>
                                <td>{{ $row->sexoanimal->descripcion }}</td>

                                <td>{{ $row->edad }}</td>
                                <td>{{ $row->propietario->razonsocial }}</td>
                                <td>{{ $row->propietario->celular }}</td>

                                <td style="float:right;">


                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal"
                                        data-target="#exampleModal"
                                        data-whatever="{{ $row->foto !== null ? $row->foto : '' }}"><i
                                            class="fa fa-sm fa-fw fa-eye"></i></button>


                                    <a href="{{ route('mascota.edit', $row->id) }}"
                                        class="btn btn-sm btn-outline-secondary"><i class="fa fa-sm fa-fw fa-pen"></i></a>

                                    <form action="{{ route('mascota.destroy', [$row->id]) }}" method="post"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"  class="btn btn-sm btn-outline-secondary">
                                            <ion-icon name="trash-outline"><i class="fa fa-sm fa-fw fa-trash"></i>
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
                        <div id="miDiv"></div>
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
            var modal = $(this)
            var asset_user_global = "{{ asset('storage') }}" + "/" + recipient;
            console.log(asset_user_global);
            // Crea un nuevo elemento de imagen
            var imagen = document.createElement('img');
            // Establece la propiedad src con la URL de la imagen
            imagen.src = asset_user_global;
            imagen.width = '300';
            imagen.height = '250';
            // Obtén el div específico utilizando su ID
            var divObjetivo = document.getElementById('miDiv');
            divObjetivo.innerHTML = '';
            // Agrega el elemento de imagen como hijo del div objetivo
            divObjetivo.appendChild(imagen);
        })
    </script>
@endpush
