<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_usuario',
        'fecha_cobro',
        'id_venta',
        'id_compra',
        'monto',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta');
    }

    public function compra()
    {
        return $this->belongsTo(Compra_cab::class, 'id_compra');
    }
}
