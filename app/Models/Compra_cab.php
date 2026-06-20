<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra_cab extends Model
{
    use HasFactory;
   protected $table = "compras_cab";
    protected $fillable = ['id', 'fecha_emision', 'nro_factura', 'timbrado', 'id_proveedor', 'condicion_de_compra', 'total_compra', 'id_estado', 'id_usuario'];

    public function proveedor(){
        return $this->belongsTo(Proveedor::class,'id_proveedor');
    }

    public function usuario(){
        return $this->belongsTo(User::class,'id_usuario');
    }

    public function pagares(){
        return $this->hasMany(Pagare::class, 'id_compra');
    }

    public function detalles(){
        return $this->hasMany(Compra_det::class, 'id_compracab');
    }

    public function cajas(){
        return $this->hasMany(Caja::class, 'id_compra');
    }
}
