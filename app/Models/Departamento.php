<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    protected $table = 'departamentos';

    protected $primaryKey = 'codigo';

    public $incrementing = false;

    protected $fillable = ['codigo', 'nombre'];

    public function distritos()
    {
        return $this->hasMany(Distrito::class, 'departamento_codigo', 'codigo');
    }
}
