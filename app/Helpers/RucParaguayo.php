<?php

namespace App\Helpers;

use InvalidArgumentException;

/**
 * Referencia del cálculo: SET/DNIT, Dígito Verificador (basemax 11).
 * https://www.dnit.gov.py/documents/20123/224893/D%C3%ADgito%2BVerificador.pdf/fb9f86c8-245d-9dad-2dc1-ac3b3dc307a7
 * El certificado digital no se procesa con esta clase.
 */
final class RucParaguayo
{
    /** Separa el formato visible o acepta los dos campos ya separados. No valida el cálculo del DV. */
    public static function splitRuc(string $entrada, ?string $dv = null): array
    {
        $entrada = self::sinEspacios($entrada);
        $dv = $dv !== null ? self::sinEspacios($dv) : null;
        if (str_contains($entrada, '-')) {
            if (!preg_match('/^([0-9]{5,8})-([0-9])$/D', $entrada, $partes)) {
                throw new InvalidArgumentException('Use un RUC de 5 a 8 dígitos, un solo guion y un DV de un dígito.');
            }
            if ($dv !== null && $dv !== '' && $dv !== $partes[2]) {
                throw new InvalidArgumentException('El DV separado no coincide con el DV del RUC ingresado.');
            }
            return ['ruc' => $partes[1], 'dv' => $partes[2]];
        }
        self::validarNumero($entrada);
        if ($dv === null || !preg_match('/^[0-9]$/D', $dv)) {
            throw new InvalidArgumentException('Complete el identificador/DV con exactamente un dígito.');
        }
        return ['ruc' => $entrada, 'dv' => $dv];
    }

    /** El WS admite el número solo; nunca se deduce ni se recorta su último dígito. */
    public static function numeroConsulta(string $entrada): string
    {
        $entrada = self::sinEspacios($entrada);
        if (str_contains($entrada, '-')) return self::splitRuc($entrada)['ruc'];
        self::validarNumero($entrada);
        return $entrada;
    }

    /** Algoritmo publicado por SET/DNIT: módulo 11, pesos 2 a 11 desde la derecha. */
    public static function calcularDv(string $ruc): string
    {
        self::validarNumero($ruc);
        $suma = 0;
        $peso = 2;
        for ($i = strlen($ruc) - 1; $i >= 0; $i--) {
            $suma += (int) $ruc[$i] * $peso;
            $peso = $peso === 11 ? 2 : $peso + 1;
        }
        $resto = $suma % 11;
        return (string) ($resto > 1 ? 11 - $resto : 0);
    }

    /** Valida formato y cálculo; esto no comprueba la existencia en Marangatu. */
    public static function validar(string $entrada, ?string $dv = null): array
    {
        $partes = self::splitRuc($entrada, $dv);
        $esperado = self::calcularDv($partes['ruc']);
        if ($esperado !== $partes['dv']) {
            throw new InvalidArgumentException('El dígito verificador no coincide. Esperado: ' . $esperado . ', presente: ' . $partes['dv'] . '.');
        }
        return $partes;
    }

    public static function sinEspacios(string $valor): string
    {
        $limpio = preg_replace('/[\s\p{Z}]+/u', '', $valor);
        if ($limpio === null) throw new InvalidArgumentException('El RUC contiene caracteres inválidos.');
        return $limpio;
    }

    private static function validarNumero(string $ruc): void
    {
        if (!preg_match('/^[0-9]{5,8}$/D', $ruc)) {
            throw new InvalidArgumentException('El número de RUC debe contener entre 5 y 8 dígitos, sin letras, guion ni DV.');
        }
    }
}
