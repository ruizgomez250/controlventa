<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;
    protected $table = "roles";
    protected $fillable = [
        'id_usuario',
        'nombre_modelo',
        'leer',
        'borrar',
        'crear',
        'editar',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
