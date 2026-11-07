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
                if ($item->product_id) {
                    Product::whereKey($item->product_id)->increment('stok', $item->jumlah);
                }
            }

            $pesanan->update(['status' => Order::DIBATALKAN]);

            return true;
        });
    }
}
