<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class Cita extends Model
{
    use HasFactory;
    protected  $fillable = ['id', 'fecha', 'hora', 'estado_id', 'tipo_id', 'mascota_id'];

    public function estadocita(){
        return $this->belongsTo(Opcion::class,'estado_id');
    }

    public function tipoconsulta(){
        return $this->belongsTo(Opcion::class,'tipo_id');
    }

    public function mascota(){
        return $this->belongsTo(Mascota::class,'mascota_id');
    }

}
