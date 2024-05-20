@extends('adminlte::page')

@section('content_header')
    <div class="row">
        <div class="col-6">
            <h1 class="m-0 custom-heading">Lista de Ventas a Cobrar</h1>
        </div>


    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <x-adminlte-datatable id="table1" :heads="$heads" head-theme="dark" theme="light" striped hoverable
                        with-buttons>
                        @foreach ($cabecera as $compra)
                            <tr>
                                <td>{{ $compra->id }}</td>
                                <!-- Agrega aquí las columnas para 'id_persona', 'id_stock', 'numero', 'timbrado', 'estado', 'tipodoc', 'metodo_pago', etc. -->
                                <td>{{ $compra->fecha_emision }}</td>
                                <td>{{ $compra->numero_factura }}</td>
                                <td>{{ $compra->timbrado_factura }}</td>
                                <td>{{ $compra->cliente->razonsocial }}</td>
                                <td>{{ $compra->tipo_comprobante }}</td>
                                <td>{{ number_format($compra->total, 0, '.', ',') }}</td>
                                <td>{{ $compra->usuario->name }}</td>
                                <td
                                    class="{{ $compra->estado == 1 || $compra->estado == 4 ? 'text-success' : 'text-danger' }}">
                                    {{ $compra->estado == 1 ? 'Pedido Generado' : ($compra->estado == 0 ? 'Anulado' : ($compra->estado == 4 ? 'Pago parcial' : 'Pagado')) }}

                                </td>
                                <td>

                                    <a href="#" class="btn btn-sm btn-outline-secondary ver-detalle-btn"
                                        data-compra-id="{{ $compra->id }}" title="Mostrar detalles">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    @if ($compra->tipo_comprobante == 'CREDITO')
                                        <a href="#" class="btn btn-sm btn-outline-secondary pagar-cuota-btn"
                                            data-compra-id="{{ $compra->id }}" title="Pagar Cuota">
                                            <i class="fa fa-ruble-sign"></i></a>
                                    @endif
                                    <a href="#" class="btn btn-sm btn-outline-secondary pagar-monto-btn"
                                        data-compra-id="{{ $compra->id }}" title="Pagar por Monto">
                                        <i class="fa fa-sm fa-money-bill"></i></a>
                                    @if ($compra->estado == 1)
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="delete-button"
                                            onclick="borrarCompraCombustible({{ $compra->id }})" title="Anular pedido">
                                            <i class="fa fa-sm fa-fw fa-trash"></i>
                                        </button>
                                    @endif

                                </td>
                            </tr>
                        @endforeach
                    </x-adminlte-datatable>
                    <x-adminlte-modal id="detalleModal" title="Detalles de la Venta" theme="light" size="lg">
                        <div>
                            <table class="table table-sm table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th scope="col">Item</th>
                                        <th scope="col">U. Medida</th>
                                        <th scope="col">Código</th>
                                        <th scope="col">Cantidad</th>
                                        <th scope="col">Descripcion</th>
                                        <th scope="col">Precio Unit.</th>
                                        <th scope="col">Total</th>
                                        <th scope="col">IVA %</th>
                                    </tr>
                                </thead>
                                <tbody id="detalleContent">

                                </tbody>
                            </table>
                        </div>
                    </x-adminlte-modal>

                    <x-adminlte-modal id="documentosModal" title="PDF De documentos" theme="light" size="lg">
                        <div>
                            <table class="table table-sm table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th scope="col">Documento</th>
                                        <th scope="col">PDF</th>
                                    </tr>
                                </thead>
                                <tbody id="detalleContent">
                                    <tr>
                                        <th scope="col">Orden de Compra</th>
                                        <th scope="col"><a id="ordenCompraPdfLink" href="" target="_blank"
                                                class="btn btn-sm btn-outline-secondary">
                                                <i class="fa fa-sm fa-fw fa-file-pdf"></i>
                                            </a></th>
                                    </tr>

                                    <tr>
                                        <th scope="col">Nota de Recepcion</th>
                                        <th scope="col"><a id="recepcionPdfLink" href="" target="_blank"
                                                class="btn btn-sm btn-outline-secondary">
                                                <i class="fa fa-sm fa-fw fa-file-pdf"></i>
                                            </a></th>
                                    </tr>
                                    <tr>
                                        <th scope="col">Solicitud de Biens y Servicio</th>
                                        <th scope="col"><a id="bienesPdfLink" href="" target="_blank"
                                                class="btn btn-sm btn-outline-secondary">
                                                <i class="fa fa-sm fa-fw fa-file-pdf"></i>
                                            </a></th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </x-adminlte-modal>
                    <!--Modal-->
                    <x-adminlte-modal id="pagocuotaModal" title="Pago de Cuotas" theme="light" size="lg">
                        <div>
                            <table class="table table-sm table-hover" id="table1">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Nro. Cuota</th>
                                        <th>Opciones</th>
                                        <th>Cuota</th>
                                        <th>Fecha Deuda</th>
                                        <th>Fecha Vencimiento</th>
                                        <th>Fecha Pago</th>
                                        <th>Monto Abonado</th>
                                        <th>Saldo</th>
                                    </tr>
                                </thead>
                                <tbody id="detallecuota">

                                </tbody>
                            </table>
                        </div>
                    </x-adminlte-modal>

                    <!-- fin Modal-->
                    <!--Modal-->
                    <x-adminlte-modal id="pagomontoModal" title="Ingresar Monto a Pagar" theme="light" size="lg">
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
                                    <input type="hidden" name="idfac" id="idfac" value=""
                                        placeholder="Numero">
                                    <input type="number" oninput="verifMonto()" name="montoAbonar" id="montoAbonar"
                                        class="btn-success">
                                </h2>
                                <input type="hidden" name="montoAbonar1" id="montoAbonar1" class="form-control"
                                    value="" placeholder="Numero">

                                <h2><label>Descuento </label>
                                    <input type="number" oninput="verifMonto()" name="descuent" value="0"
                                        id="descuent" class="btn-success">
                                </h2>
                                <h1>
                                    <label>Diferencia Gs.</label>
                                    <label id="diferenciaAbonar" class="btn-danger">Diferencia</label>

                                </h1>
                                <h3><label>Efectivo Gs.</label>
                                    <input type="number" oninput="verifVuelto()" name="descUs" id="descUs"
                                        class="btn-warning">
                                </h3>
                                <h3>
                                    <label>Vuelto Gs.</label>
                                    <label id="vuelto" class="btn-info">0</label>

                                </h3>

                            </div>
                            <button class=" btn btn-primary " id="guardarMedida" onclick="pagar1()">
                                Guardar</button>
                        </div>

                        <!--pie modal-->
                    </x-adminlte-modal>
                </div>
            </div>
        @stop

        @push('js')
            <script>
                // Accede al ID desde la variable Blade
                cuotaboton='';
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

                function openDocumentosModal(compraId) {
                    // Cambia el atributo href del enlace dentro del modal dinámicamente
                    //document.getElementById('ordenCompraPdfLink').href = " route('ordenescomprapdf', '') }}" + '/' + compraId;


                    // Abre el modal
                    $('#documentosModal').modal('show');
                }
                var cabeceraId = {{ isset($_GET['id']) ? $_GET['id'] : '0' }};

                // if (cabeceraId !== 0) {
                //     // Construye la URL para la redirección
                //     var nuevaUrl = " route('ordenescomprapdf', '') }}" + "/" + cabeceraId;

                //     // Abre una nueva pestaña y redirecciona a la URL
                //     window.open(nuevaUrl, '_blank');
                // }
                // Agregar un evento clic al botón de eliminación
                function borrarCompraCombustible(compraId) {
                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: "Esta acción no se puede deshacer.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, eliminarlo'
                    }).then((result) => {
                        if (result.value) {
                            const form = document.createElement('form');
                            const deleteUrl = `{{ route('venta.destroy', ['ventum' => ':id']) }}`.replace(':id',
                                compraId);
                            form.setAttribute('method', 'POST');
                            form.setAttribute('action', deleteUrl);
                            form.classList.add('d-inline');

                            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                            form.innerHTML = `
                    @csrf
                    @method('DELETE')
                `;

                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                }

                // Obtener el mensaje de éxito o error de Laravel
                var successMessage = "{{ session('success') }}";
                var errorMessage = "{{ session('error') }}";

                // Mostrar el mensaje de éxito o error con SweetAlert
                if (successMessage) {
                    Swal.fire('Éxito', successMessage, 'success');
                } else if (errorMessage) {
                    Swal.fire('Error', errorMessage, 'error');
                }
                $('.ver-detalle-btn').click(function() {
                    var compraId = $(this).data('compra-id');
                    // Realiza una petición AJAX para obtener los detalles de la compra
                    $.ajax({
                        url: 'venta/' + compraId + '/detalles',
                        method: 'GET',
                        success: function(response) {
                            var detalle = response.detalles;
                            var detalleHTML = '';
                            // Construye el HTML de los detalles de la compra utilizando los datos obtenidos 
                            detalle.forEach(function(detalle) {
                                item = 1;
                                iva = detalle.monto - (detalle.cantidad * detalle.precio_u);
                                detalleHTML += '<tr><th scope="row">' + item +
                                    '</th><td>' + detalle.producto.unidaddemedida.descripcion +
                                    '</td><td>' + detalle.producto.codigo +
                                    '</td><td>' + detalle.cantidad + ' </td><td>' + detalle
                                    .descripcion +
                                    ' </td><td>' + detalle.precio_u + ' </td><td>' + detalle.monto +
                                    ' </td><td>' + detalle.tipo_impuesto + ' </td></tr>';
                                // Agrega más campos según tus necesidades
                            });

                            // Llena el contenido del modal con los detalles
                            $('#detalleContent').html(detalleHTML);

                            // Muestra el modal
                            $('#detalleModal').modal('show');
                        },
                        error: function() {
                            console.log('Error al obtener detalles de la compra');
                        }
                    });
                });
                $('.pagar-monto-btn').click(function() {
                    var compraId = $(this).data('compra-id');
                    // Realiza una petición AJAX para obtener los detalles de la compra
                    $.ajax({
                        url: 'venta/' + compraId + '/detalles',
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
                });

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

                $('.pagar-cuota-btn').click(function() {

                    var compraId = $(this).data('compra-id');
                    item = 1;
                    // Realiza una petición AJAX para obtener los detalles de la compra
                    $.ajax({
                        url: 'venta/' + compraId + '/cuotas',
                        method: 'GET',
                        success: function(response) {
                            var detalleHTML = '';
                            // Construye el HTML de los detalles de la compra utilizando los datos obtenidos 
                            response.forEach(function(detalle) {

                                fechap = detalle.fecha_pago;
                                idc='';
                                boton = '';
                                if (detalle.fecha_pago === null) {
                                    fechap = '<input id="pago' + detalle.idcuota + '" name="pago' +
                                        detalle.idcuota +
                                        '"  class="form-control" type="date" value="{{ now()->format('Y-m-d') }}">';
                                        boton = '<button class="btn-success btn-xs" onclick="pagar(' + detalle.idcuota + ')">Pagar</button>';
                                } else {
                                    idc = detalle.idcuota;
                                    boton = '<a id="documentoPagoPdfLink' + idc +
                                        '" href="{{ route('documentopagopdf', '') }}' + '/' + idc +
                                        '" target="_blank"' +
                                        ' class="btn btn-sm btn-outline-secondary">' +
                                        '<i class="fa fa-sm fa-fw fa-file-pdf"></i>' +
                                        '</a>';

                                }
                                montopagado = detalle.pagosrealizados * detalle.cuota;
                                saldo = detalle.totaldeuda - montopagado;
                                
                                detalleHTML += '<tr><th scope="row">' + item +
                                    '</th><td id="boton' + idc + '">' + boton +
                                    '</td><td>' + detalle.cuota +
                                    '</td><td>' + detalle.fecha_emision +
                                    ' </td><td>' + detalle.fecha_vencimiento +
                                    ' </td><td>' + fechap +
                                    ' </td><td>' + montopagado +
                                    ' </td><td>' + saldo + ' </td></tr>';
                                item++;
                                // Agrega más campos según tus necesidades
                            });

                            // Llena el contenido del modal con los detalles
                            document.getElementById('detallecuota').innerHTML = detalleHTML;

                            // Muestra el modal
                            $('#pagocuotaModal').modal('show');
                        },
                        error: function() {
                            console.log('Error al obtener detalles de la compra');
                        }
                    });
                });

                function pagar(idcuota) {
                    cuotaboton = $(this);
                    nombreinput = 'pago' + idcuota;
                    var fechapago = document.getElementById(nombreinput).value;
                    document.getElementById(nombreinput).innerHTML = fechapago;

                    // Mostrar el mensaje de confirmación
                    Swal.fire({
                        title: '¿Seguro que desea realizar el pago de esta cuota en esta fecha?',
                        text: 'fecha: ' + fechapago, // Puedes personalizar el mensaje con el monto de la cuota
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

                            $.ajax({
                                url: 'caja/' + idcuota + '/' + fechapago,
                                method: 'POST',
                                data: {
                                    // Enviar el token CSRF con la solicitud
                                    _token: token
                                },
                                success: function(response) {
                                    if (response.hasOwnProperty('ventaycuotas')) {
                                        // Recorre el arreglo de ventaycuotas
                                        response.ventaycuotas.forEach(function(detalle) {
                                           
                                            // Construye la URL de la ruta usando el idcuota
                                            var url = "{{ route('documentopagopdf', '') }}/" + idcuota;

                                            // Abre la URL en una nueva ventana del navegador
                                            window.open(url, '_blank');
                                            location.reload();
                                        });

                                        // Muestra el mensaje de éxito
                                       // Swal.fire('Éxito', response.success, 'success');
                                    } else {
                                        // Si la propiedad 'ventaycuotas' no está presente en la respuesta
                                        Swal.fire('Error', 'La respuesta no contiene datos válidos', 'error');
                                    }
                                },
                                error: function() {
                                    Swal.fire('Error', 'Hubo un error al procesar la solicitud', 'error');
                                }
                            });
                        }
                    });
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

                            $.ajax({
                                url: 'caja/' + idventa + '/' + montoingresado + '/' + descuento,
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
            </script>
        @endpush
