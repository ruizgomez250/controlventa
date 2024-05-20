@extends('adminlte::page')



@section('content_header')
    <h1 class="m-0 custom-heading ">Registrar Proveedor</h1>
@stop

@section('content')
@section('plugins.BootstrapSwitch', true)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('proveedor.store') }}" method="post">
                        @csrf
                        @method('POST')
                        {{-- With label, invalid feedback disabled and form group class --}}

                        <div class="row">
                            <x-adminlte-input name="razonsocial" label="Razón Social"
                                placeholder="Ingresar nombre de persona o empresa" fgroup-class="col-md-6"
                                value="{{ old('razonsocial') }}" style="text-align: center;" label-class="text-primary">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-primary">
                                        <i class="fas fa-user "></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                                @error('razonsocial')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            <x-adminlte-input name="ruc" label="Ruc" placeholder="Ingresar ruc"
                                fgroup-class="col-md-4" />
                                @error('ruc')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                            @php
                                $config = [
                                    'onColor' => 'success',
                                    'offColor' => 'gray',
                                    'onText' => 'Activo',
                                    'offText' => 'Inactivo',
                                    'state' => false,
                                    'labelText' => '<i class="fas fa-power-off text-muted"></i>',
                                ];
                            @endphp
                            <x-adminlte-input-switch name="estado" label="Estado" igroup-size="sm" :config="$config" />
                        </div>

                        <div class="row">
                            
                                <x-adminlte-input name="celular" label="Celular" placeholder="Ingresar número de celular"
                                fgroup-class="col-md-3" value="{{ old('celular') }}" style="text-align: center;"
                                label-class="text-success">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-success">
                                        <i class="fas fa-phone "></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                            
                            <x-adminlte-input name="correo" type="email" label="Email"
                                placeholder="Ingresar dirección de correo electronico" fgroup-class="col-md-3"
                                value="{{ old('correo') }}" style="text-align: center;" label-class="text-info">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-info">
                                        <i class="fas fa-at "></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                            <x-adminlte-input name="direccion" label="Dirección"
                                placeholder="Ingresar dirección de domicilio" fgroup-class="col-md-6"
                                value="{{ old('direccion') }}" style="text-align: center;" label-class="text-danger">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-danger">
                                        <i class="fas fa-map-marker "></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>

                        <div class="row">
                            {{-- Disabled --}}
                            <x-adminlte-textarea name="observacion" label="Observación" fgroup-class="col-md-12"
                                label-class="text-warning">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-warning">
                                        <i class="fas fa-lg fa-file-alt "></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-textarea>
                        </div>


                        <div class="row">
                            <div class="form-group col-md-12">
                                <a class="btn btn-danger mx-1" style="float: right;" href="{{route('proveedor.index')}}">Cancelar</a>
                                <x-adminlte-button class="btn-group mx-1" style="float: right;" type="submit" label="Registrar"
                                    theme="primary" icon="fas fa-lg fa-save" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
