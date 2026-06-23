# Gestión de Compras

## Objetivo
Aprender a registrar compras a proveedores, incluyendo detalle de productos, cuotas y pagos.

---

## Requisitos
- Permiso: `compra leer`, `compra crear`
- Tener productos y proveedores registrados previamente

---

## 1. Lista de Compras

1. Andá a **Movimientos → Compras → Lista de Compras**.
2. Se muestra una tabla con todas las compras registradas.
3. Hacé clic en el icono `+` de cada fila para ver el detalle de productos.

![Lista de compras][screenshot-compras-index]

---

## 2. Registrar una Compra

### Paso 1: Datos de la compra

1. Andá a **Movimientos → Compras → Registrar Compra**.
2. Completá:

| Campo | Obligatorio |
|-------|:-----------:|
| **Proveedor** | ✅ (usá el buscador) |
| **Fecha de Emisión** | ✅ |
| **Número de Factura** | ✅ |
| **Timbrado** | |
| **Condición de Compra** | Contado o Crédito |

### Paso 2: Agregar productos

1. En la sección **Detalle de Compra**, buscá un producto por nombre o código.
2. Seleccioná el producto y completá:
   - **Cantidad**
   - **Precio Unitario**
3. Hacé clic en **Agregar**.
4. Repetí para cada producto.

> 📝 Podés escribir la descripción manualmente si el producto no existe en el sistema.

### Paso 3: Confirmar

1. Revisá el listado de productos agregados.
2. Hacé clic en **Guardar Compra**.

✅ El stock de los productos se incrementa automáticamente.

---

## 3. Cuotas (Compras a Crédito)

Si la compra es a crédito, se generarán cuotas automáticamente según la configuración del sistema.

### Ver cuotas
1. En la lista de compras, hacé clic en el botón **Cuotas**.
2. Se muestra el plan de pagos con fechas de vencimiento y montos.

### Pagar una cuota
Ver tutorial de [Caja / Cobranzas](caja.md).

---

## 4. Anular una Compra

1. En la lista de compras, hacé clic en **Eliminar** (🗑️) en la compra correspondiente.
2. Confirmá la operación.

> ⚠️ El stock se descuenta automáticamente al anular la compra.

---

## Consejos

- 📝 Usá el autocomplete de proveedores para buscarlos rápidamente por nombre o RUC.
- 📝 Verificá que el número de factura no esté repetido.
- 📝 Si comprás en el extranjero, registrá el tipo de cambio en la observación.

---

[screenshot-compras-index]: https://placehold.co/800x400/1e293b/f1f5f9?text=Lista+de+Compras
