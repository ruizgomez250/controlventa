<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    protected $table = "productos";//le personalizo el nombre a la tabla
    protected  $fillable = ['id', 'codigo', 'descripcion', 'detalle', 'id_categoria','stock','id_medida', 'estado','pcosto', 'pventa', 'observacion', 'impuesto'];
    
    public function categoriaproducto(){
        return $this->belongsTo(Opcion::class,'id_categoria');
    }


    public function unidaddemedida(){
        return $this->belongsTo(Opcion::class,'id_medida');
    }
}
