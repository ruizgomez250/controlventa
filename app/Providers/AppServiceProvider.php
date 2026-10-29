<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        try {
            $grupos = [
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
            ];
            $nombres = [];
            foreach ($grupos as $model => $acciones) {
                foreach ($acciones as $accion) {
                    $nombres[] = $model . ' ' . $accion;
                }
            }
            // Una sola consulta; no se comparten resultados entre empresas/conexiones.
            $existentes = Permission::whereIn('name', $nombres)->pluck('name')->all();
            $creado = false;
            foreach (array_diff($nombres, $existentes) as $permiso) {
                Permission::create(['name' => $permiso]);
                $creado = true;
            }
            if ($creado) {
                app(PermissionRegistrar::class)->forgetCachedPermissions();
            }
        } catch (\Exception $e) {
        }
    }
}
