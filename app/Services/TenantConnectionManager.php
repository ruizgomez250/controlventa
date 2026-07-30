<?php

namespace App\Services;

use App\Models\Empresa;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class TenantConnectionManager
{
    public function connect(Empresa $empresa): void
    {
        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => $empresa->database_host,
            'port' => $empresa->database_port,
            'database' => $empresa->database_name,
            'username' => $empresa->database_username,
            'password' => $empresa->database_password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ]);

        DB::purge('tenant');
        Config::set('database.default', 'tenant');
        DB::reconnect('tenant');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function central(): void
    {
        Config::set('database.default', 'mysql');
        DB::purge('tenant');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
