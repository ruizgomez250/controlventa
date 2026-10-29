<?php

namespace App\Http\Controllers\Concerns;

use App\Helpers\RucParaguayo;
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
}
