@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 custom-heading">Registrar Compra</h1>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/jquery-ui-1.13.2/jquery-ui.min.css') }}">
    <style>
        /* 🔥 Contenedor que obliga al scroll sí o sí */
        .scroll-area {
            display: block !important;
            width: 100% !important;
            overflow-x: auto !important;
            overflow-y: hidden;
            white-space: nowrap !important;
            min-width: 1200px;
            border: 1px solid #dee2e6;
            border-radius: 12px;
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
            background: #0d6efd;
            color: #fff;
            font-weight: bold;
            padding: 10px 0;
            border-radius: 12px 12px 0 0;
        }

        /* Ajustes para los inputs dentro de las columnas */
        .scroll-area .form-control {
            width: 100%;
            height: 38px;
            padding: 6px 12px;
            font-size: 14px;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('compra.store') }}" method="post" autocomplete="off"
                        onkeypress="return event.keyCode != 13;">
                        @csrf
                        @method('POST')

                        <div class="row mb-3">
                            <div class="form-group col-md-2">
                                <label for="fechaemision">FECHA DE EMISIÓN</label>
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
                                                checked>
                                            <label class="form-check-label">Contado</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="condicion" value="CREDITO">
                                            <label class="form-check-label">Crédito</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <x-adminlte-input type="hidden" id="proveedor_id" name="proveedor_id" />
                        </div>

                        <x-adminlte-card title="Proveedor" class="text-primary mb-3">
                            <div class="row">
                                <x-adminlte-input type="number" id="cod_proveedor" name="cod_proveedor"
                                    onchange="cambiarCod()" placeholder="Código" label="COD." fgroup-class="col-md-1"
                                    required />
                                <x-adminlte-select2 name="id_proveedor" id="id_proveedor" label="RAZON SOCIAL"
                                    data-placeholder="Seleccionar un proveedor..." fgroup-class="col-md-8"
                                    onchange="actualizarNumeroDocumento()">
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text bg-gradient-primary">
                                            <i class="fas fa-truck"></i>
                                        </div>
                                    </x-slot>
                                    @foreach ($proveedor as $item)
                                        <option value="{{ $item->id }}" data-ruc="{{ $item->ruc }}">
                                            {{ $item->razonsocial }}</option>
                                    @endforeach
                                </x-adminlte-select2>
                                <x-adminlte-input type="text" id="numero_documento" name="numero_documento"
                                    placeholder="RUC" label="RUC" readonly fgroup-class="col-md-2" />
                            </div>
                        </x-adminlte-card>

                        <hr>

                        <!-- 🔥 CONTENEDOR QUE GARANTIZA EL SCROLL -->
                        <div class="scroll-area">
                            <div class="header-row">
                                <div class="col-small">ITEM</div>
                                <div class="col-small">UNDM</div>
                                <div class="col-medium">CÓDIGO</div>
                                <div class="col-fixed">CANTIDAD</div>
                                <div class="col-large">DESCRIPCIÓN</div>
                                <div class="col-medium">PRECIO UNIT.</div>
                                <div class="col-fixed">PRECIO TOTAL</div>
                                <div class="col-small">IVA</div>
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
                                <x-adminlte-button type="submit" label="Registrar" theme="primary"
                                    icon="fas fa-lg fa-save" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
    <script src="{{ asset('vendor/jquery-ui-1.13.2/jquery-ui.min.js') }}"></script>
    <script>
        const itemsContainer = document.getElementById('items');
        const totalSumElement = document.getElementById('total-sum');
        let totalSum = 0;

        // Navegación con teclado
        document.addEventListener('keydown', function(e) {
            if (e.altKey && e.shiftKey && e.key === 'C') {
                e.preventDefault();
                document.querySelector('input[name="codigo1[]"]')?.focus();
            }
            if (e.altKey && e.shiftKey && e.key === 'F') {
                e.preventDefault();
                document.getElementById('fechaemision').focus();
            }
        });

        document.querySelectorAll(
                'input[name="fechaemision"], input[name="nrofactura"], input[name="timbrado"], input[name="cod_proveedor"]')
            .forEach((input, index, array) => {
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        if (array[index + 1]) {
                            array[index + 1].focus();
                        } else {
                            document.querySelector('input[name="codigo1[]"]')?.focus();
                        }
                    }
                });
            });

        function sanitizeInput(input) {
            let value = input.value;
            value = value.replace(/[^0-9.]/g, '').replace(/,/g, '.');
            input.value = value;
            actualizarSumaTotal();
        }

        function cambiarCod() {
            const cod = document.querySelector('input[name="cod_proveedor"]').value;
            const select = document.getElementById('id_proveedor');
            const options = select.options;

            for (let i = 0; i < options.length; i++) {
                if (options[i].value === cod) {
                    select.value = cod;
                    $('#id_proveedor').val(cod).trigger('change.select2');
                    actualizarNumeroDocumento();
                    return;
                }
            }
            actualizarNumeroDocumento();
        }

        function actualizarNumeroDocumento() {
            const select = document.getElementById('id_proveedor');
            const selectedOption = select.options[select.selectedIndex];
            document.getElementById('numero_documento').value = selectedOption.getAttribute('data-ruc');
            document.getElementById('cod_proveedor').value = selectedOption.value;
        }

        function actualizarSumaTotal() {
            totalSum = 0;
            const rows = document.querySelectorAll('#items .item');

            rows.forEach((item, index) => {
                const qtyInput = item.querySelector('input[name="cantidad[]"]');
                const priceInput = item.querySelector('input[name="precio[]"]');
                const ivaInput = item.querySelector('input[name="iva[]"]');
                const itemInput = item.querySelector('input[name="item[]"]');
                const totalInput = item.querySelector('input[name="total[]"]');
                const taxBadge = item.querySelector('.tax-badge');

                if (!qtyInput || !priceInput || !ivaInput) return;

                const qty = parseFloat(qtyInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                const iva = parseFloat(ivaInput.value) || 0;
                const subtotal = qty * price;

                // Actualizar número de ítem
                if (itemInput) itemInput.value = index + 1;

                // Actualizar total y badge de IVA
                if (totalInput) totalInput.value = subtotal.toFixed(2);
                if (taxBadge) taxBadge.textContent = iva + '%';

                totalSum += subtotal;
            });

            totalSumElement.textContent = totalSum.toFixed(2);
        }

        function addNewItem() {
            const newItem = document.createElement("div");
            newItem.classList.add("item", "px-2", "py-1");

            newItem.innerHTML = `
                <div class="d-flex flex-nowrap align-items-center py-2" style="white-space: nowrap;">
                    <div class="px-1 col-small">
                        <input type="number" name="item[]" class="form-control" value="1" readonly>
                    </div>
                    
                    <div class="px-1 col-small">
                        <input type="text" name="unidad[]" class="form-control" value="UNIDAD" required>
                    </div>

                    <input type="hidden" name="iva[]">
                    <input type="hidden" name="codigo[]">
                    <input type="hidden" name="productoid[]">
                    <input type="hidden" name="pmayorista[]">
                    <input type="hidden" name="cmayorista[]">
                    <input type="hidden" name="configuracionv[]">
                    <input type="hidden" name="precioorig[]">
                    <input type="hidden" name="tipo_impuesto[]">

                    <div class="px-1 col-medium">
                        <input type="text" name="codigo1[]" class="form-control"
                               placeholder="Código" onchange="cambiarDescripcion(this)" required>
                    </div>

                    <div class="px-1 col-fixed">
                        <input type="text" name="cantidad[]" class="form-control"
                               placeholder="Cantidad" required step="any" oninput="sanitizeInput(this)">
                    </div>

                    <div class="px-1 col-large">
                        <input type="text" name="descripcion[]" class="autocomplete-producto form-control"
                               placeholder="Descripción" required style="font-size: 12px;">
                    </div>

                    <div class="px-1 col-medium">
                        <input type="text" name="precio[]" class="form-control"
                               placeholder="Precio" required oninput="sanitizeInput(this)">
                    </div>

                    <div class="px-1 col-fixed">
                        <input type="text" name="total[]" class="form-control"
                               value="0" placeholder="Total" readonly>
                    </div>

                    <div class="px-1 col-small text-center">
                        <span class="badge bg-info tax-badge" style="font-size:13px; padding:8px 6px; display:inline-block; width:100%;">0%</span>
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

            // Configurar eventos de teclado
            const codigoInput = newItem.querySelector('input[name="codigo1[]"]');
            codigoInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    newItem.querySelector('input[name="cantidad[]"]').focus();
                }
            });

            const cantidadInput = newItem.querySelector('input[name="cantidad[]"]');
            cantidadInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    newItem.querySelector('input[name="descripcion[]"]').focus();
                }
            });

            const descripcionInput = newItem.querySelector('input[name="descripcion[]"]');
            descripcionInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    newItem.querySelector('input[name="precio[]"]').focus();
                }
            });

            const precioInput = newItem.querySelector('input[name="precio[]"]');
            precioInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addNewItem();
                }
            });

            newItem.querySelectorAll('input').forEach(inp => {
                inp.addEventListener('input', actualizarSumaTotal);
            });

            actualizarSumaTotal();
            codigoInput.focus();
        }

        function cambiarDescripcion(input) {
            traerCargarDatosProducto(input.value, input);
        }

        function traerCargarDatosProducto(codigo, inputRef) {
            const condicion = document.querySelector('input[name="condicion"]:checked').value;
            const cantpago = condicion === 'CREDITO' ? 1 : 1; // Ajustar según necesidad

            $.post("{{ route('obtenercodproducto') }}", {
                codigo: codigo,
                cantpago: cantpago,
                _token: '{{ csrf_token() }}'
            }, function(response) {
                if (response.producto) {
                    const p = response.producto;
                    const row = $(inputRef).closest('.d-flex');

                    row.find('input[name="descripcion[]"]').val(p.descripcion + ' (' + Math.trunc(p.stock) + ')');
                    row.find('input[name="codigo[]"]').val(p.id);
                    row.find('input[name="codigo1[]"]').val(p.codigo);
                    row.find('input[name="unidad[]"]').val(p.unidaddemedida?.descripcion || 'UNIDAD');
                    row.find('input[name="iva[]"]').val(p.impuesto);
                    row.find('input[name="precio[]"]').val(p.pventa);
                    row.find('input[name="precioorig[]"]').val(p.pventa);
                    row.find('input[name="tipo_impuesto[]"]').val(p.id_impuesto);
                    console.log(row.find('input[name="tipo_impuesto[]"]').val());
                    
                    row.find('input[name="pmayorista[]"]').val(p.pmayorista || 0);
                    row.find('input[name="cmayorista[]"]').val(p.cmayorista || 0);

                    const config = response.configuracion || {};
                    row.find('input[name="configuracionv[]"]').val(config.estado || 0);
                }
                actualizarSumaTotal();
            }).fail(function(error) {
                console.error('Error en la petición AJAX:', error);
            });
        }

        // Autocomplete con event delegation (funciona en items agregados dinámicamente)
        $(document).on('focus', '.autocomplete-producto', function() {
            if ($(this).data("ui-autocomplete")) return;

            $(this).autocomplete({
                minLength: 0,
                source: function(request, response) {
                    $.ajax({
                        url: "{{ route('obtenerproducto') }}",
                        dataType: "json",
                        data: {
                            term: request.term
                        },
                        success: function(data) {
                            response($.map(data, function(p) {
                                return {
                                    label: p.descripcion + ' (' + Math.trunc(p.stock) + ')',
                                    value: p.descripcion,
                                    codigo: p.codigo,
                                    id: p.id
                                };
                            }));
                        }
                    });
                },
                select: function(event, ui) {
                    traerCargarDatosProducto(ui.item.codigo, this);
                    $(this).closest('.d-flex').find('input[name="cantidad[]"]').focus();
                },
                autoFocus: true
            });
        });

        // Inicializar primer ítem
        document.addEventListener("DOMContentLoaded", function() {
            addNewItem();
            // 🔵 PROVEEDOR POR DEFECTO
            const proveedorDefault = 1; // <-- ID del proveedor que quieres cargar

            $('#id_proveedor').val(proveedorDefault).trigger('change.select2');
            document.getElementById('cod_proveedor').value = proveedorDefault;

            actualizarNumeroDocumento();
        });
    </script>
@endpush
