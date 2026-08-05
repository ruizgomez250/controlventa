<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class TenantProvisioner
{
    public function __construct(
        private readonly TenantConnectionManager $connections,
    ) {
    }

    public function provision(array $data): Empresa
    {
        $suffix = strtolower(Str::random(10));
        $databaseName = config('tenancy.database_prefix').'db_'.$suffix;
        $databaseUsername = substr(config('tenancy.database_user_prefix').$suffix, 0, 32);
        $databasePassword = Str::password(32, symbols: false);
        $databaseCreated = false;
        $databaseUserCreated = false;
        $empresa = null;

        $this->assertSafeIdentifier($databaseName);
        $this->assertSafeIdentifier($databaseUsername);

        try {
            DB::connection('mysql')->statement(
                "CREATE DATABASE `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
            );
            $databaseCreated = true;

            DB::connection('mysql')->statement(
                "CREATE USER '{$databaseUsername}'@'%' IDENTIFIED BY '{$databasePassword}'"
            );
            $databaseUserCreated = true;

            DB::connection('mysql')->statement(
                "GRANT ALL PRIVILEGES ON `{$databaseName}`.* TO '{$databaseUsername}'@'%'"
            );

            $empresa = Empresa::query()->create([
                'nombre' => $data['nombre'],
                'dominio' => $data['dominio'],
                'database_name' => $databaseName,
                'database_host' => config('database.connections.mysql.host'),
                'database_port' => config('database.connections.mysql.port'),
                'database_username' => $databaseUsername,
                'database_password' => $databasePassword,
                'email_admin' => $data['email_admin'],
                'password_admin' => $data['password_admin'],
                'activo' => true,
                'estado' => 'activa',
                'fecha_expiracion' => now()->addDays(config('billing.subscription_days')),
            ]);

            $this->connections->connect($empresa);

            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--force' => true,
                '--path' => 'database/migrations',
            ]);

            Artisan::call('db:seed', [
                '--database' => 'tenant',
                '--class' => PermissionSeeder::class,
                '--force' => true,
            ]);

            $admin = User::query()->create([
                'name' => 'Administrador',
                'email' => $data['email_admin'],
                'password' => $data['password_admin'],
            ]);
            $admin->syncPermissions(\Spatie\Permission\Models\Permission::all());

            $this->connections->central();

            return $empresa->fresh();
        } catch (\Throwable $exception) {
            $this->connections->central();

            $empresa?->delete();

            if ($databaseCreated) {
                DB::connection('mysql')->statement("DROP DATABASE IF EXISTS `{$databaseName}`");
            }

            if ($databaseUserCreated) {
                DB::connection('mysql')->statement("DROP USER IF EXISTS '{$databaseUsername}'@'%'");
            }

            throw new RuntimeException('No se pudo aprovisionar la empresa.', previous: $exception);
        }
    }

    private function assertSafeIdentifier(string $identifier): void
    {
        if (! preg_match('/^[a-z0-9_]+$/', $identifier)) {
            throw new RuntimeException('Identificador de base de datos no válido.');
        }
    }
}
