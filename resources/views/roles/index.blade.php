@extends('adminlte::page')



@section('content_header')
    <h1 class="m-0 custom-heading">Permisos de Usuarios</h1>
@stop
@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/jquery-ui-1.13.2/jquery-ui.min.css') }}">
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    {{-- 'id', 'codigo', 'descripcion', 'detalle', 'id_categoria', 'id_estado','pcosto', 'pventa', 'observacion' --}}





                    <div class="row">
                        {{-- With Label --}}



                        <x-adminlte-select2 name="idusuario" id="idusuario" label="USUARIOS" fgroup-class="col-md-4">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-secondary">
                                    <i class="fas fa-user"></i>
                                </div>
                            </x-slot>
                            @foreach ($usuarios as $item)
                                <option value={{ $item->id }}>{{ $item->name }}</option>
                            @endforeach
                        </x-adminlte-select2>
                        <button class="btn btn-secondary" style="float: right;" onclick="mostrarPermisos()">Generar</button>



                    </div>

                    <hr>



                    <!-- Agrega este elemento para mostrar la suma total -->

                    </form>

                </div>
            </div>
        </div>
    </div>
    <x-adminlte-modal id="permisosModal" title="Permisos del Usuario" theme="light" size="lg">
        <div>
            <form method="POST" action="{{ route('rol.store') }}">
                @csrf
                <div class="row">
                    <input type="hidden" name="id_usuario" id="id_usuario">
                    <div class="col-6">
                        <h5 class="custom-heading">Cliente</h5>
                        <label><input type="checkbox" name="permisos[cliente_leer]" id="cliente_leer"> Leer</label>
                        <label><input type="checkbox" name="permisos[cliente_borrar]" id="cliente_borrar"> Borrar</label>
                        <label><input type="checkbox" name="permisos[cliente_crear]" id="cliente_crear"> Crear</label>
                        <label><input type="checkbox" name="permisos[cliente_editar]" id="cliente_editar"> Editar</label>
                    </div>
                    <div class="col-6">
                        <h5 class="custom-heading">Proveedor</h5>
                        <label><input type="checkbox" name="permisos[proveedor_leer]" id="proveedor_leer"> Leer</label>
                        <label><input type="checkbox" name="permisos[proveedor_borrar]" id="proveedor_borrar">
                            Borrar</label>
                        <label><input type="checkbox" name="permisos[proveedor_crear]" id="proveedor_crear"> Crear</label>
                        <label><input type="checkbox" name="permisos[proveedor_editar]" id="proveedor_editar">
                            Editar</label>
                    </div>
                    <div class="col-6">
                        <h5 class="custom-heading">Producto</h5>
                        <label><input type="checkbox" name="permisos[producto_leer]" id="producto_leer"> Leer</label>
                        <label><input type="checkbox" name="permisos[producto_borrar]" id="producto_borrar"> Borrar</label>
                        <label><input type="checkbox" name="permisos[producto_crear]" id="producto_crear"> Crear</label>
                        <label><input type="checkbox" name="permisos[producto_editar]" id="producto_editar"> Editar</label>
                    </div>
                    <div class="col-6">
                        <h5 class="custom-heading">Compra</h5>
                        <label><input type="checkbox" name="permisos[compra_leer]" id="compra_leer"> Leer</label>
                        <label><input type="checkbox" name="permisos[compra_borrar]" id="compra_borrar"> Borrar</label>
                        <label><input type="checkbox" name="permisos[compra_crear]" id="compra_crear"> Crear</label>
                        <label><input type="checkbox" name="permisos[compra_editar]" id="compra_editar"> Editar</label>
                    </div>
                    <div class="col-6">
                        <h5 class="custom-heading">Venta</h5>
                        <label><input type="checkbox" name="permisos[venta_leer]" id="venta_leer"> Leer</label>
                        <label><input type="checkbox" name="permisos[venta_borrar]" id="venta_borrar"> Borrar</label>
                        <label><input type="checkbox" name="permisos[venta_crear]" id="venta_crear"> Crear</label>
                        <label><input type="checkbox" name="permisos[venta_editar]" id="venta_editar"> Editar</label>
                    </div>
                    <div class="col-6">
                        <h5 class="custom-heading">Caja</h5>
                        <label><input type="checkbox" name="permisos[caja_leer]" id="caja_leer"> Leer</label>
                        <label><input type="checkbox" name="permisos[caja_borrar]" id="caja_borrar"> Borrar</label>
                        <label><input type="checkbox" name="permisos[caja_crear]" id="caja_crear"> Crear</label>
                        <label><input type="checkbox" name="permisos[caja_editar]" id="caja_editar"> Editar</label>
                    </div>
                    <div class="col-6">
                        <h5 class="custom-heading">Reporte Caja</h5>
                        <label><input type="checkbox" name="permisos[cajareporte_leer]" id="cajareporte_leer"> Leer</label>
                        <label><input type="checkbox" name="permisos[cajareporte_borrar]" id="cajareporte_borrar"> Borrar</label>
                        <label><input type="checkbox" name="permisos[cajareporte_crear]" id="cajareporte_crear"> Crear</label>
                        <label><input type="checkbox" name="permisos[cajareporte_editar]" id="cajareporte_editar"> Editar</label>
                    </div>
                    <div class="col-6">
                        <h5 class="custom-heading">Roles</h5>
                        <label><input type="checkbox" name="permisos[rol_leer]" id="rol_leer"> Leer</label>
                        <label><input type="checkbox" name="permisos[rol_borrar]" id="rol_borrar"> Borrar</label>
                        <label><input type="checkbox" name="permisos[rol_crear]" id="rol_crear"> Crear</label>
                        <label><input type="checkbox" name="permisos[rol_editar]" id="rol_editar"> Editar</label>
                    </div>
                    
                </div>
                <button class="btn
                                btn-primary" type="submit">Guardar</button>
            </form>
        </div>
    </x-adminlte-modal>
@stop

@push('js')
    <script src="{{ asset('vendor/jquery-ui-1.13.2/jquery-ui.min.js') }}"></script>
    <script>
        function mostrarPermisos() {
            var checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = false;
            });

            userId = document.getElementById('idusuario').value;
            // Usando getElementById
            document.getElementById('id_usuario').value=userId;

            

            $.ajax({
                url: 'getroles/' + userId,
                method: 'GET',
                success: function(response) {
                    if (response.length != 0) {
                        response.forEach(function(permisos) {
                            modelo = permisos.nombre_modelo;
                            modeloMinusc = modelo.toLowerCase();

                            // Verifica cada permiso y marca el checkbox correspondiente
                            if (permisos.leer == 1) {
                                nombre = modeloMinusc + '_leer';
                                var checkbox = document.getElementById(nombre);
                                checkbox.checked = true;
                            }
                            if (permisos.borrar == 1) {
                                nombre = modeloMinusc + '_borrar';
                                var checkbox = document.getElementById(nombre);
                                checkbox.checked = true;
                            }
                            if (permisos.crear == 1) {
                                nombre = modeloMinusc + '_crear';
                                var checkbox = document.getElementById(nombre);
                                checkbox.checked = true;
                            }
                            if (permisos.editar == 1) {
                                nombre = modeloMinusc + '_editar';
                                var checkbox = document.getElementById(nombre);
                                checkbox.checked = true;
                            }
                        });
                    }
                    $('#detalleModal').modal('show');
                },
                error: function() {
                    console.log('Error al obtener detalles de la compra');
                }
            });
            $('#permisosModal').modal('show');
        }
        // Mostrar el mensaje de éxito o error con SweetAlert
        if (successMessage) {
            Swal.fire('Éxito', successMessage, 'success');
        } else if (errorMessage) {
            Swal.fire('Error', errorMessage, 'error');
        }
    </script>
@endpush
