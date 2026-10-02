<?php

namespace App\Support;

/**
 * Validação de CPF (11) e CNPJ (14) com dígitos verificadores.
 * O Asaas rejeita documentos inválidos ou o placeholder 00000000000.
 */
class CpfCnpj
{
    public static function digits(?string $value): string
    {
        return preg_replace('/\D+/', '', (string) $value) ?? '';
    }

    public static function isValid(?string $value): bool
    {
        $digits = static::digits($value);

        return match (strlen($digits)) {
            11 => static::isValidCpf($digits),
            14 => static::isValidCnpj($digits),
            default => false,
        };
    }

    public static function normalize(?string $value): ?string
    {
        return static::isValid($value) ? static::digits($value) : null;
    }

    private static function isValidCpf(string $cpf): bool
    {
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $cpf[$i] * (($t + 1) - $i);
            }
            $digit = ((10 * $sum) % 11) % 10;
            if ((int) $cpf[$t] !== $digit) {
                return false;
            }
        }

        return true;
    }

    private static function isValidCnpj(string $cnpj): bool
    {
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        $w1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $w2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $d1 = static::mod11($cnpj, $w1);
        $d2 = static::mod11($cnpj, $w2);

        return (int) $cnpj[12] === $d1 && (int) $cnpj[13] === $d2;
    }

    /**
     * @param  list<int>  $weights
     */
    private static function mod11(string $digits, array $weights): int
    {
        $sum = 0;
        foreach ($weights as $i => $weight) {
            $sum += (int) $digits[$i] * $weight;
        }

        $mod = $sum % 11;

        return $mod < 2 ? 0 : 11 - $mod;
    }
}
