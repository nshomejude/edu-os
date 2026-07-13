<?php

namespace App\Support;

/**
 * ISO/IEC 7064 MOD 37-2 check character (FR-NTR-ID-03): lets hand-typed NCIDs
 * be validated offline. Charset 0-9 A-Z with '*' as the 37th check character.
 */
class CheckDigit
{
    private const CHARS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ*';

    private static function value(string $c): int
    {
        $pos = strpos(self::CHARS, strtoupper($c));

        return $pos === false ? 0 : $pos;
    }

    public static function compute(string $input): string
    {
        $p = 36;
        foreach (str_split(preg_replace('/[^0-9A-Za-z]/', '', $input)) as $c) {
            $s = ($p + self::value($c)) % 36;
            $p = (2 * ($s === 0 ? 36 : $s)) % 37;
        }

        return self::CHARS[(38 - $p) % 37];
    }

    /** NCID with trailing check character: {base}-{C}. */
    public static function append(string $base): string
    {
        return $base.'-'.self::compute($base);
    }

    public static function validate(string $ncidWithCheck): bool
    {
        if (! preg_match('/^(.*)-([0-9A-Z*])$/', strtoupper($ncidWithCheck), $m)) {
            return false;
        }

        return self::compute($m[1]) === $m[2];
    }
}
