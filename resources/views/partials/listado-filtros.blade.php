<form method="GET" action="{{ url()->current() }}" class="mb-3">
    <div class="form-row align-items-end">
        <div class="form-group col-md-4">
            <label for="listado-buscar">Buscar</label>
            <input type="search" id="listado-buscar" name="buscar" value="{{ request('buscar') }}"
                class="form-control" placeholder="Nombre, documento, factura…" maxlength="100">
        </div>
        <div class="form-group col-md-2">
            <label for="listado-cantidad">Por página</label>
            <select id="listado-cantidad" name="por_pagina" class="form-control">
                @foreach ([25, 50, 100] as $cantidad)
                    <option value="{{ $cantidad }}" @selected(request('por_pagina', 25) == $cantidad)>{{ $cantidad }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-2">
            <label for="listado-orden">Ordenar por</label>
            <select id="listado-orden" name="orden" class="form-control">
                @foreach ($ordenables as $columna)
                    <option value="{{ $columna }}" @selected(request('orden', 'id') === $columna)>
                        {{ ['id' => 'ID', 'razonsocial' => 'Razón social', 'ruc' => 'RUC', 'correo' => 'Correo', 'estado' => 'Estado', 'id_estado' => 'Estado', 'fecha_emision' => 'Fecha', 'nro_factura' => 'Factura', 'numero_factura' => 'Factura', 'timbrado' => 'Timbrado', 'timbrado_factura' => 'Timbrado', 'total' => 'Total', 'total_compra' => 'Total'][$columna] }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-2">
            <label for="listado-direccion">Dirección</label>
            <select id="listado-direccion" name="direccion" class="form-control">
                <option value="desc" @selected(request('direccion', 'desc') === 'desc')>Descendente</option>
                <option value="asc" @selected(request('direccion') === 'asc')>Ascendente</option>
            </select>
        </div>
        <div class="form-group col-md-2">
            <button class="btn btn-primary" type="submit">Aplicar</button>
            <a href="{{ url()->current() }}" class="btn btn-outline-secondary mt-1 mt-xl-0">Limpiar</a>
        </div>
    </div>
</form>
