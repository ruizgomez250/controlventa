<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Cliente extends Model
{
    use HasFactory;
    protected $table = 'clientes'; 
    //use SoftDeletes;
    protected  $fillable = ['id','razonsocial','ruc','direccion','correo','telefono','celular','estado','observacion'];
    
    // Relación con las ventas
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_cliente');
    }

}
