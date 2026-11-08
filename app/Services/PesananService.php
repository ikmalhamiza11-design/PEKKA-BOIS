<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class PesananService
{
    public function batalkan(int $id, ?int $userId = null): bool
    {
        return DB::transaction(function () use ($id, $userId) {
            $pesanan = Order::with('items')
                ->when($userId, fn ($query) => $query->where('user_id', $userId))
                ->lockForUpdate()
                ->findOrFail($id);

            if (! $pesanan->bisaDibatalkan()) {
                return false;
            }

            foreach ($pesanan->items as $item) {
                $produk = $item->product_id ? Product::lockForUpdate()->find($item->product_id) : null;

                if ($produk) {
                    $stokBaru = $produk->stok + $item->jumlah;

                    // Stok harian tidak boleh melebihi jatah hari itu saat pesanan lama dibatalkan.
                    if ($produk->stok_harian !== null && ! $produk->preOrder()) {
                        $stokBaru = max($produk->stok, min($stokBaru, $produk->stok_harian));
                    }

                    $produk->update(['stok' => $stokBaru]);
                }
            }

            $pesanan->update(['status' => Order::DIBATALKAN]);

            return true;
        });
    }
}
