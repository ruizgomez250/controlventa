<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntregaInsumo extends Model
{
    use HasFactory;
    protected $table = "entrega_insumos";
    protected $fillable = ['fecha', 'id_persona', 'observacion', 'id_usuario', 'estado'];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function detalles()
    {
        return $this->hasMany(EntregaInsumoDetalle::class, 'id_entrega');
    }
}
