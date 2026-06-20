<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
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
            ];
            $creado = false;
            foreach ($grupos as $model => $acciones) {
                foreach ($acciones as $accion) {
                    $permiso = $model . ' ' . $accion;
                    if (!Permission::where('name', $permiso)->exists()) {
                        Permission::create(['name' => $permiso]);
                        $creado = true;
                    }
                }
            }
            if ($creado) {
                app(PermissionRegistrar::class)->forgetCachedPermissions();
            }
        } catch (\Exception $e) {
        }
    }
}
