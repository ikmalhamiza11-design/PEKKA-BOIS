<?php

namespace App\Support;

class Telepon
{
    public static function normalisasi(?string $nomor): string
    {
        $angka = preg_replace('/\D+/', '', (string) $nomor);

        if (str_starts_with($angka, '62')) {
            return $angka;
        }

        if (str_starts_with($angka, '0')) {
            return '62'.substr($angka, 1);
        }

        return '62'.$angka;
    }
}
