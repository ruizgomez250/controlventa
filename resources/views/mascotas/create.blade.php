@extends('adminlte::page')



@section('content_header')
    <h1 class="m-0 text-dark" style="background-color:orange ">Registrar Mascota</h1>
@stop
@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/jquery-ui-1.13.2/jquery-ui.min.css') }}">
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('mascota.store') }}" method="post" autocomplete="off"
                        enctype="multipart/form-data">
                        @csrf
                        @method('POST')
                        {{-- 'id', 'codigo', 'descripcion', 'detalle', 'id_categoria', 'id_estado','pcosto', 'pventa', 'observacion' --}}

                        <div class="row">




                            <x-adminlte-input name="nombre" label="Nombre" placeholder="Ingresar nombre de paciente"
                                fgroup-class="col-md-6" />
                            @error('nombre')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror

                            <x-adminlte-input name="edad" label="Edad" fgroup-class="col-md-2" />

                            <x-adminlte-select name="sexo_id" label="Sexo" data-placeholder="Seleccionar sexo..."
                                fgroup-class="col-md-4">
                                @foreach ($sexo as $item)
                                    <option value={{ $item->id }}>{{ $item->descripcion }}</option>
                                @endforeach
                            </x-adminlte-select>

                            <x-adminlte-select id="especie_id" name="listaespecie" label="Especie"
                                data-placeholder="Seleccionar especie..." fgroup-class="col-md-4">
                                @foreach ($especie as $item)
                                    <option value={{ $item->id }}>{{ $item->descripcion }}</option>
                                @endforeach
                            </x-adminlte-select>



                            <x-adminlte-select id="resultado_select" name="raza_id" label="Raza"
                                data-placeholder="Seleccionar raza..." fgroup-class="col-md-4">

                            </x-adminlte-select>


                        </div>



                        <div class="row">
                            <x-adminlte-input type="text" id="search" name="search" label="Propietario"
                                fgroup-class="col-md-6" />


                                <x-adminlte-select name="estado_id" label="Estado" data-placeholder="Seleccionar estado..."
                                fgroup-class="col-md-4">
                                @foreach ($estadomascota as $item)
                                    <option value={{ $item->id }}>{{ $item->descripcion }}</option>
                                @endforeach
                            </x-adminlte-select>

                            <x-adminlte-input type="hidden" id="propietario_id" name="propietario_id" 
                            fgroup-class="col-md-1" />









                        </div>


                        <div class="row ">

                            <x-adminlte-input type="file" id="foto" name="foto" onchange="mostrarImagen()"
                                label="Foto" fgroup-class="col-md-6" accept="image/*" />

                        </div>

                        <div class="row ml-5 zoom">
                            <img id="imagen-previa" src="#" alt="Vista previa de la imagen"
                                style="display: none; width: 100px;
                            height: 100px;">
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
                            $('#propietario_id').val('');
                            console.log('hola');
                        }
                    }
                });
            },
            select: function(event, ui) {
                $('#propietario_id').val(ui.item.id);
            }
        });

        document.getElementById('especie_id').addEventListener('change', function() {
            var specie_id = this.value;
            console.log(specie_id);
            if (specie_id) {

                fetch('consulta/' + specie_id)
                    .then(response => response.json())
                    .then(data => {
                        var resultadoSelect = document.getElementById('resultado_select');
                        resultadoSelect.innerHTML =
                            '<option value="">Seleccionar</option>'; // Limpiar opciones anteriores
                        data.forEach(function(resultado) {
                            var option = document.createElement('option');
                            option.value = resultado.id;
                            option.textContent = resultado.nombre;
                            resultadoSelect.appendChild(option);
                        });

                    });
            } else {
                // Limpiar el segundo <select> si no se selecciona ninguna categoría
                var resultadoSelect = document.getElementById('resultado_select');
                resultadoSelect.innerHTML = '<option value="">Vacío</option>';
            }
        });

        function mostrarImagen() {
            var archivo = document.getElementById("foto").files[0];
            var reader = new FileReader();

            reader.onload = function(e) {
                document.getElementById("imagen-previa").src = e.target.result;
                document.getElementById("imagen-previa").style.display = "block";
            }

            reader.readAsDataURL(archivo);
        }
    </script>
@endpush
