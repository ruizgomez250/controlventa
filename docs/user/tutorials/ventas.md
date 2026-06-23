# Gestión de Ventas

## Objetivo
Aprender a registrar ventas, manejar crédito/contado, cargar productos por QR y gestionar cuotas.

---

## Requisitos
- Permiso: `venta leer`, `venta crear`
- Tener productos con stock suficiente y clientes registrados

---

## 1. Lista de Ventas

1. Andá a **Movimientos → Ventas → Lista de Ventas**.
2. Se muestra una tabla con todas las ventas registradas.
3. Hacé clic en el icono `+` de cada fila para ver el detalle de productos.

![Lista de ventas][screenshot-ventas-index]

---

## 2. Registrar una Venta

### Paso 1: Datos de la venta

1. Andá a **Movimientos → Ventas → Registrar Venta**.
2. Completá:

| Campo | Obligatorio |
|-------|:-----------:|
| **Cliente** | ✅ (usá el buscador) |
| **Tipo de Comprobante** | ✅ (Contado o Crédito) |
| **Fecha de Emisión** | ✅ |
| **Número de Factura** | |
| **Timbrado** | |

### Paso 2: Agregar productos

1. En la sección **Detalle de Venta**, buscá un producto:
   - Escribí el nombre o código en el buscador.
   - Seleccioná el producto de la lista.
2. Completá **Cantidad**.
3. Hacé clic en **Agregar**.
4. Repetí para cada producto.

> 📝 Si el cliente tiene un descuento especial, ingresalo en el campo **Descuento** antes de confirmar.

### Paso 3: Confirmar

1. Revisá el detalle de la venta (productos, cantidades, total).
2. Hacé clic en **Guardar Venta**.

✅ El stock se descuenta automáticamente.

---

## 3. Carga por Código QR

Podés escanear códigos QR impresos para cargar productos más rápido.

1. En el formulario de venta, usá un lector de QR (celular o scanner).
2. Escaneá el código QR del producto.
3. El producto se agrega automáticamente al detalle de la venta.
4. Ajustá la cantidad si es necesario.

> 📝 Los códigos QR se generan desde **Productos → QR / Código Barras**.

---

## 4. Ventas a Crédito

Si seleccionaste **Crédito** como tipo de comprobante:

1. Al guardar la venta, el sistema genera automáticamente cuotas con sus vencimientos.
2. Las cuotas se calculan según la configuración de **% por Cuota** en Administración.
3. Podés ver las cuotas desde el botón **Cuotas** en la lista de ventas.

### Cobrar cuotas
Ver tutorial de [Caja / Cobranzas](caja.md).

---

## 5. Anular una Venta

1. En la lista de ventas, hacé clic en **Eliminar** (🗑️).
2. Confirmá la operación.

> ⚠️ Al anular, el stock se restaura automáticamente. Solo se pueden anular ventas en estado "Pedido Generado".

---

## Consejos

- 📝 Para ventas rápidas, registrá un cliente genérico llamado "Consumidor Final".
- 📝 Verificá el stock disponible antes de confirmar la venta.
- 📝 Si el cliente paga con cheques, registralo en el módulo de Cheques.

---

[screenshot-ventas-index]: https://placehold.co/800x400/1e293b/f1f5f9?text=Lista+de+Ventas
