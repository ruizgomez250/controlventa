<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoPrecioTier extends Model
{
    use HasFactory;
    protected $table = 'producto_precio_tiers';
    protected $fillable = ['id_producto', 'cantidad_desde', 'precio_unitario', 'orden'];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }
}
