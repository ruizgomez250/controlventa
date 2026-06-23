# Módulo de Caja / Cobranzas

## Objetivo
Aprender a gestionar los cobros de ventas a crédito, pagos de compras y consultar el historial de cobros.

---

## Requisitos
- Permiso: `caja leer`

---

## 1. Ventas a Cobrar

Muestra las ventas a crédito que tienen cuotas pendientes.

1. Andá a **Caja → Cobranzas → Ventas a Cobrar**.
2. Se listan las ventas con saldo pendiente.
3. Hacé clic en **Cuotas** para ver el detalle de cada venta.

![Ventas a cobrar][screenshot-caja-ventas]

### Pagar una cuota

1. En la ventana de cuotas, hacé clic en el botón **Pagar** de la cuota correspondiente.
2. Ingresá la **fecha de pago**.
3. Confirmá.

✅ La cuota se marca como pagada y se registra en el historial.

### Pagar un monto parcial

1. En lugar de pagar la cuota completa, usá la opción **Pagar Monto**.
2. Ingresá el **monto a cobrar**.
3. Opcionalmente, ingresá un **descuento**.
4. Confirmá.

> 📝 Usá esta opción cuando el cliente paga menos del valor total de la cuota.

---

## 2. Compras a Pagar

Muestra las compras a crédito con cuotas pendientes de pago al proveedor.

1. Andá a **Caja → Cobranzas → Compras a Pagar**.
2. El funcionamiento es similar a Ventas a Cobrar, pero del lado del proveedor.

---

## 3. Cobrados

Historial de todos los pagos registrados.

1. Andá a **Caja → Cobranzas → Cobrados**.
2. Podés filtrar por fecha para ver los cobros de un día específico.
3. Se muestra:
   - Fecha de cobro
   - Venta o compra asociada
   - Monto cobrado
   - Usuario que registró el cobro

![Cobrados][screenshot-caja-cobrados]

---

## 4. Recibos de Pago

Al registrar un pago, el sistema genera automáticamente un recibo PDF.

1. Desde el historial de cobrados, hacé clic en **Ver Recibo**.
2. Se descarga un PDF con los datos del pago.
3. Podés imprimirlo o enviarlo por email al cliente.

---

[screenshot-caja-ventas]: https://placehold.co/800x400/1e293b/f1f5f9?text=Ventas+a+Cobrar
[screenshot-caja-cobrados]: https://placehold.co/800x400/1e293b/f1f5f9?text=Cobrados
