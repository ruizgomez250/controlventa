<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    protected $fillable = [
        'user_id',
        'concepto',
        'monto',
        'fecha',
        'metodo_pago',
        'comprobante',
        'observacion',
        'estado'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    
}

