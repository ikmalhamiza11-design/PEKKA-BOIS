<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\PesananService;
use Illuminate\Console\Command;

class PesananKedaluwarsa extends Command
{
    protected $signature = 'pesanan:kedaluwarsa {--jam=24 : Batas waktu pembayaran dalam jam}';

    protected $description = 'Batalkan pesanan yang belum dibayar melewati batas waktu dan kembalikan stoknya';

    public function handle(PesananService $layanan): int
    {
        $batas = now()->subHours(max(1, (int) $this->option('jam')));
        $jumlah = 0;

        Order::where('status', Order::MENUNGGU_PEMBAYARAN)
            ->where('updated_at', '<', $batas)
            ->pluck('id')
            ->each(function ($id) use ($layanan, &$jumlah) {
                if ($layanan->batalkan($id)) {
                    $jumlah++;
                }
            });

        $this->info($jumlah.' pesanan dibatalkan.');

        return self::SUCCESS;
    }
}
