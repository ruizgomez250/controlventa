@extends('adminlte::page')



@section('content_header')
    <h1 class="m-0 text-dark">Eventos</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    {{-- Minimal example / fill data using the component slot --}}
                    <x-adminlte-datatable id="table1" :heads="$heads"  head-theme="dark" theme="light" striped hoverable with-buttons>
                        @foreach ($evento as $row)
                            <tr>
                                <td>{{ $row->id }}</td>
                                <td>{{ $row->nombre }}</td>
                                <td>{{ $row->descripcion }}</td>
                                <td>
                                    @if ($row->tipo === 0)
                                        Sin Dato
                                    @elseif ($row->tipo === 1)
                                        Concierto
                                    @else
                                        Fútbol
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('evento.edit', $row->id) }}" class="btn btn-warning">Editar</a>
                                    <form action="{{ route('evento.destroy', $row->id) }}" method="post" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
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
