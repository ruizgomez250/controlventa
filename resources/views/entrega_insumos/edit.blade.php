@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 custom-heading">{{ __('Editar Entrega de Insumos') }}</h1>
@stop

@section('css')
    <style>
        .scroll-area {
            display: block !important;
            width: 100% !important;
            overflow-x: auto !important;
            overflow-y: hidden;
            white-space: nowrap !important;
            min-width: 600px;
            border: 1px solid #dee2e6;
            border-radius: 12px;
        }
        .col-small {
            width: 80px;
            min-width: 80px;
            max-width: 80px;
            text-align: center;
        }
        .col-medium {
            width: 140px;
            min-width: 140px;
            max-width: 140px;
            text-align: center;
        }
        .col-large {
            width: 300px;
            min-width: 300px;
            max-width: 300px;
        }
        .header-row {
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            align-items: center;
            background: #0d6efd;
            color: #fff;
            font-weight: bold;
            padding: 10px 0;
            border-radius: 12px 12px 0 0;
        }
        .scroll-area .form-control {
            width: 100%;
            height: 38px;
            padding: 6px 12px;
            font-size: 14px;
        }
    </style>
@endsection

@section('content')
    <form action="{{ route('entrega_insumo.update', $entregaInsumo) }}" method="post" autocomplete="off">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <x-adminlte-input type="date" id="fecha" name="fecha" label="Fecha"
                        value="{{ $entregaInsumo->fecha }}" fgroup-class="col-md-3" required />
                    <x-adminlte-select2 name="id_persona" id="id_persona" label="Persona Receptora"
                        data-placeholder="{{ __('Seleccionar persona...') }}" fgroup-class="col-md-5" required>
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-primary">
                                <i class="fas fa-user"></i>
                            </div>
                        </x-slot>
                        @foreach ($personas as $item)
                            <option value="{{ $item->id }}" {{ $entregaInsumo->id_persona == $item->id ? 'selected' : '' }}>
                                {{ $item->nombre }} {{ $item->apellido }} ({{ $item->documento }})
                            </option>
                        @endforeach
                    </x-adminlte-select2>
                </div>

                <div class="row mb-3">
                    <x-adminlte-textarea name="observacion" label="Observación" fgroup-class="col-md-12">{{ $entregaInsumo->observacion }}
                    </x-adminlte-textarea>
                </div>

                <hr>

                <div class="scroll-area">
                    <div class="header-row">
                        <div class="col-small">{{ __('ITEM') }}</div>
                        <div class="col-large">{{ __('PRODUCTO') }}</div>
                        <div class="col-medium">{{ __('CANTIDAD') }}</div>
                        <div class="col-small"></div>
                    </div>
                    <div id="items">
                        @foreach ($entregaInsumo->detalles as $det)
                            <div class="item px-2 py-1">
                                <div class="d-flex flex-nowrap align-items-center py-2" style="white-space: nowrap;">
                                    <div class="px-1 col-small">
                                        <input type="number" class="form-control" value="{{ $loop->iteration }}" readonly>
                                    </div>
                                    <div class="px-1 col-large">
                                        <select name="id_producto[]" class="form-control select2-producto" required>
                                            <option value="">{{ __('Seleccionar producto...') }}</option>
                                            @foreach ($productos as $p)
                                                <option value="{{ $p->id }}" {{ $det->id_producto == $p->id ? 'selected' : '' }}>
                                                    {{ $p->codigo }} - {{ $p->descripcion }} (Stock: {{ $p->stock }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="px-1 col-medium">
                                        <input type="number" name="cantidad[]" class="form-control" placeholder="Cantidad" required step="any" min="0.001" value="{{ $det->cantidad }}">
                                    </div>
                                    <div class="px-1 col-small">
                                        <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                            onclick="this.closest('.item').remove(); reindexItems();">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="row mb-3 mt-2">
                    <div class="col-6">
                        <button type="button" class="btn btn-primary" onclick="addNewItem()">{{ __('Agregar Ítem') }}</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 text-right">
                        <a class="btn btn-danger mx-1" href="{{ route('entrega_insumo.index') }}">{{ __('Cancelar') }}</a>
                        <x-adminlte-button type="submit" label="Guardar Cambios" theme="primary" icon="fas fa-lg fa-save" />
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@push('js')
    <script>
        const itemsContainer = document.getElementById('items');

        function addNewItem() {
            const index = document.querySelectorAll('#items .item').length;
            const newItem = document.createElement("div");
            newItem.classList.add("item", "px-2", "py-1");

            const productosOptions = `@foreach ($productos as $p)
                <option value="{{ $p->id }}">{{ $p->codigo }} - {{ $p->descripcion }} (Stock: {{ $p->stock }})</option>
            @endforeach`;

            newItem.innerHTML = `
                <div class="d-flex flex-nowrap align-items-center py-2" style="white-space: nowrap;">
                    <div class="px-1 col-small">
                        <input type="number" class="form-control" value="${index + 1}" readonly>
                    </div>
                    <div class="px-1 col-large">
                        <select name="id_producto[]" class="form-control select2-producto" required>
                            <option value="">{{ __('Seleccionar producto...') }}</option>
                            ${productosOptions}
                        </select>
                    </div>
                    <div class="px-1 col-medium">
                        <input type="number" name="cantidad[]" class="form-control" placeholder="Cantidad" required step="any" min="0.001">
                    </div>
                    <div class="px-1 col-small">
                        <button type="button" class="btn btn-outline-danger btn-sm w-100"
                            onclick="this.closest('.item').remove(); reindexItems();">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;

            itemsContainer.appendChild(newItem);

            $(newItem).find('.select2-producto').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        }

        function reindexItems() {
            const items = document.querySelectorAll('#items .item');
            items.forEach((item, i) => {
                item.querySelector('.col-small input').value = i + 1;
            });
        }

        $(document).ready(function() {
            $('.select2-producto').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        });
    </script>
@endpush
