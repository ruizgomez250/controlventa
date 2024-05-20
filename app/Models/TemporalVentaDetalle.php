<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemporalVentaDetalle extends Model
{
    use HasFactory;
    protected $table = 'temporal_detalle_venta';

    protected $fillable = [
        'producto_id',
        'user_id',
        // Agrega aquí cualquier otro campo que necesites para tu tabla
    ];

    // Relación con el modelo Producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    // Relación con el modelo User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
