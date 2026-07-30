<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Services\TenantConnectionManager;
use App\Services\TenantProvisioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(Request $request, TenantProvisioner $provisioner): RedirectResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'dominio' => 'required|string|max:255|unique:empresas,dominio|regex:/^[a-z0-9\-]+$/',
            'email_admin' => 'required|email|max:255|unique:empresas,email_admin',
            'password_admin' => 'required|string|min:8|confirmed',
        ]);

        try {
            $provisioner->provision($request->only([
                'nombre',
                'dominio',
                'email_admin',
                'password_admin',
            ]));

            return redirect()->route('empresas.index')->with('success', 'Empresa creada exitosamente.');
        } catch (\Exception $e) {
            report($e);

            return redirect()
                ->route('empresas.create')
                ->withInput($request->except(['password_admin', 'password_admin_confirmation']))
                ->with('error', 'No se pudo crear la empresa. Revisá el registro del sistema.');
        }
    }

    public function edit(Empresa $empresa): View
    {
        return view('empresas.edit', compact('empresa'));
    }

    public function update(
        Request $request,
        Empresa $empresa,
        TenantConnectionManager $connections,
    ): RedirectResponse {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'dominio' => 'required|string|max:255|unique:empresas,dominio,'.$empresa->id.'|regex:/^[a-z0-9\-]+$/',
            'password_admin' => 'nullable|string|min:8|confirmed',
            'activo' => 'boolean',
            'fecha_expiracion' => 'nullable|date',
        ]);

        $empresa->update($request->only([
            'nombre', 'dominio', 'activo', 'fecha_expiracion',
        ]));

        if ($request->has('activo')) {
            $empresa->update([
                'estado' => $request->boolean('activo') ? 'activa' : 'suspendida',
                'suspendida_at' => $request->boolean('activo') ? null : now(),
                'eliminable_at' => $request->boolean('activo')
                    ? null
                    : now()->addDays(config('tenancy.retention_days')),
            ]);
        }

        if ($request->filled('password_admin')) {
            $empresa->update(['password_admin' => $request->password_admin]);

            $connections->connect($empresa);

            \App\Models\User::where('email', $empresa->email_admin)
                ->update(['password' => $request->password_admin]);

            $connections->central();
        }

        return redirect()->route('empresas.index')->with('success', 'Empresa actualizada exitosamente.');
    }

    public function destroy(Empresa $empresa): RedirectResponse
    {
        $empresa->update([
            'activo' => false,
            'estado' => 'suspendida',
            'suspendida_at' => now(),
            'eliminable_at' => now()->addDays(config('tenancy.retention_days')),
        ]);

        return redirect()
            ->route('empresas.index')
            ->with('success', 'Empresa suspendida. Sus datos se conservarán durante el periodo de retención.');
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
                $permiso = $model.' '.$accion;
                if (! Permission::where('name', $permiso)->exists()) {
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
