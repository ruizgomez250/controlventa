<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = "ventas";//le personalizo el nombre de la tabla
    protected $fillable = [
        'id_usuario', 'id_cliente', 'tipo_comprobante', 'total', 'fecha_emision', 'numero_factura', 'timbrado_factura', 'fecha_vencimiento','estado'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function detalles()
    {
        return $this->hasMany(VentaDetalle::class, 'id_venta');
    }
}
