<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionsByModel = [
            'cliente' => ['leer', 'crear', 'editar', 'borrar'],
            'proveedor' => ['leer', 'crear', 'editar', 'borrar'],
            'producto' => ['leer', 'crear', 'editar', 'borrar'],
            'compra' => ['leer', 'crear', 'editar', 'borrar'],
            'venta' => ['leer', 'crear', 'editar', 'borrar'],
            'caja' => ['leer', 'crear', 'editar', 'borrar'],
            'cajareporte' => ['leer', 'crear', 'editar', 'borrar'],
            'reporte' => ['leer', 'crear', 'editar', 'borrar'],
            'rol' => ['leer', 'crear', 'editar', 'borrar'],
            'gasto' => ['leer', 'crear', 'editar', 'borrar'],
            'cheque' => ['leer', 'crear', 'editar', 'borrar'],
            'tabla_porcentaje' => ['leer', 'modificar'],
            'configuracion' => ['modificar'],
            'empresa' => ['leer', 'crear', 'editar', 'borrar'],
            'persona' => ['leer', 'crear', 'editar', 'borrar'],
            'entrega_insumo' => ['leer', 'crear', 'editar', 'borrar'],
        ];

        $allPermissions = [];
        foreach ($permissionsByModel as $model => $actions) {
            foreach ($actions as $action) {
                $permissionName = strtolower($model) . ' ' . $action;
                $allPermissions[] = $permissionName;
                Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
            }
        }

        $this->command->info('Permisos creados: ' . implode(', ', $allPermissions));
    }
}
