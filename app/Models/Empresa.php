<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class Empresa extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = [
        'nombre',
        'dominio',
        'database_name',
        'database_host',
        'database_port',
        'database_username',
        'database_password',
        'email_admin',
        'password_admin',
        'activo',
        'estado',
        'fecha_expiracion',
        'suspendida_at',
        'eliminable_at',
    ];

    protected $hidden = [
        'database_password',
        'password_admin',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_expiracion' => 'datetime',
        'suspendida_at' => 'datetime',
        'eliminable_at' => 'datetime',
        'password_admin' => 'hashed',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function setDatabasePasswordAttribute($value): void
    {
        $this->attributes['database_password'] = Crypt::encryptString($value);
    }

    public function getDatabasePasswordAttribute($value): string
    {
        return Crypt::decryptString($value);
    }

    public function scopeActivo(Builder $query): Builder
    {
        return $query
            ->where('activo', true)
            ->where('estado', 'activa')
            ->where(function (Builder $query) {
                $query->whereNull('fecha_expiracion')
                    ->orWhere('fecha_expiracion', '>', now());
            });
    }
}
