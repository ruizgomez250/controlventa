<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Distrito extends Model
{
    protected $table = 'distritos';

    protected $fillable = ['codigo', 'nombre', 'departamento_codigo'];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_codigo', 'codigo');
    }

    public function ciudades()
    {
        return $this->hasMany(Ciudad::class, 'distrito_id');
    }
}
