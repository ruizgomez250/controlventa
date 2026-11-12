<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TiendaProducto extends Model
{
    protected $table = 'tienda_productos';
    protected $primaryKey = 'producto_id';
    public $incrementing = false;
    protected $guarded = [];
    protected $casts = ['visible' => 'boolean', 'premium' => 'boolean', 'destacado' => 'boolean', 'precio_oferta' => 'decimal:2', 'oferta_hasta' => 'datetime'];
}
