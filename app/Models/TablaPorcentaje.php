<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TablaPorcentaje extends Model
{
    use HasFactory;
    protected $table = 'tabla_porcentajes'; // Nombre de la tabla en la base de datos

    protected $fillable = [
        'porcentaje',
        'cuota',
        'estado',
    ];
}
