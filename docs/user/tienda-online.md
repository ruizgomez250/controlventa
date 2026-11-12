# Tienda online de cada sistema

MiniFashion incorpora una tienda pública responsive con un frontend que consume exclusivamente la API de catálogo de ControlVenta. No requiere WordPress ni acceso público a las APIs administrativas. En esta instalación: `/controlventa/public/tienda`.

## Puesta en marcha

1. Ejecutar `php artisan migrate --path=database/migrations/2026_11_12_000001_create_tienda_module.php` en cada instalación independiente.
2. Abrir **Tienda online → Configurar catálogo**. Cargar nombre comercial, WhatsApp internacional (ejemplo `595981123456`), color y textos. El catálogo está habilitado inicialmente; los pedidos no se habilitan hasta configurar WhatsApp.
3. Los productos activos con precio mayor a cero aparecen en la tienda. Desde esta configuración se pueden ocultar, destacar, marcar premium y asignar una oferta menor al precio de venta. La fecha de vencimiento usa la zona horaria de ControlVenta.
4. Abrir `/tienda`. El catálogo se pagina de a 12 productos. La búsqueda, categorías y filtros se consultan en la API.

Las nuevas empresas creadas por TenantProvisioner reciben la migración automáticamente. Para empresas existentes en este mismo servidor usar `php artisan tienda:migrar ID_EMPRESA` o `php artisan tienda:migrar --todas`. El comando solo instala estas tablas, sin tocar SIFEN, facturas ni configuración comercial existente. Después cada empresa configura su tienda desde su propio dominio.

## Varias tiendas / API

El middleware existente selecciona la base de datos según el dominio: `empresa-a.dominio/tienda` consulta `empresa-a.dominio/api/tienda/v1`; `empresa-b.dominio/tienda` consulta su propia API y base. Las instalaciones independientes pueden mantener sus propios dominios. No se recibe un `tenant_id` desde el navegador para cambiar la empresa.

La interfaz está en `public/tienda/tienda.js`, `public/tienda/tienda.css` y `resources/views/tienda/index.blade.php`. Consume la URL declarada en `<meta name="tienda-api">`. Para alojar el frontend en otro servidor se puede renderizar/exportar ese HTML, servir los dos assets y apuntar esa meta a la API HTTPS de la empresa correspondiente. También deben actualizarse los enlaces de inicio del HTML al dominio del frontend. No incluir tokens administrativos ni credenciales de bases de datos. La política CORS existente permite las consultas públicas sin cookies de autenticación. La separación actual de empresas usa los subdominios definidos en tenancy; un dominio personalizado requiere configurarlo en ese resolutor antes de utilizarlo como servidor de API.

Endpoints públicos:

| Método | Ruta relativa a `/api/tienda/v1` | Uso |
|---|---|---|
| GET | `/inicio` | Identidad pública y categorías; no devuelve secretos |
| GET | `/productos` | `buscar`, `categoria`, `seccion=todos/ofertas/premium/destacados`, `orden=nuevos/precio_asc/precio_desc`, `page`, `ids[]` (máximo 24) |
| GET | `/productos/{id}` | Detalle público |
| GET | `/imagenes/{id}` | Solo imágenes de productos publicados |
| POST | `/pedidos` | Preparar pedido con precios y stock verificados |
| GET | `/pedidos/{uuid}` | Estado privado con cabecera `X-Pedido-Token` |

Ejemplo de creación de pedido (JSON):

```json
{
  "referencia": "574807d9-387c-45bb-8d92-894dd9828415",
  "token": "secreto-aleatorio-del-navegador-de-al-menos-32-caracteres",
  "nombre": "Nombre del cliente",
  "telefono": "0981123456",
  "entrega": "retiro",
  "items": [{"id": 1, "cantidad": 2, "precio": 50000}]
}
```

`referencia` identifica un intento: repetir exactamente la misma solicitud devuelve el mismo pedido, sin duplicarlo. Cada nuevo intento distinto debe tener otra referencia UUID. `token` se genera en el navegador; solo se guarda su hash en la base. Se debe conservar para consultar el estado. Para `entrega=envio`, `direccion` es obligatoria. `observaciones` es opcional. El servidor calcula los totales y devuelve error si cambiaron los precios o no hay stock suficiente.

La respuesta incluye `pedido` y `whatsapp_url`. El visitante debe pulsar el enlace y confirmar el envío en WhatsApp. Guardar el pedido no acredita que se haya enviado ese mensaje. No se utiliza la API Business de WhatsApp ni se envían mensajes automáticos.

## Pedidos e historial

**Tienda online → Pedidos web** permite seguir estados pendiente, confirmado, preparando, entregado y cancelado. Los pedidos se mantienen separados de las ventas: no reservan ni descuentan inventario, no registran pagos, no generan clientes y no emiten documentos SIFEN. Confirmar disponibilidad y envío con el cliente antes de registrar la venta habitual.

Carrito, favoritos (hasta 24), vistos recientemente (hasta 24) y últimos pedidos se guardan en el navegador, separados por identificador de tienda y API. El cliente puede consultar estados y repetir productos disponibles con precios actuales. Se necesita el mismo navegador; esta primera versión no incorpora cuentas de clientes ni sincronización entre dispositivos. “Sobre tus datos” permite borrar el historial local; los pedidos recibidos por el comercio se conservan.

Premium significa una selección de productos destacada. No implica membresías pagas. Las recomendaciones son productos relacionados por categoría, sin seguimiento entre tiendas ni servicios de publicidad.

## Verificación

`php vendor/phpunit/phpunit/phpunit --filter=TiendaTest` usa bases SQLite temporales: catálogo paginado, campos privados, ofertas y vencimientos, premium, validación de stock y precios, idempotencia, privacidad del seguimiento, imágenes y separación de dos sistemas con IDs iguales.
