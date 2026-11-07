<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatDataContoh;
use Tests\TestCase;

class KeranjangTest extends TestCase
{
    use MembuatDataContoh;
    use RefreshDatabase;

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->get(route('keranjang'))->assertRedirect(route('login'));
        $this->post(route('keranjang.tambah'), ['product_id' => 1])->assertRedirect(route('login'));
        $this->get(route('checkout'))->assertRedirect(route('login'));
    }

    public function test_email_belum_terverifikasi_diarahkan_ke_verifikasi(): void
    {
        $pembeli = $this->buatPembeli(['email_verified_at' => null]);

        $this->actingAs($pembeli)->get(route('keranjang'))->assertRedirect(route('verification.notice'));
    }

    public function test_keranjang_kosong_menampilkan_ajakan_belanja(): void
    {
        $this->actingAs($this->buatPembeli())
            ->get(route('keranjang'))
            ->assertOk()
            ->assertSee('Keranjangmu masih kosong');
    }

    public function test_produk_berhasil_ditambahkan_dengan_jumlah_awal_satu(): void
    {
        $pembeli = $this->buatPembeli();
        $produk = $this->buatProduk($this->buatPenjual()->shop);

        $this->actingAs($pembeli)
            ->post(route('keranjang.tambah'), ['product_id' => $produk->id])
            ->assertSessionHas('pesan');

        $this->assertSame(1, $pembeli->keranjang()->count());
        $this->assertSame(1, $pembeli->keranjang()->first()->jumlah);
    }

    public function test_produk_ditambahkan_dengan_jumlah_tertentu(): void
    {
        $pembeli = $this->buatPembeli();
        $produk = $this->buatProduk($this->buatPenjual()->shop, null, ['stok' => 10]);

        $this->actingAs($pembeli)->post(route('keranjang.tambah'), ['product_id' => $produk->id, 'jumlah' => 3]);

        $this->assertSame(3, $pembeli->keranjang()->first()->jumlah);
    }

    public function test_menambah_produk_yang_sama_menjumlahkan_tanpa_baris_baru(): void
    {
        $pembeli = $this->buatPembeli();
        $produk = $this->buatProduk($this->buatPenjual()->shop, null, ['stok' => 10]);

        $this->actingAs($pembeli)->post(route('keranjang.tambah'), ['product_id' => $produk->id, 'jumlah' => 2]);
        $this->actingAs($pembeli)->post(route('keranjang.tambah'), ['product_id' => $produk->id, 'jumlah' => 3]);

        $this->assertSame(1, $pembeli->keranjang()->count());
        $this->assertSame(5, $pembeli->keranjang()->first()->jumlah);
    }

    public function test_jumlah_melebihi_stok_ditolak(): void
    {
        $pembeli = $this->buatPembeli();
        $produk = $this->buatProduk($this->buatPenjual()->shop, null, ['stok' => 3]);

        $this->actingAs($pembeli)
            ->post(route('keranjang.tambah'), ['product_id' => $produk->id, 'jumlah' => 4])
            ->assertSessionHas('galat');

        $this->assertSame(0, $pembeli->keranjang()->count());
    }

    public function test_total_di_keranjang_tidak_boleh_melebihi_stok(): void
    {
        $pembeli = $this->buatPembeli();
        $produk = $this->buatProduk($this->buatPenjual()->shop, null, ['stok' => 3]);
        $this->isiKeranjang($pembeli, $produk, 2);

        $this->actingAs($pembeli)
            ->post(route('keranjang.tambah'), ['product_id' => $produk->id, 'jumlah' => 2])
            ->assertSessionHas('galat');

        $this->assertSame(2, $pembeli->keranjang()->first()->jumlah);
    }

    public function test_produk_stok_habis_ditolak(): void
    {
        $pembeli = $this->buatPembeli();
        $produk = $this->buatProduk($this->buatPenjual()->shop, null, ['stok' => 0]);

        $this->actingAs($pembeli)->post(route('keranjang.tambah'), ['product_id' => $produk->id])->assertSessionHas('galat');

        $this->assertSame(0, $pembeli->keranjang()->count());
    }

    public function test_produk_nonaktif_atau_toko_belum_terverifikasi_ditolak(): void
    {
        $pembeli = $this->buatPembeli();
        $nonaktif = $this->buatProduk($this->buatPenjual()->shop, null, ['aktif' => false]);
        $tokoMenunggu = $this->buatProduk($this->buatPenjual(Shop::MENUNGGU)->shop);

        $this->actingAs($pembeli)->post(route('keranjang.tambah'), ['product_id' => $nonaktif->id])->assertSessionHas('galat');
        $this->actingAs($pembeli)->post(route('keranjang.tambah'), ['product_id' => $tokoMenunggu->id])->assertSessionHas('galat');

        $this->assertSame(0, $pembeli->keranjang()->count());
    }

    public function test_produk_yang_tidak_ada_ditolak(): void
    {
        $pembeli = $this->buatPembeli();

        $this->actingAs($pembeli)->post(route('keranjang.tambah'), ['product_id' => 9999])->assertSessionHas('galat');

        $this->assertSame(0, $pembeli->keranjang()->count());
    }

    public function test_penjual_tidak_bisa_membeli_produk_tokonya_sendiri(): void
    {
        $penjual = $this->buatPenjual();
        $produk = $this->buatProduk($penjual->shop);

        $this->actingAs($penjual)->post(route('keranjang.tambah'), ['product_id' => $produk->id])->assertSessionHas('galat');

        $this->assertSame(0, $penjual->keranjang()->count());
    }

    public function test_penjual_boleh_membeli_produk_toko_lain(): void
    {
        $penjual = $this->buatPenjual();
        $produkLain = $this->buatProduk($this->buatPenjual()->shop);

        $this->actingAs($penjual)->post(route('keranjang.tambah'), ['product_id' => $produkLain->id])->assertSessionHas('pesan');

        $this->assertSame(1, $penjual->keranjang()->count());
    }

    public function test_jumlah_tidak_valid_ditolak(): void
    {
        $pembeli = $this->buatPembeli();
        $produk = $this->buatProduk($this->buatPenjual()->shop);

        foreach ([0, -2, 'banyak', 1000] as $jumlah) {
            $this->actingAs($pembeli)
                ->post(route('keranjang.tambah'), ['product_id' => $produk->id, 'jumlah' => $jumlah])
                ->assertSessionHasErrors('jumlah');
        }

        $this->assertSame(0, $pembeli->keranjang()->count());
    }

    public function test_produk_wajib_dipilih(): void
    {
        $this->actingAs($this->buatPembeli())
            ->post(route('keranjang.tambah'), [])
            ->assertSessionHasErrors('product_id');
    }

    public function test_keranjang_dikelompokkan_per_toko_dengan_total_benar(): void
    {
        $pembeli = $this->buatPembeli();
        $tokoA = $this->buatPenjual()->shop;
        $tokoB = $this->buatPenjual()->shop;
        $this->isiKeranjang($pembeli, $this->buatProduk($tokoA, null, ['nama' => 'Keripik A', 'harga' => 10000]), 2);
        $this->isiKeranjang($pembeli, $this->buatProduk($tokoB, null, ['nama' => 'Dodol B', 'harga' => 5000]), 3);

        $this->actingAs($pembeli)
            ->get(route('keranjang'))
            ->assertOk()
            ->assertSee('Keripik A')
            ->assertSee('Dodol B')
            ->assertSee($tokoA->nama)
            ->assertSee($tokoB->nama)
            ->assertSee('Rp 35.000')
            ->assertViewHas('total', 35000)
            ->assertViewHas('adaMasalah', false);
    }

    public function test_keranjang_hanya_menampilkan_milik_sendiri(): void
    {
        $saya = $this->buatPembeli();
        $lain = $this->buatPembeli();
        $toko = $this->buatPenjual()->shop;
        $this->isiKeranjang($saya, $this->buatProduk($toko, null, ['nama' => 'Punya Saya']));
        $this->isiKeranjang($lain, $this->buatProduk($toko, null, ['nama' => 'Punya Orang Lain']));

        $this->actingAs($saya)
            ->get(route('keranjang'))
            ->assertSee('Punya Saya')
            ->assertDontSee('Punya Orang Lain');
    }

    public function test_jumlah_berhasil_diubah(): void
    {
        $pembeli = $this->buatPembeli();
        $produk = $this->buatProduk($this->buatPenjual()->shop, null, ['stok' => 10]);
        $item = $this->isiKeranjang($pembeli, $produk, 2);

        $this->actingAs($pembeli)->patch(route('keranjang.ubah', $item->id), ['jumlah' => 5])->assertRedirect(route('keranjang'));

        $this->assertSame(5, $item->fresh()->jumlah);
    }

    public function test_ubah_jumlah_melebihi_stok_ditolak(): void
    {
        $pembeli = $this->buatPembeli();
        $produk = $this->buatProduk($this->buatPenjual()->shop, null, ['stok' => 4]);
        $item = $this->isiKeranjang($pembeli, $produk, 2);

        $this->actingAs($pembeli)->patch(route('keranjang.ubah', $item->id), ['jumlah' => 5])->assertSessionHas('galat');

        $this->assertSame(2, $item->fresh()->jumlah);
    }

    public function test_ubah_jumlah_kurang_dari_satu_atau_bukan_angka_ditolak(): void
    {
        $pembeli = $this->buatPembeli();
        $item = $this->isiKeranjang($pembeli, $this->buatProduk($this->buatPenjual()->shop), 2);

        $this->actingAs($pembeli)->patch(route('keranjang.ubah', $item->id), ['jumlah' => 0])->assertSessionHasErrors('jumlah');
        $this->actingAs($pembeli)->patch(route('keranjang.ubah', $item->id), ['jumlah' => 'abc'])->assertSessionHasErrors('jumlah');
        $this->actingAs($pembeli)->patch(route('keranjang.ubah', $item->id), [])->assertSessionHasErrors('jumlah');

        $this->assertSame(2, $item->fresh()->jumlah);
    }

    public function test_item_orang_lain_tidak_bisa_diubah_atau_dihapus(): void
    {
        $saya = $this->buatPembeli();
        $lain = $this->buatPembeli();
        $itemLain = $this->isiKeranjang($lain, $this->buatProduk($this->buatPenjual()->shop), 2);

        $this->actingAs($saya)->patch(route('keranjang.ubah', $itemLain->id), ['jumlah' => 5])->assertNotFound();
        $this->actingAs($saya)->delete(route('keranjang.hapus', $itemLain->id))->assertNotFound();

        $this->assertSame(2, $itemLain->fresh()->jumlah);
    }

    public function test_item_berhasil_dihapus(): void
    {
        $pembeli = $this->buatPembeli();
        $item = $this->isiKeranjang($pembeli, $this->buatProduk($this->buatPenjual()->shop));

        $this->actingAs($pembeli)->delete(route('keranjang.hapus', $item->id))->assertRedirect(route('keranjang'));

        $this->assertSame(0, CartItem::count());
    }

    public function test_produk_yang_stoknya_berkurang_ditandai_dan_checkout_dikunci(): void
    {
        $pembeli = $this->buatPembeli();
        $produk = $this->buatProduk($this->buatPenjual()->shop, null, ['stok' => 10]);
        $this->isiKeranjang($pembeli, $produk, 5);
        $produk->update(['stok' => 2]);

        $this->actingAs($pembeli)
            ->get(route('keranjang'))
            ->assertOk()
            ->assertSee('Stok tersisa 2.')
            ->assertViewHas('adaMasalah', true);
    }

    public function test_produk_yang_dinonaktifkan_penjual_ditandai_di_keranjang(): void
    {
        $pembeli = $this->buatPembeli();
        $produk = $this->buatProduk($this->buatPenjual()->shop);
        $this->isiKeranjang($pembeli, $produk);
        $produk->update(['aktif' => false]);

        $this->actingAs($pembeli)
            ->get(route('keranjang'))
            ->assertSee('Produk ini tidak lagi tersedia.')
            ->assertViewHas('adaMasalah', true);
    }

    public function test_kartu_produk_di_katalog_punya_tombol_tambah_ke_keranjang(): void
    {
        $this->buatProduk($this->buatPenjual()->shop, null, ['nama' => 'Sambal Roa']);

        $this->actingAs($this->buatPembeli())
            ->get(route('katalog'))
            ->assertSee('Sambal Roa')
            ->assertSee(route('keranjang.tambah'), false);
    }

    public function test_kartu_produk_milik_sendiri_tidak_punya_tombol_tambah(): void
    {
        $penjual = $this->buatPenjual();
        $this->buatProduk($penjual->shop, null, ['nama' => 'Sambal Roa']);

        $this->actingAs($penjual)
            ->get(route('katalog'))
            ->assertSee('Produk Anda')
            ->assertDontSee('name="product_id"', false);
    }

    public function test_tamu_melihat_ajakan_masuk_di_kartu_produk(): void
    {
        $produk = $this->buatProduk($this->buatPenjual()->shop, null, ['nama' => 'Sambal Roa']);

        $this->get(route('katalog'))->assertSee('Masuk untuk membeli');
        $this->get(route('produk.show', $produk->id))->assertSee('Masuk untuk membeli');
    }

    public function test_detail_produk_punya_formulir_keranjang_untuk_pembeli(): void
    {
        $produk = $this->buatProduk($this->buatPenjual()->shop);

        $this->actingAs($this->buatPembeli())
            ->get(route('produk.show', $produk->id))
            ->assertOk()
            ->assertSee('id="form-keranjang"', false)
            ->assertSee('+ Keranjang');
    }

    public function test_ikon_keranjang_menampilkan_jumlah_produk(): void
    {
        $pembeli = $this->buatPembeli();
        $toko = $this->buatPenjual()->shop;
        $this->isiKeranjang($pembeli, $this->buatProduk($toko));
        $this->isiKeranjang($pembeli, $this->buatProduk($toko));

        $this->actingAs($pembeli)
            ->get(route('beranda'))
            ->assertSee('Keranjang belanja (2 produk)', false);
    }
}
