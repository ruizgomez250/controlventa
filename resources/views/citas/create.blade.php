@extends('adminlte::page')



@section('content_header')
    <h1 class="m-0 text-dark" style="background-color:orange ">Registrar Cita</h1>
@stop
@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/jquery-ui-1.13.2/jquery-ui.min.css') }}">
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('cita.store') }}" method="post" autocomplete="off">
                        @csrf
                        @method('POST')
                        {{-- 'id', 'codigo', 'descripcion', 'detalle', 'id_categoria', 'id_estado','pcosto', 'pventa', 'observacion' --}}

                        <div class="row">
                            <x-adminlte-input type="text" id="search" name="search" label="Razón Social"
                                fgroup-class="col-md-6" />
                            <x-adminlte-input type="text" id="ruc" name="ruc" label="RUC" readonly
                                fgroup-class="col-md-3" />
                            <x-adminlte-input type="text" id="telefono" name="telefono" label="Teléfono" readonly
                                fgroup-class="col-md-3" />
                        </div>
                        <div class="row">
                            <x-adminlte-select name="mascota_id" id="mascota_id" label="Mascota"
                                data-placeholder="Seleccionar mascota..." fgroup-class="col-md-4">
                            </x-adminlte-select>
                            <x-adminlte-input type="text" id="raza" name="raza" label="Raza" readonly
                                fgroup-class="col-md-3" />
                            <x-adminlte-input type="text" id="sexo" name="sexo" label="Sexo" readonly
                                fgroup-class="col-md-2" />
                            <x-adminlte-input type="text" id="edad" name="edad" label="Edad" readonly
                                fgroup-class="col-md-2" />

                        </div>
                        <div class="row">
                            <x-adminlte-select name="tipo_id" label="Tipo de Consulta"
                                data-placeholder="Seleccionar tipo de consulta..." fgroup-class="col-md-4">
                                @foreach ($tipo as $item)
                                    <option value={{ $item->id }}>{{ $item->descripcion }}</option>
                                @endforeach
                            </x-adminlte-select>
                            {{-- With Label --}}
                            @php
                                $config1 = ['format' => 'DD-MM-YYYY'];
                            @endphp
                            <x-adminlte-input-date label="Fecha" name="fecha" id="fecha"
                                value="{{ now()->format('d-m-Y') }}" :config="$config1" />


                            {{-- With Label --}}
                            @php
                                $config2 = ['format' => 'HH:mm'];
                            @endphp
                            <x-adminlte-input-date label="Hora" name="hora" id="hora"
                                value="{{ now()->format('h:i') }}" :config="$config2" />

                        </div>



                        <div class="row">
                            <div class="form-group col-md-12">
                                <x-adminlte-button class="btn-group" style="float: right;" type="submit" label="Registrar"
                                    theme="primary" icon="fas fa-lg fa-save" />
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
        $('#search').autocomplete({
            minlength: 3,
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('autocomplete') }}",
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
                $('#telefono').val(ui.item.celular);
                console.log(ui.item.id + '  ' + ui.item.ruc + ' : ' + ui.item.celular);
                poblarmascotas(ui.item.id);
            }
        });


        function poblarmascotas(id) {
            console.log('ID: ' + id);
            if (id) {
                fetch('../cita/propietario/' + id)
                    .then(response => response.json())
                    .then(data => {
                        var resultadoSelect = document.getElementById('mascota_id');
                        resultadoSelect.innerHTML =
                            '<option value="">Seleccionar</option>'; // Limpiar opciones anteriores                           
                        data.forEach(function(resultado) {
                            console.log(resultado);
                            var option = document.createElement('option');
                            option.value = resultado.id;
                            console.log('value: ' + option.value);
                            option.textContent = resultado.nombre;
                            resultadoSelect.appendChild(option);
                        });
                    });
            } else {
                var resultadoSelect = document.getElementById('mascota_id');
                resultadoSelect.innerHTML = '<option value="">Vacío</option>';
            }
        }

        $('#mascota_id').on('change', function() {
            var selectedOption = $(this).val(); // Obtén el valor de la opción seleccionada
            // Realiza la solicitud AJAX al servidor

            //
            $.ajax({
                url: "../maestro",
                method: "get",
                data: {
                    id: selectedOption
                },
                success: function(response) {
                    //   console.log(response[0].edad + response[0].razaanimal.nombre + response[0].sexoanimal.descripcion );
                    // Lógica para poblar los campos adicionales en la vista con los datos obtenidos
                    $('#raza').val(response[0].razaanimal.nombre);
                    $('#sexo').val(response[0].sexoanimal.descripcion);
                    $('#edad').val(response[0].edad);
                    // Otros campos que necesites poblar
                },
                error: function(xhr, status, error) {
                    // Manejo del error si es necesario
                    console.log('LLAME AL PROGRAMADOR');
                }
            });
        });
    </script>
@endpush
