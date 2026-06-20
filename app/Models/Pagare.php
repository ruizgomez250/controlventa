<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pagare extends Model
{
    use HasFactory;
    protected $table = "pagare";
    protected $fillable = ['fecha_emision', 'fecha_vencimiento', 'monto', 'id_venta', 'id_compra', 'fecha_pago', 'estado','caja'];
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta');
    }
    public function compra()
    {
        return $this->belongsTo(Compra_cab::class, 'id_compra');
    }
}
