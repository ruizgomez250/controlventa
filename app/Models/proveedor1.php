<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class proveedor1 extends Model
{
    protected $table = "proveedores_1";//le personalizo el nombre de la tabla
    use HasFactory;
   
    protected  $fillable = ['id','razonsocial', 'ruc', 'direccion', 'correo', 'telefono', 'celular', 'observacion', 'id_estado'];
   
}
