<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Cliente extends Model
{
    use HasFactory;
    //use SoftDeletes;
    protected  $fillable = ['id','razonsocial','ruc','direccion','correo','telefono','celular','estado','observacion'];
    

    public function propietariomascota(){
        return $this->hasMany(Mascota::class,'id');
    }
}
