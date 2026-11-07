<?php

namespace App\Services;

use App\Exceptions\CheckoutGagal;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\Telepon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function buat(User $user, array $penerima): Collection
    {
        return DB::transaction(function () use ($user, $penerima) {
            $baris = $user->keranjang()->orderBy('id')->get();

            if ($baris->isEmpty()) {
                throw new CheckoutGagal(['Keranjangmu masih kosong.']);
            }

            $produk = Product::with('shop')
                ->whereIn('id', $baris->pluck('product_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $masalah = [];

            foreach ($baris as $item) {
                $item->setRelation('product', $produk->get($item->product_id));

                if ($pesan = $item->masalah()) {
                    $masalah[] = ($item->product?->nama ?? 'Produk').': '.$pesan;
                }
            }

            if ($masalah !== []) {
                throw new CheckoutGagal($masalah);
            }

            $pesanan = collect();

            foreach ($baris->groupBy(fn ($item) => $item->product->shop_id) as $tokoId => $kelompok) {
                $subtotal = $kelompok->sum(fn ($item) => $item->product->harga * $item->jumlah);

                $order = Order::create([
                    'kode' => Order::buatKode(),
                    'user_id' => $user->id,
                    'shop_id' => $tokoId,
                    'status' => Order::MENUNGGU_PEMBAYARAN,
                    'nama_penerima' => $penerima['nama_penerima'],
                    'telepon' => Telepon::normalisasi($penerima['telepon']),
                    'alamat' => $penerima['alamat'],
                    'catatan' => $penerima['catatan'] ?? null,
                    'subtotal' => $subtotal,
                    'total' => $subtotal,
                ]);

                foreach ($kelompok as $item) {
                    $order->items()->create([
                        'product_id' => $item->product_id,
                        'nama' => $item->product->nama,
                        'harga' => $item->product->harga,
                        'jumlah' => $item->jumlah,
                        'subtotal' => $item->product->harga * $item->jumlah,
                    ]);

                    $item->product->decrement('stok', $item->jumlah);
                }

                $pesanan->push($order);
            }

            $user->keranjang()->delete();

            return $pesanan;
        });
    }
}
