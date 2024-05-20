<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opcion extends Model
{
    protected $table = "opciones";//le personalizo el nombre de la tabla
    protected $fillable = ['id','descripcion', 'id_dominio']; // Campos que se pueden asignar en masa

    use HasFactory;
    public function opcionclientes(){
        return $this->hasMany(Cliente::class,'id');
    }

    public function opcionproveedores(){
        return $this->hasMany(Proveedor::class,'id');
    }

    public function opcionproductos(){
        return $this->hasMany(Producto::class,'id');
    }

    public function opcionsexo(){
        return $this->hasMany(Mascota::class,'id');
    }

    public function opcionespecie(){
        return $this->hasMany(Raza::class,'id');
    }

    public function opcioncita(){
        return $this->hasMany(Cita::class,'id');
    }

    public function opcioncompra(){
        return $this->hasMany(Compra::class,'id');
    }


}
