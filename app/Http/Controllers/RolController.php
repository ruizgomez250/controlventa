<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolController extends Controller
{
    private const GRUPOS = [
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
        'persona' => ['leer', 'crear', 'editar', 'borrar'],
        'entrega_insumo' => ['leer', 'crear', 'editar', 'borrar'],
        'reporte_stock' => ['leer'],
        'reporte_venta' => ['leer'],
        'reporte_financiero' => ['leer'],
        'reporte_management' => ['leer'],
    ];

    public function index(): View
    {
        if (!auth()->user()->can('rol crear')) {
            return view('sinpermiso.index');
        }
        $usuarios = User::all();

        $permissionGroups = self::GRUPOS;
        $displayNames = [
            'cliente' => 'Cliente',
            'proveedor' => 'Proveedor',
            'producto' => 'Producto',
            'compra' => 'Compra',
            'venta' => 'Venta',
            'caja' => 'Caja',
            'cajareporte' => 'Caja Reporte',
            'reporte' => 'Reporte',
            'rol' => 'Rol',
            'gasto' => 'Gasto',
            'cheque' => 'Cheque',
            'tabla_porcentaje' => 'Tabla Porcentaje',
            'configuracion' => 'Configuración',
            'empresa' => 'Empresa',
            'persona' => 'Persona',
            'entrega_insumo' => 'Entrega Insumo',
            'reporte_stock' => 'Reporte Stock',
            'reporte_venta' => 'Reporte Ventas',
            'reporte_financiero' => 'Reporte Financiero',
            'reporte_management' => 'Reporte Gestión',
        ];
        $actionLabels = [
            'leer' => 'Ver',
            'crear' => 'Crear',
            'editar' => 'Editar',
            'borrar' => 'Eliminar',
            'modificar' => 'Modificar',
            'comercial' => 'Datos Comerciales',
            'stock' => 'Stock',
        ];
        $actionIcons = [
            'leer' => 'fa-eye',
            'crear' => 'fa-plus',
            'editar' => 'fa-edit',
            'borrar' => 'fa-trash',
            'modificar' => 'fa-cog',
            'comercial' => 'fa-chart-line',
            'stock' => 'fa-warehouse',
        ];
        $actionColors = [
            'leer' => 'info',
            'crear' => 'success',
            'editar' => 'warning',
            'borrar' => 'danger',
            'modificar' => 'secondary',
            'comercial' => 'primary',
            'stock' => 'secondary',
        ];

        return view('roles.index', compact('usuarios', 'permissionGroups', 'displayNames', 'actionLabels', 'actionIcons', 'actionColors'));
    }

    public function getRoles($id): JsonResponse|RedirectResponse
    {
        if (!auth()->user()->can('rol leer')) {
            return redirect()->route('sinpermiso');
        }
        $user = User::find($id);
        if (!$user) {
            return response()->json([]);
        }
        $permissions = $user->getAllPermissions()->pluck('name');
        return response()->json($permissions);
    }

    public function store(Request $request): RedirectResponse
    {
        if (!auth()->user()->can('rol crear')) {
            return view('sinpermiso.index');
        }
        $idUsuario = $request->input('id_usuario');
        $user = User::find($idUsuario);
        if (!$user) {
            return redirect()->back()->with('error', 'Usuario no encontrado.');
        }
        $permisos = array_unique($request->input('permisos', []));
        $existing = Permission::whereIn('name', $permisos)->pluck('name')->toArray();
        $missing = array_diff($permisos, $existing);
        foreach ($missing as $name) {
            Permission::create(['name' => $name]);
        }
        try {
            $user->syncPermissions($permisos);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        } catch (\Exception $e) {
            return redirect()->route('rol.index')->with('error', 'Error al guardar permisos: ' . $e->getMessage());
        }
        return redirect()->route('rol.index')->with('success', 'Permisos actualizados exitosamente.');
    }

    public function createUser(): View
    {
        if (!auth()->user()->can('rol crear')) {
            return view('sinpermiso.index');
        }
        return view('roles.create-user');
    }

    public function storeUser(Request $request): RedirectResponse
    {
        if (!auth()->user()->can('rol crear')) {
            return view('sinpermiso.index');
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('rol.index')->with('success', "Usuario {$user->name} creado exitosamente.");
    }

    public function storePermission(Request $request): JsonResponse
    {
        if (!auth()->user()->can('rol crear')) {
            return response()->json(['error' => 'Sin permiso'], 403);
        }
        $request->validate(['nombre' => 'required|string|max:255|unique:permissions,name']);
        Permission::create(['name' => $request->nombre]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        return response()->json(['success' => 'Permiso creado correctamente.']);
    }
}
