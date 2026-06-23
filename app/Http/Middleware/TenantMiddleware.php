<?php

namespace App\Http\Middleware;

use App\Models\Empresa;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $parts = explode('.', $host);

        $subdominio = $parts[0] ?? null;

        if (!$subdominio || $subdominio === 'www' || $subdominio === 'localhost') {
            return $next($request);
        }

        $empresa = Empresa::where('dominio', $subdominio)->first();

        if (!$empresa) {
            return $next($request);
        }

        if (!$empresa->activo) {
            abort(403, 'La empresa está desactivada.');
        }

        if ($empresa->fecha_expiracion && now()->greaterThan($empresa->fecha_expiracion)) {
            abort(403, 'La suscripción de la empresa ha expirado.');
        }

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

        Config::set('database.default', 'tenant');

        $this->asegurarPermisos();

        View::share('empresa', $empresa);

        return $next($request);
    }

    private function asegurarPermisos(): void
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
