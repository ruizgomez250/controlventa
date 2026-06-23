# Gestión de Gastos

## Objetivo
Aprender a registrar y dar seguimiento a los gastos operativos.

---

## Requisitos
- Permiso: `gasto leer`, `gasto crear`, `gasto editar`

---

## 1. Lista de Gastos

1. Andá a **Movimientos → Gastos → Lista de Gastos**.
2. Se muestra una tabla con todos los gastos registrados, con su estado.

![Lista de gastos][screenshot-gastos-index]

---

## 2. Registrar un Gasto

1. Andá a **Movimientos → Gastos → Registrar Gastos**.
2. Completá los campos:

| Campo | Obligatorio |
|-------|:-----------:|
| **Concepto** | ✅ |
| **Monto** | ✅ |
| **Fecha** | ✅ |
| **Método de Pago** | ✅ (Efectivo, Transferencia, Cheque, etc.) |
| **Comprobante** | Número de factura o recibo |
| **Observación** | |

3. Hacé clic en **Guardar**.

---

## 3. Estados del Gasto

Cada gasto puede tener uno de estos estados:

| Estado | Significado |
|--------|-------------|
| **Pendiente** | Registrado pero no aprobado |
| **Aprobado** | Verificado y aprobado |
| **Rechazado** | No corresponde o inválido |

---

## 4. Editar un Gasto

1. En la lista, hacé clic en **Editar** (✏️).
2. Modificá los datos necesarios (incluyendo el estado).
3. Guardá los cambios.

---

[screenshot-gastos-index]: https://placehold.co/800x400/1e293b/f1f5f9?text=Lista+de+Gastos
