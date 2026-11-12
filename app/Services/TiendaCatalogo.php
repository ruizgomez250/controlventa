<?php

namespace App\Services;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Builder;

class TiendaCatalogo
{
    public function query(): Builder
    {
        return Producto::query()->where('estado', 1)->where('pventa', '>', 0)
            ->where(fn (Builder $q) => $q->whereDoesntHave('tienda')->orWhereHas('tienda', fn (Builder $t) => $t->where('visible', true)))
            ->with(['tienda', 'categoriaproducto', 'clothingSize', 'garmentType']);
    }

    public function oferta(Producto $producto): bool
    {
        $t = $producto->tienda;
        return $t && $t->precio_oferta !== null && (float) $t->precio_oferta > 0
            && (float) $t->precio_oferta < (float) $producto->pventa
            && (!$t->oferta_hasta || $t->oferta_hasta->isFuture());
    }

    public function producto(Producto $p): array
    {
        $oferta = $this->oferta($p);
        $precio = $oferta ? (float) $p->tienda->precio_oferta : (float) $p->pventa;
        return [
            'id' => $p->id, 'codigo' => $p->codigo, 'nombre' => $p->descripcion,
            'descripcion' => $p->detalle, 'precio' => $precio,
            'precio_anterior' => $oferta ? (float) $p->pventa : null,
            'descuento' => $oferta ? (int) round(100 * (1 - $precio / (float) $p->pventa)) : 0,
            'oferta_hasta' => $oferta ? $p->tienda->oferta_hasta?->toIso8601String() : null,
            'stock' => max(0, (float) $p->stock),
            'premium' => (bool) $p->tienda?->premium, 'destacado' => (bool) $p->tienda?->destacado,
            'categoria_id' => $p->id_categoria, 'categoria' => $p->categoriaproducto?->descripcion,
            'marca' => $p->brand, 'color' => $p->color,
            'talle' => $p->clothingSize?->name,
            'imagen' => $p->imagen ? route('tienda.api.imagen', ['id' => $p->id]) : null,
        ];
    }
}
