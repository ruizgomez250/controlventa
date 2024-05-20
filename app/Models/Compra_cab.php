<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra_cab extends Model
{
    use HasFactory;
   protected $table = "compras_cab";
    protected $fillable = ['id', 'fecha_emision', 'nro_factura', 'id_proveedor', 'condicion_de_compra', 'total_compra', 'id_estado', 'id_usuario'];

    public function estadocompra(){
        return $this->belongsTo(Opcion::class,'id_estado');
    }

    public function proveedor(){
        return $this->belongsTo(Proveedor::class,'id_proveedor');
    }

    public function usuario(){
        return $this->belongsTo(User::class,'id_usuario');
    }



}
