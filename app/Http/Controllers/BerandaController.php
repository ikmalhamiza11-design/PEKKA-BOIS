<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Kelompok;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\View\View;

class BerandaController extends Controller
{
    public function __invoke(): View
    {
        $kategori = Category::orderBy('urutan')->get();

        $produk = Product::tampil()
            ->with(['shop.kelompok', 'category'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->latest()
            ->take(8)
            ->get();

        $unggulan = Product::tampil()
            ->where('unggulan', true)
            ->with(['shop.kelompok', 'category'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->latest()
            ->take(6)
            ->get();

        $statistik = [
            ['nilai' => Kelompok::count(), 'label' => 'Kelompok PEKKA'],
            ['nilai' => Shop::where('status', Shop::TERVERIFIKASI)->count(), 'label' => 'Usaha terverifikasi'],
            ['nilai' => $kategori->count(), 'label' => 'Kategori usaha'],
        ];

        return view('beranda', [
            'kategori' => $kategori,
            'produk' => $produk,
            'unggulan' => $unggulan,
            'sorotan' => $unggulan->first(),
            'statistik' => $statistik,
        ]);
    }
}
