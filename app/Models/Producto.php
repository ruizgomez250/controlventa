<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    protected $table = "productos"; //le personalizo el nombre a la tabla
    protected $fillable = ['id', 'client_uuid', 'bale_id', 'bale_unit_cost', 'codigo', 'descripcion', 'detalle', 'id_categoria', 'garment_type_id', 'clothing_size_id', 'brand', 'color', 'age_group', 'collection', 'stock', 'stock_minimo', 'stock_inicial', 'stock_maximo', 'ubicacion_deposito', 'id_medida', 'estado', 'pcosto', 'pventa', 'observacion', 'imagen', 'id_impuesto', 'id_proveedor', 'pmayorista', 'cmayorista', 'dmayorista', 'tipo'];

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

    public function garmentType()
    {
        return $this->belongsTo(GarmentType::class);
    }

    public function bale()
    {
        return $this->belongsTo(Bale::class);
    }

    public function additionalBales()
    {
        return $this->belongsToMany(Bale::class, 'bale_product_items')->withPivot('quantity')->withTimestamps();
    }

    public function clothingSize()
    {
        return $this->belongsTo(ClothingSize::class);
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
