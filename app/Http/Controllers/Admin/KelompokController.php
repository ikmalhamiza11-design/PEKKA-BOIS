<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelompok;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KelompokController extends Controller
{
    private const BATAS_IMPOR = 200;

    public function index(Request $request): View
    {
        $kata = $request->query('q');
        $kata = is_string($kata) ? mb_substr(trim($kata), 0, 100) : '';

        $kelompok = Kelompok::withCount('shops')
            ->when($kata !== '', function (Builder $query) use ($kata) {
                $pola = '%'.addcslashes($kata, '%_\\').'%';

                $query->where(function (Builder $cari) use ($pola) {
                    $cari->where('nama', 'like', $pola)
                        ->orWhere('desa', 'like', $pola)
                        ->orWhere('kecamatan', 'like', $pola);
                });
            })
            ->orderBy('nama')
            ->paginate(25)
            ->withQueryString();

        return view('admin.kelompok.index', [
            'kelompok' => $kelompok,
            'kata' => $kata,
            'total' => Kelompok::count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request);

        if ($this->sudahAda($data['nama'], $data['desa'], $data['kecamatan'])) {
            return back()->withInput()->with('galat', 'Kelompok dengan nama, desa, dan kecamatan yang sama sudah ada.');
        }

        Kelompok::create($data);

        return redirect()->route('admin.kelompok.index')->with('pesan', 'Kelompok '.$data['nama'].' ditambahkan.');
    }

    public function edit(int $id): View
    {
        return view('admin.kelompok.edit', ['kelompok' => Kelompok::findOrFail($id)]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $kelompok = Kelompok::findOrFail($id);
        $data = $this->validasi($request);

        if ($this->sudahAda($data['nama'], $data['desa'], $data['kecamatan'], $kelompok->id)) {
            return back()->withInput()->with('galat', 'Kelompok dengan nama, desa, dan kecamatan yang sama sudah ada.');
        }

        $kelompok->update($data);

        return redirect()->route('admin.kelompok.index')->with('pesan', 'Data kelompok diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $kelompok = Kelompok::withCount('shops')->findOrFail($id);

        if ($kelompok->shops_count > 0 || User::where('kelompok_id', $kelompok->id)->exists()) {
            return redirect()->route('admin.kelompok.index')->with('galat', 'Kelompok '.$kelompok->nama.' masih punya toko atau admin, jadi tidak bisa dihapus.');
        }

        $kelompok->delete();

        return redirect()->route('admin.kelompok.index')->with('pesan', 'Kelompok '.$kelompok->nama.' dihapus.');
    }

    public function impor(Request $request): RedirectResponse
    {
        $request->validate([
            'daftar' => ['required', 'string', 'max:30000'],
        ]);

        $baris = preg_split('/\R/u', (string) $request->input('daftar'), -1, PREG_SPLIT_NO_EMPTY);
        $baris = array_values(array_filter(array_map('trim', $baris), fn ($item) => $item !== ''));

        if (count($baris) > self::BATAS_IMPOR) {
            return back()->withInput()->with('galat', 'Maksimal '.self::BATAS_IMPOR.' baris sekali tempel.');
        }

        $tambah = 0;
        $lewat = 0;
        $salah = [];

        foreach ($baris as $nomor => $isi) {
            $bagian = array_map('trim', preg_split('/[\t;|]/u', $isi));
            $nama = $bagian[0] ?? '';
            $desa = ($bagian[1] ?? '') !== '' ? $bagian[1] : null;
            $kecamatan = ($bagian[2] ?? '') !== '' ? $bagian[2] : null;

            if ($nama === '' || mb_strlen($nama) > 255 || mb_strlen((string) $desa) > 255 || mb_strlen((string) $kecamatan) > 255) {
                $salah[] = $nomor + 1;

                continue;
            }

            if ($this->sudahAda($nama, $desa, $kecamatan)) {
                $lewat++;

                continue;
            }

            Kelompok::create(['nama' => $nama, 'desa' => $desa, 'kecamatan' => $kecamatan]);
            $tambah++;
        }

        $ringkasan = $tambah.' kelompok ditambahkan, '.$lewat.' dilewati karena sudah ada';

        if ($salah !== []) {
            $ringkasan .= ', baris '.implode(', ', array_slice($salah, 0, 10)).' dilewati karena nama kosong atau terlalu panjang';
        }

        return redirect()->route('admin.kelompok.index')->with($tambah > 0 ? 'pesan' : 'galat', $ringkasan.'.');
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'desa' => ['nullable', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        return [
            'nama' => trim($data['nama']),
            'desa' => filled($data['desa'] ?? null) ? trim($data['desa']) : null,
            'kecamatan' => filled($data['kecamatan'] ?? null) ? trim($data['kecamatan']) : null,
            'latitude' => filled($data['latitude'] ?? null) ? (float) $data['latitude'] : null,
            'longitude' => filled($data['longitude'] ?? null) ? (float) $data['longitude'] : null,
        ];
    }

    private function sudahAda(string $nama, ?string $desa, ?string $kecamatan, ?int $kecuali = null): bool
    {
        $query = Kelompok::query()->whereRaw('lower(nama) = ?', [mb_strtolower($nama)]);

        foreach (['desa' => $desa, 'kecamatan' => $kecamatan] as $kolom => $nilai) {
            if ($nilai === null) {
                $query->whereNull($kolom);
            } else {
                $query->whereRaw('lower('.$kolom.') = ?', [mb_strtolower($nilai)]);
            }
        }

        return $query->when($kecuali, fn (Builder $q) => $q->whereKeyNot($kecuali))->exists();
    }
}
