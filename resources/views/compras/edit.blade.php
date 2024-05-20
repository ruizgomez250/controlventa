@extends('adminlte::page')



@section('content_header')
    <h1 class="m-0 text-dark" style="background-color:#5DBFFF ">Editar Datos Del Cliente</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('cliente.update', $cliente = $cli) }}" method="post">
                        @csrf
                        @method('put')
                        {{-- With label, invalid feedback disabled and form group class --}}

                        <div class="row">
                            <x-adminlte-input name="razonsocial" label="Razón Social"
                                placeholder="Ingresar nombre de persona o empresa" fgroup-class="col-md-8"  value="{{ $cliente->razonsocial}}" />
                                <x-adminlte-input name="ruc" label="Ruc"
                                placeholder="Ingresar ruc" fgroup-class="col-md-4" value="{{ $cliente->ruc}}" />
                        </div>

                        <div class="row">
                            <x-adminlte-input name="celular" label="Celular"
                                placeholder="Ingresar número de celular" fgroup-class="col-md-3" value="{{ $cliente->celular}}"/>
                                <x-adminlte-input name="correo" type="email" label="Email"
                                placeholder="Ingresar dirección de correo electronico" fgroup-class="col-md-3" value="{{ $cliente->correo}}"/>
                                <x-adminlte-input name="direccion" label="Dirección"
                                placeholder="Ingresar dirección de domicilio" fgroup-class="col-md-6"  value="{{ $cliente->direccion}} "/>
                        </div>

                        <div class="row">
                            {{-- Disabled --}}
                            <x-adminlte-textarea name="observacion" label="Observación" 
                                fgroup-class="col-md-12" >
                                {{ $cliente->observacion}}
                            </x-adminlte-textarea>
                        </div>                

                        <div class="row">
                            <x-adminlte-select name="id_estado" label="Estado del Cliente" data-placeholder="Seleccionar una opción..." fgroup-class="col-md-3">
                                @foreach ($opcion as $item)
                                    <option value={{ $item->id }} {{ $item->id == $cliente->id_estado ? 'selected' : '' }} >{{ $item->descripcion }}</option>
                                @endforeach                                
                            </x-adminlte-select>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-12">
                                <a class="btn btn-danger" style="float: right;" href="{{route('cliente.index')}}">Cancelar</a>
                                <x-adminlte-button class="btn-group mr-2" style="float: right;" type="submit" label="Guardar" theme="primary"
                                    icon="fas fa-lg fa-save" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
