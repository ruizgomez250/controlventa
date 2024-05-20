<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mascota extends Model
{
    use HasFactory;
    protected $table = "mascotas";
    protected  $fillable = [ 'id', 'nombre', 'foto', 'edad', 'sexo_id', 'raza_id', 'propietario_id', 'estado_id'];
    
    public function sexoanimal()
    {
        return $this->belongsTo(Opcion::class, 'sexo_id');
    }

    public function razaanimal()
    {
        return $this->belongsTo(Raza::class, 'raza_id');
    }

    public function propietario()
    {
        return $this->belongsTo(Cliente::class, 'propietario_id');
    }
}
