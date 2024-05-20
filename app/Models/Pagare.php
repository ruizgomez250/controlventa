<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pagare extends Model
{
    use HasFactory;
    protected $table = "pagare";//le personalizo el nombre de la tabla
    protected $fillable = ['fecha_emision', 'fecha_vencimiento', 'monto', 'id_venta', 'fecha_pago', 'estado','caja']; // Campos que se pueden asignar en masa
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta');
    }
}
