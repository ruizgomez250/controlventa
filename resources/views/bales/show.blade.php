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
    <div class="card-body border-bottom">
        <form method="GET" action="{{ route('bales.show', $bale) }}">
            <div class="row">
                <div class="form-group col-md-4">
                    <label>Edad</label>
                    <select name="age_group[]" class="form-control select2" multiple data-placeholder="Todas las edades">
                        @foreach($ageGroups as $age)<option value="{{ $age }}" @selected(in_array($age, request('age_group', [])))>{{ $age }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>Sexo</label>
                    <select name="gender[]" class="form-control select2" multiple data-placeholder="Todos los sexos">
                        @foreach($genders as $gender)<option value="{{ $gender }}" @selected(in_array($gender, request('gender', [])))>{{ $gender }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>Tipo de ropa</label>
                    <select name="garment_type_id[]" class="form-control select2" multiple data-placeholder="Todos los tipos">
                        @foreach($garmentTypes as $type)<option value="{{ $type->id }}" @selected(in_array((string) $type->id, request('garment_type_id', [])))>{{ $type->name }}</option>@endforeach
                    </select>
                </div>
            </div>
            <button class="btn btn-primary"><i class="fas fa-filter"></i> Aplicar filtros</button>
            <a href="{{ route('bales.show', $bale) }}" class="btn btn-outline-secondary">Limpiar</a>
        </form>
    </div>
    <form method="GET" action="{{ route('venta.create') }}" id="formSeleccionados">
    <div class="card-body border-bottom py-2 d-flex justify-content-between align-items-center">
        <span><strong id="cantidadSeleccionada">0</strong> mercaderías seleccionadas</span>
        @can('venta crear')
        <button type="submit" class="btn btn-success" id="btnEnviarSeleccion" disabled><i class="fas fa-shopping-cart"></i> Registrar venta con selección</button>
        @endcan
    </div>
    <div class="card-body table-responsive p-0"><table class="table table-hover table-striped mb-0">
        <thead><tr><th><input type="checkbox" id="seleccionarTodos" title="Seleccionar todos los visibles"></th><th>Foto</th><th>Descripción</th><th>Edad / Sexo</th><th>Tipo / Talla</th><th>Stock</th><th>Costo</th><th>Precio de venta</th></tr></thead>
        <tbody>
        @forelse($products as $product)
            <tr>
                <td><input type="checkbox" class="producto-check" name="productos[]" value="{{ $product->id }}" @disabled($product->stock <= 0)></td>
                <td><img src="{{ $product->imagen_url }}" alt="{{ $product->descripcion }}" class="img-thumbnail" style="width:70px;height:70px;object-fit:cover"></td>
                <td>{{ $product->descripcion }}@if($product->color)<br><small class="text-muted">{{ $product->brand }}{{ $product->brand && $product->color ? ' · ' : '' }}{{ $product->color }}</small>@endif</td>
                <td>{{ $product->age_group ?: '—' }}<br><small>{{ $product->gender ?: 'Sin sexo' }}</small></td>
                <td>{{ $product->garmentType?->name ?: '—' }}<br><small>{{ $product->clothingSize?->name ?: 'Sin talla' }}</small></td>
                <td>{{ number_format($product->stock,0,',','.') }}</td>
                <td>Gs. {{ number_format($product->bale_unit_cost ?: $product->pcosto,0,',','.') }}</td>
                <td><strong>Gs. {{ number_format($product->pventa,0,',','.') }}</strong></td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-5"><i class="fas fa-box-open fa-3x mb-3 d-block"></i>No hay mercaderías para los filtros seleccionados.</td></tr>
        @endforelse
        </tbody>
    </table></div></form>
    @if($products->hasPages())<div class="card-footer">{{ $products->links() }}</div>@endif
</div>
@stop

@push('js')
<script>
$(function () {
    $('.select2').select2({ width: '100%', allowClear: true });
    const checks = $('.producto-check:not(:disabled)');
    function actualizarSeleccion() {
        const cantidad = checks.filter(':checked').length;
        $('#cantidadSeleccionada').text(cantidad);
        $('#btnEnviarSeleccion').prop('disabled', cantidad === 0);
        $('#seleccionarTodos').prop('checked', cantidad > 0 && cantidad === checks.length);
    }
    $('#seleccionarTodos').on('change', function () { checks.prop('checked', this.checked); actualizarSeleccion(); });
    checks.on('change', actualizarSeleccion);
    $('#formSeleccionados').on('submit', function (event) {
        if (!checks.is(':checked')) { event.preventDefault(); Swal.fire('Atención', 'Seleccione al menos una mercadería.', 'warning'); }
    });
});
</script>
@endpush
