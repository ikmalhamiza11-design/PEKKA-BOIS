<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UlasanController extends Controller
{
    public function index(Request $request): View
    {
        $admin = $request->user();
        $filter = $request->query('filter');
        $filter = in_array($filter, ['tampil', 'disembunyikan', 'rendah'], true) ? $filter : 'semua';
        $kata = $request->query('q');
        $kata = is_string($kata) ? mb_substr(trim($kata), 0, 100) : '';

        $ulasan = $admin->cakupanUlasan()
            ->with(['user', 'orderItem', 'shop'])
            ->when($filter === 'tampil', fn (Builder $query) => $query->tampil())
            ->when($filter === 'disembunyikan', fn (Builder $query) => $query->disembunyikan())
            ->when($filter === 'rendah', fn (Builder $query) => $query->where('rating', '<=', 2))
            ->when($kata !== '', function (Builder $query) use ($kata) {
                $pola = '%'.addcslashes($kata, '%_\\').'%';

                $query->where(function (Builder $cari) use ($pola) {
                    $cari->where('komentar', 'like', $pola)
                        ->orWhereHas('shop', fn (Builder $toko) => $toko->where('nama', 'like', $pola))
                        ->orWhereHas('orderItem', fn (Builder $item) => $item->where('nama', 'like', $pola));
                });
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.ulasan.index', [
            'ulasan' => $ulasan,
            'filter' => $filter,
            'kata' => $kata,
            'jumlahDisembunyikan' => $admin->cakupanUlasan()->disembunyikan()->count(),
        ]);
    }

    public function sembunyikan(Request $request, int $id): RedirectResponse
    {
        $ulasan = $request->user()->cakupanUlasan()->findOrFail($id);

        $data = $request->validate([
            'alasan' => ['required', 'string', 'min:5', 'max:300'],
        ]);

        if ($ulasan->disembunyikan()) {
            return redirect()->back(fallback: route('admin.ulasan.index'))->with('galat', 'Ulasan ini sudah disembunyikan.');
        }

        $ulasan->sembunyikan($request->user(), trim($data['alasan']));

        return redirect()->back(fallback: route('admin.ulasan.index'))->with('pesan', 'Ulasan disembunyikan dari halaman produk dan perhitungan penilaian.');
    }

    public function tampilkan(Request $request, int $id): RedirectResponse
    {
        $ulasan = $request->user()->cakupanUlasan()->findOrFail($id);

        if (! $ulasan->disembunyikan()) {
            return redirect()->back(fallback: route('admin.ulasan.index'))->with('galat', 'Ulasan ini sudah tampil.');
        }

        $ulasan->tampilkanKembali($request->user());

        return redirect()->back(fallback: route('admin.ulasan.index'))->with('pesan', 'Ulasan ditampilkan kembali.');
    }
}
