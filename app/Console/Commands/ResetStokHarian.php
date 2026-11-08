<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetStokHarian extends Command
{
    protected $signature = 'stok:reset-harian';

    protected $description = 'Kembalikan stok produk berstok harian ke jatah hariannya';

    public function handle(): int
    {
        $jumlah = Product::whereNotNull('stok_harian')
            ->where('pre_order', false)
            ->update(['stok' => DB::raw('stok_harian')]);

        $this->info($jumlah.' produk diatur ulang ke stok harian.');

        return self::SUCCESS;
    }
}
