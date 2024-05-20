<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;
    protected  $fillable = ['id', 'fecha', 'hora', 'estado_id', 'tipo_id', 'mascota_id'];
    public function categoriaproductos(){
        return $this->hasMany(Cliente::class,'id');
    }
}
