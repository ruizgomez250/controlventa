<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActividadEconomica extends Model
{
    protected $table = 'actividades_economicas';

    protected $primaryKey = 'codigo';

    public $incrementing = false;

    protected $keyType = 'integer';

    protected $fillable = ['codigo', 'descripcion'];

    public function getDescripcionConCodigoAttribute(): string
    {
        return "{$this->codigo} - {$this->descripcion}";
    }
}
