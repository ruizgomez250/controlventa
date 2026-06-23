# Gestión de Cheques

## Objetivo
Aprender a registrar y gestionar cheques emitidos y recibidos, con alertas de vencimiento.

---

## Requisitos
- Permiso: `cheque leer`, `cheque crear`, `cheque editar`

---

## 1. Lista de Cheques

1. Andá a **Movimientos → Cheques → Lista de Cheques**.
2. La tabla muestra todos los cheques con indicadores:
   - **Verdes**: cheques próximos a vencer (3 días)
   - **Rojos**: cheques vencidos
3. Usá los botones de exportación para descargar la lista.

![Lista de cheques][screenshot-cheques-index]

---

## 2. Registrar un Cheque

1. Andá a **Movimientos → Cheques → Registrar Cheque**.
2. Completá:

| Campo | Obligatorio |
|-------|:-----------:|
| **Tipo** | ✅ (A Cobrar o A Pagar) |
| **Número de Cheque** | ✅ |
| **Banco** | ✅ |
| **Titular** | ✅ |
| **Monto** | ✅ |
| **Fecha de Emisión** | ✅ |
| **Fecha de Cobro** | ✅ (fecha de vencimiento) |
| **Observación** | |

3. Hacé clic en **Guardar**.

---

## 3. Estados del Cheque

| Estado | Significado |
|--------|-------------|
| **Pendiente** | Aún no cobrado ni pagado |
| **Cobrado** | Ya se hizo efectivo |
| **Anulado** | Cancelado |

---

## 4. Editar un Cheque

1. En la lista, hacé clic en **Editar** (✏️).
2. Actualizá los datos (ej: cambiar estado a "Cobrado" cuando se haga efectivo).
3. Guardá los cambios.

---

[screenshot-cheques-index]: https://placehold.co/800x400/1e293b/f1f5f9?text=Lista+de+Cheques
