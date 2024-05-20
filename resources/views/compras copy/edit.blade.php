@extends('adminlte::page')



@section('content_header')
    <h1 class="m-0 custom-heading">Editar Orden de Compra</h1>
@stop



@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" style="background-color:#D3D4D5;">
                <div class="card-body">
                    <form id="miFormulario" action="{{ route('compras.update', ['compra' => $cabecera->id]) }}" method="POST"
                        autocomplete="off">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="row col-10">

                                <x-adminlte-input type="number" id="numero_orden" name="numero_orden"
                                    placeholder="Nº de ORDEN" label="ORDEN Nº" value="{{$cabecera->numero_orden }}"  fgroup-class="col-md-2" required />
                                {{-- Fecha de Emisión de la Factura. --}}
                                <x-adminlte-input-date label="FECHA ORDEN" name="fecha" id="fecha" type="date"
                                value="{{ date('Y-m-d', strtotime($cabecera->fecha)) }}"  fgroup-class="col-md-2" required />


                                {{-- Nombre o Razón Social del Emisor (nombre de la empresa o persona que emite la factura). --}}

                                {{-- Número de Factura. --}}
                                <x-adminlte-input-date label="PLAZO ENTREGA" name="plazo_entrega" id="plazo_entrega"
                                    type="date"  value="{{ date('Y-m-d', strtotime($cabecera->plazo_entrega)) }}" fgroup-class="col-md-2" required />
                                <x-adminlte-input-date label="FECHA OFERTA" name="fecha_oferta" id="fecha_oferta"
                                    type="date"  value="{{ date('Y-m-d', strtotime($cabecera->fecha_oferta)) }}" fgroup-class="col-md-2" required />

                            </div>
                            <div class="row col-10">

                                {{-- Nombre o Razón Social del Emisor (nombre de la empresa o persona que emite la factura). --}}
                                <x-adminlte-select2 name="id_proveedor" id="id_proveedor" label="PROVEEDOR"
                                    data-placeholder="Seleccionar un proveedor..." fgroup-class="col-md-4"
                                    onchange="actualizarNumeroDocumento()">
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text bg-gradient-red">
                                            <i class="fas fa-truck"></i>
                                        </div>
                                    </x-slot>
                                    
                                    @foreach ($proveedor as $item)
                                        <option value="{{ $item->id }}" data-ruc="{{ $item->ruc }}" {{ ($cabecera->id_proveedor == $item->id) ? 'selected' : '';}}>
                                            {{ $item->razonsocial }}</option>
                                    @endforeach
                                </x-adminlte-select2>
                                {{-- Número de Identificación Tributaria del Emisor (RUC). --}}
                                <x-adminlte-input type="text" id="numero_documento" name="numero_documento"
                                    placeholder="RUC" label="RUC" readonly fgroup-class="col-md-2" />

                                <x-adminlte-select2 name="id_secciones" id="id_secciones" label="SECCION"
                                    data-placeholder="Seleccionar una seccion..." fgroup-class="col-md-4">
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text bg-gradient-primary">
                                            <i class="fas fa-building"></i>
                                        </div>
                                    </x-slot>
                                    @foreach ($seccion as $item)
                                        <option value="{{ $item->id }}" {{ ($cabecera->id_proveedor == $item->id) ? 'selected' : '';}}>
                                            {{ $item->codigo . ' ' . $item->descripcion }}</option>
                                    @endforeach
                                </x-adminlte-select2>

                            </div>
                            <div class="row col-10">
                                <x-adminlte-input type="number" value="{{$notarec->numero_factura}}" id="numero_factura" name="numero_factura"
                                    placeholder="Nº de FACTURA" label="Factura Nº" fgroup-class="col-md-2" required />
                                <x-adminlte-input-date label="FECHA FACTURA" name="fecha_factura" id="fecha_factura"
                                    type="date" value="{{ date('Y-m-d', strtotime($notarec->fecha_factura)) }}" fgroup-class="col-md-2" required />
                                <x-adminlte-input-date label="ENTIDAD" name="entidad" id="entidad" type="text"
                                value="{{$notarec->entidad}}" fgroup-class="col-md-6" required />
                            </div>
                            <div class="row col-10">
                                <x-adminlte-input type="number" id="numero_remision" name="numero_remision"
                                    placeholder="Nº de Remision" label="REMISION Nº" fgroup-class="col-md-2" value="{{$notarec->numero_nota_remision}}" required />
                                <x-adminlte-input type="number" id="numero_recepcion" name="numero_recepcion"
                                    placeholder="Nº de Recepcion" label="RECEPCION Nº" value="{{$notarec->numero}}" fgroup-class="col-md-2" required />
                                <x-adminlte-input type="number" id="numero_nota_pedido_interno"
                                    name="numero_nota_pedido_interno" value="{{$notarec->numero_nota}}" placeholder="Nº de Nota" label="NOTA Nº"
                                    fgroup-class="col-md-2" required />
                                <x-adminlte-input type="number" id="numero_solicitud" name="numero_solicitud"
                                    placeholder="Nº de solicitud" value="{{$solicbienes->numero_solicitud}}" label="SOLIC. BIENES Nº" fgroup-class="col-md-2"
                                    required />
                            </div>


                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-8">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12">
                                                <x-adminlte-select2 name="producto" id="producto" label="AGREGAR OBJETO"
                                                    data-placeholder="Seleccionar un producto..."
                                                    fgroup-class="col-md-12">
                                                    <x-slot name="prependSlot">
                                                        <div class="input-group-text bg-gradient-success">
                                                            <i class="fas fa-box"></i>
                                                        </div>
                                                    </x-slot>
                                                    <x-slot name="appendSlot">
                                                        <x-adminlte-button theme="outline-success" id="btn-add-item"
                                                            data-toggle="modal" data-target="#addCatModal"
                                                            icon="fas fa-lg fa-plus text-success" />
                                                    </x-slot>
                                                    @foreach ($objeto as $item)
                                                        <option value="{{ $item->id }}"
                                                            data-descripcion="{{ $item->objeto }}">
                                                            {{ $item->objeto }}</option>
                                                    @endforeach
                                                </x-adminlte-select2>
                                            </div>
                                            <div class="col-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="form-check">
                                                        <input type="radio" class="form-check-input" id="inlineRadio1"
                                                            name="tipocompra" value="0">Exentas
                                                    </div>
                                                    <div class="form-check ml-2">
                                                        <input type="radio" class="form-check-input" id="inlineRadio2"
                                                            name="tipocompra" value="1">5%
                                                    </div>
                                                    <div class="form-check ml-2">
                                                        <input type="radio" class="form-check-input" id="inlineRadio3"
                                                            name="tipocompra" value="2" checked>10%
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <div class="items">
                            <div class="item" style="background-color: yellow;">
                                <div class="row ml-2">
                                    <label for="" class="col-1">ITEM</label>
                                    <label for="" class="col-1">UNDM</label>
                                    <label for="" class="col-1">CÓDIGO</label>
                                    <label for="" class="col-1">CANTIDAD</label>
                                    <label for="" class="col-3">DESCRIPCION</label>
                                    <label for="" class="col-1">PRECIO UNITARIO</label>
                                    <label for="" class="col-1">EXENTAS</label>
                                    <label for="" class="col-1">5%</label>
                                    <label for="" class="col-1">10%</label>
                                </div>
                            </div>
                        </div>
                        @foreach ($detalle as $item)
                            <div class="row ml-1">
                                <input type="number" name="item[]" class="codigo_id form-control col-1"
                                    placeholder="Código" value="{{ $item->item }}" required readonly>
                                <input type="text" name="unidad[]" value="{{ $item->unidad }}"
                                    class="codigo_id form-control col-1" placeholder="U. medida" value="" required>
                                <input type="hidden" name="iva[]" class="codigo_id form-control col-2"
                                    placeholder="Código" value="{{ $item->porcentaje_iva }}" required readonly>
                                <input type="text" name="codigo[]" class="codigo_id form-control col-1"
                                    placeholder="Código" value="{{ $item->id_objeto }}" required readonly>
                                <input type="text" name="cantidad[]" step="any" class="form-control col-1"
                                    placeholder="Cantidad" value ="{{ $item->cantidad }}" required required required
                                    oninput="sanitizeInput(this)">
                                <input type="text" name="descripcion[]"
                                    class="autocomplete-producto form-control col-3 " placeholder="Descripcion"
                                    value="{{ $item->descripcion }}" required readonly
                                    style="font-size: 12px;>
                            <input type="hidden"
                                    name="productoid[]" class="producto_id form-control col-2" required>
                                <input type="text" name="precio[]" value="{{ $item->precio_unitario }}"
                                    class="form-control col-1" placeholder="Precio " required
                                    oninput="sanitizeInput(this)">
                                <input type="text" name="exenta[]"
                                    value="{{ $item->porcentaje_iva == 0 ? $item->precio : 0 }}"
                                    class="form-control col-1" placeholder="Exenta" required
                                    oninput="sanitizeInput(this)" {{ $item->porcentaje_iva != 0 ? 'readonly' : '' }}>
                                <input type="number" name="cinco[]"
                                    value="{{ $item->porcentaje_iva == 1 ? $item->precio : 0 }}"
                                    class="form-control col-1" placeholder="iva 5%" required
                                    {{ $item->porcentaje_iva != 1 ? 'readonly' : '' }}>
                                <input type="text" name="diez[]"
                                    value="{{ $item->porcentaje_iva == 2 ? $item->precio : 0 }}"
                                    class="form-control col-1" placeholder="iva 10%" required
                                    oninput="sanitizeInput(this)" {{ $item->porcentaje_iva != 2 ? 'readonly' : '' }}>

                                <button class="btn-remove btn btn-outline-danger ml-2" type="button"
                                    onclick="removeItem(this)"><i class="fa fa-trash" aria-hidden="true"></i></button>

                            </div>




                            <div class="row ml-2">

                                <!-- Reemplaza estas etiquetas por las correspondientes a Exentas, 5% y 10% -->
                                <label class="col-1">{{ $item->exentas }}</label>
                                <label class="col-1">{{ $item->iva_5 }}</label>
                                <label class="col-1">{{ $item->iva_10 }}</label>
                            </div>
                        @endforeach




                        <div class="row">
                            <!-- Agrega este elemento para mostrar la suma total -->
                            <div>Suma Total: <span id="total-sum">0</span></div>
                            <div class="form-group col-md-12">
                                <a class="btn btn-danger mx-1" style="float: right;"
                                    href="{{ route('compras.index') }}">Cancelar</a>
                                <x-adminlte-button class="btn-group" style="float: right;" type="submit" label="Grabar"
                                    theme="primary" icon="fas fa-fw fa-lg fa-save" />
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
    <script>
        $(document).ready(function() {
            $('#miFormulario').on('keydown', 'input', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault(); // Evita que el formulario se envíe
                }
            });
        });

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
        const button = document.getElementById("btn-add-item");
        button.addEventListener("focus", function() {
            button.style.backgroundColor = "#07F700";
        });

        button.addEventListener("blur", function() {
            button.style.backgroundColor = "#C9CFC9";
        });




        // Script para seleccionar RUC
        actualizarNumeroDocumento();

        function actualizarNumeroDocumento() {
            var select2 = document.getElementById("id_proveedor");
            var numeroDocumentoInput = document.getElementById("numero_documento");
            var selectedOption = select2.options[select2.selectedIndex];
            var numeroDocumento = selectedOption.getAttribute("data-ruc");
            numeroDocumentoInput.value = numeroDocumento;
        }
        // Script para agregar y eliminar dinámicamente ítems de compra
        // document.addEventListener("DOMContentLoaded", function() {
        const btnAddItem = document.getElementById("btn-add-item");
        const itemsContainer = document.querySelector('.items');

        const totalSumElement = document.getElementById("total-sum");
        let totalSum = 0;


        // Función para agregar un nuevo ítem de compra
        function addNewItem() {
            var radioSeleccionado = document.querySelector('input[name="tipocompra"]:checked');
            var tipocompra = radioSeleccionado.value;


            // Obtener el elemento select por su ID
            const selectElement = document.getElementById("producto");

            // Obtener el valor y la descripción del elemento seleccionado
            const selectedValue = selectElement.value;
            const selectedDescripcion = selectElement.options[selectElement.selectedIndex].getAttribute(
                "data-descripcion");


            const newItem = document.createElement("div");
            newItem.classList.add("item");
            //desc=selectedDescripcion.charAt(0).toUpperCase() + selectedDescripcion.slice(1).toLowerCase();

            exent =
                '<input type="number" name="exenta[]" value="0" class="form-control col-1" placeholder="Exenta" required readonly>';
            cinc =
                '<input type="number" name="cinco[]" value="0" class="form-control col-1" placeholder="iva 5%" required readonly>';
            die =
                '<input type="number" name="diez[]" value="0" class="form-control col-1" placeholder="iva 10%" required readonly>';

            switch (tipocompra) {
                case '0':
                    exent =
                        '<input type="text" name="exenta[]" value="0" class="form-control col-1" placeholder="Exenta" required oninput="sanitizeInput(this)">';
                    break;
                case '1':
                    cinc =
                        '<input type="text" name="cinco[]" value="0" class="form-control col-1" placeholder="iva 5%" required oninput="sanitizeInput(this)">';
                    break;
                case '2':
                    die =
                        '<input type="text" name="diez[]" value="0" class="form-control col-1" placeholder="iva 10%" required oninput="sanitizeInput(this)">';
                    break;
            }
            newItem.innerHTML = `
                <div class="row ml-1">
                                    <input type="number" name="item[]" class="codigo_id form-control col-1"
                                    placeholder="Código" value="" required readonly>
                                    <input type="text" name="unidad[]" value="UNIDAD" class="codigo_id form-control col-1"
                                    placeholder="U. medida" value="" required >
                                    <input type="hidden" name="iva[]" class="codigo_id form-control col-2"
                                    placeholder="Código" value="` + tipocompra + `" required readonly>
                                    <input type="text" name="codigo[]" class="codigo_id form-control col-1"
                                    placeholder="Código" value="` + selectedValue + `" required readonly >
                                    <input type="text" name="cantidad[]" step="any" class="form-control col-1"
                                    placeholder="Cantidad"  value ="1"  required required required oninput="sanitizeInput(this)">
                                    <input type="text" name="descripcion[]" class="autocomplete-producto form-control col-3 "
                                        placeholder="Descripcion" value="` + selectedDescripcion + `" required readonly style="font-size: 12px;>
                                    <input type="hidden" name="productoid[]" class="producto_id form-control col-2"
                                        required>
                                    <input type="text" name="precio[]" value="0" class="form-control col-1" placeholder="Precio "
                                        required oninput="sanitizeInput(this)">
                                        ` + exent + cinc + die + `
                                        <button class="btn-remove btn btn-outline-danger ml-2" type="button"
                                onclick="removeItem(this)"><i class="fa fa-trash" aria-hidden="true"></i></button>
                                </div>
            `;
            itemsContainer.appendChild(newItem);

            // Agregar el evento click para eliminar el ítem
            const btnRemove = newItem.querySelector(".btn-remove");
            // btnRemove.addEventListener("click", function() {
            //     removeItem(newItem);
            // });

            const priceInput = newItem.querySelector('input[name="precio[]"]');
            priceInput.addEventListener("input", actualizarSumaTotal);

            const cantiInput = newItem.querySelector('input[name="cantidad[]"]');
            cantiInput.addEventListener("input", actualizarSumaTotal);
            cantiInput.focus();
            actualizarSumaTotal()

        }

        actualizarSumaTotal();

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
            // Itera a través de los elementos utilizando un bucle for
            itemN = 0;
            for (let i = 0; i < priceInputs.length; i++) {
                const input = priceInputs[i];
                const price = parseFloat(input.value) || 0;

                const cantidadV = cantidadInputs[i];
                const cantidad = parseFloat(cantidadV.value) || 0;

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
                    case 1:
                        exentaV.value = 0;
                        cincoporc = 0;
                        tot = tot + cincoporc
                        cincoV.value = tot;
                        diezV.value = 0;
                        break;
                    case 2:
                        exentaV.value = 0;
                        cincoV.value = 0;
                        diezporc = 0;
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





        // Agregar el evento click para agregar un nuevo ítem
        btnAddItem.addEventListener("click", addNewItem);

        function removeItem(button) {
            const item = button.closest('.row');
            if (item) {
                item.remove();

            }
            actualizarSumaTotal();
        }
        //});
    </script>
@endpush
