@extends('adminlte::page')



@section('content_header')
    <h1 class="m-0 custom-heading">Registrar Venta</h1>
@stop
@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/jquery-ui-1.13.2/jquery-ui.min.css') }}">
@endsection

@section('content')


    <form action="{{ route('venta.store') }}" method="post" autocomplete="off" onkeypress="return event.keyCode != 13;">
        @csrf
        @method('POST')

        <!--Modal-->
        <div class="modal fade" id="modalPagare">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Guardar las fechas de los pagos</h4>
                    </div>
                    <div class="modal-body d-flex justify-content-center align-items-center">
                        <table id="tblpagare" class="table table-striped table-bordered table-condensed table-hover">
                            <thead>
                                <th>Cuota</th>
                                <th>Fecha Pago</th>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Cuota 1</td>
                                    <td>Fecha de pago 1</td>
                                </tr>
                                <!-- Aquí puedes agregar más filas según sea necesario -->
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" type="button" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>


        <!-- fin Modal-->
        <!--Modal-->
        <x-adminlte-modal id="pagomontoModal" title="Ingresar Monto a Pagar" theme="light" size="lg"
            data-backdrop="static">
            <div>
                <div class="row">
                    <div class="col-sm-12 text-center">
                        <table id = "tablaModalFech" class="table table-hover table-bordered">
                            <thead align="center">
                                <tr class="fondo">
                                    <th class="txtWhite"><b>Descripcion</b></th>
                                    <th class="txtWhite"><b>Monto</b></th>
                                </tr>
                            </thead>
                            <tbody id="tablaModBody">
                            </tbody>
                        </table>
                    </div>
                    <div class="col-12 text-center">
                        <h2>
                            Cargar el monto a abonar
                        </h2>
                    </div>
                    <h2><label>Monto a Abonar Gs. </label>
                        <input type="hidden" name="idfac" id="idfac" value="" placeholder="Numero">
                        <input type="number" oninput="verifMonto()" name="montoAbonar" id="montoAbonar"
                            class="btn-success">
                    </h2>
                    <input type="hidden" name="montoAbonar1" id="montoAbonar1" class="form-control" value=""
                        placeholder="Numero">

                    <h2><label>Descuento </label>
                        <input type="number" oninput="verifMonto()" name="descuent" value="0" id="descuent"
                            class="btn-success">
                    </h2>
                    <h1>
                        <label>Diferencia Gs.</label>
                        <label id="diferenciaAbonar" class="btn-danger">Diferencia</label>

                    </h1>
                    <h3><label>Efectivo Gs.</label>
                        <input type="number" oninput="verifVuelto()" name="descUs" id="descUs" class="btn-warning">
                    </h3>
                    <h3>
                        <label>Vuelto Gs.</label>
                        <label id="vuelto" class="btn-info">0</label>

                    </h3>

                </div>
                <button type="button" class=" btn btn-primary " id="guardarMedida" onclick="pagar1()">
                    Guardar</button>
            </div>


        </x-adminlte-modal>
        <!--fin modal-->
        <div class="card">
            <div class="card-body">

                <div class="row">
                    {{-- With Label --}}
                    @php
                        $config1 = ['format' => 'DD-MM-YYYY'];
                    @endphp
                    <div class="form-group">
                        <label for="fechaemision">FECHA DE EMISIÓN (alt+shift+f)</label>
                        <input type="date" class="form-control" id="fechaemision" name="fechaemision"
                            value="{{ date('Y-m-d') }}" required>
                    </div>


                    <x-adminlte-input type="text" id="nrofactura" name="nrofactura" label="Factura Nº"
                        fgroup-class="col-md-2" value="0" required />
                    <x-adminlte-input type="text" id="timbrado" name="timbrado" label="Timbrado Nº"
                        fgroup-class="col-md-2" value="0" required />
                    <div class="card" style="width: 14rem;margin-top: -18px">
                        <div class="card-body">
                            <label for="">CONDICIÓN DE COMPRA</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="condicion" id="inlineRadio1"
                                    value="CONTADO" onchange="ocultarOpc()" checked>
                                <label class="form-check-label" for="inlineRadio1">Contado</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="condicion" id="inlineRadio2"
                                    value="CREDITO" onchange="mostrarOpc()">
                                <label class="form-check-label" for="inlineRadio2">Crédito</label>
                            </div>
                        </div>

                    </div>
                    <x-adminlte-input type="number" id="cantpago" name="cantpago" fgroup-class="col-md-1"
                        value="1" required min="1" style="display: none;" />
                    <a data-toggle="modal" id="fechasButton" href="#modalPagare" style="display: none;"><button
                            class="btn btn-success" onclick="cargarPag()"><i class="fa fa-plus-circle"></i>Fechas
                            Pagos </button></a>
                    <x-adminlte-input type="hidden" id="proveedor_id" name="proveedor_id" />


                    <div class="row">
                        <x-adminlte-card title="Clientes" class="text-primary">

                            <div class="row">
                                <x-adminlte-input type="number" id="cod_proveedor" name="cod_proveedor"
                                    onchange="cambiarCod()" placeholder="Codigo" label="COD." fgroup-class="col-md-1"
                                    required />
                                <x-adminlte-select2 name="id_proveedor" id="id_proveedor" label="NOMBRE/RAZON SOCIAL"
                                    data-placeholder="Seleccionar un proveedor..." fgroup-class="col-md-7"
                                    onchange="actualizarNumeroDocumento()">
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text bg-gradient-primary">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    </x-slot>
                                    @foreach ($clientes as $item)
                                        <option value={{ $item->id }} data-ruc="{{ $item->ruc }}">
                                            {{ $item->razonsocial }}</option>
                                    @endforeach
                                </x-adminlte-select2>
                                <x-adminlte-input type="text" id="numero_documento" name="numero_documento"
                                    placeholder="DOCUMENTO" label="NUMERO DOC." readonly fgroup-class="col-md-2" />
                                <!-- Botón para cerrar el modal -->
                                <!-- Contenedor de la vista de la cámara -->
                                <video poster="{{ asset('vendor/adminlte/dist/img/lector_QR.jpg') }}"
                                    id="preview"></video>

                            </div>
                        </x-adminlte-card>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <div id="items">
                            <div class="item" style="background-color: #343A40;">
                                <div class="row ml-2">
                                    <label for="" class="col-1" style="color: white;">ITEM</label>
                                    <label for="" class="col-1" style="color: white;">UNDM</label>
                                    <label for="" class="col-1" style="color: white;">CÓDIGO
                                        (alt+shift+c)</label>
                                    <label for="" class="col-3" style="color: white;">DESCRIPCION</label>
                                    <label for="" class="col-1" style="color: white;">CANTIDAD</label>
                                    <label for="" class="col-1" style="color: white;">PRECIO
                                        UNITARIO</label>
                                    <label for="" class="col-1" style="color: white;">EXENTAS</label>
                                    <label for="" class="col-1" style="color: white;">5%</label>
                                    <label for="" class="col-1" style="color: white;">10%</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <button onclick="addNewItem()" class="btn btn-primary mt-2" type="button">Agregar
                            Ítem</button>
                    </div>

                </div>

                <!-- Agrega este elemento para mostrar la suma total -->
                <div class="row">
                    <div class="col-12 ">
                        <div>Suma Total: <span id="total-sum">0</span></div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-12">
                        <a class="btn btn-danger mx-1" style="float: right;"
                            href="{{ route('compra.index') }}">Cancelar</a>
                        <x-adminlte-button class="btn-group" style="float: right;" type="submit" label="Registrar"
                            theme="primary" icon="fas fa-lg fa-save" />
                    </div>
                </div>

            </div>
        </div>


    </form>




@stop

@push('js')
    <script src="{{ asset('jsQR.js') }}"></script>
    <style>
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
    <script src="{{ asset('vendor/jquery-ui-1.13.2/jquery-ui.min.js') }}"></script>
    @if (session('success') && session('ultimoId') && session('estadov'))
        <script>
            var compraId = {{ session('ultimoId') }};
            //intervalID;
            var url = '/controlventa/public/venta/' + compraId + '/detalles';
            
            // Realiza una petición AJAX para obtener los detalles de la compra
            $.ajax({
                url: url,
                method: 'GET',
                success: function(response) {
                    var detalle = response.detalles;

                    // Acceder a la suma de los montos
                    var sumaMontos = response.sumaMontos;
                    var detalleHTML = '';
                    var total = 0;
                    // Construye el HTML de los detalles de la compra utilizando los datos obtenidos 
                    detalle.forEach(function(detalle) {
                        item = 1;
                        monto = parseFloat(detalle.monto) - parseFloat(sumaMontos);
                        detalleHTML += '<tr><td>' + detalle.producto.descripcion +
                            '</td><td>' + monto + ' </td></tr>';
                        total = total + monto;
                    });
                    // Asignar un valor al input
                    document.getElementById('montoAbonar').value = total;
                    document.getElementById('montoAbonar1').value = total;
                    document.getElementById('idfac').value = compraId;
                    // Llena el contenido del modal con los detalles
                    $('#tablaModBody').html(detalleHTML);

                    // Muestra el modal
                    $('#pagomontoModal').modal('show');
                },
                error: function() {
                    console.log('Error al obtener detalles de la compra');
                }
            });
        </script>
    @endif
    <script>
        const videoElement = document.getElementById('preview');

        function mostrarOpc() {
            document.getElementById("cantpago").style.display = "block";

            // Mostrar el botón
            document.getElementById("fechasButton").style.display = "inline";
        }

        function ocultarOpc() {
            document.getElementById("cantpago").style.display = "none";

            // Ocultar el botón
            document.getElementById("fechasButton").style.display = "none";
        }

        function datosA(num, fecha) {
            this.num = num;
            this.fecha = fecha;
        }

        function cargarPag() {
            var n = new Date();
            var y = n.getFullYear();
            var m = n.getMonth() + 1;
            var d = n.getDate();
            if (m < 10) m = '0' + m;
            if (d < 10) d = '0' + d;
            var fech = y + "-" + m + "-" + d;
            var cantp = document.getElementById("cantpago").value;
            var contAux = 1;
            var valor = [];

            // Agregar la primera fecha actual
            var fecha = '<tr class="filas" id="fila0">' +
                '<td><input type="date" onchange="cambiarsiguientesfechas(0)" name="fechP[]" value="' + fech + '"></td>' +
                '</tr>';
            var book = new datosA(contAux, fecha);
            valor.push(book);
            contAux++;

            // Agregar las siguientes fechas
            for (let index = 1; index < cantp; index++) {
                // Incrementar el mes
                m++;
                if (m > 12) {
                    m = 1;
                    y++;
                }
                // Formatear el mes y año
                var mm = (m < 10) ? '0' + m : m;
                var yy = y;
                // Crear la fecha
                var nextFech = yy + "-" + mm + "-" + d;
                fecha = '<tr class="filas" id="fila' + index + '">' +
                    '<td><input type="date" onchange="cambiarsiguientesfechas(' + index + ')" name="fechP[]"  id="fechP' +
                    index + '" value="' +
                    nextFech +
                    '"></td>' +
                    '</tr>';
                book = new datosA(contAux, fecha);
                valor.push(book);
                contAux++;
            }

            $('#tblpagare').DataTable({
                paging: false,
                searching: false,
                info: false,
                data: valor,
                "bDestroy": true,
                columns: [{
                        title: "Pago",
                        data: "num"
                    },
                    {
                        title: "Fecha",
                        data: "fecha"
                    }
                ]
            });



        }

        function cambiarsiguientesfechas(fila) {
            var cantp = document.getElementById("cantpago").value;
            fechMod = document.getElementsByName("fechP[]");
            fechas = fechMod[fila].value;
            for (var i = fila; i < cantp; i++) {



                var fechaActual = new Date(fechas);

                // Añadir un mes a la fecha
                fechaActual.setMonth(fechaActual.getMonth() + 1);

                // Verificar si se pasó de diciembre y ajustar el año
                if (fechaActual.getMonth() === 0) {
                    fechaActual.setFullYear(fechaActual.getFullYear() + 1);
                }

                // Formatear la nueva fecha en el formato YYYY-MM-DD
                var nuevaFecha = fechaActual.toISOString().slice(0, 10);

                // Asignar la nueva fecha al input
                iAux = i + 1;
                if (iAux < cantp) {
                    fechMod[iAux].value = nuevaFecha;
                    fechas = nuevaFecha;
                }

            }


        }
        $(document).ready(function() {
            cargarPag();
        });

        $('input[name="fechaemision"]').on('keydown', function(e) {
            // Verifica si la tecla presionada es "Enter"
            if (e.key === 'Enter') {
                e.preventDefault();
                // Enfoca en el campo de fecha
                $('input[name="nrofactura"]').focus();
            }
        });
        $('input[name="nrofactura"]').on('keydown', function(e) {
            // Verifica si la tecla presionada es "Enter"
            if (e.key === 'Enter') {
                e.preventDefault();
                // Enfoca en el campo de fecha
                $('input[name="timbrado"]').focus();
            }
        });
        $('input[name="timbrado"]').on('keydown', function(e) {
            // Verifica si la tecla presionada es "Enter"
            if (e.key === 'Enter') {
                e.preventDefault();
                // Enfoca en el campo de fecha
                $('input[name="cod_proveedor"]').focus();
            }
        });
        $('input[name="cod_proveedor"]').on('keydown', function(e) {
            // Verifica si la tecla presionada es "Enter"
            if (e.key === 'Enter') {
                e.preventDefault();
                // Enfoca en el campo de fecha
                $('input[name="codigo1[]"]').focus();
            }
        });
        /*** Juego de teclas **/
        document.addEventListener('keydown', function(event) {
            if (event.altKey && event.shiftKey && event.key === 'C') {
                $('input[name="codigo1[]"]').focus();
            }
            if (event.altKey && event.shiftKey && event.key === 'F') {
                $('#fechaemision').focus();
            }

        });

        cambiarCod();

        function sanitizeInput(input) {
            // Obtén el valor actual del campo de entrada
            let value = input.value;

            // Elimina cualquier carácter que no sea un número o un punto decimal
            value = value.replace(/[^0-9.]/g, '');

            // Reemplaza comas por puntos para números decimales
            value = value.replace(/,/g, '.');

            // Actualiza el valor del campo de entrada
            input.value = value;
            actualizarSumaTotal();
        }

        function cambiarCod() {
            // Obtener el valor del campo "cod_proveedor"
            var nuevoCod = $('input[name="cod_proveedor"]').val();

            // Buscar la opción en el select con id "id_proveedor" que tenga el nuevo código
            var select2 = document.getElementById("id_proveedor");
            var options = select2.options;

            for (var i = 0; i < options.length; i++) {
                var option = options[i];
                var dataCod = option.value; // Suponiendo que el valor de la opción es el código a buscar

                // Verificar si encontramos una coincidencia
                if (dataCod === nuevoCod) {
                    // Cambiar el valor seleccionado en el select
                    select2.value = option.value;
                    $('#id_proveedor').val(dataCod).trigger('change.select2');
                    // Llamar a la función actualizarNumeroDocumento
                    actualizarNumeroDocumento();
                    return; // Salir de la función después de encontrar una coincidencia
                }
            }

            // Si no se encontró una coincidencia, llamar a la función actualizarNumeroDocumento
            actualizarNumeroDocumento();
        }

        function actualizarNumeroDocumento() {
            var select2 = document.getElementById("id_proveedor");
            var numeroDocumentoInput = document.getElementById("numero_documento");
            var selectedOption = select2.options[select2.selectedIndex];
            var numeroDocumento = selectedOption.getAttribute("data-ruc");
            numeroDocumentoInput.value = numeroDocumento;
            var cod_proveedor = selectedOption.value;
            document.getElementById("cod_proveedor").value = cod_proveedor;
        }
        $('#search').autocomplete({
            minlength: 3,
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('obtenerproveedor') }}",
                    contentType: "application/json",
                    dataType: "json",
                    data: {
                        term: request.term
                    },
                    success: function(data) {
                        var filteredData = Object.keys(data).map(function(key) {
                            return {
                                label: data[key].razonsocial, // Atributo que deseas mostrar
                                value: data[key].razonsocial, // Valor seleccionado
                                ruc: data[key].ruc,
                                celular: data[key].celular,

                                id: data[key].id,
                            };
                        });
                        response(filteredData);
                    },
                    response: function(event, ui) {
                        if (!ui.content.length) {
                            $('#ruc').val('');
                            console.log('hola');
                        }
                    }
                });
            },
            select: function(event, ui) {
                $('#ruc').val(ui.item.ruc);
                $('#proveedor_id').val(ui.item.id);

            }
        });
        
        $(document).on('focus', '.autocomplete-producto', function() {
            $(this).autocomplete({
                minlength: 0, // Cambiamos a 0 para que se dispare el autocompletado sin escribir
                source: function(request, response) {
                    $.ajax({
                        url: "{{ route('obtenerproducto') }}",
                        contentType: "application/json",
                        dataType: "json",
                        data: {
                            term: request.term
                        },
                        success: function(data) {
                            var filteredData = Object.keys(data).map(function(key) {
                                return {
                                    label: data[key].descripcion + '(' + data[key]
                                        .stock + ')',
                                    value: data[key].descripcion,
                                    codigo: data[key].codigo,
                                    id: data[key].id,
                                };
                            });
                            response(filteredData);
                        }
                    });
                },
                select: function(event, ui) {
                    // Aquí puedes manejar lo que sucede cuando se selecciona un elemento
                    traerCargarDatosProducto(ui.item.codigo, this);
                    $('input[name="cantidad[]"]').focus(); // Movemos el foco al campo de cantidad
                },
                autoFocus: true, // Activamos el enfoque automático para facilitar la navegación con teclado
            }).keydown(function(event) {
                // Capturamos el evento keydown para verificar si se presionó Enter
                if (event.keyCode === 13 && !$(this).val()) {
                    // Si se presionó Enter y el campo está vacío
                    $('input[name="cantidad[]"]').focus(); // Movemos el foco al campo de cantidad
                }
            });
        });



        // Script para agregar y eliminar dinámicamente ítems de compra
        const itemsContainer = document.getElementById('items');
        const totalSumElement = document.getElementById('total-sum');
        let totalSum = 0;
        inputgeneral='';
        addNewItem();
        // Función para agregar un nuevo ítem de compra
        function addNewItem() {


            const newItem = document.createElement("div");
            newItem.classList.add("item");
            borrar =
                '<button class="btn-remove btn btn-outline-danger ml-2" type="button"><i class="fa fa-trash" aria-hidden="true"></i></button>';
            exent =
                '<input type="text" name="exenta[]" value="0" class="form-control col-1" placeholder="Exenta" required oninput="sanitizeInput(this)">';
            cinc =
                '<input type="text" name="cinco[]" value="0" class="form-control col-1" placeholder="iva 5%" required oninput="sanitizeInput(this)">';
            die =
                '<input type="text" name="diez[]" value="0" class="form-control col-1" placeholder="iva 10%" required oninput="sanitizeInput(this)">';


            newItem.innerHTML = `
                <div class="row ml-1">
                                    <input type="number" name="item[]" class="codigo_id form-control col-1"
                                    placeholder="Código" value="1" required readonly>
                                    <input type="text" name="unidad[]" value="UNIDAD" class="codigo_id form-control col-1"
                                    placeholder="U. medida" value="" required >
                                    <input type="hidden" name="pmayorista[]"  class="codigo_id">
                                    <input type="hidden" name="cmayorista[]"  class="codigo_id">
                                    <input type="hidden" name="condicionv[]"  class="codigo_id">
                                    <input type="hidden" name="precioorig[]"  class="codigo_id">
                                    <input type="hidden" name="iva[]" class="codigo_id form-control col-2"
                                    placeholder="Código" value="" required readonly>
                                    <input type="hidden" name="codigo[]" class="codigo_id form-control col-1" required>
                                    <input type="text" name="codigo1[]" onfocusin="leerQR(this)" onblur="closeModal()" class="codigo_id form-control col-1"
                                    placeholder="Código" onchange="cambiarDescripcion(this)" value="" required>                                    
                                    <input type="text" name="descripcion[]" class="autocomplete-producto form-control col-3 "
                                        placeholder="Descripcion" value="" required style="font-size: 12px;>
                                    <input type="hidden" name="productoid[]" class="producto_id form-control col-2"
                                        required>
                                    <input type="text" name="cantidad[]" step="any" class="form-control col-1"
                                    placeholder="Cantidad"  value =""  required oninput="sanitizeInput(this)" >
                                    <input type="text" name="precio[]" value="" class="form-control col-1" placeholder="Precio "
                                        required oninput="sanitizeInput(this)">
                                        ` + exent + cinc + die + `<button class="btn-remove btn btn-outline-danger ml-2" type="button"><i class="fa fa-trash" aria-hidden="true"></i></button>
                                        
                                </div>
            `;


            const codigoInput = newItem.querySelector('input[name="codigo1[]"]');
            codigoInput.addEventListener('keydown', function(e) {
                // Verifica si la tecla presionada es "Enter"
                if (e.key === 'Enter') {
                    // Evita el envío del formulario
                    e.preventDefault();
                    // Enfoca en el campo de producto
                    const descripcionInput = newItem.querySelector('input[name="descripcion[]"]');
                    descripcionInput.focus();

                }
            });
            // const cantidadInput = newItem.querySelector('input[name="descripcion[]"]');
            // cantidadInput.addEventListener('keydown', function(e) {
            //     // Verifica si la tecla presionada es "Enter"
            //     if (e.key === 'Enter') {
            //         // Evita el envío del formulario
            //         e.preventDefault();
            //         // Enfoca en el campo de producto
            //         const cantidadInput = newItem.querySelector('input[name="cantidad[]"]');
            //         cantidadInput.focus();
            //     }
            // });
            const descripcionInput = newItem.querySelector('input[name="cantidad[]"]');
            descripcionInput.addEventListener('keydown', function(e) {
                // Verifica si la tecla presionada es "Enter"
                if (e.key === 'Enter') {
                    // Evita el envío del formulario
                    e.preventDefault();
                    // Enfoca en el campo de producto
                    const precioInput = newItem.querySelector('input[name="precio[]"]');
                    precioInput.focus();
                    precioInput.select();
                }
            });
            const precioInput = newItem.querySelector('input[name="precio[]"]');
            precioInput.addEventListener('keydown', function(e) {
                // Verifica si la tecla presionada es "Enter"
                if (e.key === 'Enter') {
                    // Evita el envío del formulario
                    e.preventDefault();
                    // Encuentra todos los inputs siguientes después del precioInput
                    const inputs = Array.from(newItem.querySelectorAll(
                        'input[name="precio[]"], input[name="exenta[]"], input[name="cinco[]"], input[name="diez[]"]'
                    ));
                    const currentIndex = inputs.indexOf(precioInput);
                    let nextIndex = currentIndex + 1;
                    // Encuentra el siguiente input que no esté deshabilitado
                    while (nextIndex < inputs.length && inputs[nextIndex].disabled) {
                        nextIndex++;
                    }
                    // Si se encontró un input habilitado, enfoca en él y selecciona su contenido
                    if (nextIndex < inputs.length) {
                        const nextInput = inputs[nextIndex];
                        nextInput.focus();
                        nextInput.select();
                    }
                }
            });


            const exentaInput = newItem.querySelector('input[name="exenta[]"]');
            exentaInput.addEventListener('keydown', function(e) {
                // Verifica si la tecla presionada es "Enter"
                if (e.key === 'Enter') {
                    addNewItem();
                }
            });
            const cincoInput = newItem.querySelector('input[name="cinco[]"]');
            cincoInput.addEventListener('keydown', function(e) {
                // Verifica si la tecla presionada es "Enter"
                if (e.key === 'Enter') {
                    addNewItem();
                }
            });
            const diezInput = newItem.querySelector('input[name="diez[]"]');
            diezInput.addEventListener('keydown', function(e) {
                // Verifica si la tecla presionada es "Enter"
                if (e.key === 'Enter') {
                    addNewItem();

                }
            });
            itemsContainer.appendChild(newItem);

            // Agregar el nuevo elemento al contenedor
            itemsContainer.appendChild(newItem);

            // Agregar el evento click para eliminar el ítem después de que se haya agregado al contenedor
            // btnAddItem.addEventListener("click", addNewItem);
            const btnRemove = newItem.querySelector(".btn-remove");
            btnRemove.addEventListener("click", function() {
                removeItem(newItem);
            });
            const priceInput = newItem.querySelector('input[name="precio[]"]');
            priceInput.addEventListener("input", actualizarSumaTotal);

            const cantiInput = newItem.querySelector('input[name="cantidad[]"]');
            cantiInput.addEventListener("input", actualizarSumaTotal);
            actualizarSumaTotal();
            $('input[name="codigo1[]"]').focus();
            inputgeneral = $('input[name="codigo1[]"]');

        }

        let stream;
        
        function leerQR(input) {
            inputgeneral=input;
            @if ($configuracionQR && $configuracionQR->estado == 1)
                navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: "environment"
                        }
                    })
                    .then(function(stream) {
                        
                        videoElement.srcObject = stream;
                        videoElement.play();

                        const canvasElement = document.createElement('canvas');
                        const canvasContext = canvasElement.getContext('2d');
                        const frameRate = 1000 / 2; // 2 fps

                        setInterval(function() {
                            traerCargadoTemporal();
                            canvasElement.width = videoElement.videoWidth;
                            canvasElement.height = videoElement.videoHeight;
                            canvasContext.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement
                                .height);
                            const imageData = canvasContext.getImageData(0, 0, canvasElement.width,
                                canvasElement.height);
                            const code = jsQR(imageData.data, imageData.width, imageData.height);
                            if (code) {
                                url = code.data.toString();
                                const sextoSlashIndex = url.indexOf("/", // Primera "/" después de
                                    url.indexOf("/", // Quinta "/" después de
                                        url.indexOf("/", // Cuarta "/" después de
                                            url.indexOf("/", // Tercera "/" después de
                                                url.indexOf("/", // Segunda "/" después de
                                                    url.indexOf("/") + 1 // Primera "/" en la cadena
                                                ) + 1) + 1) + 1) + 1) + 1;

                                // Extrae la parte del string a partir del sexto "/"
                                const resultado = url.substring(sextoSlashIndex);
                                input.value = resultado;
                                cambiarDescripcion(input);


                            }
                        }, frameRate);
                    })
                    .catch(function(error) {
                        console.error('Error al acceder a la cámara:', error);
                    });
            @else
                frameRate = 1000 / 2;
                intervalID = setInterval(traerCargadoTemporal, frameRate);
            @endif

        }
        function traerCargadoTemporal() {
            inputCodigo=inputgeneral;
            const condicion = document.querySelector('input[name="condicion"]:checked').value;
            let cantpago = 1;
            if (condicion === 'CREDITO') {
                cantpago = document.getElementById('cantpago').value;
            }
            $.ajax({
                url: '{{ route('obtenercodtemporal') }}',
                method: 'POST',
                data: {
                    cantpago: cantpago,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.producto && response.producto.id && response.producto.descripcion) {
                        var producto = response.producto;
                        var descripcion = producto.descripcion;
                        var id = producto.id;
                        var iva = producto.impuesto;
                        var unidadMedida = producto.unidaddemedida.descripcion;
                        var precio = producto.pventa;
                        var pmayorista = producto.pmayorista;
                        var cmayorista = producto.cmayorista;
                        var stock = Math.trunc(producto.stock);
                        var configuracionv = response.configuracion.estado;
                        var codigoValue = producto.codigo;
                        $(inputCodigo).closest('.row').find('input[name="descripcion[]"]').val(descripcion +
                            '(' + stock + ')');
                        $(inputCodigo).closest('.row').find('input[name="codigo[]"]').val(id);
                        $(inputCodigo).closest('.row').find('input[name="unidad[]"]').val(unidadMedida);
                        $(inputCodigo).closest('.row').find('input[name="iva[]"]').val(iva);
                        $(inputCodigo).closest('.row').find('input[name="precio[]"]').val(precio);
                        $(inputCodigo).closest('.row').find('input[name="codigo1[]"]').val(codigoValue);
                        $(inputCodigo).closest('.row').find('input[name="pmayorista[]"]').val(pmayorista);
                        $(inputCodigo).closest('.row').find('input[name="cmayorista[]"]').val(cmayorista);
                        $(inputCodigo).closest('.row').find('input[name="configuracionv[]"]').val(
                            configuracionv);
                        $(inputCodigo).closest('.row').find('input[name="precioorig[]"]').val(
                            precio);
                        switch (iva) {
                            case 10:
                                $(inputCodigo).closest('.row').find('input[name="cinco[]"]').val(0);
                                $(inputCodigo).closest('.row').find('input[name="exenta[]"]').val(0);
                                $(inputCodigo).closest('.row').find('input[name="cinco[]"]').prop('disabled',
                                    true);
                                $(inputCodigo).closest('.row').find('input[name="exenta[]"]').prop('disabled',
                                    true);
                                break;
                            case 5:
                                $(inputCodigo).closest('.row').find('input[name="diez[]"]').val(0);
                                $(inputCodigo).closest('.row').find('input[name="exenta[]"]').val(0);
                                $(inputCodigo).closest('.row').find('input[name="diez[]"]').prop('disabled',
                                    true);
                                $(inputCodigo).closest('.row').find('input[name="exenta[]"]').prop('disabled',
                                    true);
                                break;
                            case 0:
                                $(inputCodigo).closest('.row').find('input[name="diez[]"]').val(0);
                                $(inputCodigo).closest('.row').find('input[name="cinco[]"]').val(0);
                                $(inputCodigo).closest('.row').find('input[name="diez[]"]').prop('disabled',
                                    true);
                                $(inputCodigo).closest('.row').find('input[name="cinco[]"]').prop('disabled',
                                    true);
                                break;
                            default:
                                // Código a ejecutar si la variable no coincide con ninguno de los casos anteriores
                        }
                        addNewItem();
                    } else {
                        // Maneja el caso cuando el producto no se encuentra
                        //console.error('Producto no encontrado');
                    }
                },
                error: function(error) {
                    console.error('Error en la petición AJAX:', error);
                }
            });
            actualizarSumaTotal();
        }
        function closeModal() {
            const videoElement = document.getElementById('preview');
            const stream = videoElement.srcObject; // Obtén el stream de la cámara

            // Detén el stream de video si existe
            if (stream) {
                const tracks = stream.getTracks(); // Obtén todas las pistas del stream
                tracks.forEach(track => track.stop()); // Detén cada pista
            }
            videoElement.poster = "{{ asset('vendor/adminlte/dist/img/lector_QR.jpg') }}";
            videoElement.pause();
            videoElement.play();
            clearInterval(intervalID);
        }

        function actualizarSumaTotal() {
            totalSum = 0;
            const priceInputs = document.getElementsByName("precio[]"); //trae todos los precios para recorrer
            const cantidadInputs = document.getElementsByName(
                'cantidad[]'); //trae todas las cantidades para recorrer
            const ivaInputs = document.getElementsByName(
                "iva[]"); //trae todos los impuestos para ver si es exenta iva 5 o iva 10
            const exentaInputs = document.getElementsByName(
                "exenta[]"); //trae todos los totales exentas
            const itemInputs = document.getElementsByName(
                "item[]"); //trae todos los totales exentas
            const cincoInputs = document.getElementsByName(
                "cinco[]"); //trae todos los totales cinco
            const diezInputs = document.getElementsByName(
                "diez[]"); //trae todos los totales diez
            const cmayoristaInputs = document.getElementsByName(
                'cmayorista[]');
            const pmayoristaInputs = document.getElementsByName(
                'pmayorista[]');
            const condicionvInputs = document.getElementsByName(
                'condicionv[]');

            const precioorigInputs = document.getElementsByName(
                'precioorig[]');
            // Itera a través de los elementos utilizando un bucle for
            itemN = 0;
            for (let i = 0; i < priceInputs.length; i++) {
                const input = priceInputs[i];
                price = parseFloat(input.value) || 0;

                const cantidadV = cantidadInputs[i];
                const cantidad = parseFloat(cantidadV.value) || 0;

                const cmayoristaV = cmayoristaInputs[i];
                const cmayorista = parseFloat(cmayoristaV.value) || 0;

                const pmayoristaV = pmayoristaInputs[i];
                const pmayorista = parseFloat(pmayoristaV.value) || 0;

                const condicionvV = condicionvInputs[i];
                const condicionv = parseFloat(condicionvV.value) || 0;
                if (cmayorista > 0 && cmayorista <= cantidad) {
                    preciounitmay = pmayorista / cmayorista;
                    if (condicionv == 0) {
                        price = preciounitmay;
                        input.value = preciounitmay;
                    } else {
                        const parteEntera = Math.floor(cantidad / cmayorista);
                        const diferencia = cantidad % cmayorista;
                        precioAux = price * diferencia;
                        precioAux1 = (pmayorista + precioAux) / (cmayorista + diferencia);
                        price = precioAux1;
                        input.value = precioAux1;
                    }
                } else {
                    const precioorigV = precioorigInputs[i];
                    const precioorigv = parseFloat(precioorigV.value) || 0;
                    price = precioorigv;
                    input.value = precioorigv;
                }

                const ivaV = ivaInputs[i];
                const iva = parseFloat(ivaV.value) || 0;

                itemN++;
                const itemV = itemInputs[i];
                itemV.value = itemN;

                const exentaV = exentaInputs[i];

                const cincoV = cincoInputs[i];

                const diezV = diezInputs[i];

                tot = cantidad * price;
                switch (iva) {
                    case 0:
                        exentaV.value = tot;
                        cincoV.value = 0;
                        diezV.value = 0;
                        break;
                    case 5:
                        exentaV.value = 0;
                        cincoporc = tot * 0.05;
                        tot = tot + cincoporc
                        cincoV.value = tot;
                        diezV.value = 0;
                        break;
                    case 10:
                        exentaV.value = 0;
                        cincoV.value = 0;
                        diezporc = tot * 0.1;
                        tot = tot + diezporc;
                        diezV.value = tot;
                        break;
                    default:
                        // Hacer algo si iva no coincide con ningún caso
                }


                totalSum += tot;
            }

            totalSumElement.textContent = totalSum.toFixed(2); // Mostrar la suma con dos decimales
        }



        function removeItem(itemToRemove) {
            itemsContainer.removeChild(itemToRemove);
            actualizarSumaTotal();
        }

        function cambiarDescripcion(inputCodigo) {
            var codigoValue = inputCodigo.value;
            traerCargarDatosProducto(codigoValue, inputCodigo);
        }

        function cambiarCodigo(inputProducto) {

            // Obtén el valor del producto desde el elemento actual
            var productoId = '';
            if ($(inputProducto).data('ui-autocomplete').selectedItem) {
                productoId = $(inputProducto).data('ui-autocomplete').selectedItem.codigo;
            }
            traerCargarDatosProducto(productoId, inputProducto);
            actualizarSumaTotal();

        }

        function traerCargarDatosProducto(codigoValue, inputCodigo) {
            const condicion = document.querySelector('input[name="condicion"]:checked').value;
            let cantpago = 1;
            if (condicion === 'CREDITO') {
                cantpago = document.getElementById('cantpago').value;
            }
            $.ajax({
                url: '{{ route('obtenercodproducto') }}',
                method: 'POST',
                data: {
                    codigo: codigoValue,
                    cantpago: cantpago,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.producto && response.producto.id && response.producto.descripcion) {
                        var producto = response.producto;
                        var descripcion = producto.descripcion;
                        var id = producto.id;
                        var iva = producto.impuesto;
                        var unidadMedida = producto.unidaddemedida.descripcion;
                        var precio = producto.pventa;
                        var pmayorista = producto.pmayorista;
                        var cmayorista = producto.cmayorista;
                        var stock = Math.trunc(producto.stock);
                        var configuracionv = response.configuracion.estado;
                        $(inputCodigo).closest('.row').find('input[name="descripcion[]"]').val(descripcion +
                            '(' + stock + ')');
                        $(inputCodigo).closest('.row').find('input[name="codigo[]"]').val(id);
                        $(inputCodigo).closest('.row').find('input[name="unidad[]"]').val(unidadMedida);
                        $(inputCodigo).closest('.row').find('input[name="iva[]"]').val(iva);
                        $(inputCodigo).closest('.row').find('input[name="precio[]"]').val(precio);
                        $(inputCodigo).closest('.row').find('input[name="codigo1[]"]').val(codigoValue);
                        $(inputCodigo).closest('.row').find('input[name="pmayorista[]"]').val(pmayorista);
                        $(inputCodigo).closest('.row').find('input[name="cmayorista[]"]').val(cmayorista);
                        $(inputCodigo).closest('.row').find('input[name="configuracionv[]"]').val(
                            configuracionv);
                        $(inputCodigo).closest('.row').find('input[name="precioorig[]"]').val(
                            precio);
                        switch (iva) {
                            case 10:
                                $(inputCodigo).closest('.row').find('input[name="cinco[]"]').val(0);
                                $(inputCodigo).closest('.row').find('input[name="exenta[]"]').val(0);
                                $(inputCodigo).closest('.row').find('input[name="cinco[]"]').prop('disabled',
                                    true);
                                $(inputCodigo).closest('.row').find('input[name="exenta[]"]').prop('disabled',
                                    true);
                                break;
                            case 5:
                                $(inputCodigo).closest('.row').find('input[name="diez[]"]').val(0);
                                $(inputCodigo).closest('.row').find('input[name="exenta[]"]').val(0);
                                $(inputCodigo).closest('.row').find('input[name="diez[]"]').prop('disabled',
                                    true);
                                $(inputCodigo).closest('.row').find('input[name="exenta[]"]').prop('disabled',
                                    true);
                                break;
                            case 0:
                                $(inputCodigo).closest('.row').find('input[name="diez[]"]').val(0);
                                $(inputCodigo).closest('.row').find('input[name="cinco[]"]').val(0);
                                $(inputCodigo).closest('.row').find('input[name="diez[]"]').prop('disabled',
                                    true);
                                $(inputCodigo).closest('.row').find('input[name="cinco[]"]').prop('disabled',
                                    true);
                                break;
                            default:
                                // Código a ejecutar si la variable no coincide con ninguno de los casos anteriores
                        }
                    } else {
                        // Maneja el caso cuando el producto no se encuentra
                        console.error('Producto no encontrado');
                    }
                },
                error: function(error) {
                    console.error('Error en la petición AJAX:', error);
                }
            });
            actualizarSumaTotal();
        }

        

        function pagar1() {
            var idventa = document.getElementById('idfac').value;
            montoingresado = parseFloat(document.getElementById('montoAbonar').value);
            var descuentoInput = document.getElementById('descuent').value;

            // Verificar si el input tiene datos
            var descuento = descuentoInput ? parseFloat(descuentoInput) : 0;
            // Mostrar el mensaje de confirmación
            Swal.fire({
                title: '¿Seguro que desea realizar el pago?',
                text: 'Monto a Abonar: ' +
                    montoingresado, // Puedes personalizar el mensaje con el monto de la cuota
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, realizar pago',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // El usuario confirmó, enviar la solicitud AJAX

                    // Obtener el token CSRF
                    var token = $('meta[name="csrf-token"]').attr('content');
                    //console.log('{{ url('/') }}/caja/' + idventa + '/' + montoingresado + '/' + descuento);
                    $.ajax({
                        url: '{{ url('/') }}/caja/' + idventa + '/' + montoingresado + '/' +
                            descuento,
                        method: 'POST',
                        data: {
                            // Enviar el token CSRF con la solicitud
                            _token: token
                        },
                        success: function(response) {
                            if (response.hasOwnProperty('success')) {
                                Swal.fire('Éxito', response.success, 'success');
                                var cajaId = response.caja;
                                var url = "{{ route('documentopagomontopdf', '') }}/" + cajaId;

                                window.open(url, '_blank');
                            } else {
                                Swal.fire('Error', 'La respuesta no contiene datos válidos', 'error');
                            }
                            $('#pagomontoModal').modal('hide');


                        },
                        error: function() {
                            Swal.fire('Error', 'Hubo un error al procesar la solicitud', 'error');
                        }
                    });
                }
            });
        }

        function verifMonto() {
            montoingresado = parseFloat(document.getElementById('montoAbonar').value);
            montoreal = parseFloat(document.getElementById('montoAbonar1').value);
            descuento = parseFloat(document.getElementById('descuent').value);
            montoreal = montoreal - descuento;
            if (montoingresado > montoreal) {
                document.getElementById('montoAbonar').value = montoreal;
                montoingresado = montoreal;
            } else if (montoingresado < 0) {
                document.getElementById('montoAbonar').value = 0;
                montoingresado = 0;
            }
            diferencia = montoreal - montoingresado;
            document.getElementById('diferenciaAbonar').innerHTML = diferencia;
        }

        function verifVuelto() {
            montoingresado = parseFloat(document.getElementById('montoAbonar').value);

            descus = parseFloat(document.getElementById('descUs').value);
            vuelto = descus - montoingresado;
            document.getElementById('vuelto').innerHTML = vuelto;
        }
    </script>
@endpush