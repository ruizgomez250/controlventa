<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'auditoria';

    protected $fillable = [
        'user_id',
        'accion',
        'entidad_tipo',
        'entidad_id',
        'descripcion',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
