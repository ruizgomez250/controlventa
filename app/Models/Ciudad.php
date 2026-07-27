<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ciudad extends Model
{
    protected $table = 'ciudades';

    protected $fillable = ['codigo', 'nombre', 'distrito_id'];

    public function distrito()
    {
        return $this->belongsTo(Distrito::class, 'distrito_id');
    }
}
