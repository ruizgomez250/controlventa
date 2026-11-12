<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TiendaPedido extends Model
{
    protected $guarded = [];
    protected $hidden = ['token_hash', 'solicitud_hash', 'referencia'];
    protected $casts = ['items' => 'array', 'total' => 'decimal:2'];
}
