# Documentación de la API REST

Base URL: `https://{dominio}/api` · Auth: **Sanctum** (Bearer Token)

---

## Autenticación

```
POST /api/login
```

No implementada como endpoint público. Para obtener un token:
1. El usuario debe estar autenticado vía web.
2. Se puede emitir un token Sanctum desde código.

---

## Productos

### Listar productos

```http
GET /api/productos
```

**Response** `200 OK`:
```json
[
  {
    "id": 1,
    "codigo": "582947103956",
    "descripcion": "Laptop HP ProBook 450",
    "detalle": "Core i5, 8GB RAM, 256GB SSD",
    "id_categoria": 1,
    "categoria": "Electrónicos",
    "stock": 15,
    "id_medida": 1,
    "medida": "Unidad",
    "pcosto": 3500000,
    "pventa": 4500000,
    "pmayorista": 4200000,
    "cmayorista": 10,
    "dmayorista": 5,
    "estado": 1,
    "id_impuesto": 1,
    "imagen_url": "https://...",
    "precioTiers": [
      { "cantidad_desde": 5, "precio_unitario": 4300000, "orden": 1 }
    ]
  }
]
```

### Obtener un producto

```http
GET /api/productos/{id}
```

**Response** `200 OK`:
```json
{
  "id": 1,
  "codigo": "582947103956",
  "descripcion": "Laptop HP ProBook 450",
  "stock": 15,
  "pventa": 4500000,
  "precioTiers": [...]
}
```

---

## Clientes

### Listar clientes

```http
GET /api/clientesa
```

**Response** `200 OK`:
```json
[
  {
    "id": 1,
    "razonsocial": "Juan Pérez",
    "ruc": "12345678-1",
    "direccion": "Av. Principal 123",
    "correo": "juan@email.com",
    "telefono": "0981123456",
    "celular": "0981987654",
    "estado": 1
  }
]
```

### Crear cliente

```http
POST /api/clientesa
Content-Type: application/json

{
  "razonsocial": "María García",
  "ruc": "87654321-2",
  "direccion": "Calle Secundaria 456",
  "correo": "maria@email.com",
  "telefono": "0981555666"
}
```

**Response** `201 Created`:
```json
{
  "success": true,
  "message": "Cliente creado correctamente",
  "data": { "id": 2, "razonsocial": "María García", ... }
}
```

---

## Ventas

### Listar ventas a crédito

```http
GET /api/ventas
```

**Response** `200 OK`:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "usuario": { "id": 1, "name": "Admin", "email": "admin@mail.com" },
      "cliente": { "id": 1, "razonsocial": "Juan Pérez", "ruc": "12345678-1" },
      "tipo_comprobante": "CREDITO",
      "fecha_emision": "2026-06-01",
      "numero_factura": "001-001-0000001",
      "estado": 1,
      "total": 4500000,
      "monto_pagare": 5000000,
      "detalles": [
        {
          "id": 1,
          "cantidad": 1,
          "descripcion": "Laptop HP ProBook 450",
          "monto": 4500000,
          "precio_u": 4500000,
          "tipo_impuesto": "10%",
          "producto": {
            "id": 1,
            "codigo": "582947103956",
            "descripcion": "Laptop HP ProBook 450",
            "pventa": 4500000,
            "imagen": "productos/..."
          }
        }
      ],
      "pagare": { "id": 1, "id_venta": 1, "monto": 5000000 }
    }
  ]
}
```

### Obtener una venta

```http
GET /api/ventas/{id}
```

**Response** `200 OK`:
```json
{
  "success": true,
  "data": { ... }
}
```

**Response** `404 Not Found`:
```json
{
  "success": false,
  "message": "Venta no encontrada o no está activa"
}
```

### Crear venta

```http
POST /api/ventasa
Content-Type: application/json

{
  "id_cliente": 1,
  "tipo_comprobante": "CONTADO",
  "numero_factura": "001-001-0000005",
  "detalles": [
    { "id_producto": 1, "cantidad": 2, "precio_u": 4500000, "tipo_impuesto": "10%" }
  ],
  "cuotas": [
    { "porcentaje": 100, "dias_plazo": 0 }
  ]
}
```

### Crear venta simplificada

```http
POST /api/ventasasimpli
Content-Type: application/json

{
  "id_cliente": 1,
  "tipo_comprobante": "CONTADO",
  "monto": 4500000,
  "descripcion": "Venta de prueba",
  "numero_factura": "001-001-0000010"
}
```

---

## Autocomplete

### Buscar clientes

```http
GET /autocomplete?term=juan
```

**Response** `200 OK`:
```json
[
  { "id": 1, "razonsocial": "Juan Pérez", "ruc": "12345678-1" }
]
```

### Buscar proveedores

```http
GET /autocomplete/proveedor?term=distrib
```

### Buscar productos

```http
GET /autocomplete/producto?term=laptop
```

---

## Códigos de estado

| Código | Significado |
|--------|-------------|
| 200 | OK |
| 201 | Creado |
| 400 | Bad Request |
| 401 | No autenticado |
| 403 | Sin permisos |
| 404 | No encontrado |
| 422 | Error de validación |
| 500 | Error interno |

---

## Rate Limiting

No hay rate limiting configurado actualmente.

## Paginación

Los endpoints de listado (`/api/productos`, `/api/clientesa`) devuelven todos los registros sin paginación. Esto puede cambiar en futuras versiones.
