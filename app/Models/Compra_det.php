<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra_det extends Model
{
    use HasFactory;
    protected $table = "compras_det";
    protected $filleable = ['cantidad', 'descripcion', 'id_compracab', 'id_productos', 'monto', 'precio_u', 'tipo_impuesto'];
    public function compraCab(){
        return $this->belongsTo(Compra_cab::class,'id_compracab');
    }
    public function productos(){
        return $this->belongsTo(Producto::class,'id_productos');
    }
}
