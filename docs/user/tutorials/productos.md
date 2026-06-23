# Gestión de Productos

## Objetivo
Aprender a dar de alta, editar y gestionar productos, incluyendo categorías, unidades de medida, precios mayoristas y códigos QR.

---

## Requisitos
- Permiso: `producto leer`, `producto crear`, `producto editar`

---

## 1. Lista de Productos

1. Andá a **Gestión Comercial → Productos → Lista de Productos**.
2. Se muestra una tabla con todos los productos registrados.
3. Usá el buscador para filtrar por descripción o código.
4. Usá los botones de exportación para descargar la lista.

![Lista de productos][screenshot-productos-index]

---

## 2. Registrar un Producto Nuevo

1. Andá a **Gestión Comercial → Productos → Registrar Productos**.
2. Completá los siguientes campos:

| Campo | Descripción | Obligatorio |
|-------|-------------|:-----------:|
| **Descripción** | Nombre del producto | ✅ |
| **Detalle** | Descripción adicional (opcional) | |
| **Categoría** | Seleccioná del desplegable | ✅ |
| **Unidad de Medida** | Ej: Unidad, Kg, Litro | ✅ |
| **Precio de Costo** | Lo que pagás por el producto | ✅ |
| **Precio de Venta** | Precio al público | ✅ |
| **Stock** | Cantidad inicial en depósito | ✅ |
| **Impuesto** | Porcentaje de IVA aplicable | |
| **Imagen** | Foto del producto (JPG, PNG) | |
| **Observación** | Notas internas | |

3. Hacé clic en **Guardar**.

![Formulario de producto][screenshot-productos-create]

> ⚠️ El código de producto se genera automáticamente (12 dígitos únicos).

---

## 3. Precios Mayoristas

Podés definir precios especiales por volumen.

1. En el formulario de producto, andá a la sección **Precios Mayoristas**.
2. Hacé clic en **Agregar Rango**.
3. Completá:
   - **Cantidad desde**: mínimo de unidades para aplicar este precio
   - **Precio Unitario**: precio especial
4. Repetí para cada rango que necesites.

Ejemplo:
| Cantidad desde | Precio Unitario |
|----------------|-----------------|
| 10 | $5.000 |
| 25 | $4.500 |
| 50 | $4.000 |

---

## 4. Editar un Producto

1. En la lista de productos, hacé clic en el botón **Editar** (✏️).
2. Modificá los campos necesarios.
3. Hacé clic en **Guardar**.

---

## 5. Códigos QR y Códigos de Barras

Cada producto puede tener su propio código QR y código de barras para facilitar la carga en ventas.

### Generar etiquetas QR
1. Andá a **Gestión Comercial → Productos → QR / Código Barras**.
2. Seleccioná el producto.
3. Hacé clic en **Generar QR**.
4. Se descargará un PDF con el código QR del producto.

### Generar código de barras
1. Desde la misma pantalla, hacé clic en **Generar Código de Barras**.
2. Se descargará un PDF con el código de barras.

> 📝 Los códigos QR se pueden imprimir en etiquetas y pegar en los productos físicos para escanearlos al momento de la venta.

---

## 6. Eliminar un Producto

1. En la lista de productos, hacé clic en el botón **Eliminar** (🗑️).
2. Confirmá la operación en el mensaje de confirmación.

> ⚠️ No se puede eliminar un producto que tenga movimientos (compras o ventas) asociados.

---

## Consejos

- 📝 Usá categorías para organizar los productos. Podés crear nuevas categorías desde el mismo formulario de producto.
- 📝 El stock se actualiza automáticamente al registrar compras (aumenta) y ventas (disminuye).
- 📝 Si un producto es de tipo **uso interno** (no para la venta), cambiá el campo `Tipo` en la edición.

---

[screenshot-productos-index]: https://placehold.co/800x400/1e293b/f1f5f9?text=Lista+de+Productos
[screenshot-productos-create]: https://placehold.co/800x500/1e293b/f1f5f9?text=Registrar+Producto
