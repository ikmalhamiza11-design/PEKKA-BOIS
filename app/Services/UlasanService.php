<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

class UlasanService
{
    public function ringkasan(Builder $query): array
    {
        $baris = $query->toBase()
            ->reorder()
            ->selectRaw('rating, count(*) as jumlah')
            ->groupBy('rating')
            ->pluck('jumlah', 'rating');

        $sebaran = [];
        $jumlah = 0;
        $total = 0;

        foreach ([5, 4, 3, 2, 1] as $bintang) {
            $banyak = (int) ($baris[$bintang] ?? 0);
            $sebaran[$bintang] = $banyak;
            $jumlah += $banyak;
            $total += $banyak * $bintang;
        }

        return [
            'jumlah' => $jumlah,
            'rata' => $jumlah > 0 ? round($total / $jumlah, 1) : null,
            'sebaran' => $sebaran,
        ];
    }
}
