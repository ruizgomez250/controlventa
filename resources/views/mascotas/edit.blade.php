@extends('adminlte::page')



@section('content_header')
    <h1 class="m-0 text-dark" style="background-color:#5DBFFF ">Editar Datos Del Producto</h1>
@stop

@section('content')


    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <form action="{{ route('mascota.update',$mascota->id)}}" method="post" enctype="multipart/form-data">
                        @csrf
                        @method('put')
                        <div class="row">  
                            <x-adminlte-input name="nombre" label="Nombre" value="{{ $mascota->nombre }}"
                                placeholder="Ingresar nombre de paciente" fgroup-class="col-md-6" />
                            @error('nombre')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror



                            <x-adminlte-input name="edad" label="Edad" fgroup-class="col-md-2"
                                value="{{ $mascota->edad }}" />

                            <x-adminlte-select name="sexo_id" label="Sexo" data-placeholder="Seleccionar sexo..."
                                fgroup-class="col-md-4">
                                @foreach ($sexo as $item)
                                    <option value={{ $item->id }}
                                        {{ $item->id == $mascota->sexo_id ? 'selected' : '' }}>{{ $item->descripcion }}
                                    </option>
                                @endforeach
                            </x-adminlte-select>

                            <x-adminlte-select id="especies_id" name="lista_especie" label="Especie"
                                data-placeholder="Seleccionar especie..." fgroup-class="col-md-4">
                                @foreach ($especie as $item)
                                    <option value={{ $item->id }}
                                        {{ $item->id == $mascota->razaanimal->claseanimal_id ? 'selected' : '' }}>
                                        {{ $item->descripcion }}</option>
                                @endforeach
                            </x-adminlte-select>

                            <x-adminlte-select id="resultados_select" name="raza_id" label="Raza"
                                data-placeholder="Seleccionar raza..." fgroup-class="col-md-4">

                            </x-adminlte-select>
                        </div>



                        <div class="row">
                            <x-adminlte-select name="propietario_id" label="Propietario"
                                data-placeholder="Seleccionar propietario..." fgroup-class="col-md-5">
                                @foreach ($propietario as $item)
                                    <option value={{ $item->id }} {{ $item->id == $mascota->propietario_id? 'selected' : '' }} >{{ $item->razonsocial }}</option>
                                @endforeach
                            </x-adminlte-select>

                            <x-adminlte-select name="estado_id" label="Estado" data-placeholder="Seleccionar estado..."
                                fgroup-class="col-md-5">
                                @foreach ($estadomascota as $item)
                                    <option value={{ $item->id }}>{{ $item->descripcion }}</option>
                                @endforeach
                            </x-adminlte-select>
                        </div>

                        <div class="row">
                            <x-adminlte-input type="file" name="foto" label="Foto" fgroup-class="col-md-6"
                                accept="image/*" />
                        </div>

                        <div class="row">
                            <div class="form-group col-md-12">
                                <a class="btn btn-danger" style="float: right;"
                                    href="{{ route('mascota.index') }}">Cancelar</a>
                                <x-adminlte-button class="btn-group mr-2" style="float: right;" type="submit"
                                    label="Guardar" theme="primary" icon="fas fa-lg fa-save" />
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
        window.addEventListener('DOMContentLoaded', function() {
            // Obtener referencia al elemento <select>
            var select = document.getElementById('especies_id');
            // Obtener el valor seleccionado
            var id = select.value;
            if (id) {
                // Realizar una consulta AJAX para obtener los resultados basados en la categoría seleccionada
                //php artisan optimize:clear borra todo
                fetch('../consulta/' + id)
                    .then(response => response.json())
                    .then(data => {
                        var resultadoSelect = document.getElementById('resultados_select');
                        resultadoSelect.innerHTML =
                            '<option value="">Seleccionar</option>'; // Limpiar opciones anteriores

                        data.forEach(function(resultado) {
                            var option = document.createElement('option');
                            option.value = resultado.id;
                            console.log('value: ' + option.value);
                            option.textContent = resultado.nombre;
                            resultadoSelect.appendChild(option);
                        });
                        var idraza = "{{ $mascota->raza_id }}";
                        resultadoSelect.value = idraza;
                    });
            } else {
                // Limpiar el segundo <select> si no se selecciona ninguna categoría
                var resultadoSelect = document.getElementById('resultados_select');
                resultadoSelect.innerHTML = '<option value="">Vacío</option>';
            }
        });

        document.getElementById('especies_id').addEventListener('change', function() {
            var id = this.value;
            console.log(id);
            if (id) {
                // Realizar una consulta AJAX para obtener los resultados basados en la categoría seleccionada
                //php artisan optimize:clear borra todo
                fetch('../consulta/' + id)
                    .then(response => response.json())
                    .then(data => {
                        var resultadoSelect = document.getElementById('resultados_select');
                        resultadoSelect.innerHTML =
                            '<option value="">Seleccionar</option>'; // Limpiar opciones anteriores
                        data.forEach(function(resultado) {
                            var option = document.createElement('option');
                            option.value = resultado.id;
                            console.log('value: ' + option.value);
                            option.textContent = resultado.nombre;
                            resultadoSelect.appendChild(option);
                        });
                    });
            } else {
                // Limpiar el segundo <select> si no se selecciona ninguna categoría
                var resultadoSelect = document.getElementById('resultados_select');
                resultadoSelect.innerHTML = '<option value="">Vacío</option>';
            }
        });
    </script>
@endpush
