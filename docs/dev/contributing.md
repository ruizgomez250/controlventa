# Guía de Contribución

## Convenciones de Código

### Nombrado

| Elemento | Convención | Ejemplo |
|----------|-----------|---------|
| **Controladores** | PascalCase + `Controller` | `ClienteController`, `VentaController` |
| **Modelos** | PascalCase, singular | `Cliente`, `CompraCab`, `VentaDetalle` |
| **Migraciones** | camelCase descriptivo | `add_empresa_id_to_users_table` |
| **Vistas** | snake_case en directorio del módulo | `clientes/index.blade.php` |
| **Rutas** | español, plural | `/producto`, `/caja/cobrado` |
| **Permisos** | `{modelo}_{acción}` en español | `producto leer`, `venta crear` |
| **Métodos** | camelCase | `getDetalles()`, `pagarCuota()` |
| **Variables** | camelCase | `$fechaEmision`, `$totalCompra` |

### Rutas

Usar `Route::resource()` para CRUD estándar:

```php
Route::resource('/producto', ProductoController::class);
```

Para rutas adicionales, usar nombres descriptivos:

```php
Route::get('/compra/{id}/detalles', [CompraController::class, 'getDetalles']);
```

### Verificación de permisos

```php
if (!auth()->user()->can('producto leer')) {
    return view('sinpermiso.index');
}
```

---

## Flujo Git

### Branches

```
main        → Producción
develop     → Integración
feature/xxx → Nuevas funcionalidades
fix/xxx     → Correcciones
```

### Commits

Usar mensajes claros en español:

```
feat: agrega módulo de cheques
fix: corrige cálculo de IVA en ventas
refactor: extrae lógica de pagare a servicio
docs: actualiza README con nuevos módulos
```

### Pull Requests

1. Crear branch desde `develop`.
2. Implementar los cambios.
3. Verificar que no haya errores de sintaxis.
4. Crear PR hacia `develop`.
5. Esperar revisión antes de mergear.

---

## Cómo Agregar un Nuevo Módulo CRUD

### Paso 1: Migración

```bash
php artisan make:migration create_mi_modulo_table
```

```php
Schema::create('mi_modulo', function (Blueprint $table) {
    $table->id();
    $table->string('nombre');
    $table->text('descripcion')->nullable();
    $table->boolean('estado')->default(true);
    $table->timestamps();
});
```

### Paso 2: Modelo

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MiModulo extends Model
{
    protected $fillable = ['nombre', 'descripcion', 'estado'];
}
```

### Paso 3: Controlador

```php
namespace App\Http\Controllers;

use App\Models\MiModulo;
use Illuminate\Http\Request;

class MiModuloController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('mi_modulo leer')) {
            return view('sinpermiso.index');
        }
        $data = MiModulo::all();
        return view('mi_modulo.index', compact('data'));
    }

    public function create()
    {
        if (!auth()->user()->can('mi_modulo crear')) {
            return view('sinpermiso.index');
        }
        return view('mi_modulo.create');
    }

    public function store(Request $request)
    {
        MiModulo::create($request->all());
        return redirect()->route('mi_modulo.index')
            ->with('success', 'Registro creado correctamente.');
    }

    // edit, update, destroy similar pattern
}
```

### Paso 4: Vistas

Crear en `resources/views/mi_modulo/`:

```
mi_modulo/
├── index.blade.php    @extends('adminlte::page')
├── create.blade.php   @extends('adminlte::page')
└── edit.blade.php     @extends('adminlte::page')
```

### Paso 5: Ruta

```php
Route::resource('/mi-modulo', MiModuloController::class);
```

### Paso 6: Permisos

Agregar el módulo al array `$grupos` en `TenantMiddleware::asegurarPermisos()`:

```php
'mi_modulo' => ['leer', 'crear', 'editar', 'borrar'],
```

### Paso 7: Menú

Agregar en `config/adminlte.php` en la sección `menu`:

```php
[
    'text'       => 'Mi Módulo',
    'icon'       => 'fas fa-cube',
    'icon_color' => 'primary',
    'can'        => 'mi_modulo leer',
    'submenu'    => [
        ['text' => 'Lista', 'url' => '/mi-modulo', 'can' => 'mi_modulo leer'],
        ['text' => 'Registrar', 'url' => '/mi-modulo/create', 'can' => 'mi_modulo crear'],
    ],
],
```

---

## Estructura de Vistas

Cada vista CRUD debe seguir esta estructura:

### index.blade.php

```blade
@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 custom-heading">Lista de [Módulo]</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    {{-- DataTable o tabla --}}
                </div>
            </div>
        </div>
    </div>
@stop
```

### create.blade.php / edit.blade.php

```blade
@extends('adminlte::page')

@section('content_header')
    <h1 class="m-0 custom-heading">Registrar [Módulo]</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('mi_modulo.store') }}">
                        @csrf
                        {{-- Campos del formulario --}}
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
```

---

## Buenas Prácticas

- ✅ Siempre verificar permisos al inicio de cada método del controlador.
- ✅ Usar `route()` para generar URLs, nunca URLs hardcodeadas.
- ✅ Mantener las migraciones atómicas (una tabla/alteración por archivo).
- ✅ Usar `@error` en las vistas para mostrar errores de validación.
- ✅ Usar `session('success')` / `session('error')` para mensajes flash.
- ✅ No hardcodear logos ni textos del sistema en las vistas.
- ❌ No usar etiquetas PHP cortas (`<?=`), usar Blade (`{{ }}`).
- ❌ No dejar `dd()` o `var_dump()` en el código.
