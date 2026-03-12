<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Cheque extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo',
        'numero_cheque',
        'banco',
        'titular',
        'monto',
        'fecha_emision',
        'fecha_cobro',
        'estado',
        'observacion'
    ];

    protected $dates = [
        'fecha_emision',
        'fecha_cobro'
    ];

    // Cheques próximos a vencer (3 días)
    public function scopeProximos($query)
    {
        return $query->where('fecha_cobro', '<=', Carbon::now()->addDays(3))
                     ->where('estado', 'pendiente');
    }

    // Cheques vencidos
    public function scopeVencidos($query)
    {
        return $query->where('fecha_cobro', '<', Carbon::now())
                     ->where('estado', 'pendiente');
    }
}
