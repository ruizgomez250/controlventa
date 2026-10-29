<?php

namespace App\Services;

use App\Models\SifenConfiguracion;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SifenNumeracionService
{
    public function siguiente(SifenConfiguracion $config): int
    {
        // Conserva también los números de facturas rechazadas o anuladas.
        $maximo = Venta::where('timbrado_factura', $config->timbrado)
            ->max(DB::raw("CAST(SUBSTRING_INDEX(numero_factura, '-', -1) AS UNSIGNED)"));
        return max((int) $config->ultimo_numero_factura, (int) $maximo) + 1;
    }

    public function asignar(Venta $venta): void
    {
        // El llamador debe mantener la transacción hasta guardar la venta.
        if (DB::transactionLevel() === 0) {
            throw new \LogicException('La numeración requiere una transacción.');
        }
        $config = SifenConfiguracion::query()->lockForUpdate()->first();
        if (!$config || !$config->habilitado) return;
        if (!preg_match('/^\d{8}$/', $config->timbrado ?? '') || !$config->fecha_inicio_timbrado) {
            throw ValidationException::withMessages(['timbrado' => 'Configure el timbrado y su fecha de inicio en Configuración SIFEN.']);
        }
        if (substr((string) $venta->fecha_emision, 0, 10) < $config->fecha_inicio_timbrado) {
            throw ValidationException::withMessages(['fechaemision' => 'La emisión no puede ser anterior al inicio del timbrado.']);
        }
        $numero = $this->siguiente($config);
        if ($numero > 9999999) {
            throw ValidationException::withMessages(['nrofactura' => 'La numeración de siete dígitos se agotó.']);
        }
        $config->ultimo_numero_factura = $numero;
        $config->save();
        $venta->numero_factura = str_pad((string) $numero, 7, '0', STR_PAD_LEFT);
        $venta->timbrado_factura = $config->timbrado;
        $venta->sifen_fecha_inicio_timbrado = $config->fecha_inicio_timbrado;
        $venta->sifen_establecimiento = str_pad((string) $config->establecimiento, 3, '0', STR_PAD_LEFT);
        $venta->sifen_punto_expedicion = str_pad((string) $config->punto_expedicion, 3, '0', STR_PAD_LEFT);
        $venta->sifen_ambiente = $config->ambiente;
        $venta->sifen_estado = 'por_cobrar';
        $venta->sifen_mensaje = 'Pendiente de cobro. El envío se inicia al cobrar la venta.';
    }
}
