<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GarmentType extends Model
{
    protected $fillable = ['name'];

    public function products()
    {
        return $this->hasMany(Producto::class);
    }
}
