<?php

namespace Tests\Concerns;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Kelompok;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Str;

trait MembuatDataContoh
{
    protected function buatKelompok(array $atribut = []): Kelompok
    {
        return Kelompok::create(array_merge([
            'nama' => 'Kelompok Uji',
            'desa' => 'Desa Uji',
            'kecamatan' => 'Kecamatan Uji',
        ], $atribut));
    }

    protected function buatKategori(string $nama = 'Kuliner'): Category
    {
        return Category::firstOrCreate(
            ['slug' => Str::slug($nama)],
            ['nama' => $nama, 'urutan' => 1]
        );
    }

    protected function buatPenjual(string $status = Shop::TERVERIFIKASI, array $atribut = []): User
    {
        $user = User::factory()->create($atribut);
        $user->role = User::PENJUAL;
        $user->save();

        $kelompok = Kelompok::first() ?? $this->buatKelompok();

        $user->shop()->create([
            'nama' => 'Toko '.Str::random(5),
            'kelompok_id' => $kelompok->id,
            'status' => $status,
        ]);

        return $user->fresh('shop');
    }

    protected function buatPembeli(array $atribut = []): User
    {
        return User::factory()->create($atribut);
    }

    protected function buatProduk(Shop $toko, ?Category $kategori = null, array $atribut = []): Product
    {
        return Product::create(array_merge([
            'shop_id' => $toko->id,
            'category_id' => ($kategori ?? $this->buatKategori())->id,
            'nama' => 'Produk '.Str::random(5),
            'deskripsi' => 'Deskripsi contoh',
            'harga' => 15000,
            'stok' => 10,
            'aktif' => true,
        ], $atribut));
    }

    protected function dataProduk(array $atribut = []): array
    {
        return array_merge([
            'nama' => 'Keripik Singkong',
            'category_id' => $this->buatKategori()->id,
            'deskripsi' => 'Renyah dan gurih.',
            'harga' => '15000',
            'stok' => '20',
            'aktif' => '1',
        ], $atribut);
    }

    protected function isiKeranjang(User $pembeli, Product $produk, int $jumlah = 1): CartItem
    {
        return $pembeli->keranjang()->create([
            'product_id' => $produk->id,
            'jumlah' => $jumlah,
        ]);
    }

    protected function dataCheckout(array $atribut = []): array
    {
        return array_merge([
            'nama_penerima' => 'Siti Rohmah',
            'telepon' => '0812-3456-7890',
            'alamat' => 'Jl. Raya Bogor No. 10, Cibinong',
            'catatan' => 'Tolong dibungkus rapi.',
        ], $atribut);
    }
}
