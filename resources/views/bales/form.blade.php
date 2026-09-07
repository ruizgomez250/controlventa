@extends('adminlte::page')
@section('content_header')<h1>{{ $bale->exists ? 'Editar fardo' : 'Nuevo fardo' }}</h1>@stop
@section('content')
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="card"><form method="POST" action="{{ $bale->exists ? route('bales.update',$bale) : route('bales.store') }}">@csrf @if($bale->exists)@method('PUT')@endif
<div class="card-body"><div class="row">
@if($bale->image_url)<div class="form-group col-12"><label>Foto registrada desde la APK</label><br><img src="{{ $bale->image_url }}" alt="Foto de {{ $bale->code }}" class="img-thumbnail" style="max-width:320px;max-height:220px;object-fit:cover"></div>@endif
<div class="form-group col-md-4"><label>Código (automático si se deja vacío)</label><input class="form-control" name="code" value="{{ old('code',$bale->code) }}"></div>
<div class="form-group col-md-4"><label>Fecha de compra *</label><input type="date" class="form-control" required name="purchase_date" value="{{ old('purchase_date',$bale->purchase_date?->format('Y-m-d') ?: now()->format('Y-m-d')) }}"></div>
<div class="form-group col-md-4"><label>Cantidad estimada</label><input type="number" min="1" class="form-control" name="estimated_quantity" value="{{ old('estimated_quantity',$bale->estimated_quantity) }}"></div>
<div class="form-group col-md-6"><label>Tipo de fardo</label><input class="form-control" name="bale_type" value="{{ old('bale_type',$bale->bale_type) }}"></div>
<div class="form-group col-md-6"><label>Proveedor</label><select class="form-control" name="supplier_id"><option value="">Sin proveedor</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(old('supplier_id',$bale->supplier_id)==$supplier->id)>{{ $supplier->razonsocial }} — {{ $supplier->ruc }}</option>@endforeach</select><small><a href="{{ route('proveedor.create') }}" target="_blank">Registrar nuevo proveedor</a></small></div>
<div class="form-group col-md-4"><label>Importe de compra *</label><input type="number" min="0" step="1" required class="form-control" name="purchase_amount" value="{{ old('purchase_amount',$bale->purchase_amount) }}"></div>
<div class="form-group col-md-4"><label>Transporte</label><input type="number" min="0" step="1" class="form-control" name="freight_amount" value="{{ old('freight_amount',$bale->freight_amount ?: 0) }}"></div>
<div class="form-group col-md-4"><label>Otros gastos</label><input type="number" min="0" step="1" class="form-control" name="other_costs" value="{{ old('other_costs',$bale->other_costs ?: 0) }}"></div>
<div class="form-group col-12"><label>Observaciones</label><textarea class="form-control" name="notes">{{ old('notes',$bale->notes) }}</textarea></div>
</div></div><div class="card-footer d-flex justify-content-between"><a href="{{ route('bales.index') }}" class="btn btn-outline-secondary">Cancelar</a><button class="btn btn-primary">Guardar</button></div></form></div>
@if($bale->exists && $bale->status !== 'finalized')<div class="card card-warning"><div class="card-header"><h3 class="card-title">Finalizar fardo</h3></div><form method="POST" action="{{ route('bales.finalize',$bale) }}">@csrf<div class="card-body row">
<div class="form-group col-md-6"><label>Cantidad real</label><input type="number" min="1" required class="form-control" name="actual_quantity" value="{{ $bale->actual_quantity ?: max(1,$bale->products_count ?? 1) }}"></div>
<div class="form-group col-md-6"><label>Prendas dañadas/no vendibles</label><input type="number" min="0" required class="form-control" name="damaged_quantity" value="{{ $bale->damaged_quantity ?: 0 }}"></div>
</div><div class="card-footer text-right"><button class="btn btn-warning">Finalizar y calcular costos</button></div></form></div>@endif
@stop
