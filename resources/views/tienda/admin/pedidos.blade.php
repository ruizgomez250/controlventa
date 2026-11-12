@extends('adminlte::page')
@section('content_header')<h1>Pedidos de la tienda</h1>@stop
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="alert alert-info">Un pedido pendiente se guardó antes de abrir WhatsApp. Confirmá con el cliente antes de prepararlo. El pedido no genera una venta, no descuenta stock ni emite una factura.</div>
<form method="GET" class="form-inline mb-3"><label for="estado" class="mr-2">Estado</label><select name="estado" id="estado" class="form-control mr-2"><option value="">Todos</option>@foreach(['pendiente','confirmado','preparando','entregado','cancelado'] as $estado)<option @selected(request('estado') === $estado)>{{ $estado }}</option>@endforeach</select><button class="btn btn-primary">Filtrar</button></form>
@forelse($pedidos as $pedido)
<div class="card"><div class="card-header d-flex flex-wrap justify-content-between"><strong>Pedido #{{ $pedido->id }} · {{ $pedido->nombre }}</strong><span>{{ $pedido->created_at->format('d/m/Y H:i') }} · {{ $pedido->estado }}</span></div><div class="card-body">
<p>Teléfono: {{ $pedido->telefono }} · {{ $pedido->entrega }} {{ $pedido->direccion }}<br>{{ $pedido->observaciones }}</p>
<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr></thead><tbody>@foreach($pedido->items as $item)<tr><td>{{ $item['nombre'] }} <small>({{ $item['codigo'] }})</small></td><td>{{ $item['cantidad'] }}</td><td>{{ number_format($item['precio'], 0, ',', '.') }}</td><td>{{ number_format($item['subtotal'], 0, ',', '.') }}</td></tr>@endforeach</tbody></table></div>
<strong>Total de productos: {{ number_format($pedido->total, 0, ',', '.') }} {{ $pedido->moneda }}</strong>
@can('venta crear')<form class="form-inline mt-3" method="POST" action="{{ route('tienda.pedidos.actualizar', $pedido->id) }}">@csrf @method('PUT')<label class="mr-2" for="estado-{{ $pedido->id }}">Estado</label><select id="estado-{{ $pedido->id }}" name="estado" class="form-control mr-2">@foreach(['pendiente','confirmado','preparando','entregado','cancelado'] as $estado)<option @selected($pedido->estado === $estado)>{{ $estado }}</option>@endforeach</select><button class="btn btn-primary">Actualizar</button></form>@endcan
</div></div>
@empty<p>No hay pedidos para este filtro.</p>@endforelse
{{ $pedidos->links('pagination::bootstrap-4') }}
@stop
