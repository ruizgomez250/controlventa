@extends('adminlte::page')

@section('content_header')
    <div class="row">
        <div class="col-6">
            <h1 class="m-0 custom-heading">{{ __('Lista de Productos') }}</h1>
        </div>

        <div class="col-6">
            <a href="{{ route('producto.create') }}" class="btn btn-primary" style="float: right;">
                {{ __('Registra Nuevo Producto') }}
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <x-adminlte-datatable id="table1" :heads="$heads" head-theme="dark" theme="light" striped hoverable with-buttons>
                        @foreach ($producto as $row)
                            @php
                                $imagenUrl = $row->imagen
                                    ? asset('storage/' . $row->imagen)
                                    : asset('images/default.png');

                                $detalleProducto = [
                                    'id' => $row->id,
                                    'codigo' => $row->codigo,
                                    'descripcion' => $row->descripcion,
                                    'detalle' => $row->detalle ?: '—',
                                    'imagen_url' => $imagenUrl,

                                    'unidad' => $row->unidaddemedida?->descripcion ?? '—',
                                    'categoria' => $row->categoriaproducto?->descripcion ?? '—',
                                    'proveedor' => $row->proveedor?->razonsocial ?? '—',

                                    'stock' => number_format($row->stock ?? 0, 3, ',', '.'),
                                    'stock_inicial' => number_format($row->stock_inicial ?? 0, 3, ',', '.'),
                                    'stock_minimo' => number_format($row->stock_minimo ?? 0, 3, ',', '.'),
                                    'stock_maximo' => number_format($row->stock_maximo ?? 0, 3, ',', '.'),
                                    'ubicacion_deposito' => $row->ubicacion_deposito ?: '—',

                                    'pcosto' => number_format($row->pcosto ?? 0, 0, ',', '.'),
                                    'pventa' => number_format($row->pventa ?? 0, 0, ',', '.'),
                                    'pmayorista' => number_format($row->pmayorista ?? 0, 0, ',', '.'),
                                    'cmayorista' => $row->cmayorista ?? 0,
                                    'dmayorista' => number_format($row->dmayorista ?? 0, 0, ',', '.'),

                                    'impuesto' => number_format($row->impuesto?->valor ?? 0, 2, ',', '.'),
                                    'estado' => $row->estado == 1 ? 'Activo' : 'Inactivo',
                                    'tipo' => match($row->tipo) {
                                        'venta' => 'Para la Venta',
                                        'uso_interno' => 'Uso Interno',
                                        'ambos' => 'Ambos',
                                        default => '—',
                                    },
                                    'observacion' => $row->observacion ?: '—',

                                    'tramos' => $row->precioTiers->map(function ($tier) {
                                        return [
                                            'cantidad_desde' => $tier->cantidad_desde,
                                            'precio_unitario' => number_format($tier->precio_unitario ?? 0, 0, ',', '.'),
                                        ];
                                    })->values(),
                                ];
                            @endphp

                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    @if ($row->imagen)
                                        <img src="{{ $imagenUrl }}"
                                             alt="{{ $row->descripcion }}"
                                             style="width:50px;height:50px;object-fit:cover;border-radius:4px;cursor:pointer;"
                                             onclick="mostrarImagen(@js($imagenUrl), @js($row->descripcion))">
                                    @else
                                        <span class="badge badge-secondary">Sin imagen</span>
                                    @endif
                                </td>

                                <td>{{ $row->unidaddemedida?->descripcion ?? '—' }}</td>

                                <td>{{ $row->descripcion }}</td>

                                <td>{{ $row->categoriaproducto?->descripcion ?? '—' }}</td>

                                <td>{{ number_format($row->stock ?? 0, 3, ',', '.') }}</td>

                                <td>{{ number_format($row->pcosto ?? 0, 0, ',', '.') }}</td>

                                <td>{{ number_format($row->pventa ?? 0, 0, ',', '.') }}</td>

                                <td>{{ number_format($row->impuesto?->valor ?? 0, 2, ',', '.') }} %</td>

                                <td>
                                    @if ($row->estado == 1)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-danger">Inactivo</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($row->precioTiers && $row->precioTiers->count() > 0)
                                        <small>
                                            @foreach ($row->precioTiers as $tier)
                                                {{ $tier->cantidad_desde }}+:
                                                {{ number_format($tier->precio_unitario, 0, ',', '.') }}<br>
                                            @endforeach
                                        </small>
                                    @else
                                        <small class="text-muted">{{ __('—') }}</small>
                                    @endif
                                </td>

                                <td class="text-right">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary"
                                            onclick="mostrarDetalleProducto(@js($detalleProducto))">
                                        <i class="fa fa-sm fa-fw fa-eye"></i>
                                    </button>

                                    <a href="{{ route('producto.edit', $row->id) }}"
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="fa fa-sm fa-fw fa-pen"></i>
                                    </a>

                                    <form id="delete-form-{{ $row->id }}"
                                          action="{{ route('producto.destroy', [$row->id]) }}"
                                          method="post"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')

                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary"
                                                onclick="borrar({{ $row->id }})">
                                            <i class="fa fa-sm fa-fw fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </x-adminlte-datatable>

                </div>
            </div>
        </div>
    </div>

    {{-- Modal detalle completo --}}
    <div class="modal fade" id="detalleModal" tabindex="-1" role="dialog" aria-labelledby="detalleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="detalleModalLabel">
                        <i class="fas fa-box-open"></i> {{ __('Detalle del Producto') }}
                    </h5>

                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body" id="detalleProductoContenido">
                    {{-- Se carga por JavaScript --}}
                </div>

            </div>
        </div>
    </div>

    {{-- Modal imagen --}}
    <div class="modal fade" id="imagenModal" tabindex="-1" role="dialog" aria-labelledby="imagenModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="imagenModalLabel">{{ __('Imagen') }}</h5>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body text-center">
                    <img id="imagenModalSrc"
                         src=""
                         class="img-fluid"
                         style="max-height:500px;">
                </div>

            </div>
        </div>
    </div>
@stop

@push('js')
<script>
    var moneda = @json($moneda);

    function escapeHtml(value) {
        if (value === null || value === undefined || value === '') {
            return '—';
        }

        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function mostrarDetalleProducto(producto) {
        let tramosHtml = '';

        if (producto.tramos && producto.tramos.length > 0) {
            tramosHtml = producto.tramos.map((tier, index) => {
                return `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${escapeHtml(tier.cantidad_desde)}+</td>
                        <td>${moneda} ${escapeHtml(tier.precio_unitario)}</td>
                    </tr>
                `;
            }).join('');
        } else {
            tramosHtml = `
                <tr>
                    <td colspan="3" class="text-center text-muted">Sin tramos mayoristas</td>
                </tr>
            `;
        }

        let estadoBadge = producto.estado === 'Activo'
            ? '<span class="badge badge-success">Activo</span>'
            : '<span class="badge badge-danger">Inactivo</span>';

        let html = `
            <div class="row">
                <div class="col-md-3 text-center">
                    <img src="${escapeHtml(producto.imagen_url)}"
                         alt="${escapeHtml(producto.descripcion)}"
                         class="img-fluid img-thumbnail mb-2"
                         style="max-height:220px; object-fit:cover;">

                    <h5 class="mt-2">${escapeHtml(producto.descripcion)}</h5>
                    <p class="text-muted mb-1">Código: <strong>${escapeHtml(producto.codigo)}</strong></p>
                    ${estadoBadge}
                </div>

                <div class="col-md-9">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle"></i> Información General
                            </h3>
                        </div>

                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered mb-0">
                                <tbody>
                                    <tr>
                                        <th style="width: 25%;">Descripción</th>
                                        <td>${escapeHtml(producto.descripcion)}</td>
                                    </tr>
                                    <tr>
                                        <th>Categoría</th>
                                        <td>${escapeHtml(producto.categoria)}</td>
                                    </tr>
                                    <tr>
                                        <th>Unidad de Medida</th>
                                        <td>${escapeHtml(producto.unidad)}</td>
                                    </tr>
                                    <tr>
                                        <th>Proveedor</th>
                                        <td>${escapeHtml(producto.proveedor)}</td>
                                    </tr>
                                    <tr>
                                        <th>Tipo</th>
                                        <td>${escapeHtml(producto.tipo)}</td>
                                    </tr>
                                    <tr>
                                        <th>Impuesto</th>
                                        <td>${escapeHtml(producto.impuesto)} %</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-dollar-sign"></i> Datos Comerciales
                                    </h3>
                                </div>

                                <div class="card-body p-0">
                                    <table class="table table-sm table-bordered mb-0">
                                        <tbody>
                                            <tr>
                                                <th>Precio Costo</th>
                                                <td>${moneda} ${escapeHtml(producto.pcosto)}</td>
                                            </tr>
                                            <tr>
                                                <th>Precio Venta</th>
                                                <td>${moneda} ${escapeHtml(producto.pventa)}</td>
                                            </tr>
                                            <tr>
                                                <th>Precio Mayorista</th>
                                                <td>${moneda} ${escapeHtml(producto.pmayorista)}</td>
                                            </tr>
                                            <tr>
                                                <th>Cant. Mayorista</th>
                                                <td>${escapeHtml(producto.cmayorista)}</td>
                                            </tr>
                                            <tr>
                                                <th>Desc. Mayorista</th>
                                                <td>${moneda} ${escapeHtml(producto.dmayorista)}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card card-outline card-warning">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-warehouse"></i> Stock
                                    </h3>
                                </div>

                                <div class="card-body p-0">
                                    <table class="table table-sm table-bordered mb-0">
                                        <tbody>
                                            <tr>
                                                <th>Stock Actual</th>
                                                <td>${escapeHtml(producto.stock)}</td>
                                            </tr>
                                            <tr>
                                                <th>Stock Inicial</th>
                                                <td>${escapeHtml(producto.stock_inicial)}</td>
                                            </tr>
                                            <tr>
                                                <th>Stock Mínimo</th>
                                                <td>${escapeHtml(producto.stock_minimo)}</td>
                                            </tr>
                                            <tr>
                                                <th>Stock Máximo</th>
                                                <td>${escapeHtml(producto.stock_maximo)}</td>
                                            </tr>
                                            <tr>
                                                <th>Ubicación</th>
                                                <td>${escapeHtml(producto.ubicacion_deposito)}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-outline card-info mt-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-layer-group"></i> Tramos Mayoristas
                            </h3>
                        </div>

                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>Desde Cantidad</th>
                                        <th>Precio Unitario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${tramosHtml}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card card-outline card-secondary mt-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-align-left"></i> Detalle / Observación
                            </h3>
                        </div>

                        <div class="card-body">
                            <h6><strong>Detalle:</strong></h6>
                            <p style="white-space: pre-wrap;">${escapeHtml(producto.detalle)}</p>

                            <hr>

                            <h6><strong>Observación:</strong></h6>
                            <p style="white-space: pre-wrap;">${escapeHtml(producto.observacion)}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#detalleProductoContenido').html(html);
        $('#detalleModal').modal('show');
    }

    function borrar(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminarlo',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }

    function mostrarImagen(url, descripcion) {
        $('#imagenModalSrc').attr('src', url);
        $('#imagenModalLabel').text(descripcion);
        $('#imagenModal').modal('show');
    }

    var successMessage = @json(session('success'));
    var errorMessage = @json(session('error'));

    if (successMessage) {
        Swal.fire('Éxito', successMessage, 'success');
    }

    if (errorMessage) {
        Swal.fire('Error', errorMessage, 'error');
    }
</script>
@endpush
