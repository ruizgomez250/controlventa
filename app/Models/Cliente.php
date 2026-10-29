<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;
    protected $table = 'clientes'; 
    protected  $fillable = ['id','razonsocial','ruc','dv','direccion','correo','telefono','celular','estado','observacion'];

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
