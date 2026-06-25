<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    protected $table = "productos"; //le personalizo el nombre a la tabla
    protected $fillable = ['id', 'codigo', 'descripcion', 'detalle', 'id_categoria', 'stock', 'stock_minimo', 'stock_inicial', 'stock_maximo', 'ubicacion_deposito', 'id_medida', 'estado', 'pcosto', 'pventa', 'observacion', 'imagen', 'id_impuesto', 'id_proveedor', 'pmayorista', 'cmayorista', 'dmayorista', 'tipo'];

    protected static function booted()
    {
        static::updated(function ($producto) {
            $cambios = [];
            foreach ($producto->getDirty() as $campo => $valor) {
                if ($campo === 'updated_at') continue;
                $original = $producto->getOriginal($campo);
                $cambios[] = "$campo: $original → $valor";
            }
            if (!empty($cambios)) {
                Auditoria::create([
                    'user_id' => auth()->id(),
                    'accion' => 'producto_modificado',
                    'entidad_tipo' => 'Producto',
                    'entidad_id' => $producto->id,
                    'descripcion' => 'Modificó el producto "' . $producto->descripcion . '" (cambios: ' . implode(', ', $cambios) . ')',
                ]);
            }
        });
    }

    public function categoriaproducto()
    {
        return $this->belongsTo(Opcion::class, 'id_categoria');
    }


    public function unidaddemedida()
    {
        return $this->belongsTo(Opcion::class, 'id_medida');
    }
    protected $appends = ['imagen_url'];

    public function getImagenUrlAttribute()
    {
        return $this->imagen
            ? asset('storage/' . $this->imagen)
            : asset('images/default.png');
    }

    public function impuesto()
    {
        return $this->belongsTo(Impuesto::class, 'id_impuesto');
    }

    public function precioTiers()
    {
        return $this->hasMany(ProductoPrecioTier::class, 'id_producto')->orderBy('cantidad_desde');
    }

    public function proveedorPrincipal()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }
}
