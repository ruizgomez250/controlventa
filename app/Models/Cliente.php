<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;
    protected $table = 'clientes'; 
    protected $attributes = ['es_contribuyente' => true];
    protected  $fillable = [
        'id', 'razonsocial', 'ruc', 'dv', 'es_contribuyente', 'tipo_contribuyente', 'tipo_identificacion',
        'numero_identificacion', 'direccion', 'correo', 'telefono', 'celular', 'estado', 'observacion',
    ];

    protected $casts = [
        'es_contribuyente' => 'boolean',
        'tipo_contribuyente' => 'integer',
        'tipo_identificacion' => 'integer',
    ];

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_cliente');
    }

    public function getRucLimpioAttribute(): string
    {
        try {
            return \App\Helpers\RucParaguayo::numeroConsulta((string) $this->ruc);
        } catch (\InvalidArgumentException $e) {
            return '';
        }
    }

    public function getRucSinDVAttribute(): ?string
    {
        return $this->ruc_limpio !== '' ? $this->ruc_limpio : null;
    }

    public function getDVAttribute(): ?string
    {
        return isset($this->attributes['dv']) ? (string) $this->attributes['dv'] : null;
    }

    public function getRucCompletoAttribute(): string
    {
        return (string) $this->ruc . ($this->dv !== null && $this->dv !== '' ? '-' . $this->dv : '');
    }

    public function getTieneRUCAttribute(): bool
    {
        return preg_match('/^[0-9]{5,8}$/', (string) $this->ruc) === 1
            && preg_match('/^\d$/', (string) $this->dv) === 1;
    }
}
