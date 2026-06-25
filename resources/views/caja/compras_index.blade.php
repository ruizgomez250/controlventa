@extends('adminlte::page')

@section('content_header')
    <div class="row">
        <div class="col-6">
            <h1 class="m-0 custom-heading">{{ __('Cuentas a Pagar (Compras)') }}</h1>
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
                                <th>{{ __('Fecha') }}</th>
                                <th>{{ __('Nro Factura') }}</th>
                                <th>{{ __('Timbrado') }}</th>
                                <th>{{ __('Proveedor') }}</th>
                                <th>{{ __('Condición') }}</th>
                                <th>{{ __('Total') }}</th>
                                <th>{{ __('Usuario') }}</th>
                                <th>{{ __('Estado') }}</th>
                                <th>{{ __('Acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cabecera as $compra)
                                <tr data-child-id="{{ $compra->id }}">
                                    <td class="details-control text-center">
                                        <i class="fa fa-plus-circle text-primary"></i>
                                    </td>
                                    <td>{{ $compra->id }}</td>
                                    <td>{{ $compra->fecha_emision }}</td>
                                    <td>{{ $compra->nro_factura }}</td>
                                    <td>{{ $compra->timbrado }}</td>
                                    <td>{{ $compra->proveedor->razonsocial }}</td>
                                    <td>{{ $compra->condicion_de_compra }}</td>
                                    <td>{{ number_format($compra->total_compra, 0, '.', ',') }}</td>
                                    <td>{{ $compra->usuario->name }}</td>
                                    @php
                                        $estadoTexto = '';
                                        $estadoClase = '';
                                        switch ($compra->id_estado) {
                                            case 0: $estadoTexto = 'Anulado'; $estadoClase = 'text-danger'; break;
                                            case 1: $estadoTexto = 'Pendiente'; $estadoClase = 'text-success'; break;
                                            case 2: $estadoTexto = 'Pagado'; $estadoClase = 'text-success'; break;
                                            case 4: $estadoTexto = 'Pago parcial'; $estadoClase = 'text-warning'; break;
                                            default: $estadoTexto = 'Desconocido'; $estadoClase = 'text-danger'; break;
                                        }
                                    @endphp
                                    <td>
                                        <span class="{{ $estadoClase }}">{{ $estadoTexto }}</span>
                                    </td>
                                    <td>
                                        @if ($compra->condicion_de_compra == 'CREDITO')
                                            <a href="#" class="btn btn-sm btn-outline-secondary pagar-cuota-btn"
                                                data-compra-id="{{ $compra->id }}" title="{{ __('Pagar Cuota') }}">
                                                <i class="fa fa-ruble-sign"></i>
                                            </a>
                                        @endif
                                        <a href="#" class="btn btn-sm btn-outline-secondary pagar-monto-btn"
                                            data-compra-id="{{ $compra->id }}" title="{{ __('Pagar por Monto') }}">
                                            <i class="fa fa-sm fa-money-bill"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <x-adminlte-modal id="pagocuotaModal" title="{{ __('Pago de Cuotas - Compra') }}" theme="light" size="lg">
                        <div>
                            <table class="table table-sm table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>{{ __('Nro. Cuota') }}</th>
                                        <th>{{ __('Opciones') }}</th>
                                        <th>{{ __('Cuota') }}</th>
                                        <th>{{ __('Fecha Emisión') }}</th>
                                        <th>{{ __('Fecha Vencimiento') }}</th>
                                        <th>{{ __('Fecha Pago') }}</th>
                                        <th>{{ __('Monto Abonado') }}</th>
                                        <th>{{ __('Saldo') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="detallecuota"></tbody>
                            </table>
                        </div>
                    </x-adminlte-modal>

                    <x-adminlte-modal id="pagomontoModal" title="{{ __('Ingresar Monto a Pagar') }}" theme="light" size="lg">
                        <div>
                            <div class="row">
                                <div class="col-sm-12 text-center">
                                    <table id="tablaModalFech" class="table table-hover table-bordered">
                                        <thead align="center">
                                            <tr class="bg-primary text-white">
                                                <th><b>{{ __('Descripción') }}</b></th>
                                                <th><b>{{ __('Monto') }}</b></th>
                                            </tr>
                                        </thead>
                                        <tbody id="tablaModBody"></tbody>
                                    </table>
                                </div>
                                <div class="col-12 text-center mt-3">
                                    <h4>{{ __('Cargar el monto a abonar') }}</h4>
                                </div>
                                <div class="col-12 text-center">
                                    <label>{{ __('Monto a Abonar') }} {{ $moneda }}</label>
                                    <input type="hidden" name="idfac" id="idfac" value="">
                                    <input type="number" oninput="verifMonto()" name="montoAbonar" id="montoAbonar" class="form-control d-inline w-auto">
                                </div>
                                <input type="hidden" name="montoAbonar1" id="montoAbonar1" value="">
                                <div class="col-12 text-center mt-2">
                                    <label>{{ __('Descuento') }}</label>
                                    <input type="number" oninput="verifMonto()" name="descuent" value="0" id="descuent" class="form-control d-inline w-auto">
                                </div>
                                <div class="col-12 text-center mt-3">
                                    <h4>{{ __('Diferencia') }} {{ $moneda }}<span id="diferenciaAbonar" class="text-danger">0</span></h4>
                                </div>
                                <div class="col-12 text-center mt-2">
                                    <label>{{ __('Efectivo') }} {{ $moneda }}</label>
                                    <input type="number" oninput="verifVuelto()" name="descUs" id="descUs" class="form-control d-inline w-auto">
                                </div>
                                <div class="col-12 text-center mt-2">
                                    <h4>{{ __('Vuelto') }} {{ $moneda }}<span id="vuelto" class="text-info">0</span></h4>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <button class="btn btn-primary" onclick="pagar1()">{{ __('Guardar') }}</button>
                            </div>
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
                columnDefs: [
                    { className: 'details-control', orderable: false, targets: 0 },
                    { orderable: false, targets: -1 }
                ],
                order: [[1, 'desc']],
            });

            $('#table1 tbody').on('click', 'td.details-control', function() {
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                var compraId = tr.data('child-id');

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                    $(this).find('i').removeClass('fa-minus-circle').addClass('fa-plus-circle');
                } else {
                    var $this = $(this);
                    $.ajax({
                        url: '/compra/' + compraId + '/detalles',
                        method: 'GET',
                        success: function(response) {
                            var html = '<table class="table table-bordered table-sm">' +
                                '<thead><tr><th>Item</th><th>U. Medida</th><th>Código</th><th>Cantidad</th><th>Descripción</th><th>Precio Unit.</th><th>Total</th><th>IVA %</th></tr></thead><tbody>';
                            response.forEach(function(d, i) {
                                html += '<tr><td>' + (i+1) + '</td><td>' + (d.productos?.unidaddemedida?.descripcion || '') + '</td><td>' + d.productos?.codigo + '</td><td>' + d.cantidad + '</td><td>' + d.descripcion + '</td><td>' + d.precio_u + '</td><td>' + d.monto + '</td><td>' + d.tipo_impuesto + '</td></tr>';
                            });
                            html += '</tbody></table>';
                            row.child(html).show();
                            tr.addClass('shown');
                            $this.find('i').removeClass('fa-plus-circle').addClass('fa-minus-circle');
                        }
                    });
                }
            });
        });

        $('.pagar-cuota-btn').click(function() {
            var compraId = $(this).data('compra-id');
            var item = 1;
            $.ajax({
                url: '/compra/' + compraId + '/cuotas',
                method: 'GET',
                success: function(response) {
                    var html = '';
                    response.forEach(function(d) {
                        var fechap = d.fecha_pago;
                        var boton = '';
                        if (d.fecha_pago === null) {
                            fechap = '<input id="pago' + d.idcuota + '" class="form-control" type="date" value="{{ now()->format('Y-m-d') }}" readonly>';
                            boton = '<button class="btn-success btn-xs" onclick="pagar(' + d.idcuota + ')">Pagar</button>';
                        } else {
                            boton = '<button class="btn btn-sm btn-outline-secondary" disabled><i class="fa fa-check text-success"></i> Pagado</button>';
                        }
                        var montopagado = d.pagosrealizados * d.cuota;
                        var saldo = d.totaldeuda - montopagado;
                        html += '<tr><th scope="row">' + item +
                            '</th><td id="boton' + d.idcuota + '">' + boton +
                            '</td><td>' + d.cuota.toFixed(2) +
                            '</td><td>' + d.fecha_emision +
                            '</td><td>' + d.fecha_vencimiento +
                            '</td><td>' + fechap +
                            '</td><td>' + montopagado.toFixed(2) +
                            '</td><td>' + saldo.toFixed(2) + '</td></tr>';
                        item++;
                    });
                    $('#detallecuota').html(html);
                    $('#pagocuotaModal').modal('show');
                },
                error: function() {
                    Swal.fire('Error', 'Error al obtener las cuotas', 'error');
                }
            });
        });

        $('.pagar-monto-btn').click(function() {
            var compraId = $(this).data('compra-id');
            $.ajax({
                url: '/compra/' + compraId + '/detalles',
                method: 'GET',
                success: function(response) {
                    var html = '';
                    var total = 0;
                    response.forEach(function(d) {
                        html += '<tr><td>' + (d.descripcion || d.productos?.descripcion) + '</td><td>' + d.monto + '</td></tr>';
                        total += parseFloat(d.monto) || 0;
                    });
                    $('#montoAbonar').val(total.toFixed(2));
                    $('#montoAbonar1').val(total.toFixed(2));
                    $('#idfac').val(compraId);
                    $('#tablaModBody').html(html);
                    $('#pagomontoModal').modal('show');
                },
                error: function() {
                    Swal.fire('Error', 'Error al obtener detalles', 'error');
                }
            });
        });

        function verifMonto() {
            var monto = parseFloat($('#montoAbonar').val()) || 0;
            var real = (parseFloat($('#montoAbonar1').val()) || 0) - (parseFloat($('#descuent').val()) || 0);
            monto = Math.max(0, Math.min(monto, real));
            $('#montoAbonar').val(monto.toFixed(2));
            $('#diferenciaAbonar').text((real - monto).toFixed(2));
        }

        function verifVuelto() {
            var abono = parseFloat($('#montoAbonar').val()) || 0;
            var efectivo = parseFloat($('#descUs').val()) || 0;
            $('#vuelto').text((efectivo - abono).toFixed(2));
        }

        function pagar(idcuota) {
            var fechapago = $('#pago' + idcuota).val();
            Swal.fire({
                title: '¿Seguro que desea pagar esta cuota?',
                text: 'Fecha: ' + fechapago,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, pagar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/caja/compra/' + idcuota + '/' + fechapago,
                        method: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            Swal.fire('Éxito', response.success, 'success');
                            location.reload();
                        },
                        error: function() {
                            Swal.fire('Error', 'Error al procesar el pago', 'error');
                        }
                    });
                }
            });
        }

        function pagar1() {
            var id = $('#idfac').val();
            var monto = $('#montoAbonar').val();
            var desc = $('#descuent').val() || 0;
            Swal.fire({
                title: '¿Seguro que desea realizar el pago?',
                text: 'Monto: ' + monto,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, pagar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/caja/compra/' + id + '/' + monto + '/' + desc,
                        method: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            Swal.fire('Éxito', response.success, 'success');
                            $('#pagomontoModal').modal('hide');
                            location.reload();
                        },
                        error: function() {
                            Swal.fire('Error', 'Error al procesar el pago', 'error');
                        }
                    });
                }
            });
        }

        var successMessage = "{{ session('success') }}";
        var errorMessage = "{{ session('error') }}";
        if (successMessage) Swal.fire('Éxito', successMessage, 'success');
        else if (errorMessage) Swal.fire('Error', errorMessage, 'error');
    </script>
@endpush
