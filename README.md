# EasyStock — Sistema de Gestión de Stock y Ventas

Sistema multi-tenant de gestión de inventario, ventas, compras y caja construido con Laravel 10.

---

## Requisitos

- PHP 8.1+
- MySQL 8.0+
- Composer 2.x
- Node.js 18+ (para assets)
- Extensiones PHP: bcmath, ctype, fileinfo, json, mbstring, openssl, pdo, pdo_mysql, tokenmin, xml, gd, zip

---

## Instalación

```bash
# Clonar el repositorio
git clone <url-del-repositorio> controlventa
cd controlventa

# Instalar dependencias PHP
composer install

# Instalar dependencias frontend
npm install

# Copiar y configurar entorno
cp .env.example .env
php artisan key:generate

# Compilar assets
npm run build

# Migrar y seedear base de datos
php artisan migrate --seed
```

### Configuración del archivo `.env`

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=controlventa
DB_USERNAME=root
DB_PASSWORD=

APP_URL=http://controlventa.local
```

---

## Estructura del Proyecto

```
app/
├── Console/           # Comandos Artisan
├── Exceptions/        # Manejadores de excepciones
├── Http/
│   ├── Controllers/   # Controladores
│   │   ├── Auth/      # Controladores de autenticación
│   │   └── Api/       # Controladores de API REST
│   ├── Middleware/     # Middleware (TenantMiddleware, etc.)
│   └── Requests/      # Form requests
├── Models/            # Modelos Eloquent
└── helpers/           # Funciones helper
    ├── helpers.php
    └── NumberToWords.php

config/
├── adminlte.php       # Configuración de AdminLTE
└── permission.php     # Configuración de Spatie Permission

database/
├── migrations/        # Migraciones
└── seeders/           # Seeders

resources/
├── views/             # Plantillas Blade
│   ├── auth/          # Vistas de autenticación
│   ├── caja/          # Cobranzas
│   ├── cheques/       # Gestión de cheques
│   ├── citas/         # Agenda veterinaria
│   ├── clientes/      # CRUD clientes
│   ├── compras/       # CRUD compras
│   ├── configuracion/ # Configuración del sistema
│   ├── empresas/      # Gestión multi-tenant
│   ├── evento/        # Eventos
│   ├── gasto/         # Gastos
│   ├── impuestos/     # Impuestos
│   ├── mascotas/      # Mascotas (módulo veterinario)
│   ├── productos/     # CRUD productos
│   ├── profile/       # Perfil de usuario
│   ├── proveedores/   # CRUD proveedores
│   ├── reportes/      # Reportes PDF
│   ├── roles/         # Gestión de usuarios y permisos
│   ├── tablaporc/     # Porcentajes por cuota
│   ├── ventas/        # CRUD ventas
│   └── layouts/       # Layouts base
└── sass/              # Archivos SCSS

routes/
└── web.php            # Definición de rutas

public/
└── vendor/
    └── micss/
        └── modern-theme.css  # Tema oscuro moderno
```

---

## Módulos del Sistema

### 1. Gestión Comercial

| Módulo | Descripción | Rutas |
|--------|-------------|-------|
| **Productos** | CRUD con códigos, imágenes, precios mayoristas, stock, generación de QR y códigos de barras | `/producto` |
| **Clientes** | CRUD con autocomplete, validación RUC | `/cliente` |
| **Proveedores** | CRUD con validación de compras asociadas antes de eliminar | `/proveedor` |

### 2. Movimientos

| Módulo | Descripción | Rutas |
|--------|-------------|-------|
| **Compras** | Registro con detalle líneas, incremento de stock, cuotas, pagos | `/compra` |
| **Ventas** | Registro con descuento de stock, creación de cuotas, carga por QR | `/venta` |
| **Gastos** | Registro de gastos con estado (pendiente/aprobado/rechazado) | `/gasto` |
| **Cheques** | Gestión de cheques a cobrar/pagar con vencimientos | `/cheques` |

### 3. Caja (Cobranzas)

| Función | Descripción | Ruta |
|---------|-------------|------|
| Ventas a Cobrar | Cuotas pendientes de ventas a crédito | `/caja` |
| Compras a Pagar | Cuotas pendientes de compras a crédito | `/caja/compras` |
| Cobrados | Historial de pagos recibidos por fecha | `/caja/cobrado` |

### 4. Reportes

| Reporte | Descripción |
|---------|-------------|
| Cobros por Fecha | Reporte PDF de movimientos de caja filtrado por fecha y usuario |
| Ventas por Estado | Reporte PDF separando ventas emitidas vs cobradas |
| Ganancia | Reporte PDF de margen de ganancia por producto |

### 5. Administración

| Módulo | Descripción |
|--------|-------------|
| **Impuestos** | Configuración de porcentajes de IVA |
| **% por Cuota** | Tabla de recargos por cuota para ventas a crédito |
| **Usuarios y Roles** | Matriz de permisos por usuario (Spatie Permission) |
| **Configuración** | Toggles del sistema (condición de venta, etc.) |

### 6. Perfil de Usuario

- Ver perfil
- Editar perfil (nombre, email)
- Cambiar contraseña

---

## Multi-tenencia

EasyStock usa una arquitectura **base de datos por tenant**:

1. La base de datos central (`controlventa`) almacena las empresas (`empresas` table) con sus credenciales de conexión.
2. Cada empresa tiene su propia base de datos aislada.
3. `TenantMiddleware` detecta el subdominio de la URL, carga la empresa y configura dinámicamente la conexión `tenant`.
4. `EmpresaController` permite crear nuevas empresas con bases de datos y usuarios admin autónomos.

### Flujo de creación de empresa:

```
POST /empresas/create
  → Crea registro en tabla empresas (con datos de conexión encriptados)
  → Crea base de datos independiente
  → Ejecuta migraciones en la nueva base
  → Crea usuario admin con todos los permisos
```

---

## Sistema de Permisos

Usa **Spatie Laravel Permission v6** con permisos planos en formato `{modelo} {acción}`.

### Permisos disponibles

| Módulo | Acciones |
|--------|----------|
| cliente, proveedor, producto, compra, venta, caja, cajareporte, reporte, rol, gasto, cheque | leer, crear, editar, borrar |
| tabla_porcentaje | leer, modificar |
| configuracion | modificar |
| empresa | leer, crear, editar, borrar |

### Verificación en controladores

```php
if (!auth()->user()->can('producto leer')) {
    return view('sinpermiso.index');
}
```

---

## Modelo de Datos

### Tablas Principales

```
usuarios ──┬── ventas ──┬── ventas_detalles ── producto
           │            └── pagare ── caja
           ├── compras_cab ──┬── compras_det ── producto
           │                 └── pagare ── caja
           ├── gastos
           └── empresas

clientes ─── ventas
proveedores ── compras_cab
productos ──┬── producto_precio_tiers
            ├── impuestos
            └── opciones (categorías, unidades)
```

### Diagrama de Entidades

| Tabla | Descripción | Claves Foráneas |
|-------|-------------|-----------------|
| `users` | Usuarios del sistema | empresa_id → empresas |
| `empresas` | Compañías (tenants) | — |
| `clientes` | Clientes | — |
| `proveedores` | Proveedores | — |
| `productos` | Productos con stock | id_categoria → opciones, id_medida → opciones, id_impuesto → impuestos |
| `producto_precio_tiers` | Precios mayoristas escalonados | id_producto → productos |
| `impuestos` | Tasas de impuesto | — |
| `ventas` | Cabecera de ventas | id_usuario → users, id_cliente → clientes |
| `ventas_detalles` | Líneas de detalle de venta | id_venta → ventas, id_producto → productos |
| `compras_cab` | Cabecera de compras | id_proveedor → proveedores, id_usuario → users |
| `compras_det` | Líneas de detalle de compra | id_compracab → compras_cab, id_productos → productos |
| `pagare` | Cuotas de crédito (ventas y compras) | id_venta → ventas, id_compra → compras_cab |
| `cajas` | Pagos registrados | id_usuario → users, id_venta → ventas, id_compra → compras_cab |
| `gastos` | Gastos operativos | user_id → users |
| `cheques` | Cheques emitidos/recibidos | — |
| `tabla_porcentajes` | Porcentajes de recargo por cuota | — |
| `opciones` | Valores dinámicos (categorías, unidades) | id_dominio → dominios |
| `dominios` | Dominios para opciones dinámicas | — |
| `configuraciones` | Configuraciones del sistema | — |

---

## Generación de PDFs

EasyStock usa **TCPDF** para generar documentos:

| Documento | Controlador | Ruta |
|-----------|-------------|------|
| Códigos QR de productos | ProductoController | `/qrproducto/{id}` |
| Códigos de barras | ProductoController | `/barcodeproducto/{id}` |
| Recibo de pago (cuota) | VentaController | `/documentopagopdf/{id}` |
| Recibo de pago (monto) | VentaController | `/documentopagomontopdf/{id}` |
| Reporte de caja por fechas | CajaReporteController | `/cajareportepdf/{desde}/{hasta}/{idusuario?}` |
| Reporte de ganancia | ProductoreporteController | `/gananciareportepdf/{desde}/{hasta}/{idproducto?}` |
| Reporte de ventas | VentaController | `/ventareportepdf/{desde}/{hasta}/{idusuario?}` |
| Reporte ventas por estado | ReporteVentaController | `/reporteventasnuevo/{desde}/{hasta}/{idusuario?}` |

---

## Carga de Productos por QR

El sistema permite cargar productos en una venta mediante escaneo de código QR:

1. El producto tiene un código QR único (generado como PDF).
2. El scanner (`VentaController@cargarDet`) agrega productos a `temporal_detalle_venta`.
3. Al crear la venta, los productos temporales se convierten en líneas de detalle permanentes.
4. El stock se descuenta automáticamente.

---

## Tema y Personalización Visual

El sistema usa **AdminLTE 3** con un tema oscuro moderno personalizado:

- **Fuente**: Inter (vía Google Fonts)
- **Fondo**: Gradiente oscuro (#0f172a → #1e293b) con animación
- **Componentes**: Efecto glassmorphism en cards, sidebar y navbar
- **Botones**: Gradiente púrpura (#6366f1 → #8b5cf6)
- **Inputs**: Fondo oscuro con borde indigo en foco

El CSS del tema está en `public/vendor/micss/modern-theme.css`.

---

## Dependencias Clave

| Paquete | Versión | Propósito |
|---------|---------|-----------|
| laravel/framework | ^10.10 | Framework base |
| jeroennoten/laravel-adminlte | ^3.9 | Panel administrativo |
| spatie/laravel-permission | 6.0 | Roles y permisos |
| livewire/livewire | ^3.0 | Componentes dinámicos |
| tecnickcom/tcpdf | ^6.6 | Generación de PDFs |
| endroid/qr-code | ^5.0 | Generación de QR |
| milon/barcode | ^10.0 | Códigos de barras |
| intervention/image | ~2.0 | Manipulación de imágenes |
| realrashid/sweet-alert | ^7.0 | Alertas flash |

---

## Convenciones de Código

- **Nombrado de rutas**: Laravel resource (`/producto`, `/cliente`, etc.)
- **Permisos**: `{modelo}_{acción}` en español (ej: `producto leer`)
- **Controladores**: PascalCase (ej: `ClienteController`)
- **Modelos**: PascalCase, singular (ej: `Cliente`, `Compra_cab`)
- **Migraciones**: Con nombres descriptivos en camelCase
- **Vistas**: snake_case (ej: `clientes/index.blade.php`)
- **Rutas web**: español (ej: `/caja/cobrado`, `/reportes/vendidos`)

---

## API REST

Hay un endpoint API disponible en `/api`:

```http
GET /api/ventas                     # Lista ventas a crédito activas
GET /api/ventas/{id}                # Detalle de una venta
```

---

## Licencia

Sistema de uso interno.
