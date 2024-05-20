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
                <form action="{{ route('producto.store') }}" method="post" autocomplete="off">
                    @csrf
                    @method('POST')
                    {{-- 'id', 'codigo', 'descripcion', 'detalle', 'id_categoria', 'id_estado','pcosto', 'pventa', 'observacion' --}}

                    <div class="row">
                        <x-adminlte-input name="codigo" label="Código" placeholder="Código" fgroup-class="col-md-3"
                            value="{{ $barra = generarcodigo() }}" style="text-align: center;" label-class="text-info">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-info">
                                    <i class="fas fa-barcode "></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>


                        <x-adminlte-input name="descripcion" label="Descripción"
                            placeholder="Ingresar descripción del producto" fgroup-class="col-md-7" />
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
                                <option value="10" >10 %</option>
                                <option value="5" >5 %</option>
                                <option value="0" >0 %</option>
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
                        </x-adminlte-textarea>

                        <x-adminlte-select2 name="id_categoria" id="id_categoria" label="Categoria"
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
                                <option value={{ $item->id }}>{{ $item->descripcion }}</option>
                            @endforeach
                        </x-adminlte-select2>
                        
                    </div>

                    <div class="row">
                        <x-adminlte-input name="stock" type="number" label="Stock" fgroup-class="col-md-2"
                            step="any" min="0" value="0.000" />

                        <x-adminlte-select name="id_medida" label="Unidad de Medida"
                            data-placeholder="Seleccionar una medida..." fgroup-class="col-md-3">
                            @foreach ($medida as $item)
                                <option value={{ $item->id }}>{{ $item->descripcion }}</option>
                            @endforeach
                        </x-adminlte-select>





                        <x-adminlte-input name="pcosto" id="pcosto" type="number" label="Precio Costo" fgroup-class="col-md-2"
                            value="0" min="0" oninput="calcularPorcentajeAumento()"/>


                        <x-adminlte-input name="porcentaje" id="porcentaje" type="number" label="% Margen" fgroup-class="col-md-1"
                            value="0" min="0" max="100" step="any" oninput="calcularPrecioVenta()" label-class="text-success" />
                        <x-adminlte-input name="pventa" id="pventa" type="number" label="Precio Venta" fgroup-class="col-md-2"
                            value="0" min="0" oninput="calcularPorcentajeAumento()" />

                        {{-- Label, and prepend icon --}}
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
                        <div class="form-group col-md-12">
                            <a class="btn btn-danger mx-1" style="float: right;"
                                href="{{ route('producto.index') }}">Cancelar</a>
                            <x-adminlte-button class="btn-group mx-1" style="float: right;" type="submit"
                                label="Registrar" theme="primary" icon="fas fa-lg fa-save" />
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
                    <button type="button" class="btn btn-primary" onclick="guardarCategoria('{{ route('guardar-categoria') }}','formCrearCategoria',true,'id_categoria','borrar-categoria')">Guardar</button>
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
{{-- Example button to open modal --}}
@stop
@push('js')
<script>
    function calcularPorcentajeAumento() {
        // Obtén los valores de precio de costo y precio de venta
        var pcosto = parseFloat(document.getElementById('pcosto').value);
        var pventa = parseFloat(document.getElementById('pventa').value);

        // Calcula el porcentaje de aumento si ambos valores son mayores a 0
        if (pcosto > 0 && pventa > 0) {
            var porcentajeAumento = ((pventa - pcosto) / pcosto) * 100;

            // Muestra el resultado en el campo de porcentaje
            document.getElementById('porcentaje').value = porcentajeAumento.toFixed(2);
        } else {
            // Si uno de los valores es 0, establece el porcentaje en 0
            document.getElementById('porcentaje').value = 0;
        }
    }
    function calcularPrecioVenta() {
        // Obtén los valores de precio de costo y porcentaje
        var pcosto = parseFloat(document.getElementById('pcosto').value);
        var porcentaje = parseFloat(document.getElementById('porcentaje').value);

        // Verifica si el porcentaje ingresado es mayor a 0
        if (porcentaje > 0) {
            // Calcula el precio de venta
            var pventa = pcosto * (1 + porcentaje / 100);
            
            // Muestra el resultado en el campo de precio de venta
            document.getElementById('pventa').value = pventa.toFixed(2);
        } else {
            // Si el porcentaje es 0 o negativo, establece el precio de venta en 0
            document.getElementById('pventa').value = 0;
        }
    }
    // Obtén una referencia a la tabla DataTable

    var dataTable;

    $(document).ready(function() {
        if (!$.fn.DataTable.isDataTable('#table1')) {
            // DataTable no se ha inicializado en #table1, así que lo inicializamos
            dataTable = $('#table1').DataTable({
                // Configuración de DataTables
            });
        } else {
            // DataTable ya se ha inicializado en #table1, por lo que simplemente obtenemos la instancia existente
            dataTable = $('#table1').DataTable();
        }
    });

    function guardarCategoria(guardarUrl,nombreFormulario,addSelect2,nombreselect2,borrar) {
        $.ajax({
            type: "POST",
            url: guardarUrl,
            data: $("#"+ nombreFormulario).serialize(),
            dataType: 'json',
            success: function(response) {
                var id = response.id;
                var descripcion= response.descripcion;

                var data=response.data;
            
                
                if(addSelect2){
                    agregarSelect2(id,descripcion,nombreselect2);
                }
                // Mostrar mensaje de éxito o hacer algo con la respuesta
                Swal.fire(
                    'guardado exitoso!',
                    'Presione el boton!',
                    'success'
                )
                url="{{ url('/') }}/"+borrar+"/" + id;
                // Crear el botón de eliminación como una cadena HTML
                var nuevaFila ='<button type="button" class="btn btn-sm btn-outline-secondary" id="delete-button" data-url="'+url+'" onclick="borrar(this,\'' + nombreselect2 + '\',true)">'+
                    '<ion-icon name="trash-outline"><i class="fa fa-sm fa-fw fa-trash"></i></ion-icon> </button>';
                // 'filaDatos' con los datos de la fila que deseas agregar
                var filaDatos = [data,nuevaFila];
               

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

    function borrar(borrarcategoria,select2,utilizaselect2) {
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
                var borrarCategoriaUrl =borrarcategoria.getAttribute('data-url');//"{{ url('/borrar-categoria') }}/" + id; // Generar la URL con el id
                
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
                            id=response.id;
                            $(".table-row[data-id='" + id + "']").remove();
                            dataTable.row('.table-row[data-id=' + id + ']').remove().draw(
                            false);
                            if(utilizaselect2)
                                borrarSelect2(id,select2);

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

    function borrarSelect2(valueToDelete,nombreselect) {
        
        // Establece el nuevo valor en el select2
        $('#'+nombreselect+' option[value="' + valueToDelete + '"]').remove();
        $('#' + nombreselect).trigger('change.select2');
    }

    function agregarSelect2(id,descripcion,nombreselect2) {

        // Crea un nuevo elemento <option>
        var nuevaOpcion = new Option(descripcion,id, true, true);
        // Agrega la nueva opción al elemento select
        $('#' + nombreselect2).append(nuevaOpcion);

        // Luego, puedes actualizar el select para que se reflejen los cambios
        $('#' + nombreselect2).trigger('change.select2');
    }
</script>
@endpush
