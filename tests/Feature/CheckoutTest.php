<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatDataContoh;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use MembuatDataContoh;
    use RefreshDatabase;

    public function test_checkout_dengan_keranjang_kosong_diarahkan_ke_keranjang(): void
    {
        $this->actingAs($this->buatPembeli())
            ->get(route('checkout'))
            ->assertRedirect(route('keranjang'))
            ->assertSessionHas('galat');
    }

    public function test_halaman_checkout_menampilkan_ringkasan_dan_formulir(): void
    {
        $pembeli = $this->buatPembeli(['name' => 'Siti Rohmah', 'phone' => '6281234567890']);
        $this->isiKeranjang($pembeli, $this->buatProduk($this->buatPenjual()->shop, null, ['nama' => 'Keripik A', 'harga' => 10000]), 2);

        $this->actingAs($pembeli)
            ->get(route('checkout'))
            ->assertOk()
            ->assertSee('Keripik A')
            ->assertSee('Rp 20.000')
            ->assertSee('Siti Rohmah')
            ->assertSee('081234567890')
            ->assertSee('Buat Pesanan');
    }

    public function test_checkout_ditolak_jika_ada_produk_bermasalah(): void
    {
        $pembeli = $this->buatPembeli();
        $produk = $this->buatProduk($this->buatPenjual()->shop, null, ['stok' => 5]);
        $this->isiKeranjang($pembeli, $produk, 3);
        $produk->update(['stok' => 1]);

        $this->actingAs($pembeli)->get(route('checkout'))->assertRedirect(route('keranjang'))->assertSessionHas('galat');
    }

    public function test_pesanan_satu_toko_berhasil_dibuat(): void
    {
        $pembeli = $this->buatPembeli();
        $toko = $this->buatPenjual()->shop;
        $produk = $this->buatProduk($toko, null, ['nama' => 'Keripik A', 'harga' => 10000, 'stok' => 10]);
        $this->isiKeranjang($pembeli, $produk, 3);

        $respons = $this->actingAs($pembeli)->post(route('checkout.store'), $this->dataCheckout());

        $pesanan = Order::first();
        $respons->assertRedirect(route('pesanan.show', $pesanan->id))->assertSessionHas('pesan');

        $this->assertSame(1, Order::count());
        $this->assertSame($pembeli->id, $pesanan->user_id);
        $this->assertSame($toko->id, $pesanan->shop_id);
        $this->assertSame(Order::MENUNGGU_PEMBAYARAN, $pesanan->status);
        $this->assertSame(30000, $pesanan->subtotal);
        $this->assertSame(30000, $pesanan->total);
        $this->assertSame('Siti Rohmah', $pesanan->nama_penerima);
        $this->assertSame('6281234567890', $pesanan->telepon);
        $this->assertMatchesRegularExpression('/^PKB-\d{8}-[A-Z0-9]{5}$/', $pesanan->kode);

        $baris = $pesanan->items()->first();
        $this->assertSame('Keripik A', $baris->nama);
        $this->assertSame(10000, $baris->harga);
        $this->assertSame(3, $baris->jumlah);
        $this->assertSame(30000, $baris->subtotal);
        $this->assertSame($produk->id, $baris->product_id);

        $this->assertSame(7, $produk->fresh()->stok);
        $this->assertSame(0, CartItem::count());
    }

    public function test_keranjang_dari_dua_toko_dipecah_menjadi_dua_pesanan(): void
    {
        $pembeli = $this->buatPembeli();
        $tokoA = $this->buatPenjual()->shop;
        $tokoB = $this->buatPenjual()->shop;
        $produkA = $this->buatProduk($tokoA, null, ['harga' => 10000, 'stok' => 10]);
        $produkB = $this->buatProduk($tokoB, null, ['harga' => 5000, 'stok' => 10]);
        $this->isiKeranjang($pembeli, $produkA, 2);
        $this->isiKeranjang($pembeli, $produkB, 4);

        $this->actingAs($pembeli)
            ->post(route('checkout.store'), $this->dataCheckout())
            ->assertRedirect(route('pesanan'));

        $this->assertSame(2, Order::count());
        $this->assertSame(20000, Order::where('shop_id', $tokoA->id)->first()->total);
        $this->assertSame(20000, Order::where('shop_id', $tokoB->id)->first()->total);
        $this->assertSame(8, $produkA->fresh()->stok);
        $this->assertSame(6, $produkB->fresh()->stok);
        $this->assertSame(0, CartItem::count());
        $this->assertSame(2, Order::distinct('kode')->count('kode'));
    }

    public function test_dua_produk_dari_satu_toko_masuk_satu_pesanan(): void
    {
        $pembeli = $this->buatPembeli();
        $toko = $this->buatPenjual()->shop;
        $this->isiKeranjang($pembeli, $this->buatProduk($toko, null, ['harga' => 10000]), 1);
        $this->isiKeranjang($pembeli, $this->buatProduk($toko, null, ['harga' => 2500]), 2);

        $this->actingAs($pembeli)->post(route('checkout.store'), $this->dataCheckout());

        $this->assertSame(1, Order::count());
        $this->assertSame(15000, Order::first()->total);
        $this->assertSame(2, OrderItem::count());
    }

    public function test_harga_di_pesanan_tidak_berubah_walau_harga_produk_naik(): void
    {
        $pembeli = $this->buatPembeli();
        $produk = $this->buatProduk($this->buatPenjual()->shop, null, ['harga' => 10000]);
        $this->isiKeranjang($pembeli, $produk, 1);

        $this->actingAs($pembeli)->post(route('checkout.store'), $this->dataCheckout());
        $produk->update(['harga' => 99000, 'nama' => 'Nama Diganti']);

        $baris = OrderItem::first();
        $this->assertSame(10000, $baris->harga);
        $this->assertSame('Produk', substr($baris->nama, 0, 6));
    }

    public function test_catatan_boleh_kosong(): void
    {
        $pembeli = $this->buatPembeli();
        $this->isiKeranjang($pembeli, $this->buatProduk($this->buatPenjual()->shop));

        $this->actingAs($pembeli)
            ->post(route('checkout.store'), $this->dataCheckout(['catatan' => '']))
            ->assertSessionHasNoErrors();

        $this->assertNull(Order::first()->catatan);
    }

    public function test_isian_alamat_yang_tidak_valid_ditolak_dan_tidak_mengubah_data(): void
    {
        $pembeli = $this->buatPembeli();
        $produk = $this->buatProduk($this->buatPenjual()->shop, null, ['stok' => 10]);
        $this->isiKeranjang($pembeli, $produk, 2);

        $kasus = [
            'nama_penerima' => ['', str_repeat('a', 101)],
            'telepon' => ['', '12345', 'abcdefghij'],
            'alamat' => ['', 'pendek', str_repeat('a', 501)],
            'catatan' => [str_repeat('a', 301)],
        ];

        foreach ($kasus as $kolom => $nilaiSalah) {
            foreach ($nilaiSalah as $nilai) {
                $this->actingAs($pembeli)
                    ->post(route('checkout.store'), $this->dataCheckout([$kolom => $nilai]))
                    ->assertSessionHasErrors($kolom);
            }
        }

        $this->assertSame(0, Order::count());
        $this->assertSame(10, $produk->fresh()->stok);
        $this->assertSame(1, CartItem::count());
    }

    public function test_stok_yang_berkurang_sebelum_checkout_membatalkan_semuanya(): void
    {
        $pembeli = $this->buatPembeli();
        $aman = $this->buatProduk($this->buatPenjual()->shop, null, ['stok' => 10]);
        $bermasalah = $this->buatProduk($this->buatPenjual()->shop, null, ['stok' => 10]);
        $this->isiKeranjang($pembeli, $aman, 2);
        $this->isiKeranjang($pembeli, $bermasalah, 5);
        $bermasalah->update(['stok' => 1]);

        $this->actingAs($pembeli)
            ->post(route('checkout.store'), $this->dataCheckout())
            ->assertRedirect(route('keranjang'))
            ->assertSessionHas('galat');

        $this->assertSame(0, Order::count());
        $this->assertSame(10, $aman->fresh()->stok);
        $this->assertSame(1, $bermasalah->fresh()->stok);
        $this->assertSame(2, CartItem::count());
    }

    public function test_produk_dinonaktifkan_sebelum_checkout_ditolak(): void
    {
        $pembeli = $this->buatPembeli();
        $produk = $this->buatProduk($this->buatPenjual()->shop);
        $this->isiKeranjang($pembeli, $produk);
        $produk->update(['aktif' => false]);

        $this->actingAs($pembeli)->post(route('checkout.store'), $this->dataCheckout())->assertSessionHas('galat');

        $this->assertSame(0, Order::count());
        $this->assertSame(1, CartItem::count());
    }

    public function test_toko_yang_tidak_lagi_terverifikasi_ditolak(): void
    {
        $pembeli = $this->buatPembeli();
        $penjual = $this->buatPenjual();
        $this->isiKeranjang($pembeli, $this->buatProduk($penjual->shop));
        $penjual->shop->update(['status' => Shop::DITOLAK]);

        $this->actingAs($pembeli)->post(route('checkout.store'), $this->dataCheckout())->assertSessionHas('galat');

        $this->assertSame(0, Order::count());
    }

    public function test_produk_toko_sendiri_di_keranjang_ditolak_saat_checkout(): void
    {
        $penjual = $this->buatPenjual();
        $produk = $this->buatProduk($penjual->shop);
        $this->isiKeranjang($penjual, $produk);

        $this->actingAs($penjual)->post(route('checkout.store'), $this->dataCheckout())->assertSessionHas('galat');

        $this->assertSame(0, Order::count());
        $this->assertSame(10, $produk->fresh()->stok);
    }

    public function test_checkout_dengan_keranjang_kosong_tidak_membuat_pesanan(): void
    {
        $this->actingAs($this->buatPembeli())
            ->post(route('checkout.store'), $this->dataCheckout())
            ->assertRedirect(route('keranjang'))
            ->assertSessionHas('galat');

        $this->assertSame(0, Order::count());
    }

    public function test_checkout_tidak_memakai_keranjang_orang_lain(): void
    {
        $saya = $this->buatPembeli();
        $lain = $this->buatPembeli();
        $produk = $this->buatProduk($this->buatPenjual()->shop);
        $this->isiKeranjang($lain, $produk, 2);

        $this->actingAs($saya)->post(route('checkout.store'), $this->dataCheckout())->assertSessionHas('galat');

        $this->assertSame(0, Order::count());
        $this->assertSame(1, CartItem::count());
    }

    public function test_stok_habis_tepat_setelah_pembelian_terakhir(): void
    {
        $pembeli = $this->buatPembeli();
        $produk = $this->buatProduk($this->buatPenjual()->shop, null, ['stok' => 3]);
        $this->isiKeranjang($pembeli, $produk, 3);

        $this->actingAs($pembeli)->post(route('checkout.store'), $this->dataCheckout());

        $this->assertSame(0, $produk->fresh()->stok);

        $pembeliLain = $this->buatPembeli();
        $this->actingAs($pembeliLain)
            ->post(route('keranjang.tambah'), ['product_id' => $produk->id])
            ->assertSessionHas('galat');
    }

    public function test_checkout_dibatasi_sepuluh_percobaan_per_menit(): void
    {
        $pembeli = $this->buatPembeli();

        foreach (range(1, 10) as $ke) {
            $this->actingAs($pembeli)->post(route('checkout.store'), $this->dataCheckout())->assertRedirect(route('keranjang'));
        }

        $this->actingAs($pembeli)->post(route('checkout.store'), $this->dataCheckout())->assertStatus(429);
    }
}
