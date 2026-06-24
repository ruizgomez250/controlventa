<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class EmpresaController extends Controller
{
    public function index(): View
    {
        $empresas = Empresa::all();
        return view('empresas.index', compact('empresas'));
    }

    public function create(): View
    {
        return view('empresas.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'dominio' => 'required|string|max:255|unique:empresas,dominio|regex:/^[a-z0-9\-]+$/',
            'email_admin' => 'required|email|max:255|unique:empresas,email_admin',
            'password_admin' => 'required|string|min:8|confirmed',
        ]);

        $slug = Str::slug($request->nombre);
        $timestamp = now()->format('Ymd_His');
        $databaseName = 'empresa_' . $slug . '_' . $timestamp;
        $databaseUsername = 'user_' . $slug . '_' . now()->format('Ymd');
        $databasePassword = Str::random(16);

        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');

        try {
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            DB::statement("CREATE USER IF NOT EXISTS '{$databaseUsername}'@'%' IDENTIFIED BY '{$databasePassword}'");
            DB::statement("GRANT ALL PRIVILEGES ON `{$databaseName}`.* TO '{$databaseUsername}'@'%'");
            DB::statement("FLUSH PRIVILEGES");

            $empresa = Empresa::create([
                'nombre' => $request->nombre,
                'dominio' => $request->dominio,
                'database_name' => $databaseName,
                'database_host' => $host,
                'database_port' => $port,
                'database_username' => $databaseUsername,
                'database_password' => $databasePassword,
                'email_admin' => $request->email_admin,
                'password_admin' => $request->password_admin,
                'activo' => true,
            ]);

            Config::set('database.connections.tenant', [
                'driver' => 'mysql',
                'host' => $host,
                'port' => $port,
                'database' => $databaseName,
                'username' => $databaseUsername,
                'password' => $databasePassword,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'prefix_indexes' => true,
                'strict' => true,
                'engine' => null,
            ]);

            Config::set('database.default', 'tenant');

            Artisan::call('migrate', [
                '--force' => true,
                '--path' => 'database/migrations',
            ]);

            $adminUser = \App\Models\User::create([
                'name' => 'Administrador',
                'email' => $request->email_admin,
                'password' => $request->password_admin,
            ]);

            $this->crearPermisosBase($adminUser);

            Config::set('database.default', 'mysql');

            $adminUserCentral = \App\Models\User::create([
                'empresa_id' => $empresa->id,
                'name' => 'Administrador',
                'email' => $request->email_admin,
                'password' => $request->password_admin,
            ]);

            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $this->asignarTodosPermisos($adminUserCentral);

            return redirect()->route('empresas.index')->with('success', 'Empresa creada exitosamente.');
        } catch (\Exception $e) {
            Config::set('database.default', 'mysql');
            return redirect()->route('empresas.create')->with('error', 'Error al crear la empresa: ' . $e->getMessage());
        }
    }

    public function edit(Empresa $empresa): View
    {
        return view('empresas.edit', compact('empresa'));
    }

    public function update(Request $request, Empresa $empresa): RedirectResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'dominio' => 'required|string|max:255|unique:empresas,dominio,' . $empresa->id . '|regex:/^[a-z0-9\-]+$/',
            'password_admin' => 'nullable|string|min:8|confirmed',
            'activo' => 'boolean',
            'fecha_expiracion' => 'nullable|date',
        ]);

        $empresa->update($request->only([
            'nombre', 'dominio', 'activo', 'fecha_expiracion',
        ]));

        if ($request->filled('password_admin')) {
            $empresa->update(['password_admin' => $request->password_admin]);

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

            \App\Models\User::where('email', $empresa->email_admin)
                ->update(['password' => $request->password_admin]);

            Config::set('database.default', 'mysql');

            \App\Models\User::where('empresa_id', $empresa->id)
                ->update(['password' => $request->password_admin]);
        }

        return redirect()->route('empresas.index')->with('success', 'Empresa actualizada exitosamente.');
    }

    public function destroy(Empresa $empresa): RedirectResponse
    {
        try {
            $databaseName = $empresa->database_name;
            $databaseUsername = $empresa->database_username;

            $empresa->delete();

            DB::statement("DROP DATABASE IF EXISTS `{$databaseName}`");
            DB::statement("DROP USER IF EXISTS '{$databaseUsername}'@'%'");
            DB::statement("FLUSH PRIVILEGES");

            return redirect()->route('empresas.index')->with('success', 'Empresa eliminada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('empresas.index')->with('error', 'Error al eliminar la empresa: ' . $e->getMessage());
        }
    }

    private function crearPermisosBase(\App\Models\User $adminUser = null): void
    {
        $grupos = [
            'cliente' => ['leer', 'crear', 'editar', 'borrar'],
            'proveedor' => ['leer', 'crear', 'editar', 'borrar'],
            'producto' => ['leer', 'crear', 'editar', 'borrar', 'comercial', 'stock'],
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

        $allPermissions = [];

        foreach ($grupos as $model => $acciones) {
            foreach ($acciones as $accion) {
                $permiso = $model . ' ' . $accion;
                if (!Permission::where('name', $permiso)->exists()) {
                    Permission::create(['name' => $permiso]);
                }
                $allPermissions[] = $permiso;
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if ($adminUser) {
            $adminUser->syncPermissions($allPermissions);
        }
    }

    private function asignarTodosPermisos(\App\Models\User $user): void
    {
        $permissions = Permission::all()->pluck('name');
        $user->syncPermissions($permissions);
    }
}
