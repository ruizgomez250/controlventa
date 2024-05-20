<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    protected $table = "ventas_detalles";//le personalizo el nombre de la tabla
    protected $fillable = [
        'id_venta', 'id_producto', 'cantidad', 'descripcion', 'monto', 'precio_u', 'tipo_impuesto'
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }
}
