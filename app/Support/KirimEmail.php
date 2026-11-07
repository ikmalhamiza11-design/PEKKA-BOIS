<?php

namespace App\Support;

class KirimEmail
{
    /**
     * Jalankan aksi yang mengirim email. Kalau pengiriman gagal (misalnya Gmail menolak
     * atau jaringan putus), galatnya dicatat di log dan pengguna tidak melihat halaman error.
     */
    public static function coba(callable $aksi): bool
    {
        try {
            $aksi();

            return true;
        } catch (\Throwable $galat) {
            report($galat);

            return false;
        }
    }
}
