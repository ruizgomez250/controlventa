<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    protected $table = "productos"; //le personalizo el nombre a la tabla
<<<<<<< HEAD
    protected  $fillable = ['id', 'codigo', 'descripcion', 'detalle', 'id_categoria', 'stock', 'id_medida', 'estado', 'pcosto', 'pventa', 'observacion', 'impuesto', 'imagen'];
=======
    protected  $fillable = ['id', 'codigo', 'descripcion', 'detalle', 'id_categoria', 'stock', 'id_medida', 'estado', 'pcosto', 'pventa', 'observacion', 'id_impuesto', 'pmayorista', 'cmayorista', 'dmayorista','tipo'];
>>>>>>> sisventa

    public function categoriaproducto()
    {
        return $this->belongsTo(Opcion::class, 'id_categoria');
    }


    public function unidaddemedida()
    {
        return $this->belongsTo(Opcion::class, 'id_medida');
    }
<<<<<<< HEAD
    protected $appends = ['imagen_url'];

    public function getImagenUrlAttribute()
    {
        return $this->imagen
            ? asset('storage/' . $this->imagen)
            : asset('images/default.png');
=======
    public function impuesto()
    {
        return $this->belongsTo(Impuesto::class, 'id_impuesto');
>>>>>>> sisventa
    }
}
