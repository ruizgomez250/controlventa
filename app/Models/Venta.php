<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'ventas'; //le personalizo el nombre de la tabla

    protected $fillable = [
        'id_usuario',
        'client_uuid',
        'id_cliente',
        'tipo_comprobante',
        'payment_method',
        'total',
        'fecha_emision',
        'numero_factura',
        'timbrado_factura',
        'fecha_vencimiento',
        'estado',
    ];

    protected static function booted()
    {
        static::created(function ($venta) {
            Auditoria::create([
                'user_id' => auth()->id() ?: $venta->id_usuario,
                'accion' => 'venta_creada',
                'entidad_tipo' => 'Venta',
                'entidad_id' => $venta->id,
                'descripcion' => 'Creó la venta N° '.($venta->numero_factura ?: $venta->id).' por Gs. '.number_format($venta->total, 0, ',', '.'),
            ]);
        });

        static::updated(function ($venta) {
            if ($venta->isDirty('estado') && $venta->estado == 0) {
                Auditoria::create([
                    'user_id' => auth()->id() ?: $venta->id_usuario,
                    'accion' => 'factura_anulada',
                    'entidad_tipo' => 'Venta',
                    'entidad_id' => $venta->id,
                    'descripcion' => 'Anuló la factura N° '.($venta->numero_factura ?: $venta->id).' del cliente '.($venta->cliente->razonsocial ?? 'N/A'),
                ]);
            }
        });
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function detalles()
    {
        return $this->hasMany(VentaDetalle::class, 'id_venta');
    }

    public function pagare()
    {
        return $this->hasOne(Pagare::class, 'id_venta');
    }

    public function pagares()
    {
        return $this->hasMany(Pagare::class, 'id_venta');
    }
}
