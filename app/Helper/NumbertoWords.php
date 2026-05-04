<?php

namespace App\Helper;

class NumberToWords
{
    private static $units = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
    private static $teens = ['diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve'];
    private static $tens = ['', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
    private static $hundreds = [1 => 'cien', 2 => 'doscientos', 3 => 'trescientos', 4 => 'cuatrocientos', 5 => 'quinientos', 6 => 'seiscientos', 7 => 'setecientos', 8 => 'ochocientos', 9 => 'novecientos'];

    public function toWords($number, $decimalPlaces = 2)
    {
        $number = (float) $number;
        $guarani = floor($number);
        $centavos = round(($number - $guarani) * 100);

        $words = $this->convertNumberToWords($guarani);
        $words .= ' guaraníes';

        if ($centavos > 0) {
            $words .= ' con ' . $this->convertNumberToWords($centavos) . ' céntimos';
        }

        return ucfirst($words);
    }

    private function convertNumberToWords($number)
    {
        if ($number == 0) {
            return 'cero';
        }

        if ($number < 1000) {
            return $this->convertLessThanThousand($number);
        }

        if ($number < 1000000) {
            return $this->convertThousandToWords($number);
        }

        return $this->convertLessThanMillion($number);
    }

    private function convertThousandToWords($number)
    {
        $thousands = floor($number / 1000);
        $remainder = $number % 1000;
        $words = '';

        if ($thousands > 0) {
            if ($thousands == 1) {
                $words .= 'mil';
            } else {
                $words .= $this->convertLessThanThousand($thousands) . ' mil';
            }
            if ($remainder > 0) {
                $words .= ' ';
            }
        }

        if ($remainder > 0) {
            $words .= $this->convertLessThanThousand($remainder);
        }

        return $words;
    }

    private function convertLessThanMillion($number)
    {
        $million = floor($number / 1000000);
        $remainder = $number % 1000000;
        $words = '';

        if ($million > 0) {
            if ($million == 1) {
                $words .= 'un millón';
            } else {
                $words .= $this->convertLessThanThousand($million) . ' millones';
            }
            if ($remainder > 0) {
                $words .= ' ';
            }
        }

        if ($remainder > 0) {
            $words .= $this->convertThousandToWords($remainder);
        }

        return $words;
    }

    private function convertLessThanThousand($number)
    {
        if ($number == 0) {
            return '';
        }

        $hundreds = floor($number / 100);
        $remainder = $number % 100;
        $words = '';

        // Procesar centenas
        if ($hundreds > 0) {
            if ($hundreds == 1 && $remainder == 0) {
                return 'cien';
            }
            
            // Verificar que el índice existe
            if (isset(self::$hundreds[$hundreds])) {
                $words = self::$hundreds[$hundreds];
            } else {
                $words = self::$hundreds[1]; // fallback a 'cien'
            }
            
            if ($remainder > 0) {
                $words .= ' ';
            }
        }

        // Procesar números del 1 al 99
        if ($remainder > 0) {
            if ($remainder < 10) {
                $words .= self::$units[$remainder];
            } elseif ($remainder < 20) {
                $words .= self::$teens[$remainder - 10];
            } else {
                $tens = floor($remainder / 10);
                $units = $remainder % 10;
                
                if ($tens >= 2) {
                    $words .= self::$tens[$tens];
                    
                    if ($units > 0) {
                        if ($tens == 2) {
                            // Caso especial: veintiuno, veintidós, etc.
                            $words .= 'i' . self::$units[$units];
                        } else {
                            $words .= ' y ' . self::$units[$units];
                        }
                    }
                }
            }
        }

        return $words;
    }
}