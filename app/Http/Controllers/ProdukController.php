<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use App\Services\UlasanService;
use Illuminate\View\View;

class ProdukController extends Controller
{
    public function show(int $id, UlasanService $layananUlasan): View
    {
        $produk = Product::tampil()
            ->with(['shop.kelompok', 'category'])
            ->findOrFail($id);

        $lainnya = Product::tampil()
            ->with(['shop.kelompok', 'category'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('shop_id', $produk->shop_id)
            ->whereKeyNot($produk->id)
            ->latest()
            ->take(4)
            ->get();

        $ulasan = Review::tampil()->where('product_id', $produk->id)
            ->with('user')
            ->latest('id')
            ->paginate(5, ['*'], 'ulasan')
            ->fragment('ulasan');

        return view('produk.show', [
            'produk' => $produk,
            'lainnya' => $lainnya,
            'ulasan' => $ulasan,
            'ringkasan' => $layananUlasan->ringkasan(Review::tampil()->where('product_id', $produk->id)),
            'ringkasanToko' => $layananUlasan->ringkasan(Review::tampil()->where('shop_id', $produk->shop_id)),
        ]);
    }
}
