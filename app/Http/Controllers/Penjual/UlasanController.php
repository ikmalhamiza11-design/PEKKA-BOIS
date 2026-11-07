<?php

namespace App\Http\Controllers\Penjual;

use App\Http\Controllers\Controller;
use App\Services\UlasanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UlasanController extends Controller
{
    public function index(Request $request, UlasanService $layanan): View
    {
        $toko = $request->user()->shop;
        $filter = $request->query('filter') === 'belum_dibalas' ? 'belum_dibalas' : '';

        $ulasan = $toko->reviews()
            ->with(['user', 'orderItem'])
            ->when($filter === 'belum_dibalas', fn ($query) => $query->whereNull('balasan'))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('penjual.ulasan.index', [
            'ulasan' => $ulasan,
            'filter' => $filter,
            'ringkasan' => $layanan->ringkasan($toko->reviews()->getQuery()),
            'belumDibalas' => $toko->reviews()->whereNull('balasan')->count(),
        ]);
    }

    public function balas(Request $request, int $id): RedirectResponse
    {
        $ulasan = $request->user()->shop->reviews()->findOrFail($id);

        $data = $request->validate([
            'balasan' => ['required', 'string', 'max:500'],
        ]);

        $ulasan->update([
            'balasan' => $data['balasan'],
            'dibalas_at' => now(),
        ]);

        return redirect()->back(fallback: route('penjual.ulasan.index'))->with('pesan', 'Balasanmu sudah tampil di halaman produk.');
    }
}
