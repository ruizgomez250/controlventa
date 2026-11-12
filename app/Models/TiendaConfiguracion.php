<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TiendaConfiguracion extends Model
{
    protected $table = 'tienda_configuraciones';
    public $incrementing = false;
    protected $guarded = [];
    protected $casts = ['habilitada' => 'boolean'];

    public static function actual(): self
    {
        return static::firstOrCreate(['id' => 1], ['identificador' => (string) Str::uuid()])->refresh();
    }
}
