@extends('adminlte::page')

@section('content_header')
    <div class="row">
        <div class="col-6">
            <h1 class="m-0 custom-heading">{{ __('Facturas Emitidas') }}</h1>
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
                                <th>{{ __('Detalles') }}</th>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Fecha de Emisión') }}</th>
                                <th>{{ __('Número de Factura') }}</th>
                                <th>{{ __('Timbrado Factura') }}</th>
                                <th>{{ __('Cliente') }}</th>
                                <th>{{ __('Tipo de Comprobante') }}</th>
                                <th>{{ __('Total') }}</th>
                                <th>{{ __('Usuario') }}</th>
                                <th>{{ __('Estado') }}</th>
                                <th>{{ __('Acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cabecera as $venta)
                                <tr data-child-id="{{ $venta->id }}">
                                    <td class="details-control text-center">
                                        <i class="fa fa-plus-circle text-primary"></i>
                                    </td>
                                    <td>{{ $venta->id }}</td>
                                    <td>{{ $venta->fecha_emision }}</td>
                                    <td>{{ $venta->numero_factura }}</td>
                                    <td>{{ $venta->timbrado_factura }}</td>
                                    <td>{{ $venta->cliente->razonsocial }}</td>
                                    <td>{{ $venta->tipo_comprobante }}</td>
                                    <td>{{ number_format($venta->total, 0, '.', ',') }}</td>
                                    <td>{{ $venta->usuario->name }}</td>
                                    <td>
                                        @if ($venta->estado == 1)
                                            <span class="badge badge-warning">Pedido Generado</span>
                                        @elseif ($venta->estado == 0)
                                            <span class="badge badge-danger">Anulado</span>
                                        @elseif ($venta->estado == 4)
                                            <span class="badge badge-info">Pago parcial</span>
                                        @else
                                            <span class="badge badge-success">Pagado</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if (auth()->user()->can('venta borrar') && in_array($venta->estado, [2, 4]))
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="anularFactura({{ $venta->id }})">
                                                <i class="fa fa-sm fa-fw fa-ban"></i> Anular
                                            </button>
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
                }],
                order: [
                    [1, 'desc']
                ],
            });

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

            $('#table1 tbody').on('click', 'td.details-control', function() {
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                var ventaId = tr.data('child-id');
                var iconCell = $(this).find('i');

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                    iconCell.removeClass('fa-minus-circle').addClass('fa-plus-circle');
                } else {
                    $.ajax({
                        url: '{{ url('/') }}/venta/' + ventaId + '/detalles',
                        method: 'GET',
                        success: function(response) {
                            var detalles = response.detalles;
                            row.child(format(detalles)).show();
                            tr.addClass('shown');
                            iconCell.removeClass('fa-plus-circle').addClass('fa-minus-circle');
                        },
                        error: function() {
                            console.log('Error al obtener detalles de la venta');
                        }
                    });
                }
            });
        });

        function anularFactura(ventaId) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Se anulará la factura y se restaurará el stock.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, anular factura'
            }).then((result) => {
                if (result.value) {
                    const form = document.createElement('form');
                    const deleteUrl = `{{ route('venta.destroy', ['ventum' => ':id']) }}`.replace(':id', ventaId);
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
