<?php

namespace App\Console\Commands;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Console\Command;

class SetujuiToko extends Command
{
    protected $signature = 'toko:setujui {email}';

    protected $description = 'Setujui toko milik penjual (sementara, sampai halaman verifikasi admin kelompok dibuat)';

    public function handle(): int
    {
        $toko = User::where('email', $this->argument('email'))->first()?->shop;

        if (! $toko) {
            $this->error('Toko untuk email itu tidak ditemukan.');

            return self::FAILURE;
        }

        $toko->update(['status' => Shop::TERVERIFIKASI]);
        $this->info('Toko "'.$toko->nama.'" sekarang terverifikasi.');

        return self::SUCCESS;
    }
}
