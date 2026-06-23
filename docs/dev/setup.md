# Entorno de Desarrollo

## Requisitos

| Herramienta | Versión |
|-------------|---------|
| PHP | ^8.1 |
| MySQL | 8.0+ |
| Composer | 2.x |
| Node.js | 18+ |
| npm | 9+ |
| Extensiones PHP | bcmath, ctype, fileinfo, json, mbstring, openssl, pdo, pdo_mysql, tokenizer, xml, gd, zip |

---

## Instalación

```bash
# Clonar el repositorio
git clone <url> controlventa
cd controlventa

# Instalar dependencias PHP
composer install

# Instalar dependencias frontend
npm install

# Crear archivo de entorno
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate

# Compilar assets
npm run build

# Migrar y seedear base de datos central
php artisan migrate --seed
```

---

## Configuración del archivo `.env`

```env
APP_NAME=EasyStock
APP_ENV=local
APP_DEBUG=true
APP_URL=http://controlventa.local

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=controlventa
DB_USERNAME=root
DB_PASSWORD=
```

---

## Multi-tenancy en entorno local

Para probar multi-tenancy localmente:

### Opción 1: Usar subdominios con `hosts`

1. Editá `/etc/hosts` (Linux/Mac) o `C:\Windows\System32\drivers\etc\hosts` (Windows):

```
127.0.0.1  controlventa.local
127.0.0.1  empresa1.controlventa.local
127.0.0.1  empresa2.controlventa.local
```

2. Creá una empresa desde el panel de administración con dominio `empresa1`.
3. Accedé vía `http://empresa1.controlventa.local`.

### Opción 2: Sin subdominios

Si no necesitás probar multi-tenancy, el middleware pasa transparente para localhost y requests sin subdominio.

---

## Estructura de Bases de Datos

```
Base central (controlventa):
  - Tablas del sistema (users, empresas, migrations, ...)
  - Tablas Spatie de permisos centrales

Base de cada tenant (ej: easy stock_empresa1):
  - Tablas de negocio (clientes, productos, ventas, compras, ...)
  - Tablas Spatie de permisos del tenant
```

---

## Compilación de Assets

```bash
# Desarrollo (hot reload)
npm run dev

# Producción
npm run build
```

El proyecto usa **Vite** con `laravel-vite-plugin`. Los archivos fuente están en:
- `resources/sass/app.scss` → CSS principal (Bootstrap + variables)
- `resources/js/app.js` → JS principal

El tema oscuro moderno se carga desde:
- `public/vendor/micss/modern-theme.css` (cargado directamente, no requiere build)

---

## Comandos Útiles

```bash
# Limpiar caché
php artisan optimize:clear

# Crear migración
php artisan make:migration create_xxx_table

# Ejecutar migraciones
php artisan migrate

# Rollback migraciones
php artisan migrate:rollback

# Seeders
php artisan db:seed --class=PermissionSeeder

# Tinker (REPL interactivo)
php artisan tinker

# Ver rutas
php artisan route:list

# Ver logs
tail -f storage/logs/laravel.log
```

---

## Tests

```bash
# Ejecutar tests
php artisan test

# O con PHPUnit
./vendor/bin/phpunit
```

> ⚠️ Actualmente no hay tests unitarios ni de feature implementados.

---

## Resolución de Problemas Comunes

### Error: "Class not found"
```bash
composer dump-autoload
```

### Error: "No application encryption key"
```bash
php artisan key:generate
```

### Error de conexión a base de datos
Verificá que MySQL esté corriendo y que las credenciales en `.env` sean correctas.

### Error de Vite
```bash
npm install
npm run build
```

### Error 403 "La empresa esta desactivada"
Verificá en la base central que el registro `empresas` tenga `activo = 1`.

### Error 403 "Suscripción expirada"
Verificá que `fecha_expiracion` sea nula o posterior a la fecha actual.
