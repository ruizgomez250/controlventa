<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminFullPermissionsSeeder extends Seeder
{
    private const GRUPOS = [
        'cliente'           => ['leer', 'crear', 'editar', 'borrar'],
        'proveedor'         => ['leer', 'crear', 'editar', 'borrar'],
        'producto'          => ['leer', 'crear', 'editar', 'borrar', 'comercial', 'stock'],
        'compra'            => ['leer', 'crear', 'editar', 'borrar'],
        'venta'             => ['leer', 'crear', 'editar', 'borrar'],
        'caja'              => ['leer', 'crear', 'editar', 'borrar'],
        'cajareporte'       => ['leer', 'crear', 'editar', 'borrar'],
        'reporte'           => ['leer', 'crear', 'editar', 'borrar'],
        'rol'               => ['leer', 'crear', 'editar', 'borrar'],
        'gasto'             => ['leer', 'crear', 'editar', 'borrar'],
        'cheque'            => ['leer', 'crear', 'editar', 'borrar'],
        'tabla_porcentaje'  => ['leer', 'modificar'],
        'configuracion'     => ['modificar'],
        'empresa'           => ['leer', 'crear', 'editar', 'borrar'],
        'persona'           => ['leer', 'crear', 'editar', 'borrar'],
        'entrega_insumo'    => ['leer', 'crear', 'editar', 'borrar'],
        'fardo'             => ['leer', 'crear', 'editar', 'finalizar'],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $allPermissions = [];

        foreach (self::GRUPOS as $model => $actions) {
            foreach ($actions as $action) {
                $permissionName = strtolower($model) . ' ' . $action;
                $allPermissions[] = $permissionName;

                Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ]);
            }
        }

        $role = Role::firstOrCreate([
            'name' => 'Administrador',
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($allPermissions);

        $user = User::updateOrCreate(
            [
                'email' => 'admin@controlventa.com',
            ],
            [
                'name' => 'Administrador',
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles([$role]);

        // También asigna permisos directos al usuario.
        // Esto ayuda si en alguna parte del sistema se consulta can()
        // directamente contra permisos del usuario.
        $user->syncPermissions($allPermissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info('Usuario administrador creado/actualizado correctamente.');
        $this->command->info('Email: admin@controlventa.com');
        $this->command->info('Password: 12345678');
        $this->command->info('Rol asignado: Administrador');
        $this->command->info('Permisos asignados: ' . count($allPermissions));
    }
}
