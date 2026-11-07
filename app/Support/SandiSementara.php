<?php

namespace App\Support;

class SandiSementara
{
    /**
     * Sandi 8 karakter yang mudah dibaca lewat WhatsApp (tanpa huruf/angka yang mirip)
     * dan selalu memenuhi aturan: huruf besar, huruf kecil, dan angka.
     */
    public static function buat(): string
    {
        $karakter = [
            static::ambil('ABCDEFGHJKLMNPQRSTUVWXYZ', 2),
            static::ambil('abcdefghijkmnpqrstuvwxyz', 3),
            static::ambil('23456789', 3),
        ];

        $semua = str_split(implode('', $karakter));

        for ($i = count($semua) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$semua[$i], $semua[$j]] = [$semua[$j], $semua[$i]];
        }

        return implode('', $semua);
    }

    protected static function ambil(string $himpunan, int $jumlah): string
    {
        $hasil = '';

        for ($i = 0; $i < $jumlah; $i++) {
            $hasil .= $himpunan[random_int(0, strlen($himpunan) - 1)];
        }

        return $hasil;
    }
}
