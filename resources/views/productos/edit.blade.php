@extends('adminlte::page')

@section('plugins.BootstrapSwitch', true)

@section('content_header')
    <h1 class="m-0 custom-heading">{{ __('Editar Datos Del Producto') }}</h1>
@stop

@section('content')
@php
    $defaultImage = asset('images/default.png');

    $imagenActual = $imagenUrl
        ?? ($producto->imagen ? asset('storage/' . $producto->imagen) : $defaultImage);
@endphp

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('producto.update', $producto) }}"
                      method="POST"
                      enctype="multipart/form-data"
                      autocomplete="off">

                    @csrf
                    @method('PUT')

                    <ul class="nav nav-tabs" id="productoTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active"
                               id="general-tab"
                               data-toggle="tab"
                               href="#general"
                               role="tab"
                               aria-controls="general"
                               aria-selected="true">
                                <i class="fas fa-info-circle"></i> {{ __('General') }}
                            </a>
                        </li>

                        @can('producto comercial')
                            <li class="nav-item">
                                <a class="nav-link"
                                   id="comercial-tab"
                                   data-toggle="tab"
                                   href="#comercial"
                                   role="tab"
                                   aria-controls="comercial"
                                   aria-selected="false">
                                    <i class="fas fa-chart-line"></i> {{ __('Datos Comerciales') }}
                                </a>
                            </li>
                        @endcan

                        @can('producto stock')
                            <li class="nav-item">
                                <a class="nav-link"
                                   id="stock-tab"
                                   data-toggle="tab"
                                   href="#stock"
                                   role="tab"
                                   aria-controls="stock"
                                   aria-selected="false">
                                    <i class="fas fa-warehouse"></i> {{ __('Stock') }}
                                </a>
                            </li>
                        @endcan
                    </ul>

                    <div class="tab-content mt-3">

                        {{-- TAB GENERAL --}}
                        <div class="tab-pane fade show active"
                             id="general"
                             role="tabpanel"
                             aria-labelledby="general-tab">

                            <div class="row">
                                <x-adminlte-input name="codigo"
                                                  label="Código"
                                                  placeholder="{{ __('Código') }}"
                                                  fgroup-class="col-md-3"
                                                  value="{{ old('codigo', $producto->codigo) }}"
                                                  style="text-align: center;"
                                                  label-class="text-info">
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text bg-info">
                                            <i class="fas fa-barcode"></i>
                                        </div>
                                    </x-slot>
                                </x-adminlte-input>

                                <x-adminlte-input name="descripcion"
                                                  label="Descripción"
                                                  placeholder="{{ __('Descripción') }}"
                                                  fgroup-class="col-md-7"
                                                  value="{{ old('descripcion', $producto->descripcion) }}" />

                                <x-adminlte-select name="id_impuesto"
                                                   id="id_impuesto"
                                                   label="Impuesto"
                                                   fgroup-class="col-md-2"
                                                   label-class="text-success">
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text bg-gradient-success">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                    </x-slot>

                                    @foreach ($impuestos as $imp)
                                        <option value="{{ $imp->id }}"
                                            {{ old('id_impuesto', $producto->id_impuesto) == $imp->id ? 'selected' : '' }}>
                                            {{ $imp->valor_formateado ?? number_format($imp->valor, 2, ',', '.') }}%
                                            ({{ $imp->descripcion }})
                                        </option>
                                    @endforeach
                                </x-adminlte-select>
                            </div>

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <strong>Verificar los datos:</strong>
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="imagen">{{ __('Imagen del Producto') }}</label>

                                        <div id="preview-container" class="mb-2">
                                            <img id="preview"
                                                 src="{{ $imagenActual }}"
                                                 class="img-fluid img-thumbnail"
                                                 style="max-height:180px;">
                                        </div>

                                        <div class="custom-file">
                                            <input type="file"
                                                   name="imagen"
                                                   id="imagen"
                                                   class="custom-file-input"
                                                   accept="image/*">

                                            <label class="custom-file-label" for="imagen">
                                                {{ __('Seleccionar imagen') }}
                                            </label>
                                        </div>

                                        <button type="button"
                                                id="remove-preview"
                                                class="btn btn-sm btn-outline-secondary mt-1 d-none">
                                            <i class="fas fa-times"></i> {{ __('Quitar imagen seleccionada') }}
                                        </button>

                                        <small class="form-text text-muted">
                                            {{ __('Si no seleccionás una nueva imagen, se mantiene la actual.') }}
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <x-adminlte-textarea name="detalle"
                                                         label="Detalle del Producto"
                                                         placeholder="{{ __('Ingresar detalle del producto') }}"
                                                         label-class="text-warning">
                                        <x-slot name="prependSlot">
                                            <div class="input-group-text bg-warning">
                                                <i class="fas fa-lg fa-file-alt"></i>
                                            </div>
                                        </x-slot>
                                        {{ old('detalle', $producto->detalle) }}
                                    </x-adminlte-textarea>

                                    <x-adminlte-select2 name="id_proveedor"
                                                        id="id_proveedor"
                                                        label="Proveedor Principal"
                                                        data-placeholder="{{ __('Seleccionar un proveedor...') }}"
                                                        fgroup-class="col-md-12"
                                                        label-class="text-info">
                                        <x-slot name="prependSlot">
                                            <div class="input-group-text bg-info">
                                                <i class="fas fa-truck"></i>
                                            </div>
                                        </x-slot>

                                        <option value=""></option>

                                        @foreach ($proveedores as $prov)
                                            <option value="{{ $prov->id }}"
                                                {{ old('id_proveedor', $producto->id_proveedor) == $prov->id ? 'selected' : '' }}>
                                                {{ $prov->razonsocial }}
                                            </option>
                                        @endforeach
                                    </x-adminlte-select2>
                                </div>
                            </div>

                            <div class="row">
                                <x-adminlte-select2 name="id_categoria"
                                                    id="id_categoria"
                                                    label="Categoría"
                                                    data-placeholder="{{ __('Seleccionar una categoría...') }}"
                                                    fgroup-class="col-md-5"
                                                    label-class="text-danger">
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text bg-gradient-red">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                    </x-slot>

                                    <x-slot name="appendSlot">
                                        <x-adminlte-button theme="outline-danger"
                                                           data-toggle="modal"
                                                           data-target="#addCatModal"
                                                           icon="fas fa-lg fa-plus text-danger" />
                                    </x-slot>

                                    <option value="">{{ __('Seleccionar una categoría...') }}</option>

                                    @foreach ($categoria as $item)
                                        <option value="{{ $item->id }}"
                                            {{ old('id_categoria', $producto->id_categoria) == $item->id ? 'selected' : '' }}>
                                            {{ $item->descripcion }}
                                        </option>
                                    @endforeach
                                </x-adminlte-select2>

                                <x-adminlte-select2 name="id_medida"
                                                    id="id_medida"
                                                    label="Unidad Medida"
                                                    data-placeholder="{{ __('Seleccionar una medida...') }}"
                                                    fgroup-class="col-md-4"
                                                    label-class="text-danger">
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text bg-gradient-red">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                    </x-slot>

                                    <x-slot name="appendSlot">
                                        <x-adminlte-button theme="outline-danger"
                                                           data-toggle="modal"
                                                           data-target="#addunidmedidaModal"
                                                           icon="fas fa-lg fa-plus text-danger" />
                                    </x-slot>

                                    <option value="">{{ __('Seleccionar una medida...') }}</option>

                                    @foreach ($medida as $item)
                                        <option value="{{ $item->id }}"
                                            {{ old('id_medida', $producto->id_medida) == $item->id ? 'selected' : '' }}>
                                            {{ $item->descripcion }}
                                        </option>
                                    @endforeach
                                </x-adminlte-select2>

                                <x-adminlte-select name="estado"
                                                   label="Estado"
                                                   fgroup-class="col-md-3">
                                    <option value="1" {{ old('estado', $producto->estado) == 1 ? 'selected' : '' }}>
                                        {{ __('Activo') }}
                                    </option>
                                    <option value="0" {{ old('estado', $producto->estado) == 0 ? 'selected' : '' }}>
                                        {{ __('Inactivo') }}
                                    </option>
                                </x-adminlte-select>
                            </div>

                            <div class="row">
                                <x-adminlte-select name="tipo"
                                                   label="Tipo de Producto"
                                                   fgroup-class="col-md-4"
                                                   label-class="text-secondary">
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text bg-secondary">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                    </x-slot>

                                    <option value="venta" {{ old('tipo', $producto->tipo) == 'venta' ? 'selected' : '' }}>
                                        {{ __('Para la Venta') }}
                                    </option>
                                    <option value="uso_interno" {{ old('tipo', $producto->tipo) == 'uso_interno' ? 'selected' : '' }}>
                                        {{ __('Uso Interno') }}
                                    </option>
                                    <option value="ambos" {{ old('tipo', $producto->tipo) == 'ambos' ? 'selected' : '' }}>
                                        {{ __('Ambos') }}
                                    </option>
                                </x-adminlte-select>

                                <div class="col-md-4 text-right">
                                    {!! $qrCode !!}
                                </div>
                            </div>
                        </div>

                        @can('producto comercial')
                            {{-- TAB COMERCIAL --}}
                            <div class="tab-pane fade"
                                 id="comercial"
                                 role="tabpanel"
                                 aria-labelledby="comercial-tab">

                                <div class="card card-outline card-success">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-dollar-sign"></i> {{ __('Precios') }}
                                        </h3>
                                    </div>

                                    <div class="card-body">
                                        <div class="row">
                                            <x-adminlte-input name="pcosto"
                                                              id="pcosto"
                                                              type="number"
                                                              label="Precio Costo"
                                                              value="{{ old('pcosto', $producto->pcosto ?? 0) }}"
                                                              fgroup-class="col-md-4"
                                                              min="0"
                                                              step="any"
                                                              oninput="calcularPorcentajeAumento()" />

                                            <x-adminlte-input name="porcentaje"
                                                              id="porcentaje"
                                                              type="number"
                                                              label="% Margen"
                                                              fgroup-class="col-md-2"
                                                              value="0"
                                                              min="0"
                                                              max="100"
                                                              step="any"
                                                              oninput="calcularPrecioVenta()"
                                                              label-class="text-success" />

                                            <x-adminlte-input name="pventa"
                                                              id="pventa"
                                                              type="number"
                                                              label="Precio Venta"
                                                              value="{{ old('pventa', $producto->pventa ?? 0) }}"
                                                              fgroup-class="col-md-4"
                                                              min="0"
                                                              step="any"
                                                              oninput="calcularPorcentajeAumento()" />
                                        </div>

                                        <div class="row">
                                            <div class="col-6 rcorners2 importet">
                                                <p>
                                                    <strong>{{ __('Margen según fórmula:') }}</strong>
                                                    {{ __('(Precio Venta - Costo) / Precio Venta;') }}
                                                </p>
                                                <h1 id="margenganancia" class="text-center">0 %</h1>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card card-outline card-info mt-3">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-layer-group"></i>
                                            {{ __('Precios Mayoristas por Tramos') }}
                                        </h3>

                                        <div class="card-tools">
                                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        <p class="text-muted">
                                            {{ __('Define precios especiales según la cantidad comprada.') }}
                                        </p>

                                        <table class="table table-sm table-bordered" id="tierTable">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th style="width:40px;">{{ __('#') }}</th>
                                                    <th>{{ __('Desde Cantidad') }}</th>
                                                    <th>{{ __('Precio Unitario Gs.') }}</th>
                                                    <th style="width:50px;"></th>
                                                </tr>
                                            </thead>

                                            <tbody id="tierBody">
                                                @php
                                                    $oldCantidades = old('tier_cantidad');
                                                    $oldPrecios = old('tier_precio');
                                                @endphp

                                                @if ($oldCantidades)
                                                    @foreach ($oldCantidades as $i => $cantidad)
                                                        <tr>
                                                            <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                                            <td>
                                                                <input type="number"
                                                                       name="tier_cantidad[]"
                                                                       class="form-control form-control-sm"
                                                                       value="{{ $cantidad }}"
                                                                       min="1">
                                                            </td>
                                                            <td>
                                                                <input type="number"
                                                                       name="tier_precio[]"
                                                                       class="form-control form-control-sm"
                                                                       value="{{ $oldPrecios[$i] ?? '' }}"
                                                                       min="0"
                                                                       step="any">
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <button type="button"
                                                                        class="btn btn-danger btn-sm"
                                                                        onclick="this.closest('tr').remove(); renumerarTiers();">
                                                                    <i class="fa fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    @foreach ($producto->precioTiers as $tier)
                                                        <tr>
                                                            <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                                            <td>
                                                                <input type="number"
                                                                       name="tier_cantidad[]"
                                                                       class="form-control form-control-sm"
                                                                       value="{{ $tier->cantidad_desde }}"
                                                                       min="1">
                                                            </td>
                                                            <td>
                                                                <input type="number"
                                                                       name="tier_precio[]"
                                                                       class="form-control form-control-sm"
                                                                       value="{{ $tier->precio_unitario }}"
                                                                       min="0"
                                                                       step="any">
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <button type="button"
                                                                        class="btn btn-danger btn-sm"
                                                                        onclick="this.closest('tr').remove(); renumerarTiers();">
                                                                    <i class="fa fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>

                                        <button type="button"
                                                class="btn btn-success btn-sm mt-2"
                                                onclick="addTierRow()">
                                            <i class="fas fa-plus"></i> {{ __('Agregar Tramo') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endcan

                        @can('producto stock')
                            {{-- TAB STOCK --}}
                            <div class="tab-pane fade"
                                 id="stock"
                                 role="tabpanel"
                                 aria-labelledby="stock-tab">

                                <div class="row">
                                    <x-adminlte-input name="stock_inicial"
                                                      id="stock_inicial"
                                                      type="number"
                                                      label="Stock Inicial"
                                                      value="{{ old('stock_inicial', $producto->stock_inicial ?? 0) }}"
                                                      fgroup-class="col-md-3"
                                                      step="any"
                                                      min="0" />

                                    <x-adminlte-input name="stock"
                                                      type="number"
                                                      label="Stock Actual"
                                                      value="{{ old('stock', $producto->stock ?? 0) }}"
                                                      fgroup-class="col-md-3"
                                                      step="any"
                                                      min="0" />

                                    <x-adminlte-input name="stock_minimo"
                                                      id="stock_minimo"
                                                      type="number"
                                                      label="Stock Mínimo"
                                                      value="{{ old('stock_minimo', $producto->stock_minimo ?? 0) }}"
                                                      fgroup-class="col-md-3"
                                                      step="any"
                                                      min="0" />

                                    <x-adminlte-input name="stock_maximo"
                                                      id="stock_maximo"
                                                      type="number"
                                                      label="Stock Máximo"
                                                      value="{{ old('stock_maximo', $producto->stock_maximo ?? 0) }}"
                                                      fgroup-class="col-md-3"
                                                      step="any"
                                                      min="0" />
                                </div>

                                <div class="row">
                                    <x-adminlte-input name="ubicacion_deposito"
                                                      label="Ubicación en Depósito"
                                                      value="{{ old('ubicacion_deposito', $producto->ubicacion_deposito) }}"
                                                      placeholder="{{ __('Ej: Estante A, Pasillo 3') }}"
                                                      fgroup-class="col-md-6" />
                                </div>
                            </div>
                        @endcan
                    </div>

                    <div class="row mt-3">
                        <div class="form-group col-md-12 text-right">
                            <a class="btn btn-danger mx-1" href="{{ route('producto.index') }}">
                                {{ __('Cancelar') }}
                            </a>

                            <x-adminlte-button type="submit"
                                               label="Guardar"
                                               theme="primary"
                                               icon="fas fa-lg fa-save" />
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

{{-- MODAL CATEGORÍA --}}
<div class="modal fade" id="addCatModal" tabindex="-1" role="dialog" aria-labelledby="addCatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="custom-heading" id="addCatModalLabel">{{ __('Categoría del Producto') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="formCrearCategoria">
                    @csrf

                    <x-adminlte-input name="descripcion"
                                      id="cat_descripcion"
                                      label="Descripción"
                                      placeholder="{{ __('Descripción') }}"
                                      fgroup-class="col-md-12"
                                      label-class="text-danger">
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-danger">
                                <i class="fas fa-tag"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input>

                    <input type="hidden" id="cat_id_dominio" name="id_dominio" value="3">
                </form>

                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    {{ __('Cerrar') }}
                </button>

                <button type="button"
                        class="btn btn-primary"
                        onclick="guardarCategoria('{{ route('guardar-categoria') }}','formCrearCategoria',true,'id_categoria','borrar-categoria')">
                    {{ __('Guardar') }}
                </button>
            </div>

            <div class="modal-footer">
                <x-adminlte-datatable id="tableCategorias"
                                       :heads="$headcat"
                                       head-theme="dark"
                                       theme="light"
                                       striped
                                       hoverable
                                       with-buttons>
                    @foreach ($categoria as $row)
                        <tr class="table-row" data-id="{{ $row->id }}">
                            <td>{{ $row->descripcion }}</td>
                            <td>
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary"
                                        data-url="{{ url('borrar-categoria') . '/' . $row->id }}"
                                        onclick="borrar(this,'id_categoria',true)">
                                    <i class="fa fa-sm fa-fw fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </x-adminlte-datatable>
            </div>

        </div>
    </div>
</div>

{{-- MODAL UNIDAD --}}
<div class="modal fade" id="addunidmedidaModal" tabindex="-1" role="dialog" aria-labelledby="addUnidadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="custom-heading" id="addUnidadModalLabel">{{ __('Unidad medida del Producto') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="formCrearUnidad">
                    @csrf

                    <x-adminlte-input name="descripcion"
                                      id="unidad_descripcion"
                                      label="Descripción"
                                      placeholder="{{ __('Descripción') }}"
                                      fgroup-class="col-md-12"
                                      label-class="text-danger">
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-danger">
                                <i class="fas fa-tag"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input>

                    <input type="hidden" id="unidad_id_dominio" name="id_dominio" value="5">
                </form>

                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    {{ __('Cerrar') }}
                </button>

                <button type="button"
                        class="btn btn-primary"
                        onclick="guardarUnidad('{{ route('guardar-unidad') }}','formCrearUnidad',true,'id_medida','borrar-unidad')">
                    {{ __('Guardar') }}
                </button>
            </div>

            <div class="modal-footer">
                <x-adminlte-datatable id="tableMedidas"
                                       :heads="$headcat"
                                       head-theme="dark"
                                       theme="light"
                                       striped
                                       hoverable
                                       with-buttons>
                    @foreach ($medida as $row)
                        <tr class="table-row" data-id="{{ $row->id }}">
                            <td>{{ $row->descripcion }}</td>
                            <td>
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary"
                                        data-url="{{ url('borrar-unidad') . '/' . $row->id }}"
                                        onclick="borrar(this,'id_medida',true)">
                                    <i class="fa fa-sm fa-fw fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </x-adminlte-datatable>
            </div>

        </div>
    </div>
</div>
@stop

@push('js')
<script>
    var successMessage = @json(session('success'));
    var errorMessage = @json(session('error'));
    var imagenActual = @json($imagenActual);

    if (successMessage) {
        Swal.fire('Éxito', successMessage, 'success');
    }

    if (errorMessage) {
        Swal.fire('Error', errorMessage, 'error');
    }

    function calcularPorcentajeAumento() {
        var pcosto = parseFloat(document.getElementById('pcosto')?.value || 0);
        var pventa = parseFloat(document.getElementById('pventa')?.value || 0);

        if (pcosto > 0 && pventa > 0) {
            var porcentajeAumento = ((pventa - pcosto) / pcosto) * 100;
            var margenGanancia = ((pventa - pcosto) / pventa) * 100;

            document.getElementById('porcentaje').value = porcentajeAumento.toFixed(2);

            const margenGananciaElement = document.getElementById('margenganancia');
            if (margenGananciaElement) {
                margenGananciaElement.textContent = margenGanancia.toFixed(2) + ' %';
            }
        } else {
            if (document.getElementById('porcentaje')) {
                document.getElementById('porcentaje').value = 0;
            }

            const margenGananciaElement = document.getElementById('margenganancia');
            if (margenGananciaElement) {
                margenGananciaElement.textContent = '0 %';
            }
        }
    }

    function calcularPrecioVenta() {
        var pcosto = parseFloat(document.getElementById('pcosto')?.value || 0);
        var porcentaje = parseFloat(document.getElementById('porcentaje')?.value || 0);

        if (porcentaje > 0 && pcosto >= 0) {
            var pventa = pcosto * (1 + porcentaje / 100);
            document.getElementById('pventa').value = pventa.toFixed(2);
        } else {
            document.getElementById('pventa').value = 0;
        }

        calcularPorcentajeAumento();
    }

    let tierCounter = $('#tierBody tr').length;

    function addTierRow(cantidad, precio) {
        tierCounter++;

        var row = '<tr id="tierRow' + tierCounter + '">' +
            '<td class="text-center align-middle">' + tierCounter + '</td>' +
            '<td><input type="number" name="tier_cantidad[]" class="form-control form-control-sm" value="' + (cantidad || '') + '" min="1" placeholder="Ej: 12"></td>' +
            '<td><input type="number" name="tier_precio[]" class="form-control form-control-sm" value="' + (precio || '') + '" min="0" step="any" placeholder="Precio unitario"></td>' +
            '<td class="text-center align-middle">' +
            '<button type="button" class="btn btn-danger btn-sm" onclick="this.closest(\'tr\').remove(); renumerarTiers();"><i class="fa fa-trash"></i></button>' +
            '</td>' +
            '</tr>';

        $('#tierBody').append(row);
        renumerarTiers();
    }

    function renumerarTiers() {
        $('#tierBody tr').each(function(i) {
            $(this).find('td:first').text(i + 1);
        });

        tierCounter = $('#tierBody tr').length;
    }

    var dataTable;
    var dataTable1;

    $(document).ready(function() {
        $('#addCatModal').appendTo('body');
        $('#addunidmedidaModal').appendTo('body');

        if (typeof bsCustomFileInput !== 'undefined') {
            bsCustomFileInput.init();
        }

        calcularPorcentajeAumento();
        renumerarTiers();

        $(document).on('change', '#imagen', function() {
            const file = this.files && this.files[0];
            const preview = $('#preview');
            const removeBtn = $('#remove-preview');

            if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.attr('src', e.target.result);
                };

                reader.readAsDataURL(file);
                removeBtn.removeClass('d-none');

                $('.custom-file-label[for="imagen"]').text(file.name);
            }
        });

        $(document).on('click', '#remove-preview', function() {
            $('#preview').attr('src', imagenActual);
            $('#remove-preview').addClass('d-none');
            $('#imagen').val('');
            $('.custom-file-label[for="imagen"]').text('{{ __('Seleccionar imagen') }}');
        });

        if (!$.fn.DataTable.isDataTable('#tableCategorias')) {
            dataTable = $('#tableCategorias').DataTable({});
        } else {
            dataTable = $('#tableCategorias').DataTable();
        }

        if (!$.fn.DataTable.isDataTable('#tableMedidas')) {
            dataTable1 = $('#tableMedidas').DataTable({});
        } else {
            dataTable1 = $('#tableMedidas').DataTable();
        }
    });

    function guardarCategoria(guardarUrl, nombreFormulario, addSelect2, nombreselect2, borrarUrlBase) {
        $.ajax({
            type: "POST",
            url: guardarUrl,
            data: $("#" + nombreFormulario).serialize(),
            dataType: 'json',
            success: function(response) {
                var id = response.id;
                var descripcion = response.descripcion;
                var data = response.data || descripcion;

                if (addSelect2) {
                    agregarSelect2(id, descripcion, nombreselect2);
                }

                Swal.fire('Guardado exitoso', 'Registro agregado correctamente.', 'success');

                var url = "{{ url('/') }}/" + borrarUrlBase + "/" + id;

                var nuevaFila =
                    '<button type="button" class="btn btn-sm btn-outline-secondary" data-url="' +
                    url + '" onclick="borrar(this,\'' + nombreselect2 + '\',true)">' +
                    '<i class="fa fa-sm fa-fw fa-trash"></i></button>';

                var filaDatos = [data, nuevaFila];

                var addedRow = dataTable.row.add(filaDatos).draw(false).node();
                $(addedRow).attr("data-id", id);
                $(addedRow).addClass("table-row");

                $('#cat_descripcion').val('');
            },
            error: function(error) {
                console.log(error);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Hubo problemas al intentar guardar.'
                });
            }
        });
    }

    function guardarUnidad(guardarUrl, nombreFormulario, addSelect2, nombreselect2, borrarUrlBase) {
        $.ajax({
            type: "POST",
            url: guardarUrl,
            data: $("#" + nombreFormulario).serialize(),
            dataType: 'json',
            success: function(response) {
                var id = response.id;
                var descripcion = response.descripcion;
                var data = response.data || descripcion;

                if (addSelect2) {
                    agregarSelect2(id, descripcion, nombreselect2);
                }

                Swal.fire('Guardado exitoso', 'Registro agregado correctamente.', 'success');

                var url = "{{ url('/') }}/" + borrarUrlBase + "/" + id;

                var nuevaFila =
                    '<button type="button" class="btn btn-sm btn-outline-secondary" data-url="' +
                    url + '" onclick="borrar(this,\'' + nombreselect2 + '\',true)">' +
                    '<i class="fa fa-sm fa-fw fa-trash"></i></button>';

                var filaDatos = [data, nuevaFila];

                var addedRow = dataTable1.row.add(filaDatos).draw(false).node();
                $(addedRow).attr("data-id", id);
                $(addedRow).addClass("table-row");

                $('#unidad_descripcion').val('');
            },
            error: function(error) {
                console.log(error);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Hubo problemas al intentar guardar.'
                });
            }
        });
    }

    function borrar(boton, select2, utilizaselect2) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminarlo',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                var borrarUrl = boton.getAttribute('data-url');

                $.ajax({
                    type: "DELETE",
                    url: borrarUrl,
                    data: {
                        "_token": "{{ csrf_token() }}"
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.hasOwnProperty('error')) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.error
                            });
                        } else {
                            var id = response.id;

                            $(".table-row[data-id='" + id + "']").remove();

                            if (select2 === 'id_categoria') {
                                dataTable.row(".table-row[data-id='" + id + "']").remove().draw(false);
                            } else {
                                dataTable1.row(".table-row[data-id='" + id + "']").remove().draw(false);
                            }

                            if (utilizaselect2) {
                                borrarSelect2(id, select2);
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: 'Registro eliminado con éxito.'
                            });
                        }
                    },
                    error: function(error) {
                        console.log(error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Hubo problemas al intentar eliminar.'
                        });
                    }
                });
            }
        });
    }

    function borrarSelect2(valueToDelete, nombreselect) {
        $('#' + nombreselect + ' option[value="' + valueToDelete + '"]').remove();
        $('#' + nombreselect).trigger('change.select2');
    }

    function agregarSelect2(id, descripcion, nombreselect2) {
        var nuevaOpcion = new Option(descripcion, id, true, true);
        $('#' + nombreselect2).append(nuevaOpcion);
        $('#' + nombreselect2).trigger('change.select2');
    }
</script>
@endpush
