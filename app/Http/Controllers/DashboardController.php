<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $toko = $user->shop;

        $ringkasan = $toko ? [
            'total' => $toko->products()->count(),
            'aktif' => $toko->products()->where('aktif', true)->count(),
            'habis' => $toko->products()->where('stok', '<', 1)->count(),
        ] : null;

        return view('dashboard', [
            'user' => $user,
            'shop' => $toko?->load('kelompok'),
            'ringkasan' => $ringkasan,
            'perluDiproses' => $toko ? $toko->orders()->whereIn('status', [Order::DIBAYAR, Order::DIPROSES])->count() : 0,
            'perluVerifikasi' => $toko ? $toko->orders()->where('status', Order::MENUNGGU_VERIFIKASI)->count() : 0,
        ]);
    }
}
