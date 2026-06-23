# Sistema de Permisos

EasyStock usa **Spatie Laravel Permission v6** con una estructura de permisos plana en formato `{modelo} {acción}`.

---

## Permisos por Módulo

| Módulo | Permisos | Total |
|--------|----------|:-----:|
| **cliente** | leer, crear, editar, borrar | 4 |
| **proveedor** | leer, crear, editar, borrar | 4 |
| **producto** | leer, crear, editar, borrar | 4 |
| **compra** | leer, crear, editar, borrar | 4 |
| **venta** | leer, crear, editar, borrar | 4 |
| **caja** | leer, crear, editar, borrar | 4 |
| **cajareporte** | leer, crear, editar, borrar | 4 |
| **reporte** | leer, crear, editar, borrar | 4 |
| **rol** | leer, crear, editar, borrar | 4 |
| **gasto** | leer, crear, editar, borrar | 4 |
| **cheque** | leer, crear, editar, borrar | 4 |
| **tabla_porcentaje** | leer, modificar | 2 |
| **configuracion** | modificar | 1 |
| **empresa** | leer, crear, editar, borrar | 4 |

**Total: 48 permisos** en 14 módulos.

---

## Cómo se crean los permisos

### Automáticamente en TenantMiddleware

Cada request pasa por `TenantMiddleware::asegurarPermisos()` que verifica si existen todos los permisos en la base del tenant y los crea si faltan:

```php
private function asegurarPermisos(): void
{
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

    foreach ($grupos as $model => $acciones) {
        foreach ($acciones as $accion) {
            $permiso = $model . ' ' . $accion;
            if (!Permission::where('name', $permiso)->exists()) {
                Permission::create(['name' => $permiso]);
            }
        }
    }
}
```

---

## Verificación en Controladores

```php
// Patrón estándar
public function index()
{
    if (!auth()->user()->can('producto leer')) {
        return view('sinpermiso.index');
    }
    // ... lógica del controlador
}
```

```php
// Con redirect
if (!auth()->user()->can('venta crear')) {
    return redirect()->route('sinpermiso');
}
```

---

## Tablas de Spatie

| Tabla | Propósito |
|-------|-----------|
| `permissions` | Lista de permisos (name, guard_name) |
| `roles_spatie` | Roles (name, guard_name) — no se usan activamente |
| `model_has_permissions` | Permisos directos por usuario (model_type, model_id) |
| `model_has_roles` | Roles por usuario |
| `role_has_permissions` | Permisos por rol |

---

## Asignación de Permisos

La asignación se hace desde la interfaz en **Administración → Usuarios y Roles → Ver / Asignar Permisos**.

```php
// Backend: RolController@store
public function store(Request $request)
{
    $user = User::find($request->user_id);
    $permisos = $request->permisos ?? [];
    $user->syncPermissions($permisos);

    return redirect()->back()->with('success', 'Permisos actualizados.');
}
```

---

## Perfiles Sugeridos

| Perfil | Permisos |
|--------|----------|
| **Cajero** | `venta crear`, `venta leer`, `caja leer`, `cliente leer` |
| **Depósito** | `producto leer`, `producto crear`, `compra crear`, `compra leer`, `proveedor leer` |
| **Administrador** | Todos los permisos |
| **Gerente** | Todos los permisos `leer` + `reporte leer` |
| **Contador** | `gasto leer`, `gasto crear`, `reporte leer`, `cajareporte leer` |

---

## Sistema Legacy (Rol model)

Existe un sistema legacy en la tabla `roles` con estructura:

```php
// app/Models/Rol
// id, id_usuario, nombre_modelo, leer, borrar, crear, editar
```

Usado por `helpers.php::verificaModelo()`:

```php
function verificaModelo(string $idusuario, string $nombremodelo)
{
    $permisos = Rol::where('id_usuario', $idusuario)
        ->where('nombre_modelo', $nombremodelo)
        ->first();
    return $permisos ? true : false;
}
```

> ⚠️ Este sistema legacy está deprecado. Todos los módulos nuevos deben usar Spatie Permission.

---

## Buenas Prácticas

- ✅ Usar permisos directos por usuario (no roles) — es el patrón actual del sistema.
- ✅ El permiso `leer` controla la visibilidad del módulo en el menú lateral.
- ✅ El permiso `borrar` debe asignarse solo a usuarios de confianza.
- ❌ No crear permisos nuevos sin agregarlos al array en `TenantMiddleware`.
- ❌ No usar el sistema legacy `Rol` para módulos nuevos.
