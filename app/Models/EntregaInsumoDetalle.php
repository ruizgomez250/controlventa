<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntregaInsumoDetalle extends Model
{
    use HasFactory;
    protected $table = "entrega_insumo_detalles";
    protected $fillable = ['id_entrega', 'id_producto', 'cantidad'];

    public function entrega()
    {
        return $this->belongsTo(EntregaInsumo::class, 'id_entrega');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }
}
