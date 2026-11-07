<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PesananController extends Controller
{
    public function index(Request $request): View
    {
        return view('pesanan.index', [
            'pesanan' => $request->user()->orders()->with(['shop', 'items'])->latest('id')->paginate(10),
        ]);
    }

    public function show(Request $request, int $id): View
    {
        return view('pesanan.show', [
            'pesanan' => $request->user()->orders()->with(['shop.kelompok', 'items'])->findOrFail($id),
        ]);
    }

    public function batal(Request $request, int $id): RedirectResponse
    {
        $request->user()->orders()->findOrFail($id);

        $berhasil = DB::transaction(function () use ($request, $id) {
            $pesanan = Order::with('items')
                ->where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->findOrFail($id);

            if (! $pesanan->bisaDibatalkan()) {
                return false;
            }

            foreach ($pesanan->items as $item) {
                if ($item->product_id) {
                    Product::whereKey($item->product_id)->increment('stok', $item->jumlah);
                }
            }

            $pesanan->update(['status' => Order::DIBATALKAN]);

            return true;
        });

        if (! $berhasil) {
            return redirect()->route('pesanan.show', $id)->with('galat', 'Pesanan ini sudah tidak bisa dibatalkan.');
        }

        return redirect()->route('pesanan.show', $id)->with('pesan', 'Pesanan dibatalkan dan stok dikembalikan.');
    }
}
