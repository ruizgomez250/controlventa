@extends('adminlte::page')

@section('content_header')
    <div class="row">
        <div class="col-6">
            <h1 class="m-0 custom-heading">Lista de Ventas </h1>
        </div>
        <div class="col-6">
            <!-- Puedes agregar un enlace para crear una nueva compra de combustible aquí -->
            <a href="{{ route('venta.create') }}" class="btn btn-primary " style="float: right;">Nueva Venta</a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <x-adminlte-datatable id="table1" :heads="$heads" head-theme="dark" theme="light" striped
                        hoverable with-buttons>
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
                                    class="{{$compra->estado == 1 ? 'text-info' : ($compra->estado == 0 ? 'text-danger' : ($compra->estado == 4 ? 'text-primary' : 'text-success'))  }}">
                                    {{ $compra->estado == 1 ? 'Pedido Generado' : ($compra->estado == 0 ? 'Anulado' : ($compra->estado == 4 ? 'Pago parcial' : 'Pagado')) }}</td>
                                <td>

                                    <a href="#" class="btn btn-sm btn-outline-secondary ver-detalle-btn"
                                        data-compra-id="{{ $compra->id }}"><i class="fa fa-eye"></i></a>
                                    @if ($compra->estado == 1)
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="delete-button"
                                            onclick="borrarCompraCombustible({{ $compra->id }})"><i
                                                class="fa fa-sm fa-fw fa-trash"></i></button>
                                    @endif

                                </td>
                            </tr>
                        @endforeach
                    </x-adminlte-datatable>
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
                    var detalleHTML = '';
                    // Construye el HTML de los detalles de la compra utilizando los datos obtenidos 
                    response.forEach(function(detalle) {
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
    </script>
@endpush
