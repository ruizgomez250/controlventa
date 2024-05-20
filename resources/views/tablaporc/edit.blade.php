@extends('adminlte::page')



@section('content_header')
    <h1 class="m-0 custom-heading">Editar Datos Del Producto</h1>
@stop

@section('content')
@section('plugins.BootstrapSwitch', true)

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('producto.update', $producto) }}" method="post">
                    @csrf
                    @method('put')
                    {{-- With label, invalid feedback disabled and form group class --}}
                    {{-- 'id', 'codigo', 'descripcion', 'detalle', 'id_categoria', 'id_estado','pcosto', 'pventa', 'observacion' --}}



                    <div class="row">

                        <x-adminlte-input name="codigo" label="Código" placeholder="Código" fgroup-class="col-md-3"
                            value="{{ $producto->codigo }}" style="text-align: center;" label-class="text-info">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-info">
                                    <i class="fas fa-barcode "></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>
                        @error('codigo')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <x-adminlte-input name="descripcion" label="Descripción" value="{{ $producto->descripcion }}"
                            fgroup-class="col-md-7" />
                        @error('descripcion')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <x-adminlte-select name="impuesto" id="impuesto" label="Impuesto"
                            data-placeholder="Seleccionar una categoría..." fgroup-class="col-md-2"
                            label-class="text-success">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-success">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                            </x-slot>
                            <option value="10" {{ $producto->impuesto == 10 ? 'selected' : '' }}>10 %</option>
                            <option value="5" {{ $producto->impuesto == 5 ? 'selected' : '' }}>5 %</option>
                            <option value="0" {{ $producto->impuesto == 0 ? 'selected' : '' }}>0 %</option>
                        </x-adminlte-select>

                    </div>



                    <div class="row">
                        {{-- Disabled --}}
                        <x-adminlte-textarea name="detalle" label="Detalle del Producto" fgroup-class="col-md-7"
                            placeholder="Ingresar detalle del producto" label-class="text-warning">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-warning">
                                    <i class="fas fa-lg fa-file-alt "></i>
                                </div>
                            </x-slot>
                            {{ $producto->detalle }}
                        </x-adminlte-textarea>

                        <x-adminlte-select2 name="id_categoria" label="Categoria"
                            data-placeholder="Seleccionar una categoría..." fgroup-class="col-md-5"
                            label-class="text-danger">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-red">
                                    <i class="fas fa-tag"></i>
                                </div>
                            </x-slot>
                            <x-slot name="appendSlot">
                                <x-adminlte-button theme="outline-danger" data-toggle="modal" data-target="#addCatModal"
                                    icon="fas fa-lg fa-plus text-danger" />
                            </x-slot>



                            @foreach ($categoria as $item)
                                <<option value={{ $item->id }}
                                    {{ $item->id == $producto->id_categoria ? 'selected' : '' }}>
                                    {{ $item->descripcion }}</option>
                            @endforeach
                        </x-adminlte-select2>
                    </div>


                    <div class="row">

                        <x-adminlte-input name="stock" type="number" label="Stock" fgroup-class="col-md-2"
                            step="any" min="0" value="{{ $producto->stock }}" />

                        <x-adminlte-select name="id_medida" label="Unidad de Medida"
                            data-placeholder="Seleccionar una medida..." fgroup-class="col-md-3">
                            @foreach ($medida as $item)
                                <option value={{ $item->id }}
                                    {{ $item->id == $producto->id_medida ? 'selected' : '' }}>{{ $item->descripcion }}
                                </option>
                            @endforeach
                        </x-adminlte-select>




                        <x-adminlte-input name="pcosto" type="number" label="Precio Costo"
                            value="{{ $producto->pcosto }}" fgroup-class="col-md-2" />

                        <x-adminlte-input name="pventa" type="number" label="Precio Venta"
                            value="{{ $producto->pventa }}" fgroup-class="col-md-2" />
                        @php
                            $config = [
                                'onColor' => 'success',
                                'offColor' => 'gray',
                                'onText' => 'Activo',
                                'offText' => 'Inactivo',
                                'state' => $producto->estado === 0 ? false : true,
                                'labelText' => '<i class="fas fa-power-off text-muted"></i>',
                            ];
                        @endphp
                        <x-adminlte-input-switch name="estado" label="Estado" igroup-size="sm" :config="$config" />
                        {{-- <x-adminlte-select name="id_estado" label="Estado"
                            data-placeholder="Seleccionar una opción..." fgroup-class="col-md-3">
                            
                            @foreach ($opcion as $item)
                            <option value={{ $item->id }} {{ $item->id == $producto->id_estado ? 'selected' : '' }} >{{ $item->descripcion }}</option>
                            @endforeach
                        </x-adminlte-select> --}}
                    </div>


                    <div class="mt-3 col-4">
                        <?php
                        echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG($producto->codigo, 'PHARMA', 2, 60) . '" alt="barcode"   /></br>';
                        
                        ?>
                        <div class="row" style="justify-content: center;"> P-{{ $producto->codigo }}</div>

                    </div>



                    <div class="row">
                        <div class="form-group col-md-12">
                            <a class="btn btn-danger" style="float: right;"
                                href="{{ route('producto.index') }}">Cancelar</a>
                            <x-adminlte-button class="btn-group mr-2" style="float: right;" type="submit"
                                label="Guardar" theme="primary" icon="fas fa-lg fa-save" />
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
