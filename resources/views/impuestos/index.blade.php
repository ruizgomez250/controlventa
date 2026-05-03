@extends('adminlte::page')

@section('content_header')
    <div class="row">
        <div class="col-6">
            <h1 class="m-0 custom-heading">Lista de Impuestos</h1>
        </div>
        <div class="col-6">
            <a href="{{ route('impuestos.create') }}" class="btn btn-primary" style="float: right;">
                Registrar Nuevo Impuesto
            </a>
        </div>
    </div>
@stop

@push('js')
<script>
    $(document).ready(function() {

        var Toast = Swal.mixin({
            toast: true,
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 3000
        });

        @if (session('success'))
            Toast.fire({
                icon: 'success',
                title: 'Operación Exitosa',
                text: '{{ session('success') }}',
            });
        @endif

        @if (session('error'))
            Toast.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session('error') }}',
            });
        @endif

        $('.delete-button').on('click', function () {
            let form = $(this).closest('.delete-form');
            Swal.fire({
                title: 'Confirmar eliminación',
                text: '¿Deseas eliminar este impuesto?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <x-adminlte-datatable
                    id="table1"
                    :heads="$heads"
                    head-theme="dark"
                    striped
                    hoverable
                    with-buttons>

                    @foreach ($impuestos as $row)
                        <tr>
                            <td>{{ $row->id }}</td>
                            <td>{{ $row->descripcion }}</td>
                            <td class="text-right">
                                {{ $row->valor_formateado }} %
                            </td>
                            <td style="float:right;">
                                <a href="{{ route('impuestos.edit', $row->id) }}"
                                   class="btn btn-outline-secondary">
                                    <i class="fa fa-sm fa-fw fa-pen"></i>
                                </a>

                                <form action="{{ route('impuestos.destroy', $row->id) }}"
                                      method="post"
                                      class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            class="btn btn-outline-secondary delete-button">
                                        <i class="fa fa-sm fa-fw fa-trash"></i>
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
@stop
