# Reportes

## Objetivo
Aprender a generar y usar los reportes PDF del sistema para análisis de caja, ganancia y ventas.

---

## Requisitos
- Permiso: `cajareporte leer`, `reporte leer`

---

## 1. Reporte de Cobros por Fecha

Genera un PDF con todos los movimientos de caja en un rango de fechas.

1. Andá a **Reportes → Cobros por Fecha**.
2. Completá los filtros:

| Campo | Descripción |
|-------|-------------|
| **Fecha Desde** | Inicio del período |
| **Fecha Hasta** | Fin del período |
| **Usuario** | (Opcional) Filtrar por un usuario específico |

3. Hacé clic en **Generar Reporte**.
4. Se descargará un PDF con:
   - Fecha, tipo de movimiento, cliente/proveedor, monto, usuario
   - Totales por período

![Reporte de caja][screenshot-reporte-caja]

---

## 2. Reporte de Ventas por Estado

Muestra las ventas agrupadas por estado (generadas vs cobradas).

1. Andá a **Reportes → Ventas por Estado**.
2. Completá los filtros:
   - **Fecha Desde / Hasta**
   - **Usuario** (opcional)
3. Hacé clic en **Generar Reporte**.
4. El PDF incluye:
   - Ventas emitidas (estado: Pedido Generado)
   - Ventas cobradas (estado: Pagado)
   - Resumen con totales

---

## 3. Reporte de Ganancia

Muestra el margen de ganancia por producto en un período.

1. Andá a la pantalla de reporte de ganancia desde el menú de reportes.
2. Completá los filtros:
   - **Fecha Desde / Hasta**
   - **Producto** (opcional — si no seleccionás, incluye todos)
3. Hacé clic en **Generar Reporte**.
4. El PDF incluye:
   - Producto, cantidad vendida, costo, precio de venta, ganancia unitaria, margen %

---

## Consejos

- 📝 Todos los reportes se descargan en formato PDF listo para imprimir.
- 📝 Si el reporte tiene muchas páginas, usá los filtros para acotar el período.
- 📝 Los reportes se pueden guardar en tu computadora o compartir por email.

---

[screenshot-reporte-caja]: https://placehold.co/800x500/1e293b/f1f5f9?text=Reporte+de+Caja
