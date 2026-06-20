@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 custom-heading">
        <i class="fas fa-shield-alt"></i> Permisos de Usuarios
    </h1>
@stop

@section('css')
    <style>
        .permission-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: #fff;
            transition: box-shadow 0.2s;
        }
        .permission-card:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .permission-card h5 {
            margin-top: 0;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e9ecef;
            font-weight: 600;
        }
        .permission-card .form-check {
            margin-bottom: 6px;
        }
        .permission-card .form-check-label {
            cursor: pointer;
            user-select: none;
        }
        .select-all-link {
            font-size: 0.85rem;
            cursor: pointer;
            color: #007bff;
            margin-left: 5px;
        }
        .select-all-link:hover {
            text-decoration: underline;
        }
        .badge-count {
            font-size: 0.8rem;
            margin-left: 8px;
        }
        #permisos-container {
            display: none;
        }
        .user-info-bar {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px 18px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        .permiso-checkbox:checked {
            accent-color: #28a745;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-end">
                        <x-adminlte-select2 name="idusuario" id="idusuario" label="Seleccionar Usuario" fgroup-class="col-md-5">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-info">
                                    <i class="fas fa-user"></i>
                                </div>
                            </x-slot>
                            <option value="">-- Seleccione un usuario --</option>
                            @foreach ($usuarios as $item)
                                <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->email }})</option>
                            @endforeach
                        </x-adminlte-select2>
                        <div class="col-md-2 mb-3">
                            <button class="btn btn-info" onclick="cargarPermisos()" id="btnCargar">
                                <i class="fas fa-sync"></i> Cargar Permisos
                            </button>
                        </div>
                        <div class="col-md-2 mb-3">
                            <button class="btn btn-success" data-toggle="modal" data-target="#crearPermisoModal">
                                <i class="fas fa-plus"></i> Crear Permiso
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Crear Permiso --}}
    <div class="modal fade" id="crearPermisoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="crearPermisoForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Crear Nuevo Permiso</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">Crea un permiso nuevo para que aparezca en la lista y pueda asignarse a los usuarios.</p>
                        <x-adminlte-input name="nombre" id="nombrePermiso" label="Nombre del Permiso" placeholder="Ej: tabla_porcentaje leer" />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="permisos-container">
        <div class="user-info-bar" id="userInfoBar">
            <i class="fas fa-user-circle"></i>
            <strong>Usuario:</strong> <span id="userNameDisplay"></span>
            <span class="badge badge-info ml-2" id="permCountDisplay">0 permisos</span>
        </div>

        <form method="POST" action="{{ route('rol.store') }}" id="permisosForm">
            @csrf
            <input type="hidden" name="id_usuario" id="id_usuario">

            <div class="row">
                @foreach ($permissionGroups as $model => $actions)
                    <div class="col-md-6">
                        <div class="permission-card">
                            <h5>
                                <i class="fas fa-cube"></i> {{ $displayNames[$model] ?? ucfirst($model) }}
                                <a class="select-all-link" onclick="toggleGroup('{{ $model }}', true)">
                                    <i class="fas fa-check-circle"></i> Todo
                                </a>
                                <a class="select-all-link" onclick="toggleGroup('{{ $model }}', false)">
                                    <i class="fas fa-times-circle"></i> Nada
                                </a>
                                <span class="badge badge-light badge-count" id="count_{{ $model }}">0/{{ count($actions) }}</span>
                            </h5>
                            <div class="row">
                                @foreach ($actions as $action)
                                    @php
                                        $permName = $model . ' ' . $action;
                                        $inputId = $model . '_' . $action;
                                    @endphp
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input permiso-checkbox group-{{ $model }}"
                                                   type="checkbox"
                                                   name="permisos[]"
                                                   value="{{ $permName }}"
                                                   id="{{ $inputId }}"
                                                   onchange="updateCount('{{ $model }}')">
                                            <label class="form-check-label" for="{{ $inputId }}">
                                                <i class="fas {{ $actionIcons[$action] ?? 'fa-check' }} text-{{ $actionColors[$action] ?? 'secondary' }}"></i>
                                                {{ $actionLabels[$action] ?? $action }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row mt-3">
                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Guardar Permisos
                    </button>
                </div>
            </div>
        </form>
    </div>
@stop

@push('js')
    <script>
        function cargarPermisos() {
            var userId = document.getElementById('idusuario').value;
            if (!userId) {
                Swal.fire('Atención', 'Seleccione un usuario primero.', 'warning');
                return;
            }

            document.getElementById('id_usuario').value = userId;
            var allCheckboxes = document.querySelectorAll('input[type="checkbox"]');
            allCheckboxes.forEach(function(cb) { cb.checked = false; });

            var userName = document.querySelector('#idusuario option:checked').text;
            document.getElementById('userNameDisplay').textContent = userName;

            $.ajax({
                url: 'getroles/' + userId,
                method: 'GET',
                success: function(response) {
                    response.forEach(function(permName) {
                        var inputId = permName.replace(' ', '_');
                        var checkbox = document.getElementById(inputId);
                        if (checkbox) {
                            checkbox.checked = true;
                        }
                    });
                    var modelNames = @json(array_keys($permissionGroups));
                    modelNames.forEach(function(m) { updateCount(m); });
                    document.getElementById('permisos-container').style.display = 'block';
                    updatePermCount();
                    $('#permisos-container').hide().fadeIn(300);
                },
                error: function() {
                    Swal.fire('Error', 'No se pudieron cargar los permisos.', 'error');
                }
            });
        }

        function toggleGroup(group, state) {
            var checkboxes = document.querySelectorAll('.group-' + group);
            checkboxes.forEach(function(cb) { cb.checked = state; });
            updateCount(group);
            updatePermCount();
        }

        function updateCount(group) {
            var checkboxes = document.querySelectorAll('.group-' + group);
            var checked = 0;
            checkboxes.forEach(function(cb) { if (cb.checked) checked++; });
            var total = checkboxes.length;
            document.getElementById('count_' + group).textContent = checked + '/' + total;
        }

        function updatePermCount() {
            var allCheckboxes = document.querySelectorAll('input[name="permisos[]"]');
            var checked = 0;
            allCheckboxes.forEach(function(cb) { if (cb.checked) checked++; });
            document.getElementById('permCountDisplay').textContent = checked + ' permisos';
        }

        $(document).ready(function() {
            var successMessage = '{{ session('success') }}';
            var errorMessage = '{{ session('error') }}';
            if (successMessage) {
                Swal.fire('Éxito', successMessage, 'success');
            } else if (errorMessage) {
                Swal.fire('Error', errorMessage, 'error');
            }
        });

        $('#crearPermisoForm').on('submit', function(e) {
            e.preventDefault();
            var btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true);
            $.post('{{ route("permisos.crear") }}', $(this).serialize(), function(res) {
                $('#crearPermisoModal').modal('hide');
                $('#crearPermisoForm')[0].reset();
                Swal.fire('Éxito', res.success, 'success');
            }).fail(function(xhr) {
                var msg = xhr.responseJSON?.error || xhr.responseJSON?.errors?.nombre?.[0] || 'Error al crear permiso';
                Swal.fire('Error', msg, 'error');
            }).always(function() {
                btn.prop('disabled', false);
            });
        });
    </script>
@endpush
