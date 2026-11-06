<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ProdukController extends Controller
{
    public function show(int $id): View
    {
        $produk = Product::tampil()
            ->with(['shop.kelompok', 'category'])
            ->findOrFail($id);

        $lainnya = Product::tampil()
            ->with(['shop.kelompok', 'category'])
            ->where('shop_id', $produk->shop_id)
            ->whereKeyNot($produk->id)
            ->latest()
            ->take(4)
            ->get();

        return view('produk.show', [
            'produk' => $produk,
            'lainnya' => $lainnya,
        ]);
    }
}
