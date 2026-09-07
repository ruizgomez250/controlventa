@extends('adminlte::page')

@section('content_header')
<div class="d-flex justify-content-between align-items-center"><h1>Fardos</h1>@can('fardo crear')<a href="{{ route('bales.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo fardo</a>@endcan</div>
@stop

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="card"><div class="card-body table-responsive"><table class="table table-bordered table-hover">
<thead><tr><th>Foto</th><th>Código</th><th>Fecha</th><th>Tipo / Proveedor</th><th>Costo total</th><th>Prendas</th><th>Costo unitario</th><th>Estado</th><th>Acciones</th></tr></thead>
<tbody>@foreach($bales as $bale)<tr>
<td>@if($bale->image_url)<img src="{{ $bale->image_url }}" alt="{{ $bale->code }}" class="img-thumbnail" style="width:72px;height:72px;object-fit:cover">@else<span class="text-muted">Sin foto</span>@endif</td><td><strong>{{ $bale->code }}</strong></td><td>{{ $bale->purchase_date->format('d/m/Y') }}</td><td>{{ $bale->bale_type ?: '—' }}<br><small>{{ $bale->supplier_name }}</small></td>
<td>Gs. {{ number_format($bale->total_cost, 0, ',', '.') }}</td><td>{{ $bale->products_count }} / {{ $bale->estimated_quantity ?: '?' }} @if($bale->damaged_quantity)<br><small class="text-danger">{{ $bale->damaged_quantity }} dañadas</small>@endif</td>
<td>Gs. {{ number_format($bale->unit_cost, 0, ',', '.') }}</td><td><span class="badge badge-{{ $bale->status === 'finalized' ? 'success' : ($bale->status === 'in_progress' ? 'warning' : 'secondary') }}">{{ ['pending'=>'Pendiente','in_progress'=>'En proceso','finalized'=>'Finalizado'][$bale->status] }}</span></td>
<td><a href="{{ route('bales.show',$bale) }}" class="btn btn-sm btn-info" title="Ver mercaderías"><i class="fas fa-box-open"></i> Mercaderías</a> @can('fardo editar')<a href="{{ route('bales.edit',$bale) }}" class="btn btn-sm btn-outline-primary" title="Editar fardo"><i class="fas fa-edit"></i></a>@endcan</td>
</tr>@endforeach</tbody></table></div></div>
@stop
