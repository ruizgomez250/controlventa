<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = "proveedores";//le personalizo el nombre de la tabla
    use HasFactory;
   
    protected  $fillable = ['id','razonsocial', 'ruc', 'direccion', 'correo', 'telefono', 'celular', 'observacion', 'id_estado'];
   
    public function estadoproveedor(){
        return $this->belongsTo(Opcion::class,'id_estado');
    }

    public function proveedorcompra(){
        return $this->hasMany(Compra_cab::class,'id');
    }

}
