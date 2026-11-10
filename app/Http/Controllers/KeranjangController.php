<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KeranjangController extends Controller
{
    public function index(Request $request): View
    {
        $items = $request->user()->keranjang()
            ->with('product.shop.kelompok')
            ->orderBy('id')
            ->get();

        $masalah = $items->contains(fn ($item) => $item->masalah() !== null);

        return view('keranjang.index', [
            'items' => $items,
            'perToko' => $items->groupBy(fn ($item) => $item->product->shop_id),
            'total' => $items->sum(fn ($item) => $item->subtotal),
            'adaMasalah' => $masalah,
        ]);
    }

    public function tambah(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'jumlah' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $user = $request->user();
        $jumlah = (int) ($data['jumlah'] ?? 1);

        $produk = Product::tampil()->with('shop')->find($data['product_id']);

        if (! $produk) {
            return back()->with('galat', 'Produk ini tidak tersedia.');
        }

        if ($produk->shop->user_id === $user->id) {
            return back()->with('galat', 'Kamu tidak bisa membeli produk dari tokomu sendiri.');
        }

        if ($kendala = $produk->kendalaToko()) {
            return back()->with('galat', $kendala);
        }

        if ($produk->stok < 1) {
            return back()->with('galat', 'Stok produk ini sedang habis.');
        }

        $sudahAda = (int) $user->keranjang()->where('product_id', $produk->id)->value('jumlah');
        $total = $sudahAda + $jumlah;

        if ($total > $produk->stok) {
            $pesan = $sudahAda > 0
                ? 'Stok tersisa '.$produk->stok.' dan kamu sudah punya '.$sudahAda.' di keranjang.'
                : 'Stok tersisa '.$produk->stok.'.';

            return back()->with('galat', $pesan);
        }

        $user->keranjang()->updateOrCreate(
            ['product_id' => $produk->id],
            ['jumlah' => $total]
        );

        return back()->with('pesan', '"'.$produk->nama.'" masuk ke keranjang.');
    }

    public function ubah(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'jumlah' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $item = $request->user()->keranjang()->with('product')->findOrFail($id);

        if ($data['jumlah'] > $item->product->stok) {
            return redirect()->route('keranjang')->with('galat', 'Stok "'.$item->product->nama.'" tersisa '.$item->product->stok.'.');
        }

        $item->update(['jumlah' => $data['jumlah']]);

        return redirect()->route('keranjang');
    }

    public function hapus(Request $request, int $id): RedirectResponse
    {
        $request->user()->keranjang()->findOrFail($id)->delete();

        return redirect()->route('keranjang')->with('pesan', 'Produk dihapus dari keranjang.');
    }
}
