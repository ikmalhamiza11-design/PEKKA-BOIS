<?php

namespace App\Http\Controllers;

use App\Models\Kelompok;
use App\Models\Shop;
use Illuminate\View\View;

class PetaController extends Controller
{
    public function __invoke(): View
    {
        $kelompok = Kelompok::query()
            ->whereHas('shops', fn ($query) => $query->where('status', Shop::TERVERIFIKASI))
            ->withCount(['shops as toko_count' => fn ($query) => $query->where('status', Shop::TERVERIFIKASI)])
            ->orderBy('kecamatan')
            ->orderBy('nama')
            ->get();

        $titik = $kelompok
            ->filter(fn (Kelompok $item) => $item->punyaKoordinat())
            ->map(fn (Kelompok $item) => [
                'nama' => $item->nama,
                'wilayah' => collect([$item->desa, $item->kecamatan])->filter()->implode(', '),
                'toko' => $item->toko_count,
                'lat' => $item->latitude,
                'lng' => $item->longitude,
                'url' => route('katalog', ['kelompok' => $item->id]),
            ])
            ->values();

        return view('peta.index', [
            'kelompok' => $kelompok->groupBy(fn (Kelompok $item) => $item->kecamatan ?: 'Kecamatan belum diisi'),
            'titik' => $titik,
            'jumlahKelompok' => $kelompok->count(),
        ]);
    }
}
