<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Raza extends Model
{
    use HasFactory;
    protected $fillable =[ 'id', 'nombre','claseanimal_id', 'estado_id'];
    public function especieanimal(){
        return $this->belongsTo(Opcion::class,'claseanimal_id');
    }

    public function razaan(){
        return $this->hasMany(Mascota::class,'id');
    }
}
