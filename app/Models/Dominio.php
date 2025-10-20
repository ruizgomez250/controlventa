<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dominio extends Model
{
    use HasFactory;

    protected $table = 'dominios'; // Especificar el nombre de la tabla

    // Definir los campos que se pueden asignar en masa
    protected $fillable = [
        'id',
        'descripcion',
        'estado',
    ];

    // Relación con la tabla 'opciones'
    public function opciones()
    {
        return $this->hasMany(Opcion::class, 'id_dominio');
    }
}
