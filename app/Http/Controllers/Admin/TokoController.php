<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelompok;
use App\Models\Shop;
use App\Notifications\HasilVerifikasiToko;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TokoController extends Controller
{
    private const STATUS = [Shop::MENUNGGU, Shop::TERVERIFIKASI, Shop::DITOLAK];

    public function index(Request $request): View
    {
        $admin = $request->user();
        $status = $this->teks($request, 'status');
        $status = in_array($status, [...self::STATUS, 'semua'], true) ? $status : Shop::MENUNGGU;
        $kata = mb_substr(trim($this->teks($request, 'q')), 0, 100);
        $kelompokId = $admin->adalahAdminDinas() ? max(0, (int) $this->teks($request, 'kelompok')) : 0;

        $toko = $admin->cakupanToko()
            ->with(['user', 'kelompok'])
            ->withCount('products')
            ->when($status !== 'semua', fn (Builder $query) => $query->where('status', $status))
            ->when($kelompokId > 0, fn (Builder $query) => $query->where('kelompok_id', $kelompokId))
            ->when($kata !== '', function (Builder $query) use ($kata) {
                $pola = '%'.addcslashes($kata, '%_\\').'%';

                $query->where(function (Builder $cari) use ($pola) {
                    $cari->where('nama', 'like', $pola)
                        ->orWhereHas('user', fn (Builder $user) => $user->where('name', 'like', $pola)->orWhere('email', 'like', $pola));
                });
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $hitung = $admin->cakupanToko()
            ->selectRaw('status, count(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        return view('admin.toko.index', [
            'toko' => $toko,
            'status' => $status,
            'kata' => $kata,
            'kelompokId' => $kelompokId,
            'kelompok' => $admin->adalahAdminDinas() ? Kelompok::orderBy('nama')->get() : collect(),
            'hitung' => $hitung,
        ]);
    }

    public function show(Request $request, int $id): View
    {
        $toko = $request->user()->cakupanToko()
            ->with(['user', 'kelompok', 'diverifikasiOleh'])
            ->withCount('products')
            ->findOrFail($id);

        return view('admin.toko.show', ['toko' => $toko]);
    }

    public function setujui(Request $request, int $id): RedirectResponse
    {
        $toko = $request->user()->cakupanToko()->with('user')->findOrFail($id);

        if ($toko->status === Shop::TERVERIFIKASI) {
            return redirect()->route('admin.toko.show', $toko->id)->with('galat', 'Toko ini sudah terverifikasi.');
        }

        $toko->setujui($request->user());
        $this->beritahu($toko);

        return redirect()->route('admin.toko.show', $toko->id)->with('pesan', 'Toko disetujui. Produk aktifnya kini tampil di katalog.');
    }

    public function tolak(Request $request, int $id): RedirectResponse
    {
        $toko = $request->user()->cakupanToko()->with('user')->findOrFail($id);

        $data = $request->validate([
            'alasan' => ['required', 'string', 'min:5', 'max:300'],
        ]);

        if ($toko->status === Shop::DITOLAK) {
            return redirect()->route('admin.toko.show', $toko->id)->with('galat', 'Toko ini sudah ditolak sebelumnya.');
        }

        $toko->tolak($request->user(), trim($data['alasan']));
        $this->beritahu($toko);

        return redirect()->route('admin.toko.show', $toko->id)->with('pesan', 'Toko ditolak dan alasannya sudah disampaikan ke penjual.');
    }

    private function beritahu(Shop $toko): void
    {
        try {
            $toko->user->notify(new HasilVerifikasiToko($toko->fresh()));
        } catch (\Throwable $galat) {
            report($galat);
        }
    }

    private function teks(Request $request, string $kunci): string
    {
        $nilai = $request->query($kunci, '');

        return is_string($nilai) ? $nilai : '';
    }
}
