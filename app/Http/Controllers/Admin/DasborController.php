<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelompok;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DasborController extends Controller
{
    public function __invoke(Request $request): View
    {
        $admin = $request->user();
        $idToko = $admin->cakupanToko()->select('shops.id');

        $statistik = [
            'menunggu' => $admin->cakupanToko()->where('status', Shop::MENUNGGU)->count(),
            'terverifikasi' => $admin->cakupanToko()->where('status', Shop::TERVERIFIKASI)->count(),
            'ditolak' => $admin->cakupanToko()->where('status', Shop::DITOLAK)->count(),
            'produk' => Product::whereIn('shop_id', $idToko)->count(),
            'pesanan' => Order::whereIn('shop_id', $idToko)->count(),
            'ulasan' => $admin->cakupanUlasan()->tampil()->count(),
            'ulasan_disembunyikan' => $admin->cakupanUlasan()->disembunyikan()->count(),
        ];

        $statistikDinas = $admin->adalahAdminDinas() ? [
            'kelompok' => Kelompok::count(),
            'penjual' => User::where('role', User::PENJUAL)->count(),
            'pembeli' => User::where('role', User::PEMBELI)->count(),
            'admin_kelompok' => User::where('role', User::ADMIN_KELOMPOK)->count(),
        ] : null;

        return view('admin.dasbor', [
            'admin' => $admin->load('kelompok'),
            'statistik' => $statistik,
            'statistikDinas' => $statistikDinas,
            'antrean' => $admin->cakupanToko()
                ->with(['user', 'kelompok'])
                ->where('status', Shop::MENUNGGU)
                ->oldest('id')
                ->take(5)
                ->get(),
        ]);
    }
}
