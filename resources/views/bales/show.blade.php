@extends('adminlte::page')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div><h1 class="mb-0">Mercaderías de {{ $bale->code }}</h1><small class="text-muted">{{ $bale->bale_type ?: 'Fardo sin tipo' }} · {{ $bale->supplier_name ?: 'Sin proveedor' }}</small></div>
    <a href="{{ route('bales.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Volver a fardos</a>
</div>
@stop

@section('content')
<div class="row">
    @if($bale->image_url)<div class="col-md-3"><div class="card"><img src="{{ $bale->image_url }}" class="card-img-top" alt="Foto de {{ $bale->code }}" style="height:190px;object-fit:cover"></div></div>@endif
    <div class="{{ $bale->image_url ? 'col-md-9' : 'col-12' }}"><div class="row">
        <div class="col-sm-4"><div class="small-box bg-info"><div class="inner"><h3>{{ $bale->products_count }}</h3><p>Mercaderías registradas</p></div><div class="icon"><i class="fas fa-tshirt"></i></div></div></div>
        <div class="col-sm-4"><div class="small-box bg-success"><div class="inner"><h3>Gs. {{ number_format($bale->total_cost,0,',','.') }}</h3><p>Costo total del fardo</p></div><div class="icon"><i class="fas fa-money-bill-wave"></i></div></div></div>
        <div class="col-sm-4"><div class="small-box bg-warning"><div class="inner"><h3>Gs. {{ number_format($bale->unit_cost,0,',','.') }}</h3><p>Costo promedio</p></div><div class="icon"><i class="fas fa-calculator"></i></div></div></div>
    </div></div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Listado de mercaderías</h3></div>
    <div class="card-body table-responsive p-0"><table class="table table-hover table-striped mb-0">
        <thead><tr><th>Foto</th><th>Descripción</th><th>Tipo / Talla</th><th>Stock</th><th>Costo</th><th>Precio de venta</th></tr></thead>
        <tbody>
        @forelse($products as $product)
            <tr>
                <td><img src="{{ $product->imagen_url }}" alt="{{ $product->descripcion }}" class="img-thumbnail" style="width:70px;height:70px;object-fit:cover"></td>
                <td>{{ $product->descripcion }}@if($product->color)<br><small class="text-muted">{{ $product->brand }}{{ $product->brand && $product->color ? ' · ' : '' }}{{ $product->color }}</small>@endif</td>
                <td>{{ $product->garmentType?->name ?: '—' }}<br><small>{{ $product->clothingSize?->name ?: 'Sin talla' }}</small></td>
                <td>{{ number_format($product->stock,0,',','.') }}</td>
                <td>Gs. {{ number_format($product->bale_unit_cost ?: $product->pcosto,0,',','.') }}</td>
                <td><strong>Gs. {{ number_format($product->pventa,0,',','.') }}</strong></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-5"><i class="fas fa-box-open fa-3x mb-3 d-block"></i>Este fardo todavía no tiene mercaderías registradas.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    @if($products->hasPages())<div class="card-footer">{{ $products->links() }}</div>@endif
</div>
@stop
