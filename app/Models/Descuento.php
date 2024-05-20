<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Descuento extends Model
{
    use HasFactory;
    protected $table = 'descuentos'; // Nombre de la tabla en la base de datos

    protected $fillable = [
        'id_venta', // Clave foránea de la tabla ventas en su campo id
        'monto',    // Monto del descuento
        'estado', 
    ];

    // Relación con el modelo Venta
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta');
    }
}
