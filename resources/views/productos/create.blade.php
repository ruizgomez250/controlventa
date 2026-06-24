@extends('adminlte::page')



@section('content_header')
    <h1 class="m-0 custom-heading">Registrar Producto</h1>
@stop

@section('content')
@section('plugins.BootstrapSwitch', true)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('producto.store') }}" method="post" enctype="multipart/form-data" autocomplete="off">
                    @csrf
                    @method('POST')

                    <ul class="nav nav-tabs" id="productoTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-controls="general" aria-selected="true">
                                <i class="fas fa-info-circle"></i> General
                            </a>
                        </li>
                        @can('producto comercial')
                        <li class="nav-item">
                            <a class="nav-link" id="comercial-tab" data-toggle="tab" href="#comercial" role="tab" aria-controls="comercial" aria-selected="false">
                                <i class="fas fa-chart-line"></i> Datos Comerciales
                            </a>
                        </li>
                        @endcan
                        @can('producto stock')
                        <li class="nav-item">
                            <a class="nav-link" id="stock-tab" data-toggle="tab" href="#stock" role="tab" aria-controls="stock" aria-selected="false">
                                <i class="fas fa-warehouse"></i> Stock
                            </a>
                        </li>
                        @endcan
                    </ul>

                    <div class="tab-content mt-3">
                        {{-- TAB: GENERAL --}}
                        <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                            <div class="row">
                                <x-adminlte-input name="codigo" label="Código" placeholder="Código" fgroup-class="col-md-3"
                                    value="{{ $barra = generarcodigo() }}" style="text-align: center;" label-class="text-info">
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text bg-info">
                                            <i class="fas fa-barcode"></i>
                                        </div>
                                    </x-slot>
                                </x-adminlte-input>

                                <x-adminlte-input name="descripcion" label="Descripción"
                                    placeholder="Ingresar descripción del producto" fgroup-class="col-md-7" />
                                @error('descripcion')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                                <x-adminlte-select name="id_impuesto" id="id_impuesto" label="Impuesto" fgroup-class="col-md-2"
                                    label-class="text-success">
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text bg-gradient-success">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                    </x-slot>
                                    @foreach ($impuestos as $imp)
                                        <option value="{{ $imp->id }}"
                                            {{ old('id_impuesto', 1) == $imp->id ? 'selected' : '' }}>
                                            {{ $imp->valor_formateado }}% ({{ $imp->descripcion }})
                                        </option>
                                    @endforeach
                                </x-adminlte-select>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="imagen">Imagen del Producto</label>
                                        <div id="preview-container" class="mb-2">
                                            <img id="preview" src="{{ asset('images/default.png') }}" class="img-fluid img-thumbnail" style="max-height:180px;">
                                        </div>
                                        <div class="custom-file">
                                            <input type="file" name="imagen" id="imagen" class="custom-file-input" accept="image/*">
                                            <label class="custom-file-label" for="imagen">Seleccionar imagen</label>
                                        </div>
                                        <button type="button" id="remove-preview" class="btn btn-sm btn-outline-secondary mt-1 d-none">
                                            <i class="fas fa-times"></i> Quitar imagen
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <x-adminlte-textarea name="detalle" label="Detalle del Producto"
                                        placeholder="Ingresar detalle del producto" label-class="text-warning">
                                        <x-slot name="prependSlot">
                                            <div class="input-group-text bg-warning">
                                                <i class="fas fa-lg fa-file-alt"></i>
                                            </div>
                                        </x-slot>
                                    </x-adminlte-textarea>

                                    <x-adminlte-select2 name="id_proveedor" label="Proveedor Principal"
                                        data-placeholder="Seleccionar un proveedor..." fgroup-class="col-md-12"
                                        label-class="text-info">
                                        <x-slot name="prependSlot">
                                            <div class="input-group-text bg-info">
                                                <i class="fas fa-truck"></i>
                                            </div>
                                        </x-slot>
                                        <option value=""></option>
                                        @foreach ($proveedores as $prov)
                                            <option value="{{ $prov->id }}">{{ $prov->razonsocial }}</option>
                                        @endforeach
                                    </x-adminlte-select2>
                                </div>
                            </div>

                            <div class="row">
                                <x-adminlte-select2 name="id_categoria" id="id_categoria" label="Categoría" fgroup-class="col-md-5"
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
                                        <option value="{{ $item->id }}">{{ $item->descripcion }}</option>
                                    @endforeach
                                </x-adminlte-select2>

                                <x-adminlte-select2 name="id_medida" id="id_medida" label="Unidad Medida" fgroup-class="col-md-5"
                                    label-class="text-danger">
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text bg-gradient-red">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                    </x-slot>
                                    <x-slot name="appendSlot">
                                        <x-adminlte-button theme="outline-danger" data-toggle="modal"
                                            data-target="#addunidmedidaModal" icon="fas fa-lg fa-plus text-danger" />
                                    </x-slot>
                                    @foreach ($medida as $item)
                                        <option value="{{ $item->id }}">{{ $item->descripcion }}</option>
                                    @endforeach
                                </x-adminlte-select2>

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
                                <x-adminlte-select name="estado" label="Estado" fgroup-class="col-md-2">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </x-adminlte-select>
                            </div>

                            <div class="row">
                                <x-adminlte-select name="tipo" label="Tipo de Producto" fgroup-class="col-md-4"
                                    label-class="text-secondary">
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text bg-secondary">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                    </x-slot>
                                    <option value="venta">Para la Venta</option>
                                    <option value="uso_interno">Uso Interno</option>
                                    <option value="ambos">Ambos</option>
                                </x-adminlte-select>
                            </div>
                        </div>

                        @can('producto comercial')
                        {{-- TAB: DATOS COMERCIALES --}}
                        <div class="tab-pane fade" id="comercial" role="tabpanel" aria-labelledby="comercial-tab">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-dollar-sign"></i> Precios</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <x-adminlte-input name="pcosto" id="pcosto" type="number" label="Precio Costo"
                                            fgroup-class="col-md-4" value="0" min="0"
                                            oninput="calcularPorcentajeAumento()" />

                                        <x-adminlte-input name="porcentaje" id="porcentaje" type="number" label="% Margen"
                                            fgroup-class="col-md-2" value="0" min="0" max="100" step="any"
                                            oninput="calcularPrecioVenta()" label-class="text-success" />

                                        <x-adminlte-input name="pventa" id="pventa" type="number" label="Precio Venta"
                                            fgroup-class="col-md-4" value="0" min="0"
                                            oninput="calcularPorcentajeAumento()" />
                                    </div>
                                    <div class="row">
                                        <div class="col-6 rcorners2 importet">
                                            <p><strong>Margen según fórmula:</strong> (Precio Venta - Costo) / Precio Venta;</p>
                                            <h1 id="margenganancia" class="text-center">0 %</h1>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card card-outline card-info mt-3">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-layer-group"></i> Precios Mayoristas por Tramos</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">Define precios especiales según la cantidad comprada. El sistema usará automáticamente el tramo que corresponda.</p>
                                    <table class="table table-sm table-bordered" id="tierTable">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width:40px;">#</th>
                                                <th>Desde Cantidad</th>
                                                <th>Precio Unitario Gs.</th>
                                                <th style="width:50px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="tierBody"></tbody>
                                    </table>
                                    <button type="button" class="btn btn-success btn-sm mt-2" onclick="addTierRow()">
                                        <i class="fas fa-plus"></i> Agregar Tramo
                                    </button>
                                    <small class="text-muted ml-2">Ej: Desde 12 → 5.000, Desde 50 → 4.500</small>
                                </div>
                            </div>
                        </div>
                        @endcan

                        @can('producto stock')
                        {{-- TAB: STOCK --}}
                        <div class="tab-pane fade" id="stock" role="tabpanel" aria-labelledby="stock-tab">
                            <div class="row">
                                <x-adminlte-input name="stock_inicial" id="stock_inicial" type="number" label="Stock Inicial"
                                    fgroup-class="col-md-3" step="any" min="0" value="0.000" />

                                <x-adminlte-input name="stock" type="number" label="Stock Actual" fgroup-class="col-md-3"
                                    step="any" min="0" value="0.000" />

                                <x-adminlte-input name="stock_minimo" id="stock_minimo" type="number" label="Stock Mínimo"
                                    fgroup-class="col-md-3" step="any" min="0" value="0.000" />

                                <x-adminlte-input name="stock_maximo" id="stock_maximo" type="number" label="Stock Máximo"
                                    fgroup-class="col-md-3" step="any" min="0" value="0.000" />
                            </div>

                            <div class="row">
                                <x-adminlte-input name="ubicacion_deposito" label="Ubicación en Depósito"
                                    placeholder="Ej: Estante A, Pasillo 3" fgroup-class="col-md-6" />
                            </div>
                        </div>
                        @endcan
                    </div>

                    {{-- Botones --}}
                    <div class="row mt-3">
                        <div class="form-group col-md-12 text-right">
                            <a class="btn btn-danger mx-1" href="{{ route('producto.index') }}">Cancelar</a>
                            <x-adminlte-button type="submit" label="Registrar" theme="primary"
                                icon="fas fa-lg fa-save" />
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Themed --}}
<div class="modal fade" id="addCatModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class=" custom-heading" id="exampleModalLabel">Categoria del Producto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <form id="formCrearCategoria">
                        @csrf
                        <div class="form-group">
                            <x-adminlte-input name="descripcion" id="descripcion" label="Descripcion"
                                placeholder="Descripcion" fgroup-class="col-md-12" label-class="text-danger">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-danger">
                                        <i class="fas fa-tag "></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                            <input type="hidden" class="form-control" id="id_dominio" name="id_dominio"
                                value="3">
                        </div>
                    </form>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary"
                        onclick="guardarCategoria('{{ route('guardar-categoria') }}','formCrearCategoria',true,'id_categoria','borrar-categoria')">Guardar</button>

                </div>
            </div>
            <div class="modal-footer">

                <x-adminlte-datatable id="table1" :heads="$headcat" head-theme="dark" theme="light" striped
                    hoverable with-buttons>
                    @foreach ($categoria as $row)
                        <tr class="table-row" data-id="{{ $row->id }}">
                            <td>{{ $row->descripcion }}</td>
                            <td>
                                <form id="delete-form" class="d-inline">
                                    @csrf
                                    <input type="hidden" class="form-control" id="id" name="id"
                                        value="{{ $row->id }}">
                                    <input type="hidden" class="form-control" id="id_dominio" name="id_dominio"
                                        value="3">
                                    <input type="hidden" class="form-control" id="descripcion" name="descripcion"
                                        value="{{ $row->descripcion }}">
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                        id="delete-button" data-url="{{ url('borrar-categoria') . '/' . $row->id }}"
                                        onclick="borrar(this,'id_categoria',true)">
                                        <ion-icon name="trash-outline"><i
                                                class="fa fa-sm fa-fw fa-trash"></i></ion-icon>
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
<div class="modal fade" id="addunidmedidaModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class=" custom-heading" id="exampleModalLabel">Unidad medida del Producto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <form id="formCrearUnidad">
                        @csrf
                        <div class="form-group">
                            <x-adminlte-input name="descripcion" id="descripcion" label="Descripcion"
                                placeholder="Descripcion" fgroup-class="col-md-12" label-class="text-danger">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-danger">
                                        <i class="fas fa-tag "></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                            <input type="hidden" class="form-control" id="id_dominio" name="id_dominio"
                                value="5">
                        </div>
                    </form>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary"
                        onclick="guardarUnidad('{{ route('guardar-unidad') }}','formCrearUnidad',true,'id_medida','borrar-unidad')">Guardar</button>
                </div>
            </div>
            <div class="modal-footer">

                <x-adminlte-datatable id="table2" :heads="$headcat" head-theme="dark" theme="light" striped
                    hoverable with-buttons>
                    @foreach ($medida as $row)
                        <tr class="table-row" data-id="{{ $row->id }}">
                            <td>{{ $row->descripcion }}</td>
                            <td>
                                <form id="delete-form" class="d-inline">
                                    @csrf
                                    <input type="hidden" class="form-control" id="id" name="id"
                                        value="{{ $row->id }}">
                                    <input type="hidden" class="form-control" id="id_dominio" name="id_dominio"
                                        value="5">
                                    <input type="hidden" class="form-control" id="descripcion" name="descripcion"
                                        value="{{ $row->descripcion }}">
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                        id="delete-button" data-url="{{ url('borrar-unidad') . '/' . $row->id }}"
                                        onclick="borrar(this,'id_medida',true)">
                                        <ion-icon name="trash-outline"><i
                                                class="fa fa-sm fa-fw fa-trash"></i></ion-icon>
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
{{-- Example button to open modal --}}
@stop
@push('js')
<script>
    // Obtener el mensaje de éxito o error de Laravel
    var successMessage = "{{ session('success') }}";
    var errorMessage = "{{ session('error') }}";
    // Mostrar el mensaje de éxito o error con SweetAlert
    if (successMessage) {
        Swal.fire('Éxito', successMessage, 'success');
    } else if (errorMessage) {
        Swal.fire('Error', errorMessage, 'error');
    }
    function calcularPorcentajeAumento() {
        // Obtén los valores de precio de costo y precio de venta
        var pcosto = parseFloat(document.getElementById('pcosto').value);
        var pventa = parseFloat(document.getElementById('pventa').value);

        // Calcula el porcentaje de aumento si ambos valores son mayores a 0


        if (pcosto > 0 && pventa > 0) {
            var porcentajeAumento = ((pventa - pcosto) / pcosto) * 100;
            var margenGanancia = ((pventa - pcosto) / pventa) * 100;

            // Muestra el resultado en el campo de porcentaje
            document.getElementById('porcentaje').value = porcentajeAumento.toFixed(2);
            const margenGananciaElement = document.getElementById('margenganancia');
            margenGananciaElement.textContent = margenGanancia + ' %';

        } else {
            // Si uno de los valores es 0, establece el porcentaje en 0

            document.getElementById('porcentaje').value = 0;
        }
    }

    function calcularPrecioVenta() {
        var pcosto = parseFloat(document.getElementById('pcosto').value);
        var porcentaje = parseFloat(document.getElementById('porcentaje').value);

        if (porcentaje > 0) {
            var pventa = pcosto * (1 + porcentaje / 100);
            document.getElementById('pventa').value = pventa.toFixed(2);
        } else {
            document.getElementById('pventa').value = 0;
        }
    }

    let tierCounter = 0;
    function addTierRow(cantidad, precio) {
        tierCounter++;
        var row = '<tr id="tierRow' + tierCounter + '">' +
            '<td class="text-center align-middle">' + tierCounter + '</td>' +
            '<td><input type="number" name="tier_cantidad[]" class="form-control form-control-sm" value="' + (cantidad || '') + '" min="1" placeholder="Ej: 12" required></td>' +
            '<td><input type="number" name="tier_precio[]" class="form-control form-control-sm" value="' + (precio || '') + '" min="0" placeholder="Precio unitario" required></td>' +
            '<td class="text-center align-middle">' +
            '<button type="button" class="btn btn-danger btn-sm" onclick="this.closest(\'tr\').remove(); renumerarTiers();"><i class="fa fa-trash"></i></button>' +
            '</td>' +
            '</tr>';
        $('#tierBody').append(row);
    }

    function renumerarTiers() {
        $('#tierBody tr').each(function(i) {
            $(this).find('td:first').text(i + 1);
        });
    }
    // Obtén una referencia a la tabla DataTable

    var dataTable;
    var dataTable1;

    $(document).ready(function() {
        $('#addCatModal').appendTo('body');
        $('#addunidmedidaModal').appendTo('body');

        if (!$.fn.DataTable.isDataTable('#table1')) {
            dataTable = $('#table1').DataTable({});
        } else {
            dataTable = $('#table1').DataTable();
        }
        if (!$.fn.DataTable.isDataTable('#table2')) {
            dataTable1 = $('#table2').DataTable({});
        } else {
            dataTable1 = $('#table2').DataTable();
        }

        bsCustomFileInput.init();

        $(document).on('change', '#imagen', function() {
            const file = this.files && this.files[0];
            const preview = $('#preview');
            const container = $('#preview-container');
            const removeBtn = $('#remove-preview');
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
                container.removeClass('d-none');
                removeBtn.removeClass('d-none');
            }
        });

        $(document).on('click', '#remove-preview', function() {
            $('#preview').attr('src', '{{ asset("images/default.png") }}');
            $('#preview-container').addClass('d-none');
            $('#remove-preview').addClass('d-none');
            $('#imagen').val('');
        });
    });

    function guardarCategoria(guardarUrl, nombreFormulario, addSelect2, nombreselect2, borrar) {
        $.ajax({
            type: "POST",
            url: guardarUrl,
            data: $("#" + nombreFormulario).serialize(),
            dataType: 'json',
            success: function(response) {
                console.log(response);
                var id = response.id;
                var descripcion = response.descripcion;

                var data = response.data;


                if (addSelect2) {
                    agregarSelect2(id, descripcion, nombreselect2);
                }
                // Mostrar mensaje de éxito o hacer algo con la respuesta
                Swal.fire(
                    'guardado exitoso!',
                    'Presione el boton!',
                    'success'
                )
                url = "{{ url('/') }}/" + borrar + "/" + id;
                // Crear el botón de eliminación como una cadena HTML
                var nuevaFila =
                    '<button type="button" class="btn btn-sm btn-outline-secondary" id="delete-button" data-url="' +
                    url + '" onclick="borrar(this,\'' + nombreselect2 + '\',true)">' +
                    '<ion-icon name="trash-outline"><i class="fa fa-sm fa-fw fa-trash"></i></ion-icon> </button>';
                // 'filaDatos' con los datos de la fila que deseas agregar
                var filaDatos = [data, nuevaFila];


                // Agrega la fila a la tabla
                //dataTable.rows.add([filaDatos]).draw(false);
                var addedRow = dataTable.row.add(filaDatos).draw(false).node();
                $(addedRow).attr("data-id", id);
                $(addedRow).addClass("table-row");
                //dataTable.destroy(); // Destruye el DataTable existente
            },
            error: function(error) {
                // Manejar errores aquí
                console.log(error);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Hubo problemas al intentar guardar!'
                })
            }
        });
    }

    function guardarUnidad(guardarUrl, nombreFormulario, addSelect2, nombreselect2, borrar) {
        $.ajax({
            type: "POST",
            url: guardarUrl,
            data: $("#" + nombreFormulario).serialize(),
            dataType: 'json',
            success: function(response) {
                console.log(response);
                var id = response.id;
                var descripcion = response.descripcion;

                var data = response.data;


                if (addSelect2) {
                    agregarSelect2(id, descripcion, nombreselect2);
                }
                // Mostrar mensaje de éxito o hacer algo con la respuesta
                Swal.fire(
                    'guardado exitoso!',
                    'Presione el boton!',
                    'success'
                )
                url = "{{ url('/') }}/" + borrar + "/" + id;
                // Crear el botón de eliminación como una cadena HTML
                var nuevaFila =
                    '<button type="button" class="btn btn-sm btn-outline-secondary" id="delete-button" data-url="' +
                    url + '" onclick="borrar(this,\'' + nombreselect2 + '\',true)">' +
                    '<ion-icon name="trash-outline"><i class="fa fa-sm fa-fw fa-trash"></i></ion-icon> </button>';
                // 'filaDatos' con los datos de la fila que deseas agregar
                var filaDatos = [data, nuevaFila];
                var addedRow;
                if (nombreFormulario === 'formCrearCategoria') {
                    addedRow = dataTable.row.add(filaDatos).draw(false).node();
                } else {
                    addedRow = dataTable1.row.add(filaDatos).draw(false).node();
                }

                $(addedRow).attr("data-id", id);
                $(addedRow).addClass("table-row");
            },
            error: function(error) {
                // Manejar errores aquí
                console.log(error);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Hubo problemas al intentar guardar!'
                })
            }
        });
    }

    function borrar(borrarcategoria, select2, utilizaselect2) {
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
                var borrarCategoriaUrl = borrarcategoria.getAttribute(
                    'data-url'); //"{{ url('/borrar-categoria') }}/" + id; // Generar la URL con el id

                $.ajax({
                    type: "DELETE", // Cambiar de POST a DELETE
                    url: borrarCategoriaUrl,
                    data: {
                        "_token": "{{ csrf_token() }}"
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.hasOwnProperty('error')) {
                            // Manejar el mensaje de error
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.error
                            });
                        } else {
                            id = response.id;
                            $(".table-row[data-id='" + id + "']").remove();
                            dataTable.row('.table-row[data-id=' + id + ']').remove().draw(
                                false);
                            if (utilizaselect2)
                                borrarSelect2(id, select2);

                            // Manejar el mensaje de éxito
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: 'Categoría eliminada con éxito.'
                            });
                        }
                    },
                    error: function(error) {
                        // Manejar errores aquí
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Hubo problemas al intentar eliminar.'
                        })
                    }
                });
            }
        });
    }

    function borrarSelect2(valueToDelete, nombreselect) {

        // Establece el nuevo valor en el select2
        $('#' + nombreselect + ' option[value="' + valueToDelete + '"]').remove();
        $('#' + nombreselect).trigger('change.select2');
    }

    function agregarSelect2(id, descripcion, nombreselect2) {

        // Crea un nuevo elemento <option>
        var nuevaOpcion = new Option(descripcion, id, true, true);
        // Agrega la nueva opción al elemento select
        $('#' + nombreselect2).append(nuevaOpcion);

        // Luego, puedes actualizar el select para que se reflejen los cambios
        $('#' + nombreselect2).trigger('change.select2');
    }
</script>
@endpush
