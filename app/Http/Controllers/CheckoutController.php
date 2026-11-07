<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckoutGagal;
use App\Rules\NomorHp;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $items = $request->user()->keranjang()
            ->with('product.shop.kelompok')
            ->orderBy('id')
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('keranjang')->with('galat', 'Keranjangmu masih kosong.');
        }

        if ($items->contains(fn ($item) => $item->masalah() !== null)) {
            return redirect()->route('keranjang')->with('galat', 'Periksa dulu produk yang bertanda merah sebelum checkout.');
        }

        return view('checkout.show', [
            'user' => $request->user(),
            'perToko' => $items->groupBy(fn ($item) => $item->product->shop_id),
            'total' => $items->sum(fn ($item) => $item->subtotal),
            'jumlahItem' => $items->sum('jumlah'),
        ]);
    }

    public function store(Request $request, CheckoutService $checkout): RedirectResponse
    {
        $data = $request->validate([
            'nama_penerima' => ['required', 'string', 'max:100'],
            'telepon' => ['bail', 'required', new NomorHp],
            'alamat' => ['required', 'string', 'min:10', 'max:500'],
            'catatan' => ['nullable', 'string', 'max:300'],
        ]);

        try {
            $pesanan = $checkout->buat($request->user(), $data);
        } catch (CheckoutGagal $e) {
            return redirect()->route('keranjang')->with('galat', implode(' ', $e->masalah));
        }

        $tujuan = $pesanan->count() === 1
            ? redirect()->route('pesanan.show', $pesanan->first()->id)
            : redirect()->route('pesanan');

        return $tujuan->with('pesan', $pesanan->count() === 1
            ? 'Pesanan berhasil dibuat.'
            : $pesanan->count().' pesanan berhasil dibuat, satu untuk setiap toko.');
    }
}
