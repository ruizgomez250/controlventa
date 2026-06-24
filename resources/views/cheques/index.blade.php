@extends('adminlte::page')

@section('content_header')

<div class="row">

<div class="col-6">
<h1 class="m-0 custom-heading">{{ __('Lista de Cheques') }}</h1>
</div>

<div class="col-6">
<a href="{{ route('cheques.create') }}"
class="btn btn-primary"
style="float:right;">{{ __('Registrar Nuevo Cheque') }}</a>
</div>

</div>

@stop

@section('content')

<div class="row">
<div class="col-12">
<div class="card">
<div class="card-body">

<x-adminlte-datatable id="table1"
:heads="$heads"
head-theme="dark"
theme="light"
striped
hoverable
with-buttons>

@foreach ($cheques as $row)

<tr>

<td>{{ $loop->iteration }}</td>
<td>

@if($row->tipo == 'cobrar')

<span class="badge badge-success">{{ __('💰 Cobro') }}</span>

@elseif($row->tipo == 'pagar')

<span class="badge badge-danger">{{ __('💸 Pago') }}</span>

@else

<span class="badge badge-secondary">
{{ ucfirst($row->tipo) }}
</span>

@endif

</td>
<td>{{ $row->numero_cheque }}</td>
<td>{{ $row->banco }}</td>
<td>{{ $row->titular }}</td>
<td>{{ number_format($row->monto,0,',','.') }}</td>
<td>{{ $row->fecha_emision }}</td>
<td>{{ $row->fecha_cobro }}</td>
<td>

@php
$hoy = \Carbon\Carbon::today();
$cobro = \Carbon\Carbon::parse($row->fecha_cobro);
@endphp

@if($row->estado == 'cobrado')

<span class="badge badge-success">{{ __('🟢 Cobrado') }}</span>

@elseif($cobro->lt($hoy))

<span class="badge badge-danger">{{ __('🔴 Vencido') }}</span>

@elseif($cobro->between($hoy, $hoy->copy()->addDays(5)))

<span class="badge badge-warning">{{ __('🟡 Próximo') }}</span>

@else

<span class="badge badge-secondary">{{ __('Pendiente') }}</span>

@endif

</td>

<td style="float:right;">

<a href="{{ route('cheques.edit',$row->id) }}"
class="btn btn-sm btn-outline-secondary">
<i class="fa fa-sm fa-fw fa-pen"></i>
</a>

<form id="delete-form"
action="{{ route('cheques.destroy',$row->id) }}"
method="POST"
class="d-inline">

@csrf
@method('DELETE')

<button type="button"
class="btn btn-sm btn-outline-secondary"
onclick="borrar(this)">

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

@stop


@push('js')

<script>

function borrar(btn){

Swal.fire({
title:'¿Estás seguro?',
text:'Esta acción no se puede deshacer.',
icon:'warning',
showCancelButton:true,
confirmButtonColor:'#3085d6',
cancelButtonColor:'#d33',
confirmButtonText:'Sí, eliminar'
}).then((result)=>{

if(result.isConfirmed){

btn.closest('form').submit();

}

});

}

var successMessage="{{ session('success') }}";
var errorMessage="{{ session('error') }}";

if(successMessage){
Swal.fire('Éxito',successMessage,'success');
}
else if(errorMessage){
Swal.fire('Error',errorMessage,'error');
}

</script>

@endpush