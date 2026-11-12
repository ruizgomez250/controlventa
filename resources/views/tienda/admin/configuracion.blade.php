@extends('adminlte::page')
@section('content_header')
<div class="d-flex justify-content-between flex-wrap"><h1>Tienda online</h1><a href="{{ route('tienda.publica') }}" target="_blank" rel="noopener" class="btn btn-success">Ver mi tienda</a></div>
@stop
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="card"><div class="card-header"><h2 class="card-title">Identidad y pedidos por WhatsApp</h2></div><div class="card-body">
<form method="POST" action="{{ route('tienda.configuracion.guardar') }}">@csrf @method('PUT')
<div class="row">
<div class="form-group col-md-4"><label for="nombre">Nombre comercial</label><input id="nombre" name="nombre" value="{{ old('nombre', $config->nombre) }}" required maxlength="100" class="form-control"></div>
<div class="form-group col-md-4"><label for="whatsapp">WhatsApp con código de país</label><input id="whatsapp" name="whatsapp" value="{{ old('whatsapp', $config->whatsapp) }}" placeholder="595981123456" pattern="[1-9][0-9]{7,14}" class="form-control"><small>Solo números, sin +, espacios ni cero inicial. Dejalo vacío para deshabilitar pedidos.</small></div>
<div class="form-group col-md-2"><label for="color">Color principal</label><input id="color" name="color" type="color" value="{{ old('color', $config->color) }}" class="form-control"></div>
<div class="form-group col-md-2"><label for="habilitada">Estado</label><select id="habilitada" name="habilitada" class="form-control"><option value="1" @selected(old('habilitada', $config->habilitada) == 1)>Abierta</option><option value="0" @selected(old('habilitada', $config->habilitada) == 0)>Cerrada</option></select></div>
<div class="form-group col-md-6"><label for="descripcion">Frase de la marca</label><input id="descripcion" name="descripcion" value="{{ old('descripcion', $config->descripcion) }}" required maxlength="250" class="form-control"></div>
<div class="form-group col-md-6"><label for="aviso">Aviso superior</label><input id="aviso" name="aviso" value="{{ old('aviso', $config->aviso) }}" required maxlength="200" class="form-control"></div>
</div><button class="btn btn-primary">Guardar configuración</button>
</form></div></div>
<div class="card"><div class="card-header"><h2 class="card-title">Productos, ofertas y colección premium</h2></div><div class="card-body">
<p>Se muestran los productos activos con precio de venta. Podés ocultarlos individualmente. Las ofertas usan el precio de venta como precio anterior y vencen automáticamente.</p>
<form method="GET" class="form-inline mb-3"><label for="buscar" class="sr-only">Buscar producto</label><input name="buscar" id="buscar" value="{{ request('buscar') }}" placeholder="Buscar producto" class="form-control mr-2" maxlength="100"><button class="btn btn-outline-primary">Buscar</button></form>
<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Producto</th><th>Presentación y oferta</th></tr></thead><tbody>
@foreach($productos as $producto)
<tr><td><strong>{{ $producto->descripcion }}</strong><br><small>{{ $producto->codigo }}</small><br>Gs. {{ number_format($producto->pventa, 0, ',', '.') }}</td><td>
<form method="POST" action="{{ route('tienda.productos.guardar', $producto->id) }}">@csrf @method('PUT')
<div class="d-flex flex-wrap align-items-end" style="gap:12px">
@foreach(['visible' => 'Visible', 'premium' => 'Premium', 'destacado' => 'Destacado'] as $campo => $etiqueta)
<label class="mb-2"><input type="hidden" name="{{ $campo }}" value="0"><input type="checkbox" name="{{ $campo }}" value="1" @checked($producto->tienda ? $producto->tienda->$campo : $campo === 'visible')> {{ $etiqueta }}</label>
@endforeach
<label>Precio oferta<input type="number" name="precio_oferta" value="{{ $producto->tienda?->precio_oferta }}" min="0.01" step="0.01" class="form-control form-control-sm" placeholder="Sin oferta"></label>
<label>Válida hasta<input type="datetime-local" name="oferta_hasta" value="{{ $producto->tienda?->oferta_hasta?->format('Y-m-d\TH:i') }}" class="form-control form-control-sm"></label>
<button class="btn btn-sm btn-primary mb-2">Guardar producto</button>
</div></form></td></tr>
@endforeach
</tbody></table></div>{{ $productos->links('pagination::bootstrap-4') }}
</div></div>
@stop
