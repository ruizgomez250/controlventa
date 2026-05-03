<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Impuesto extends Model
{
    use HasFactory;

    protected $table = 'impuestos';

    protected $fillable = [
        'descripcion',
        'valor',
    ];

    /**
     * Accesor para mostrar el valor con coma
     * Ej: 10.00 -> 10,00
     */
    public function getValorFormateadoAttribute()
    {
        return number_format($this->valor, 2, ',', '.');
    }
    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_impuesto');
    }
}
