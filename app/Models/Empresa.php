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
        'fecha_expiracion',
    ];

    protected $hidden = [
        'database_password',
        'password_admin',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'fecha_expiracion' => 'datetime',
            'password_admin' => 'hashed',
        ];
    }

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
        return $query->where('activo', true);
    }
}
