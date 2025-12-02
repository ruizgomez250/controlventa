@extends('adminlte::page')

@section('content_header')
    <div class="row">
        <div class="col-6">
            <h1 class="m-0 custom-heading">Lista de Ventas</h1>
        </div>
        <div class="col-6">
            <a href="{{ route('venta.create') }}" class="btn btn-primary" style="float: right;">Nueva Venta</a>
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
                            <tr>
                                <th>Detalles</th> <!-- Columna para el botón de expansión -->
                                <th>ID</th>
                                <th>Fecha de Emisión</th>
                                <th>Número de Factura</th>
                                <th>Timbrado Factura</th>
                                <th>Cliente</th>
                                <th>Tipo de Comprobante</th>
                                <th>Total</th>
                                <th>Usuario</th>
                                <th>Estado</th>
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
                                    <td>{{ $compra->numero_factura }}</td>
                                    <td>{{ $compra->timbrado_factura }}</td>
                                    <td>{{ $compra->cliente->razonsocial }}</td>
                                    <td>{{ $compra->tipo_comprobante }}</td>
                                    <td>{{ number_format($compra->total, 0, '.', ',') }}</td>
                                    <td>{{ $compra->usuario->name }}</td>
                                    <td
                                        class="{{ $compra->estado == 1 ? 'text-info' : ($compra->estado == 0 ? 'text-danger' : ($compra->estado == 4 ? 'text-primary' : 'text-success')) }}">
                                        {{ $compra->estado == 1 ? 'Pedido Generado' : ($compra->estado == 0 ? 'Anulado' : ($compra->estado == 4 ? 'Pago parcial' : 'Pagado')) }}
                                    </td>
                                    <td>
                                        @if ($compra->estado == 1)
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                id="delete-button"
                                                onclick="borrarCompraCombustible({{ $compra->id }})"><i
                                                    class="fa fa-sm fa-fw fa-trash"></i></button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
    <script>
        $(document).ready(function() {
            // Inicialización de DataTables
            var table = $('#table1').DataTable({
                responsive: true,
                autoWidth: false,
                columnDefs: [{
                        className: 'details-control', // Agrega clase de control de detalles
                        orderable: false, // No se puede ordenar por esta columna
                        targets: 0 // Índice de la columna de flechita
                    },
                    {
                        orderable: false,
                        targets: -1 // Última columna (acciones)
                    }
                ],
                order: [
                    [1, 'desc']
                ], // Ordenar por el ID (columna 1)
            });

            // Función para generar HTML de detalles adicionales
            function format(details) {
                var detalleHTML = '<table class="table table-bordered table-hover table-sm">' +
                    '<thead>' +
                    '<tr>' +
                    '<th>Item</th>' +
                    '<th>U. Medida</th>' +
                    '<th>Código</th>' +
                    '<th>Cantidad</th>' +
                    '<th>Descripción</th>' +
                    '<th>Precio Unit.</th>' +
                    '<th>Total</th>' +
                    '<th>IVA %</th>' +
                    '</tr>' +
                    '</thead>' +
                    '<tbody>';
                details.forEach(function(detalle, index) {
                    detalleHTML += '<tr>' +
                        '<td>' + (index + 1) + '</td>' +
                        '<td>' + detalle.producto.unidaddemedida.descripcion + '</td>' +
                        '<td>' + detalle.producto.codigo + '</td>' +
                        '<td>' + detalle.cantidad + '</td>' +
                        '<td>' + detalle.descripcion + '</td>' +
                        '<td>' + detalle.precio_u + '</td>' +
                        '<td>' + detalle.monto + '</td>' +
                        '<td>' + detalle.tipo_impuesto + '</td>' +
                        '</tr>';
                });
                detalleHTML += '</tbody></table>';
                return detalleHTML;
            }

            // Evento de clic en la flechita para mostrar/ocultar detalles
            $('#table1 tbody').on('click', 'td.details-control', function() {
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                var compraId = tr.data('child-id');
                var iconCell = $(this).find('i'); // Obtener el ícono específico dentro de la celda

                if (row.child.isShown()) {
                    // Si el detalle está visible, lo ocultamos
                    row.child.hide();
                    tr.removeClass('shown');
                    iconCell.removeClass('fa-minus-circle').addClass('fa-plus-circle');
                } else {
                    // Si el detalle está oculto, lo mostramos
                    $.ajax({
                        url: '{{ url('/') }}/venta/' + compraId +
                        '/detalles', // Ajustar la URL si es necesario
                        method: 'GET',
                        success: function(response) {
                            var detalles = response.detalles;
                            // Mostramos el detalle
                            row.child(format(detalles)).show();
                            tr.addClass('shown');
                            // Cambiar el icono a minus después de mostrar los detalles
                            iconCell.removeClass('fa-plus-circle').addClass('fa-minus-circle');
                        },
                        error: function() {
                            console.log('Error al obtener detalles de la compra');
                        }
                    });
                }
            });
        });
        // Función para eliminar compra con confirmación
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
                    const deleteUrl = `{{ route('venta.destroy', ['ventum' => ':id']) }}`.replace(':id', compraId);
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
    </script>
@endpush
