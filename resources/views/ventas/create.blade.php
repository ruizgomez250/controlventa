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

        <!-- Modal Fechas de Pago -->
        <div class="modal fade" id="modalPagare">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Guardar las fechas de los pagos</h4>
                    </div>
                    <div class="modal-body d-flex justify-content-center align-items-center">
                        <table id="tblpagare" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Cuota</th>
                                    <th>Fecha Pago</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" type="button" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Monto a Pagar -->
        <x-adminlte-modal id="pagomontoModal" title="Ingresar Monto a Pagar" theme="light" size="lg"
            data-backdrop="static">
            <div class="text-center">
                <table id="tablaModalFech" class="table table-hover table-bordered">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>Descripción</th>
                            <th>Monto</th>
                        </tr>
                    </thead>
                    <tbody id="tablaModBody"></tbody>
                </table>
                <h2>Cargar el monto a abonar</h2>
                <h2>
                    Monto a Abonar Gs.
                    <input type="hidden" name="idfac" id="idfac" value="">
                    <input type="number" oninput="verifMonto()" name="montoAbonar" id="montoAbonar"
                        class="form-control d-inline w-auto">
                </h2>
                <input type="hidden" name="montoAbonar1" id="montoAbonar1" value="">
                <h2>
                    Descuento
                    <input type="number" oninput="verifMonto()" name="descuent" id="descuent" value="0"
                        class="form-control d-inline w-auto">
                </h2>
                <h1>
                    Diferencia Gs. <span id="diferenciaAbonar" class="text-danger">0</span>
                </h1>
                <h3>
                    Efectivo Gs.
                    <input type="number" oninput="verifVuelto()" name="descUs" id="descUs"
                        class="form-control d-inline w-auto">
                </h3>
                <h3>
                    Vuelto Gs. <span id="vuelto" class="text-info">0</span>
                </h3>
                <button type="button" class="btn btn-primary" onclick="pagar1()">Guardar</button>
            </div>
        </x-adminlte-modal>

        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="form-group col-md-2">
                        <label for="fechaemision">FECHA DE EMISIÓN (alt+shift+f)</label>
                        <input type="date" class="form-control" id="fechaemision" name="fechaemision"
                            value="{{ date('Y-m-d') }}" required>
                    </div>
                    <x-adminlte-input type="text" id="nrofactura" name="nrofactura" label="Factura Nº"
                        fgroup-class="col-md-2" value="0" required />
                    <x-adminlte-input type="text" id="timbrado" name="timbrado" label="Timbrado Nº"
                        fgroup-class="col-md-2" value="0" required />

                    <div class="col-md-3">
                        <div class="card" style="margin-top: -18px">
                            <div class="card-body">
                                <label>CONDICIÓN DE COMPRA</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="condicion" value="CONTADO"
                                        onchange="ocultarOpc()" checked>
                                    <label class="form-check-label">Contado</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="condicion" value="CREDITO"
                                        onchange="mostrarOpc()">
                                    <label class="form-check-label">Crédito</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <x-adminlte-input type="number" id="cantpago" name="cantpago" fgroup-class="col-md-1"
                        value="1" min="1" style="display:none;" />
                    <div class="col-md-2 d-flex align-items-end">
                        <a data-toggle="modal" id="fechasButton" href="#modalPagare" style="display:none;">
                            <button class="btn btn-success" type="button" onclick="cargarPag()">
                                <i class="fa fa-plus-circle"></i> Fechas Pagos
                            </button>
                        </a>
                    </div>
                </div>

                <x-adminlte-card title="Clientes" class="text-primary mb-3">
                    <div class="row">
                        <x-adminlte-input type="number" id="cod_proveedor" name="cod_proveedor" onchange="cambiarCod()"
                            placeholder="Código" label="COD." fgroup-class="col-md-1" required />
                        <x-adminlte-select2 name="id_proveedor" id="id_proveedor" label="NOMBRE/RAZON SOCIAL"
                            data-placeholder="Seleccionar un proveedor..." fgroup-class="col-md-7"
                            onchange="actualizarNumeroDocumento()">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-primary"><i class="fas fa-user"></i></div>
                            </x-slot>
                            @foreach ($clientes as $item)
                                <option value="{{ $item->id }}" data-ruc="{{ $item->ruc }}">
                                    {{ $item->razonsocial }}</option>
                            @endforeach
                        </x-adminlte-select2>
                        <x-adminlte-input type="text" id="numero_documento" name="numero_documento"
                            placeholder="DOCUMENTO" label="NUMERO DOC." readonly fgroup-class="col-md-2" />
                        <video poster="{{ asset('vendor/adminlte/dist/img/lector_QR.jpg') }}" id="preview"
                            class="d-none"></video>
                    </div>
                </x-adminlte-card>

                <hr>

                <style>
                    /* 🔥 Contenedor que obliga al scroll sí o sí */
                    .scroll-area {
                        display: block !important;
                        width: 100% !important;
                        overflow-x: auto !important;
                        overflow-y: hidden;
                        white-space: nowrap !important;
                        min-width: 1200px;
                        /* Ajustar según suma de columnas */
                        border: 1px solid #ccc;
                    }

                    /* 🔥 Columnas fijas que no se achican */
                    .col-small {
                        width: 80px;
                        min-width: 80px;
                        max-width: 80px;
                        text-align: center;
                    }

                    .col-fixed {
                        width: 120px;
                        min-width: 120px;
                        max-width: 120px;
                        text-align: center;
                    }

                    .col-medium {
                        width: 140px;
                        min-width: 140px;
                        max-width: 140px;
                        text-align: center;
                    }

                    .col-large {
                        width: 240px;
                        min-width: 240px;
                        max-width: 240px;
                        text-align: center;
                    }

                    .header-row {
                        display: flex;
                        flex-direction: row;
                        flex-wrap: nowrap;
                        align-items: center;
                        background: #000;
                        color: #fff;
                        font-weight: bold;
                        padding: 10px 0;
                    }
                </style>

                <!-- 🔥 CONTENEDOR QUE GARANTIZA EL SCROLL -->
                <div class="scroll-area">

                    <div class="header-row">
                        <div class="col-small">ITEM</div>
                        <div class="col-small">UNDM</div>
                        <div class="col-medium">CÓDIGO</div>
                        <div class="col-large">DESCRIPCIÓN</div>
                        <div class="col-fixed">CANTIDAD</div>
                        <div class="col-medium">PRECIO UNIT.</div>
                        <div class="col-fixed">EXENTAS</div>
                        <div class="col-small">5%</div>
                        <div class="col-small">10%</div>
                        <div class="col-small"></div>
                    </div>

                    <div id="items"></div>
                </div>






                <div class="row mb-3">
                    <div class="col-6">
                        <button type="button" class="btn btn-primary" onclick="addNewItem()">Agregar Ítem</button>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <h5>Suma Total: <span id="total-sum">0.00</span></h5>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 text-right">
                        <a class="btn btn-danger mx-1" href="{{ route('compra.index') }}">Cancelar</a>
                        <x-adminlte-button type="submit" label="Registrar" theme="primary" icon="fas fa-lg fa-save" />
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@push('js')
    <script src="{{ asset('jsQR.js') }}"></script>
    <script src="{{ asset('vendor/jquery-ui-1.13.2/jquery-ui.min.js') }}"></script>

    @if (session('success') && session('ultimoId') && session('estadov'))
        <script>
            $(function() {
                let compraId = {{ session('ultimoId') }};
                $.get(`/sisventa/public/venta/${compraId}/detalles`, function(response) {
                    let total = 0,
                        rows = '';
                    response.detalles.forEach(d => {
                        let monto = parseFloat(d.monto) - parseFloat(response.sumaMontos || 0);
                        rows +=
                            `<tr><td>${d.producto.descripcion}</td><td>${monto.toFixed(2)}</td></tr>`;
                        total += monto;
                    });
                    $('#tablaModBody').html(rows);
                    $('#montoAbonar, #montoAbonar1').val(total.toFixed(2));
                    $('#idfac').val(compraId);
                    $('#pagomontoModal').modal('show');
                }).fail(() => console.error('Error al cargar detalles.'));
            });
        </script>
    @endif

    <script>
        const itemsContainer = document.getElementById('items');
        const totalSumElement = document.getElementById('total-sum');
        let totalSum = 0;
        let inputgeneral = null;

        function mostrarOpc() {
            document.getElementById("cantpago").style.display = "block";
            document.getElementById("fechasButton").style.display = "inline";
        }

        function ocultarOpc() {
            document.getElementById("cantpago").style.display = "none";
            document.getElementById("fechasButton").style.display = "none";
        }

        document.addEventListener('keydown', e => {
            if (e.altKey && e.shiftKey && e.key === 'C') $('input[name="codigo1[]"]').first().focus();
            if (e.altKey && e.shiftKey && e.key === 'F') $('#fechaemision').focus();
        });

        // --- Funciones de validación y cálculo ---
        function sanitizeInput(input) {
            input.value = input.value.replace(/[^0-9.]/g, '').replace(/,/g, '.');
            actualizarSumaTotal();
        }

        function actualizarSumaTotal() {
            totalSum = 0;
            const rows = document.querySelectorAll('#items .item:not(:first-child) .d-flex');
            let itemN = 0;
            rows.forEach(row => {
                const qty = parseFloat(row.querySelector('input[name="cantidad[]"]').value) || 0;
                const priceOrig = parseFloat(row.querySelector('input[name="precioorig[]"]').value) || 0;
                const cmay = parseFloat(row.querySelector('input[name="cmayorista[]"]').value) || 0;
                const pmay = parseFloat(row.querySelector('input[name="pmayorista[]"]').value) || 0;
                const cond = parseFloat(row.querySelector('input[name="condicionv[]"]').value) || 0;
                let price = priceOrig;

                if (cmay > 0 && qty >= cmay) {
                    if (cond === 0) {
                        price = pmay / cmay;
                    } else {
                        const entero = Math.floor(qty / cmay);
                        const resto = qty % cmay;
                        price = (entero * pmay + resto * priceOrig) / qty;
                    }
                }

                const iva = parseFloat(row.querySelector('input[name="iva[]"]').value) || 0;
                const subtotal = qty * price;
                let exenta = 0,
                    cinco = 0,
                    diez = 0;

                if (iva === 0) exenta = subtotal;
                else if (iva === 5) cinco = subtotal * 1.05;
                else if (iva === 10) diez = subtotal * 1.10;

                row.querySelector('input[name="item[]"]').value = ++itemN;
                row.querySelector('input[name="exenta[]"]').value = exenta.toFixed(2);
                row.querySelector('input[name="cinco[]"]').value = cinco.toFixed(2);
                row.querySelector('input[name="diez[]"]').value = diez.toFixed(2);

                totalSum += (exenta + cinco + diez);
            });
            totalSumElement.textContent = totalSum.toFixed(2);
        }

        // --- Agregar Ítem ---
        function addNewItem() {
            const newItem = document.createElement("div");
            newItem.classList.add("item", "px-2", "py-1");

            newItem.innerHTML = `
    <style>
        .col-fixed {
            flex: 0 0 auto !important;
            min-width: 120px;
            max-width: 120px;
        }
        .col-small {
            flex: 0 0 auto !important;
            min-width: 80px;
            max-width: 80px;
        }
        .col-medium {
            flex: 0 0 auto !important;
            min-width: 140px;
            max-width: 140px;
        }
        .col-large {
            flex: 0 0 auto !important;
            min-width: 240px;
            max-width: 240px;
        }
        .col-small input,
        .col-fixed input,
        .col-medium input,
        .col-large input {
            width: 100% !important;
        }
    </style>

    <div class="d-flex flex-nowrap align-items-center py-2" style="white-space: nowrap;">

        <div class="px-1 col-small">
            <input type="number" name="item[]" class="form-control" value="1" readonly>
        </div>

        <div class="px-1 col-small">
            <input type="text" name="unidad[]" class="form-control" value="UNIDAD" required>
        </div>

        <input type="hidden" name="pmayorista[]">
        <input type="hidden" name="cmayorista[]">
        <input type="hidden" name="condicionv[]">
        <input type="hidden" name="precioorig[]">
        <input type="hidden" name="iva[]">
        <input type="hidden" name="codigo[]">
        <input type="hidden" name="productoid[]">

        <div class="px-1 col-medium">
            <input type="text" name="codigo1[]" class="form-control"
                   placeholder="Código" onchange="cambiarDescripcion(this)" required>
        </div>

        <div class="px-1 col-large">
            <input type="text" name="descripcion[]" class="autocomplete-producto form-control"
                   placeholder="Descripción" required style="font-size: 12px;">
        </div>

        <div class="px-1 col-fixed">
            <input type="text" name="cantidad[]" class="form-control"
                   placeholder="Cantidad" required step="any" oninput="sanitizeInput(this)">
        </div>

        <div class="px-1 col-medium">
            <input type="text" name="precio[]" class="form-control"
                   placeholder="Precio" required oninput="sanitizeInput(this)">
        </div>

        <div class="px-1 col-fixed">
            <input type="text" name="exenta[]" class="form-control"
                   value="0" placeholder="Exenta" disabled required oninput="sanitizeInput(this)">
        </div>

        <div class="px-1 col-small">
            <input type="text" name="cinco[]" class="form-control"
                   value="0" placeholder="5%" disabled required oninput="sanitizeInput(this)">
        </div>

        <div class="px-1 col-small">
            <input type="text" name="diez[]" class="form-control"
                   value="0" placeholder="10%" disabled required oninput="sanitizeInput(this)">
        </div>

        <div class="px-1 col-small">
            <button type="button"
                class="btn btn-outline-danger btn-sm w-100"
                onclick="this.closest('.item').remove(); actualizarSumaTotal();">
                <i class="fa fa-trash"></i>
            </button>
        </div>

    </div>
`;


            itemsContainer.appendChild(newItem);

            const codigoInput = newItem.querySelector('input[name="codigo1[]"]');
            codigoInput.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    newItem.querySelector('input[name="descripcion[]"]').focus();
                }
            });

            newItem.querySelectorAll('input').forEach(inp => {
                inp.addEventListener('input', actualizarSumaTotal);
                if (inp.name === 'descripcion[]') {
                    $(inp).autocomplete({
                        source: function(request, response) {
                            $.getJSON("{{ route('obtenerproducto') }}", {
                                term: request.term
                            }, data => {
                                response(Object.values(data).map(p => ({
                                    label: `${p.descripcion} (${p.stock})`,
                                    value: p.descripcion,
                                    codigo: p.codigo,
                                    id: p.id
                                })));
                            });
                        },
                        select: function(e, ui) {
                            traerCargarDatosProducto(ui.item.codigo, this);
                            $(this).closest('.d-flex').find('input[name="cantidad[]"]').focus();
                        }
                    });
                }
            });

            newItem.querySelector('input[name="cantidad[]"]').addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    newItem.querySelector('input[name="precio[]"]').focus().select();
                }
            });

            ['precio[]', 'exenta[]', 'cinco[]', 'diez[]'].forEach(name => {
                const inp = newItem.querySelector(`input[name="${name}"]`);
                inp.addEventListener('keydown', e => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        addNewItem();
                    }
                });
            });

            actualizarSumaTotal();
            codigoInput.focus();
        }

        // --- Cargar primer ítem al inicio ---
        document.addEventListener("DOMContentLoaded", () => {
            addNewItem();
        });

        // --- Funciones de producto, cliente, QR, etc. ---
        function cambiarCod() {
            const cod = $('input[name="cod_proveedor"]').val();
            const option = $(`#id_proveedor option[value="${cod}"]`);
            if (option.length) {
                $('#id_proveedor').val(cod).trigger('change');
            }
            actualizarNumeroDocumento();
        }

        function actualizarNumeroDocumento() {
            const selected = $('#id_proveedor option:selected');
            $('#numero_documento').val(selected.data('ruc'));
            $('#cod_proveedor').val(selected.val());
        }

        function cambiarDescripcion(input) {
            traerCargarDatosProducto(input.value, input);
        }

        function traerCargarDatosProducto(codigo, inputRef) {
            const condicion = document.querySelector('input[name="condicion"]:checked').value;
            const cantpago = condicion === 'CREDITO' ? $('#cantpago').val() : 1;

            $.post("{{ route('obtenercodproducto') }}", {
                codigo,
                cantpago,
                _token: '{{ csrf_token() }}'
            }, response => {
                if (response.producto) {
                    const p = response.producto;
                    const row = $(inputRef).closest('.d-flex');
                    row.find('input[name="descripcion[]"]').val(`${p.descripcion} (${Math.trunc(p.stock)})`);
                    row.find('input[name="codigo[]"]').val(p.id);
                    row.find('input[name="unidad[]"]').val(p.unidaddemedida?.descripcion || 'UNIDAD');
                    row.find('input[name="iva[]"]').val(p.impuesto);
                    row.find('input[name="precio[]"]').val(p.pventa);
                    row.find('input[name="precioorig[]"]').val(p.pventa);
                    row.find('input[name="pmayorista[]"]').val(p.pmayorista || 0);
                    row.find('input[name="cmayorista[]"]').val(p.cmayorista || 0);
                    row.find('input[name="condicionv[]"]').val(response.configuracion?.estado || 0);

                    const iva = p.impuesto;
                    if (iva === 10) {
                        row.find('input[name="cinco[]"], input[name="exenta[]"]').prop('disabled', true).val(0);
                        row.find('input[name="diez[]"]').prop('disabled', false);
                    } else if (iva === 5) {
                        row.find('input[name="diez[]"], input[name="exenta[]"]').prop('disabled', true).val(0);
                        row.find('input[name="cinco[]"]').prop('disabled', false);
                    } else {
                        row.find('input[name="cinco[]"], input[name="diez[]"]').prop('disabled', true).val(0);
                        row.find('input[name="exenta[]"]').prop('disabled', false);
                    }
                }
                actualizarSumaTotal();
            });
        }

        // --- Funciones de modal de pago ---
        function verifMonto() {
            let monto = parseFloat($('#montoAbonar').val()) || 0;
            let real = (parseFloat($('#montoAbonar1').val()) || 0) - (parseFloat($('#descuent').val()) || 0);
            monto = Math.max(0, Math.min(monto, real));
            $('#montoAbonar').val(monto.toFixed(2));
            $('#diferenciaAbonar').text((real - monto).toFixed(2));
        }

        function verifVuelto() {
            const abono = parseFloat($('#montoAbonar').val()) || 0;
            const efectivo = parseFloat($('#descUs').val()) || 0;
            $('#vuelto').text((efectivo - abono).toFixed(2));
        }

        function pagar1() {
            Swal.fire({
                title: '¿Seguro que desea realizar el pago?',
                text: `Monto a Abonar: ${$('#montoAbonar').val()}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, realizar pago',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    const id = $('#idfac').val();
                    const monto = $('#montoAbonar').val();
                    const desc = $('#descuent').val() || 0;
                    $.post(`/sisventa/public/caja/${id}/${monto}/${desc}`, {
                            _token: '{{ csrf_token() }}'
                        })
                        .done(res => {
                            Swal.fire('Éxito', res.success, 'success');
                            window.open("{{ url('/') }}/documentopagomontopdf/" + res.caja, '_blank');
                            $('#pagomontoModal').modal('hide');
                        })
                        .fail(() => Swal.fire('Error', 'Hubo un error al procesar la solicitud', 'error'));
                }
            });
        }

        // --- QR y cámara ---
        function leerQR(input) {
            inputgeneral = input;
            @if ($configuracionQR && $configuracionQR->estado == 1)
                navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: "environment"
                        }
                    })
                    .then(stream => {
                        const video = document.getElementById('preview');
                        video.srcObject = stream;
                        video.classList.remove('d-none');
                        const ctx = document.createElement('canvas').getContext('2d');
                        const interval = setInterval(() => {
                            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                                ctx.canvas.width = video.videoWidth;
                                ctx.canvas.height = video.videoHeight;
                                ctx.drawImage(video, 0, 0);
                                const code = jsQR(ctx.getImageData(0, 0, ctx.canvas.width, ctx.canvas.height)
                                    .data, ctx.canvas.width, ctx.canvas.height);
                                if (code) {
                                    const partes = code.data.split('/');
                                    if (partes.length > 6) {
                                        input.value = partes.slice(6).join('/');
                                        cambiarDescripcion(input);
                                        closeModal();
                                        clearInterval(interval);
                                    }
                                }
                            }
                        }, 500);
                    });
            @endif
        }

        function closeModal() {
            const video = document.getElementById('preview');
            if (video.srcObject) {
                video.srcObject.getTracks().forEach(t => t.stop());
                video.srcObject = null;
            }
            video.classList.add('d-none');
        }
    </script>
@endpush
