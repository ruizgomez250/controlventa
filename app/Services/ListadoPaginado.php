<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;

class ListadoPaginado
{
    public static function aplicar(Builder $query, Request $request, array $columnas, array $relaciones, array $ordenables): LengthAwarePaginator
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'por_pagina' => ['nullable', Rule::in([25, 50, 100])],
            'orden' => ['nullable', Rule::in($ordenables)],
            'direccion' => ['nullable', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $buscar = trim($filtros['buscar'] ?? '');
        if ($buscar !== '') {
            $query->where(function (Builder $q) use ($buscar, $columnas, $relaciones) {
                foreach ($columnas as $columna) {
                    $q->orWhere($columna, 'like', '%' . $buscar . '%');
                }
                foreach ($relaciones as $relacion => $columna) {
                    $q->orWhereHas($relacion, fn (Builder $r) => $r->where($columna, 'like', '%' . $buscar . '%'));
                }
            });
        }
        $orden = $filtros['orden'] ?? 'id';
        $direccion = $filtros['direccion'] ?? 'desc';
        $query->orderBy($orden, $direccion);
        if ($orden !== 'id') {
            $query->orderBy('id', $direccion);
        }

        return $query->paginate((int) ($filtros['por_pagina'] ?? 25))->withQueryString();
    }
}
