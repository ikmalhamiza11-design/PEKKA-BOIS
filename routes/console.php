<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('pesanan:kedaluwarsa')->hourly();
// Stok produk berstok harian kembali ke jatahnya setiap pagi (WIB).
Schedule::command('stok:reset-harian')->dailyAt('00:05')->timezone(config('app.zona_toko', 'Asia/Jakarta'));
