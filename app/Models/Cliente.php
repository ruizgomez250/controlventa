<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;
    protected $table = 'clientes'; 
    protected  $fillable = ['id','razonsocial','ruc','direccion','correo','telefono','celular','estado','observacion'];

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_cliente');
    }

    public function getRucLimpioAttribute(): string
    {
        return preg_replace('/[^0-9]/', '', $this->ruc ?? '');
    }

    public function getRucSinDVAttribute(): ?string
    {
        $limpio = $this->ruc_limpio;
        if (strlen($limpio) > 1) {
            return substr($limpio, 0, -1);
        }
        return null;
    }

    public function getDVAttribute(): ?string
    {
        $limpio = $this->ruc_limpio;
        if (strlen($limpio) > 1) {
            return substr($limpio, -1);
        }
        return null;
    }

    public function getTieneRUCAttribute(): bool
    {
        return strlen($this->ruc_limpio) > 1;
    }
}
