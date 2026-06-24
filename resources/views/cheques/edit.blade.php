@extends('adminlte::page')

@section('content_header')
<h1 class="m-0 custom-heading">{{ __('Editar Cheque') }}</h1>
@stop

@section('content')

<div class="row">
<div class="col-12">
<div class="card">
<div class="card-body">

<form action="{{ route('cheques.update',$cheque->id) }}" method="POST">
@csrf
@method('PUT')

<div class="row">

<x-adminlte-select name="tipo" label="Tipo de Cheque" fgroup-class="col-md-3">
<x-slot name="prependSlot">
<div class="input-group-text bg-info">
<i class="fas fa-exchange-alt"></i>
</div>
</x-slot>

<option value="cobrar" {{ $cheque->tipo=='cobrar'?'selected':'' }}>{{ __('Cheque a Cobrar') }}</option>
<option value="pagar" {{ $cheque->tipo=='pagar'?'selected':'' }}>{{ __('Cheque a Pagar') }}</option>

</x-adminlte-select>

<x-adminlte-input name="numero_cheque" label="Número de Cheque"
value="{{ $cheque->numero_cheque }}"
fgroup-class="col-md-3">
<x-slot name="prependSlot">
<div class="input-group-text bg-primary">
<i class="fas fa-money-check"></i>
</div>
</x-slot>
</x-adminlte-input>

<x-adminlte-input name="banco" label="Banco"
value="{{ $cheque->banco }}"
fgroup-class="col-md-3">
<x-slot name="prependSlot">
<div class="input-group-text bg-success">
<i class="fas fa-university"></i>
</div>
</x-slot>
</x-adminlte-input>

<x-adminlte-input name="titular" label="Titular"
value="{{ $cheque->titular }}"
fgroup-class="col-md-3">
<x-slot name="prependSlot">
<div class="input-group-text bg-warning">
<i class="fas fa-user"></i>
</div>
</x-slot>
</x-adminlte-input>

</div>

<div class="row">

<x-adminlte-input name="monto"
type="number"
step="0.01"
label="Monto"
value="{{ $cheque->monto }}"
fgroup-class="col-md-3">
<x-slot name="prependSlot">
<div class="input-group-text bg-success">
<i class="fas fa-dollar-sign"></i>
</div>
</x-slot>
</x-adminlte-input>

<x-adminlte-input name="fecha_emision"
type="date"
value="{{ $cheque->fecha_emision }}"
label="Fecha Emisión"
fgroup-class="col-md-3" />

<x-adminlte-input name="fecha_cobro"
type="date"
value="{{ $cheque->fecha_cobro }}"
label="Fecha Cobro/Pago"
fgroup-class="col-md-3" />

<x-adminlte-select name="estado"
label="Estado"
fgroup-class="col-md-3">

<option value="pendiente" {{ $cheque->estado=='pendiente'?'selected':'' }}>{{ __('Pendiente') }}</option>
<option value="cobrado" {{ $cheque->estado=='cobrado'?'selected':'' }}>{{ __('Cobrado') }}</option>
<option value="pagado" {{ $cheque->estado=='pagado'?'selected':'' }}>{{ __('Pagado') }}</option>
<option value="rechazado" {{ $cheque->estado=='rechazado'?'selected':'' }}>{{ __('Rechazado') }}</option>

</x-adminlte-select>

</div>

<div class="row">

<x-adminlte-textarea name="observacion"
label="Observación"
fgroup-class="col-md-12">

{{ $cheque->observacion }}

</x-adminlte-textarea>

</div>

<div class="row">
<div class="col-md-12 text-right">

<a href="{{ route('cheques.index') }}"
class="btn btn-danger mx-1">{{ __('Cancelar') }}</a>

<x-adminlte-button
type="submit"
label="Actualizar"
theme="primary"
icon="fas fa-save"/>

</div>
</div>

</form>

</div>
</div>
</div>
</div>

@stop