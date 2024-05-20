@extends('adminlte::page')



@section('content_header')
    <h1 class="m-0 text-dark">Registrar Evento</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('evento.store') }}" method="post">
                        @csrf
                        @method('POST')
                        {{-- With label, invalid feedback disabled and form group class --}}
                        <div class="row">
                            <x-adminlte-input name="nombre" label="Nombre" placeholder="Ingrese el nombre del evento"
                                fgroup-class="col-md-6" />
                        </div>
                        <div class="row">
                            {{-- Disabled --}}
                            <x-adminlte-textarea name="descripcion" label="Descripción" placeholder="Descripción del evento"
                                fgroup-class="col-md-6">

                            </x-adminlte-textarea>
                        </div>

                        <div class="row">
 
                            <x-adminlte-select name="tipo" label="Tipo" data-placeholder="Seleccionar una opción...">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-gradient-info">
                                        <i class="fas fa-building"></i>
                                    </div>
                                </x-slot>
                                <option value="0">Seleccione</option>
                                <option value="1">Concierto</option>
                                <option value="2">Fútbol</option>
                            </x-adminlte-select>

                        </div>
                        <div class="row">
                            <div class="form-group col-md-6" >
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
