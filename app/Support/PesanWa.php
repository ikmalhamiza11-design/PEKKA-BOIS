<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Facades\URL;

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

        $pembayaran = $pesanan->pembayaranTerakhir;

        $baris = [
            'Halo, saya pembeli dari PEKKA Bogor Istimewa. Saya sudah transfer dan mengunggah bukti pembayaran.',
            '',
            'Kode pesanan: '.$pesanan->kode,
            'Produk:',
            $produk,
            'Total yang sudah ditransfer: '.Rupiah::format($pembayaran?->jumlah ?? $pesanan->total).' (harga produk saja, sesuai total pesanan)',
            $pembayaran ? 'Atas nama pengirim: '.$pembayaran->nama_pengirim : null,
            '',
            'Nama penerima: '.$pesanan->nama_penerima,
            'Alamat: '.preg_replace('/\s*\R\s*/', ', ', trim((string) $pesanan->alamat)),
        ];

        if (filled($pesanan->catatan)) {
            $baris[] = 'Catatan: '.$pesanan->catatan;
        }

        if ($pembayaran) {
            $baris[] = '';
            $baris[] = 'Bukti transfer (foto resi):';
            $baris[] = URL::temporarySignedRoute('pembayaran.bukti.tautan', now()->addDays(7), ['id' => $pembayaran->id]);
        }

        $baris[] = '';
        $baris[] = 'Bukti transfer juga sudah saya unggah di situs. Mohon dicek ya. Untuk ambil sendiri atau diantar, dan biaya tambahannya, mari kita bicarakan di sini.';

        return implode("\n", array_filter($baris, fn ($b) => $b !== null));
    }
}
