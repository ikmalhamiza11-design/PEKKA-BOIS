<?php

namespace Tests\Concerns;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;

trait MembuatUlasan
{
    use MembuatPesananBayar;

    protected function buatPesananSelesai(?User $pembeli = null): array
    {
        $pembeli ??= $this->buatPembeli(['name' => 'Siti Rohmah']);
        $pesanan = $this->buatPesanan($pembeli);
        $pesanan->update(['status' => Order::SELESAI, 'selesai_at' => now()]);

        return [$pembeli, $pesanan->fresh(), $pesanan->items()->first()];
    }

    protected function buatUlasan(Product $produk, ?User $pembeli = null, int $rating = 5, array $atribut = []): Review
    {
        $pembeli ??= $this->buatPembeli(['name' => 'Siti Rohmah']);

        $pesanan = Order::create([
            'kode' => Order::buatKode(),
            'user_id' => $pembeli->id,
            'shop_id' => $produk->shop_id,
            'status' => Order::SELESAI,
            'nama_penerima' => $pembeli->name,
            'telepon' => '6281234567890',
            'alamat' => 'Jl. Raya Bogor No. 10, Cibinong',
            'subtotal' => $produk->harga,
            'total' => $produk->harga,
            'selesai_at' => now(),
        ]);

        $item = $pesanan->items()->create([
            'product_id' => $produk->id,
            'nama' => $produk->nama,
            'harga' => $produk->harga,
            'jumlah' => 1,
            'subtotal' => $produk->harga,
        ]);

        return Review::create(array_merge([
            'order_item_id' => $item->id,
            'user_id' => $pembeli->id,
            'product_id' => $produk->id,
            'shop_id' => $produk->shop_id,
            'rating' => $rating,
            'komentar' => 'Enak sekali',
        ], $atribut));
    }

    protected function dataUlasan(array $atribut = []): array
    {
        return array_merge(['rating' => '5', 'komentar' => 'Rasanya enak dan pengemasannya rapi.'], $atribut);
    }
}
