<?php

namespace App\Services;

use App\Models\Venta;
use Illuminate\Support\Facades\DB;

class SifenCobroService
{
    public function __construct(private SifenPkuatiaService $sifen) {}

    public function enviar(Venta $venta): array
    {
        if (!$this->sifen->isHabilitado()) {
            return ['estado' => 'sin_enviar', 'mensaje' => 'SIFEN está deshabilitado.'];
        }
        $asignada = DB::transaction(function () use ($venta) {
            $actual = Venta::query()->lockForUpdate()->findOrFail($venta->id);
            if ($actual->sifen_cdc || !in_array($actual->sifen_estado, [null, 'por_cobrar'], true)) return false;
            $actual->sifen_estado = 'enviando';
            $actual->sifen_mensaje = 'Envío iniciado después del cobro.';
            $actual->sifen_actualizado_at = now();
            $actual->sifen_ambiente = $this->sifen->getConfig()->ambiente;
            $actual->save();
            return true;
        });
        $venta->refresh();
        if (!$asignada) {
            return ['estado' => $venta->sifen_estado ?: 'pendiente', 'mensaje' => $venta->sifen_mensaje ?: 'El documento ya tiene un intento registrado. Consulte la bandeja antes de reenviar.'];
        }
        try {
            if (!preg_match('/^\d{8}$/', $venta->timbrado_factura ?? '') || (int) $venta->numero_factura < 1) {
                throw new \RuntimeException('La venta no tiene timbrado y numeración electrónica válidos. No se modificó el comprobante anterior.');
            }
            return $this->sifen->emitir($venta->load(['cliente', 'detalles']));
        } catch (\Throwable $e) {
            $this->sifen->registrarError($venta, $e->getMessage());
            return ['estado' => 'error', 'mensaje' => $venta->fresh()->sifen_mensaje];
        }
    }
}
