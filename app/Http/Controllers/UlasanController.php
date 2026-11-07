<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UlasanController extends Controller
{
    public function form(Request $request, int $id, int $itemId): View|RedirectResponse
    {
        [$pesanan, $item] = $this->cari($request, $id, $itemId);

        if ($tolak = $this->tolak($pesanan, $item)) {
            return $tolak;
        }

        return view('ulasan.form', [
            'pesanan' => $pesanan,
            'item' => $item,
            'ulasan' => $item->review,
        ]);
    }

    public function simpan(Request $request, int $id, int $itemId): RedirectResponse
    {
        [$pesanan, $item] = $this->cari($request, $id, $itemId);

        if ($tolak = $this->tolak($pesanan, $item)) {
            return $tolak;
        }

        $data = $request->validate([
            'rating' => ['required', 'in:1,2,3,4,5'],
            'komentar' => ['nullable', 'string', 'max:1000'],
        ]);

        $baru = ! $item->review;

        Review::updateOrCreate(
            ['order_item_id' => $item->id],
            [
                'user_id' => $request->user()->id,
                'product_id' => $item->product_id,
                'shop_id' => $pesanan->shop_id,
                'rating' => (int) $data['rating'],
                'komentar' => $data['komentar'] ?? null,
            ]
        );

        return redirect()->route('pesanan.show', $pesanan->id)
            ->with('pesan', $baru ? 'Terima kasih! Ulasanmu sudah terkirim.' : 'Ulasanmu sudah diperbarui.');
    }

    protected function cari(Request $request, int $id, int $itemId): array
    {
        $pesanan = $request->user()->orders()->with('items.review')->findOrFail($id);
        $item = $pesanan->items->firstWhere('id', $itemId);

        abort_if(! $item, 404);

        return [$pesanan, $item];
    }

    protected function tolak(Order $pesanan, OrderItem $item): ?RedirectResponse
    {
        if ($pesanan->status !== Order::SELESAI) {
            return redirect()->route('pesanan.show', $pesanan->id)
                ->with('galat', 'Ulasan baru bisa ditulis setelah pesanan selesai.');
        }

        if (! $item->product_id) {
            return redirect()->route('pesanan.show', $pesanan->id)
                ->with('galat', 'Produk ini sudah tidak tersedia untuk diulas.');
        }

        return null;
    }
}
