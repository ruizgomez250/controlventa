# Onboarding SaaS multiempresa

## Flujo

1. `GET /suscribirme` muestra el plan configurado.
2. `POST /suscribirme` crea una orden central en estado `pending` y redirige al proveedor.
3. El proveedor confirma el pago en `POST /api/billing/webhook/{provider}`.
4. El webhook verifica `X-Webhook-Signature` con HMAC-SHA256, valida importe, moneda y correo, y marca la orden como pagada.
5. El cliente recibe un enlace de activación de un solo uso.
6. Al completar los datos, `TenantProvisioner` crea la base y el usuario MySQL, ejecuta migraciones, carga permisos y crea el administrador.
7. El cliente inicia sesión desde `https://{subdominio}.{TENANT_BASE_DOMAIN}/login`.

La página de retorno del proveedor nunca activa una cuenta. Solamente el webhook firmado puede confirmar el pago.

## Variables requeridas

```dotenv
CENTRAL_HOSTS=controlventa.com,www.controlventa.com
TENANT_BASE_DOMAIN=controlventa.com

BILLING_PROVIDER=proveedor
BILLING_CHECKOUT_URL=https://proveedor.example/checkout
BILLING_WEBHOOK_SECRET=un-secreto-largo-y-aleatorio
BILLING_PLAN_AMOUNT=150000
BILLING_CURRENCY=PYG
BILLING_SUBSCRIPTION_DAYS=30
```

El adaptador HTTP definitivo debe ajustarse al contrato real del proveedor elegido. Algunos proveedores no aceptan parámetros por redirección y requieren crear una preferencia u operación mediante API.

## Firma del webhook genérico

El cuerpo JSON sin modificar se firma de esta manera:

```text
X-Webhook-Signature = hex(hmac_sha256(raw_body, BILLING_WEBHOOK_SECRET))
```

Campos esperados:

```json
{
  "external_id": "uuid-de-la-orden",
  "status": "paid",
  "amount": 150000,
  "currency": "PYG",
  "customer_email": "cliente@example.com"
}
```

## Suspensión y eliminación

Eliminar una empresa desde el panel solamente la suspende. Cuando finaliza el periodo configurado en `TENANT_RETENTION_DAYS`, puede revisarse:

```shell
php artisan tenants:purge-expired
```

La eliminación definitiva requiere una ejecución explícita:

```shell
php artisan tenants:purge-expired --force
```

Debe existir una copia de seguridad externa antes de usar `--force`.
