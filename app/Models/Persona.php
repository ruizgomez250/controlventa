<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;
    protected $table = "personas";
    protected $fillable = ['nombre', 'apellido', 'documento', 'telefono', 'direccion', 'observacion', 'estado'];

    public function entregas()
    {
        return $this->hasMany(EntregaInsumo::class, 'id_persona');
    }
}
