@extends('adminlte::page')

@section('content_header')
    <div class="row">
        <div class="col-6">
            <h1 class="m-0 custom-heading">Lista de Compras </h1>
        </div>
        <div class="col-6">
            <!-- Puedes agregar un enlace para crear una nueva compra de combustible aquí -->
            <a href="{{ route('compra.create') }}" class="btn btn-primary " style="float: right;">Nueva Compra</a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="table1" class="table table-bordered table-hover" theme="light">
                        <thead>
                            <th>Detalles</th> <!-- Columna para el botón de expansión -->
                            <th>ID</th>
                            <th>Fecha de Emisión</th>
                            <th>Número de Factura</th>
                            <th>Timbrado Factura</th>
                            <th>Proveedor</th>
                            <th>Condición de Compra</th>
                            <th>Total</th>
                            <th>Usuario</th>
                            <th>Categoria</th>
                            <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cabecera as $compra)
                                <tr data-child-id="{{ $compra->id }}">
                                    <td class="details-control text-center">
                                        <i class="fa fa-plus-circle text-primary"></i> <!-- Ícono de expansión -->
                                    </td>
                                    <td>{{ $compra->id }}</td>
                                    <td>{{ $compra->fecha_emision }}</td>
                                    <td>{{ $compra->nro_factura }}</td>
                                    <td>{{ $compra->timbrado }}</td>
                                    <td>{{ $compra->proveedor->razonsocial }}</td>
                                    <td>{{ $compra->condicion_de_compra }}</td>
                                    <td>{{ number_format($compra->total_compra, 0, '.', ',') }}</td>
                                    <td>{{ $compra->usuario->name }}</td>
                                    <td
                                        class="{{ $compra->estadocompra->descripcion == 'Activo' ? 'text-success' : 'text-danger' }}">
                                        {{ $compra->estadocompra->descripcion }}
                                    </td>
                                    <td>
                                        {{-- <a href="#" class="btn btn-sm btn-outline-secondary ver-detalle-btn"
                                            data-compra-id="{{ $compra->id }}">
                                            <i class="fa fa-eye"></i>
                                        </a> --}}
                                        @if ($compra->estadocompra->descripcion == 'Activo')
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                id="delete-button" onclick="borrarCompraCombustible({{ $compra->id }})">
                                                <i class="fa fa-sm fa-fw fa-trash"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>


                    <x-adminlte-modal id="detalleModal" title="Detalles de la Compra" theme="light" size="lg">
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
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
    <script>
        $(document).ready(function() {
            var table = $('#table1').DataTable({
                responsive: true,
                autoWidth: false,
                columnDefs: [{
                        className: 'details-control',
                        orderable: false,
                        targets: 0
                    },
                    {
                        orderable: false,
                        targets: -1
                    }
                ],
                order: [
                    [1, 'desc']
                ],
            });

            // Evento de clic en la flechita para mostrar/ocultar detalles
            $('#table1 tbody').on('click', 'td.details-control', function() {
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                var compraId = tr.data('child-id');

                if (row.child.isShown()) {
                    // Si el detalle está visible, lo ocultamos
                    row.child.hide();
                    tr.removeClass('shown');
                    $(this).find('i').removeClass('fa-minus-circle').addClass('fa-plus-circle');
                } else {
                    // Si el detalle está oculto, lo mostramos
                    var $this = $(this); // Guarda el contexto
                    $.ajax({
                        url: 'compra/' + compraId + '/detalles', // Usando tu ruta
                        method: 'GET',
                        success: function(response) {
                            var detalleHTML = '';
                            response.forEach(function(detalle, index) {
                                detalleHTML += '<tr><th scope="row">' + (index + 1) +
                                    '</th><td>' + detalle.productos.unidaddemedida
                                    .descripcion +
                                    '</td><td>' + detalle.productos.codigo +
                                    '</td><td>' + detalle.cantidad + '</td><td>' +
                                    detalle.descripcion +
                                    '</td><td>' + detalle.precio_u + '</td><td>' +
                                    detalle.monto +
                                    '</td><td>' + detalle.tipo_impuesto + '</td></tr>';
                            });

                            // Muestra el detalle
                            row.child(
                                '<table class="table table-bordered table-hover table-sm"><thead><tr><th>Item</th><th>U. Medida</th><th>Código</th><th>Cantidad</th><th>Descripción</th><th>Precio Unit.</th><th>Total</th><th>IVA %</th></tr></thead><tbody>' +
                                detalleHTML + '</tbody></table>').show();
                            tr.addClass('shown');
                            $this.find('i').removeClass('fa-plus-circle').addClass(
                                'fa-minus-circle');
                        },
                        error: function() {
                            console.log('Error al obtener detalles de la compra');
                        }
                    });
                }
            });
        });



        // Accede al ID desde la variable Blade
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
                    const deleteUrl = `{{ route('compra.destroy', ['compra' => ':id']) }}`.replace(':id',
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
            console.log(compraId);
            // Realiza una petición AJAX para obtener los detalles de la compra
            $.ajax({
                url: 'compra/' + compraId + '/detalles',
                method: 'GET',
                success: function(response) {
                    var detalleHTML = '';
                    // Construye el HTML de los detalles de la compra utilizando los datos obtenidos 
                    response.forEach(function(detalle) {
                        item = 1;
                        iva = detalle.precio - (detalle.cantidad * detalle.precio_unitario);
                        detalleHTML += '<tr><th scope="row">' + item +
                            '</th><td>' + detalle.productos.unidaddemedida.descripcion +
                            '</td><td>' + detalle.productos.codigo +
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
    </script>
@endpush
