@extends('adminlte::page')

@section('content_header')
    <h1>Bandeja de comprobantes</h1>
@stop

@section('content')
    <div class="alert alert-info">Consultá el último resultado guardado de cada factura. Un CDC o un QR no implica aprobación de DNIT. Usá Actualizar para recargar la bandeja.</div>
    <div class="card"><div class="card-body">
        <form method="get" action="{{ route('sifen.comprobantes') }}" class="row align-items-end">
            <div class="form-group col-md-3"><label for="buscar">Factura, CDC o cliente</label><input id="buscar" name="buscar" class="form-control" maxlength="100" value="{{ request('buscar') }}"></div>
            <div class="form-group col-md-3"><label for="estado">Estado SIFEN</label><select id="estado" name="estado" class="form-control"><option value="">Todos</option>@foreach($estados as $valor => $texto)<option value="{{ $valor }}" @selected(request('estado') === $valor)>{{ $texto }}</option>@endforeach</select></div>
            <div class="form-group col-md-2"><label for="desde">Desde</label><input type="date" id="desde" name="desde" class="form-control" value="{{ request('desde') }}"></div>
            <div class="form-group col-md-2"><label for="hasta">Hasta</label><input type="date" id="hasta" name="hasta" class="form-control" value="{{ request('hasta') }}"></div>
            <div class="form-group col-md-2"><button class="btn btn-primary" type="submit">Actualizar</button> <a href="{{ route('sifen.comprobantes') }}">Limpiar</a></div>
        </form>
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        <div class="table-responsive"><table class="table table-bordered table-hover">
            <thead><tr><th>Venta / factura</th><th>Fecha / cliente</th><th>Total Gs.</th><th>Ambiente</th><th>Estado SIFEN</th><th>Respuesta DNIT / problema</th><th>Detalle</th></tr></thead>
            <tbody>@forelse($comprobantes as $venta)
                @php
                    $estado = $venta->sifen_estado ?: 'sin_enviar';
                    $color = ['autorizado'=>'success','rechazado'=>'danger','error'=>'danger','pendiente'=>'warning','sin_respuesta'=>'warning','cancelado'=>'secondary'][$estado] ?? 'secondary';
                @endphp
                <tr>
                    <td>#{{ $venta->id }}<br>{{ $venta->numero_factura ?: 'Sin numeración' }}<br><small>Timbrado: {{ $venta->timbrado_factura ?: 'Sin asignar' }}</small></td>
                    <td>{{ $venta->fecha_emision }}<br>{{ $venta->cliente->razonsocial ?? 'Consumidor final' }}</td>
                    <td class="text-right">{{ number_format($venta->total, 0, ',', '.') }}</td>
                    <td>{{ $venta->sifen_ambiente == 1 ? 'TEST' : ($venta->sifen_ambiente == 2 ? 'Producción' : 'Sin registro') }}</td>
                    <td><span class="badge badge-{{ $color }}">{{ $estados[$estado] ?? $estado }}</span><br><small>{{ $venta->sifen_actualizado_at?->format('d/m/Y H:i:s') }}</small></td>
                    <td style="min-width:230px;max-width:400px;overflow-wrap:anywhere">
                        @if($venta->sifen_codigo)<strong>Código {{ $venta->sifen_codigo }}</strong><br>@endif
                        {{ $venta->sifen_mensaje ?: ($estado === 'sin_enviar' ? 'Sin envío registrado.' : 'Sin detalle guardado. Los comprobantes anteriores pueden tener información solo en los registros del servidor.') }}
                        @if(in_array($estado, ['error', 'sin_respuesta'], true) && $venta->sifen_cdc)<p class="text-danger mb-0"><small>Existe CDC. El envío pudo haber sido recibido; al reemitir se consulta primero en SIFEN para no duplicar la factura.</small></p>@endif
                    </td>
                    <td style="min-width:200px;max-width:350px;overflow-wrap:anywhere">
                        @if($venta->sifen_ambiente != 2)
                            @include('sifen.partials.imprimir', ['venta' => $venta])
                        @endif
                        <details class="mt-2"><summary>Ver detalle</summary>
                        <p class="mt-2"><strong>CDC:</strong> {{ $venta->sifen_cdc ?: 'No generado' }}<br><strong>Protocolo:</strong> {{ $venta->sifen_cde ?: 'Sin autorización registrada' }}</p>
                        @foreach($venta->sifen_respuesta ?? [] as $respuesta)<p><strong>{{ $respuesta['codigo'] ?? '' }}</strong> {{ $respuesta['mensaje'] ?? '' }}</p>@endforeach
                        @if($venta->sifen_ambiente == 1)<a target="_blank" rel="noopener" href="{{ route('sifen.factura-test', $venta->id) }}">Ver factura TEST / QR</a>@endif
                    </details></td>
                </tr>
            @empty<tr><td colspan="7" class="text-center">No hay comprobantes para estos filtros.</td></tr>@endforelse</tbody>
        </table></div>
        <p>{{ $comprobantes->total() }} comprobantes · Página {{ $comprobantes->currentPage() }} de {{ $comprobantes->lastPage() }}</p>
        <nav>@if($comprobantes->previousPageUrl())<a class="btn btn-outline-secondary" href="{{ $comprobantes->previousPageUrl() }}">Anterior</a>@endif @if($comprobantes->nextPageUrl())<a class="btn btn-outline-secondary" href="{{ $comprobantes->nextPageUrl() }}">Siguiente</a>@endif</nav>
    </div></div>
@stop
