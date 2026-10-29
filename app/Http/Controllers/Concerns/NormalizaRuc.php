<?php

namespace App\Http\Controllers\Concerns;

use App\Helpers\RucParaguayo;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait NormalizaRuc
{
    protected function normalizarRuc(Request $request, string $campo = 'ruc', string $campoDv = 'dv'): void
    {
        $ruc = $request->input($campo);
        $dv = $request->input($campoDv);
        if (($ruc === null || $ruc === '') && ($dv === null || $dv === '')) return;
        if (($ruc !== null && !is_string($ruc) && !is_int($ruc))
            || ($dv !== null && !is_string($dv) && !is_int($dv))) {
            throw ValidationException::withMessages([$campo => 'El RUC y el DV deben ser valores numéricos, no listas u objetos.']);
        }
        try {
            $partes = RucParaguayo::validar((string) $ruc, $dv !== null ? (string) $dv : null);
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([$campo => $e->getMessage()]);
        }
        $request->merge([$campo => $partes['ruc'], $campoDv => $partes['dv']]);
    }

    /** Completa los datos de receptor SIFEN del cliente antes de validarlo. */
    protected function prepararIdentidadSifen(Request $request, ?Cliente $cliente = null): void
    {
        if (!$request->exists('es_contribuyente')) {
            $request->merge([
                'es_contribuyente' => $cliente
                    ? (int) $cliente->es_contribuyente
                    : ($request->filled('ruc') ? 1 : 0),
            ]);
        }

        if ($request->boolean('es_contribuyente')) {
            $this->normalizarRuc($request);
            if (!$request->filled('tipo_contribuyente') && $request->filled('ruc')) {
                $conservar = $cliente?->tipo_contribuyente && $cliente->ruc === $request->input('ruc');
                $request->merge(['tipo_contribuyente' => $conservar
                    ? $cliente->tipo_contribuyente
                    : RucParaguayo::tipoContribuyente((string) $request->input('ruc'))]);
            }
            return;
        }

        $request->merge(['tipo_contribuyente' => null]);
        if (!$request->filled('tipo_identificacion')) {
            $request->merge(['tipo_identificacion' => 1]);
        }
        // Clientes cargados con ruc/dv: la cédula es el número, sin el DV del RUC.
        $numero = $request->filled('numero_identificacion') ? $request->input('numero_identificacion') : $request->input('ruc');
        if (is_string($numero) || is_int($numero)) {
            $request->merge(['numero_identificacion' => RucParaguayo::numeroDocumento(
                (int) $request->input('tipo_identificacion'),
                (string) $numero
            )]);
        }
    }
}
