<?php

namespace App\Support;

use App\Models\Order;

class PesanWa
{
    /**
     * Tautan wa.me ke penjual dengan pesan yang sudah terisi.
     * Mengembalikan null kalau penjual belum punya nomor WhatsApp.
     */
    public static function untukPenjual(Order $pesanan): ?string
    {
        $nomor = Telepon::normalisasi($pesanan->shop?->user?->phone);

        // Nomor kosong menjadi "62" saja, itu bukan nomor yang bisa dihubungi.
        if (strlen($nomor) < 9) {
            return null;
        }

        return 'https://wa.me/'.$nomor.'?text='.rawurlencode(self::isiPesan($pesanan));
    }

    public static function isiPesan(Order $pesanan): string
    {
        $produk = $pesanan->items
            ->map(fn ($item) => '- '.$item->nama.' x '.$item->jumlah.($item->pre_order_hari ? ' (pre-order, siap sekitar '.$item->pre_order_hari.' hari)' : ''))
            ->implode("\n");

        $baris = [
            'Halo, saya pembeli dari PEKKA Bogor Istimewa. Saya sudah transfer dan mengunggah bukti pembayaran.',
            '',
            'Kode pesanan: '.$pesanan->kode,
            'Produk:',
            $produk,
            'Total yang sudah ditransfer: '.Rupiah::format($pesanan->total).' (harga produk saja)',
            '',
            'Nama penerima: '.$pesanan->nama_penerima,
            'Alamat: '.preg_replace('/\s*\R\s*/', ', ', trim((string) $pesanan->alamat)),
        ];

        if (filled($pesanan->catatan)) {
            $baris[] = 'Catatan: '.$pesanan->catatan;
        }

        $baris[] = '';
        $baris[] = 'Bukti transfer sudah saya unggah di situs. Mohon dicek ya. Untuk ambil sendiri atau diantar, dan biaya tambahannya, mari kita bicarakan di sini.';

        return implode("\n", $baris);
    }
}
