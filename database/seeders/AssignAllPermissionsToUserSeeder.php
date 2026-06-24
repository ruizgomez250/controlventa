<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class AssignAllPermissionsToUserSeeder extends Seeder
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
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $allPermissions = [];
        foreach (self::GRUPOS as $model => $actions) {
            foreach ($actions as $action) {
                $allPermissions[] = strtolower($model) . ' ' . $action;
            }
        }

        $existing = Permission::whereIn('name', $allPermissions)->pluck('name')->toArray();
        $missing = array_diff($allPermissions, $existing);
        foreach ($missing as $name) {
            Permission::create(['name' => $name, 'guard_name' => 'web']);
        }

        $user = User::find(1);
        if (!$user) {
            $this->command->error('Usuario con id=1 no encontrado.');
            return;
        }

        $user->syncPermissions($allPermissions);

        $count = count($allPermissions);
        $this->command->info("{$count} permisos asignados al usuario '{$user->name}' (ID: 1).");
    }
}
