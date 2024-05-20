@extends('adminlte::page')



@section('content_header')
    <h1 class="m-0 text-dark">Actualizar Evento</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('evento.update', $evento) }}" method="post">
                        @csrf
                        @method('put')
                        {{-- With label, invalid feedback disabled and form group class --}}
                        <div class="row">
                            <x-adminlte-input name="nombre" label="Nombre" placeholder="Ingrese el nombre del evento"
                                fgroup-class="col-md-6" value="{{ $evento->nombre }}" />
                        </div>
                        <div class="row">
                            {{-- Disabled --}}
                            <x-adminlte-textarea name="descripcion" label="Descripción" placeholder="Descripción del evento"
                                fgroup-class="col-md-6">
                                {{ $evento->descripcion }}
                            </x-adminlte-textarea>
                        </div>

                        <div class="row">
                            <x-adminlte-select name="tipo" label="Tipo" data-placeholder="Seleccionar una opción..."  fgroup-class="col-md-6">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-gradient-info">
                                        <i class="fas fa-building"></i>
                                    </div>
                                </x-slot>
                                @if ($evento->tipo === 0)
                                    <option selected value="0">Seleccione</option>
                                    <option value="1">Concierto</option>
                                    <option value="2">Fútbol</option>
                                @elseif ($evento->tipo === 1)
                                    <option value="0">Seleccione</option>
                                    <option selected value="1">Concierto</option>
                                    <option value="2">Fútbol</option>
                                @else
                                    <option value="0">Seleccione</option>
                                    <option value="1">Concierto</option>
                                    <option selected value="2">Fútbol</option>
                                @endif
                            </x-adminlte-select>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <div class="pull-right">
                                    <a href="" class="btn btn-default btn-flat">Cancelar</a>
                                </div>
                                <x-adminlte-button class="btn-flat" type="submit" label="Registrar" theme="primary"
                                    icon="fas fa-lg fa-save" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
