<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Kelompok;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class KatalogController extends Controller
{
    private const PER_TAHAP = 12;
    private const BATAS_MAKS = 120;

    public function __invoke(Request $request): View
    {
        $kata = mb_substr(trim($this->teks($request, 'q')), 0, 100);
        $kelompokId = max(0, (int) $this->teks($request, 'kelompok'));
        $hargaMin = max(0, (int) $this->teks($request, 'harga_min'));
        $hargaMaks = max(0, (int) $this->teks($request, 'harga_maks'));

        if ($hargaMin > 0 && $hargaMaks > 0 && $hargaMin > $hargaMaks) {
            [$hargaMin, $hargaMaks] = [$hargaMaks, $hargaMin];
        }

        $urut = in_array($this->teks($request, 'urut'), ['termurah', 'termahal'], true)
            ? $this->teks($request, 'urut')
            : 'terbaru';

        $batas = min(max((int) $this->teks($request, 'tampil'), self::PER_TAHAP), self::BATAS_MAKS);

        $kategori = Category::orderBy('urutan')->get();
        $slugValid = $kategori->pluck('slug')->all();
        $slugDipilih = array_values(array_intersect($this->daftar($request, 'kategori'), $slugValid));

        $produk = Product::tampil()->with(['shop.kelompok', 'category']);

        if ($kata !== '') {
            $pola = '%'.addcslashes($kata, '%_\\').'%';

            $produk->where(function (Builder $cari) use ($pola) {
                $cari->where('nama', 'like', $pola)
                    ->orWhere('deskripsi', 'like', $pola)
                    ->orWhereHas('shop', function (Builder $toko) use ($pola) {
                        $toko->where('nama', 'like', $pola)
                            ->orWhereHas('kelompok', fn (Builder $kelompok) => $kelompok->where('nama', 'like', $pola));
                    });
            });
        }

        if ($slugDipilih !== []) {
            $produk->whereHas('category', fn (Builder $item) => $item->whereIn('slug', $slugDipilih));
        }

        if ($kelompokId > 0) {
            $produk->whereHas('shop', fn (Builder $toko) => $toko->where('kelompok_id', $kelompokId));
        }

        if ($hargaMin > 0) {
            $produk->where('harga', '>=', $hargaMin);
        }

        if ($hargaMaks > 0) {
            $produk->where('harga', '<=', $hargaMaks);
        }

        match ($urut) {
            'termurah' => $produk->orderBy('harga')->orderByDesc('id'),
            'termahal' => $produk->orderByDesc('harga')->orderByDesc('id'),
            default => $produk->orderByDesc('id'),
        };

        $total = (clone $produk)->count();
        $daftar = $produk->take($batas)->get();

        $kelompok = Kelompok::whereHas('shops', fn (Builder $toko) => $toko->where('status', Shop::TERVERIFIKASI))
            ->orderBy('nama')
            ->get();

        $hitung = Product::tampil()
            ->selectRaw('category_id, count(*) as jumlah')
            ->groupBy('category_id')
            ->pluck('jumlah', 'category_id');

        $parameter = array_filter([
            'q' => $kata,
            'kategori' => $slugDipilih,
            'kelompok' => $kelompokId > 0 ? $kelompokId : null,
            'harga_min' => $hargaMin > 0 ? $hargaMin : null,
            'harga_maks' => $hargaMaks > 0 ? $hargaMaks : null,
            'urut' => $urut !== 'terbaru' ? $urut : null,
        ], fn ($nilai) => $nilai !== null && $nilai !== '' && $nilai !== []);

        $chip = [];

        if ($kata !== '') {
            $chip[] = ['label' => 'Kata kunci: '.$kata, 'url' => route('katalog', Arr::except($parameter, ['q']))];
        }

        foreach ($slugDipilih as $slug) {
            $nama = $kategori->firstWhere('slug', $slug)?->nama ?? $slug;
            $sisa = array_values(array_diff($slugDipilih, [$slug]));
            $chip[] = ['label' => $nama, 'url' => route('katalog', array_filter(array_merge(Arr::except($parameter, ['kategori']), ['kategori' => $sisa])))];
        }

        if ($kelompokId > 0 && ($dipilih = $kelompok->firstWhere('id', $kelompokId))) {
            $chip[] = ['label' => $dipilih->nama, 'url' => route('katalog', Arr::except($parameter, ['kelompok']))];
        }

        if ($hargaMin > 0 || $hargaMaks > 0) {
            $label = match (true) {
                $hargaMin > 0 && $hargaMaks > 0 => 'Rp '.number_format($hargaMin, 0, ',', '.').' - Rp '.number_format($hargaMaks, 0, ',', '.'),
                $hargaMin > 0 => 'Mulai Rp '.number_format($hargaMin, 0, ',', '.'),
                default => 'Sampai Rp '.number_format($hargaMaks, 0, ',', '.'),
            };

            $chip[] = ['label' => $label, 'url' => route('katalog', Arr::except($parameter, ['harga_min', 'harga_maks']))];
        }

        $jumlahFilter = count($slugDipilih) + ($kelompokId > 0 ? 1 : 0) + ($hargaMin > 0 || $hargaMaks > 0 ? 1 : 0);

        return view('katalog.index', [
            'produk' => $daftar,
            'total' => $total,
            'kategori' => $kategori,
            'kelompok' => $kelompok,
            'hitung' => $hitung,
            'parameter' => $parameter,
            'chip' => $chip,
            'jumlahFilter' => $jumlahFilter,
            'urut' => $urut,
            'filter' => [
                'kategori' => $slugDipilih,
                'kelompok' => $kelompokId,
                'harga_min' => $hargaMin > 0 ? $hargaMin : '',
                'harga_maks' => $hargaMaks > 0 ? $hargaMaks : '',
            ],
            'adaLagi' => $total > $batas,
            'urlLagi' => route('katalog', array_merge($parameter, ['tampil' => $batas + self::PER_TAHAP])).'#hasil',
        ]);
    }

    private function teks(Request $request, string $kunci): string
    {
        $nilai = $request->query($kunci, '');

        return is_string($nilai) ? $nilai : '';
    }

    private function daftar(Request $request, string $kunci): array
    {
        $nilai = $request->query($kunci, []);
        $nilai = is_array($nilai) ? $nilai : [$nilai];

        return array_values(array_unique(array_filter($nilai, fn ($item) => is_string($item) && $item !== '')));
    }
}
