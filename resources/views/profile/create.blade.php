@extends('adminlte::page')



@section('content_header')
    <h1 class="m-0 custom-heading">Registrar Compra</h1>
@stop
@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/jquery-ui-1.13.2/jquery-ui.min.css') }}">
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('compra.store') }}" method="post" autocomplete="off">
                        @csrf
                        @method('POST')
                        {{-- 'id', 'codigo', 'descripcion', 'detalle', 'id_categoria', 'id_estado','pcosto', 'pventa', 'observacion' --}}

                        <div class="row">
                            {{-- With Label --}}
                            @php
                                $config1 = ['format' => 'DD-MM-YYYY'];
                            @endphp
                            <div class="form-group">
                                <label for="fechaemision">FECHA DE EMISIÓN</label>
                                <input type="date" class="form-control" id="fechaemision" name="fechaemision"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>


                            <x-adminlte-input type="text" id="nrofactura" name="nrofactura" label="Factura Nº"
                                fgroup-class="col-md-2" required/>
                            <x-adminlte-input type="text" id="timbrado" name="timbrado" label="Timbrado Nº"
                                fgroup-class="col-md-2" required/>
                            <div class="card" style="width: 14rem;margin-top: -18px">
                                <div class="card-body">
                                    <label for="">CONDICIÓN DE COMPRA</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="condicion" id="inlineRadio1"
                                            value="CONTADO" checked>
                                        <label class="form-check-label" for="inlineRadio1">Contado</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="condicion" id="inlineRadio2"
                                            value="CREDITO">
                                        <label class="form-check-label" for="inlineRadio2">Crédito</label>
                                    </div>
                                </div>

                            </div>
                            <x-adminlte-input type="hidden" id="proveedor_id" name="proveedor_id" />
                        </div>
                        <div class="row">
                            <x-adminlte-card title="Proveedor" class="text-primary">

                                <div class="row">
                                    <x-adminlte-input type="number" id="cod_proveedor" name="cod_proveedor"
                                        onchange="cambiarCod()" placeholder="Codigo" label="COD." fgroup-class="col-md-1"
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
                                            <option value={{ $item->id }} data-ruc="{{ $item->ruc }}">
                                                {{ $item->razonsocial }}</option>
                                        @endforeach
                                    </x-adminlte-select2>
                                    <x-adminlte-input type="text" id="numero_documento" name="numero_documento"
                                        placeholder="RUC" label="RUC" readonly fgroup-class="col-md-2" />
                                </div>
                            </x-adminlte-card>
                        </div>
                        <hr>

                        <div id="items">
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

                        <button onclick="addNewItem()" class="btn btn-primary mt-2" type="button">Agregar Ítem</button>

                        <!-- Agrega este elemento para mostrar la suma total -->
                    <div>Suma Total: <span id="total-sum">0</span></div>
                        <div class="row">
                            <div class="form-group col-md-12">
                                <a class="btn btn-danger mx-1" style="float: right;"
                                    href="{{ route('compra.index') }}">Cancelar</a>
                                <x-adminlte-button class="btn-group" style="float: right;" type="submit"
                                    label="Registrar" theme="primary" icon="fas fa-lg fa-save" />
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
                minlength: 3,
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
                                    label: data[key].descripcion,
                                    value: data[key].descripcion,
                                    codigo: data[key].codigo,
                                    id: data[key].id,
                                };
                            });
                            response(filteredData);
                        },
                        response: function(event, ui) {
                            if (!ui.content.length) {
                                console.log('hola');
                            }
                        }
                    });
                },
                // select: function(event, ui) {
                //     const productoidField = $(this).closest('.item').find('.producto_id');
                //     productoidField.val(ui.item.id);
                //     const codigoidField = $(this).closest('.item').find('.codigo_id');
                //     codigoidField.val(ui.item.codigo);
                //     console.log(ui.item.id);
                // }
            });
        });

        // Script para agregar y eliminar dinámicamente ítems de compra
        const itemsContainer = document.getElementById('items');
        const totalSumElement = document.getElementById('total-sum');
        let totalSum = 0;
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
                                    <input type="hidden" name="iva[]" class="codigo_id form-control col-2"
                                    placeholder="Código" value="" required readonly>
                                    <input type="hidden" name="codigo[]" class="codigo_id form-control col-1" required>
                                    <input type="text" name="codigo1[]" class="codigo_id form-control col-1"
                                    placeholder="Código" onchange="cambiarDescripcion(this)" value="" required>
                                    <input type="text" name="cantidad[]" step="any" class="form-control col-1"
                                    placeholder="Cantidad"  value =""  required oninput="sanitizeInput(this)" >
                                    <input type="text" name="descripcion[]" class="autocomplete-producto form-control col-3 "
                                        placeholder="Descripcion" onchange="cambiarCodigo(this)" value="" required style="font-size: 12px;>
                                    <input type="hidden" name="productoid[]" class="producto_id form-control col-2"
                                        required>
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
                    const cantidadInput = newItem.querySelector('input[name="cantidad[]"]');
                    cantidadInput.focus();
                }
            });
            const cantidadInput = newItem.querySelector('input[name="cantidad[]"]');
            cantidadInput.addEventListener('keydown', function(e) {
                // Verifica si la tecla presionada es "Enter"
                if (e.key === 'Enter') {
                    // Evita el envío del formulario
                    e.preventDefault();
                    // Enfoca en el campo de producto
                    const descripcionInput = newItem.querySelector('input[name="descripcion[]"]');
                    descripcionInput.focus();
                }
            });
            const descripcionInput = newItem.querySelector('input[name="descripcion[]"]');
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
                    case 5:
                        exentaV.value = 0;
                        cincoporc = 0;
                        tot = tot + cincoporc
                        cincoV.value = tot;
                        diezV.value = 0;
                        break;
                    case 10:
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



        function removeItem(itemToRemove) {
            itemsContainer.removeChild(itemToRemove);
            actualizarSumaTotal();
        }

        function cambiarDescripcion(inputCodigo) {
            var codigoValue = inputCodigo.value;
            traerCargarDatosProducto(codigoValue,inputCodigo);
        }

        function cambiarCodigo(inputProducto) {

            // Obtén el valor del producto desde el elemento actual
            var productoId = '';
            if ($(inputProducto).data('ui-autocomplete').selectedItem) {
                productoId = $(inputProducto).data('ui-autocomplete').selectedItem.codigo;
            }
            traerCargarDatosProducto(productoId,inputProducto);
            actualizarSumaTotal();

        }

        function traerCargarDatosProducto(codigoValue,inputCodigo) {
            $.ajax({
                url: '{{ route('obtenercodproducto') }}',
                method: 'POST',
                data: {
                    codigo: codigoValue,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response && response.id && response.descripcion) {
                        var descripcion = response.descripcion;
                        var id = response.id;
                        var iva = response.impuesto;
                        var unidadMedida = response.unidaddemedida.descripcion;
                        var precio = response.pcosto;
                        $(inputCodigo).closest('.row').find('input[name="descripcion[]"]').val(descripcion);
                        $(inputCodigo).closest('.row').find('input[name="codigo[]"]').val(id);
                        $(inputCodigo).closest('.row').find('input[name="unidad[]"]').val(unidadMedida);
                        $(inputCodigo).closest('.row').find('input[name="iva[]"]').val(iva);
                        $(inputCodigo).closest('.row').find('input[name="precio[]"]').val(precio);
                        $(inputCodigo).closest('.row').find('input[name="codigo1[]"]').val(codigoValue);
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
                        // Si la respuesta no tiene los datos esperados, limpia el input de código
                        inputCodigo.value = '';
                    }
                },
                error: function(error) {
                    console.error('Error en la petición AJAX:', error);
                }
            });
        }

        // Agregar el evento click para agregar un nuevo ítem
        //btnAddItem.addEventListener("click", addNewItem);
    </script>
@endpush
