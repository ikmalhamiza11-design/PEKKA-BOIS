<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\MembuatDataContoh;
use Tests\TestCase;

class KetersediaanTokoTest extends TestCase
{
    use MembuatDataContoh;
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /** Waktu uji dalam WIB, disimpan sebagai UTC seperti pengaturan aplikasi. */
    private function sekarangWib(string $waktu): void
    {
        Carbon::setTestNow(Carbon::parse($waktu, 'Asia/Jakarta')->utc());
    }

    private function jadwal(Shop $toko, string $buka = '08:00', string $tutup = '17:00', string $hari = '1,2,3,4,5'): void
    {
        $toko->update(['jadwal_otomatis' => true, 'jam_buka' => $buka, 'jam_tutup' => $tutup, 'hari_buka' => $hari]);
    }

    public function test_toko_libur_tidak_bisa_dipesan_tapi_produk_tetap_tampil(): void
    {
        $penjual = $this->buatPenjual();
        $penjual->shop->update(['libur' => true]);
        $produk = $this->buatProduk($penjual->shop, null, ['nama' => 'Keripik Libur']);
        $pembeli = $this->buatPembeli();

        $this->actingAs($pembeli)->post(route('keranjang.tambah'), ['product_id' => $produk->id])
            ->assertSessionHas('galat', 'Toko sedang libur.');
        $this->assertSame(0, CartItem::count());

        $this->get(route('katalog'))->assertOk()->assertSee('Keripik Libur')->assertSee('Toko libur');
        $this->get(route('produk.show', $produk->id))->assertOk()->assertSee('Toko sedang libur.');
    }

    public function test_jadwal_otomatis_membuka_dan_menutup_toko_sesuai_waktu_wib(): void
    {
        $penjual = $this->buatPenjual();
        $this->jadwal($penjual->shop);
        $produk = $this->buatProduk($penjual->shop);
        $pembeli = $this->buatPembeli();

        // Senin 10.00 WIB: buka. (Senin = 9 November 2026)
        $this->sekarangWib('2026-11-09 10:00');
        $this->actingAs($pembeli)->post(route('keranjang.tambah'), ['product_id' => $produk->id])
            ->assertSessionHas('pesan');

        // Senin 20.00 WIB: tutup.
        $this->sekarangWib('2026-11-09 20:00');
        $this->assertFalse($penjual->shop->fresh()->bukaSekarang());
        $this->assertStringContainsString('Toko sedang tutup', $produk->fresh('shop')->kendalaToko());

        // Minggu 10.00 WIB: bukan hari buka.
        $this->sekarangWib('2026-11-08 10:00');
        $this->assertFalse($penjual->shop->fresh()->bukaSekarang());
    }

    public function test_zona_waktu_dihitung_wib_bukan_utc(): void
    {
        $penjual = $this->buatPenjual();
        $this->jadwal($penjual->shop, '08:00', '17:00', '1,2,3,4,5,6,7');

        // 01.00 UTC = 08.00 WIB, tepat jam buka.
        Carbon::setTestNow(Carbon::parse('2026-11-09 01:00:00', 'UTC'));
        $this->assertTrue($penjual->shop->fresh()->bukaSekarang());

        // 00.59 UTC = 07.59 WIB, belum buka.
        Carbon::setTestNow(Carbon::parse('2026-11-09 00:59:00', 'UTC'));
        $this->assertFalse($penjual->shop->fresh()->bukaSekarang());

        // 10.00 UTC = 17.00 WIB, sudah tutup.
        Carbon::setTestNow(Carbon::parse('2026-11-09 10:00:00', 'UTC'));
        $this->assertFalse($penjual->shop->fresh()->bukaSekarang());
    }

    public function test_pre_order_masih_bisa_dipesan_di_luar_jam_buka_tapi_tidak_saat_libur(): void
    {
        $penjual = $this->buatPenjual();
        $this->jadwal($penjual->shop);
        $biasa = $this->buatProduk($penjual->shop, null, ['nama' => 'Biasa']);
        $po = $this->buatProduk($penjual->shop, null, ['nama' => 'Kue PO', 'pre_order' => true, 'pre_order_hari' => 2]);
        $pembeli = $this->buatPembeli();

        $this->sekarangWib('2026-11-09 22:00');

        $this->actingAs($pembeli)->post(route('keranjang.tambah'), ['product_id' => $biasa->id])->assertSessionHas('galat');
        $this->actingAs($pembeli)->post(route('keranjang.tambah'), ['product_id' => $po->id])->assertSessionHas('pesan');

        $penjual->shop->update(['libur' => true]);

        $this->assertNotNull($po->fresh('shop')->kendalaToko());
    }

    public function test_checkout_ditolak_kalau_toko_libur_setelah_produk_masuk_keranjang(): void
    {
        $penjual = $this->buatPenjual();
        $produk = $this->buatProduk($penjual->shop);
        $pembeli = $this->buatPembeli();
        $this->isiKeranjang($pembeli, $produk);

        $penjual->shop->update(['libur' => true]);

        $this->actingAs($pembeli)->post(route('checkout.store'), $this->dataCheckout());

        $this->assertSame(0, Order::count());
        $this->assertSame(10, $produk->fresh()->stok);
    }

    public function test_checkout_menyimpan_lama_pre_order_di_pesanan(): void
    {
        $penjual = $this->buatPenjual();
        $po = $this->buatProduk($penjual->shop, null, ['pre_order' => true, 'pre_order_hari' => 3]);
        $biasa = $this->buatProduk($penjual->shop);
        $pembeli = $this->buatPembeli();
        $this->isiKeranjang($pembeli, $po);
        $this->isiKeranjang($pembeli, $biasa);

        $this->actingAs($pembeli)->post(route('checkout.store'), $this->dataCheckout());

        $pesanan = Order::with('items')->firstOrFail();
        $this->assertSame(3, $pesanan->items->firstWhere('product_id', $po->id)->pre_order_hari);
        $this->assertNull($pesanan->items->firstWhere('product_id', $biasa->id)->pre_order_hari);

        $this->actingAs($pembeli)->get(route('pesanan.show', $pesanan->id))
            ->assertOk()->assertSee('Pre-order, siap sekitar 3 hari');
    }

    public function test_penjual_mengatur_libur_dan_jadwal(): void
    {
        $penjual = $this->buatPenjual();

        $this->actingAs($penjual)->get(route('penjual.ketersediaan.edit'))->assertOk();

        $this->actingAs($penjual)->put(route('penjual.ketersediaan.update'), [
            'libur' => '1',
            'jadwal_otomatis' => '1',
            'jam_buka' => '07:30',
            'jam_tutup' => '16:00',
            'hari_buka' => ['1', '2', '3'],
        ])->assertRedirect(route('penjual.ketersediaan.edit'))->assertSessionHas('pesan');

        $toko = $penjual->shop->fresh();
        $this->assertTrue($toko->libur);
        $this->assertTrue($toko->jadwal_otomatis);
        $this->assertSame('07:30', $toko->jam_buka);
        $this->assertSame('16:00', $toko->jam_tutup);
        $this->assertSame([1, 2, 3], $toko->hariBukaDaftar());
    }

    public function test_jadwal_ditolak_kalau_jam_tutup_sebelum_jam_buka_atau_tanpa_hari(): void
    {
        $penjual = $this->buatPenjual();

        $this->actingAs($penjual)->put(route('penjual.ketersediaan.update'), [
            'jadwal_otomatis' => '1', 'jam_buka' => '17:00', 'jam_tutup' => '08:00', 'hari_buka' => ['1'],
        ])->assertSessionHasErrors('jam_tutup');

        $this->actingAs($penjual)->put(route('penjual.ketersediaan.update'), [
            'jadwal_otomatis' => '1', 'jam_buka' => '08:00', 'jam_tutup' => '17:00',
        ])->assertSessionHasErrors('hari_buka');

        $this->assertFalse($penjual->shop->fresh()->jadwal_otomatis);
    }

    public function test_mematikan_jadwal_menghapus_jam_dan_hari(): void
    {
        $penjual = $this->buatPenjual();
        $this->jadwal($penjual->shop);

        $this->actingAs($penjual)->put(route('penjual.ketersediaan.update'), ['jadwal_otomatis' => '0'])
            ->assertSessionHasNoErrors();

        $toko = $penjual->shop->fresh();
        $this->assertFalse($toko->jadwal_otomatis);
        $this->assertNull($toko->jam_buka);
        $this->assertTrue($toko->bukaSekarang());
    }

    public function test_pembeli_dan_tamu_tidak_bisa_membuka_pengaturan_ketersediaan(): void
    {
        $this->get(route('penjual.ketersediaan.edit'))->assertRedirect(route('login'));
        $this->actingAs($this->buatPembeli())->get(route('penjual.ketersediaan.edit'))->assertForbidden();
        $this->actingAs($this->buatPembeli())->put(route('penjual.ketersediaan.update'), ['libur' => '1'])->assertForbidden();
    }

    public function test_penjual_menyimpan_stok_harian_dan_pre_order_di_produk(): void
    {
        $penjual = $this->buatPenjual();

        $this->actingAs($penjual)->post(route('penjual.produk.store'), $this->dataProduk(['nama' => 'Roti Harian', 'stok_harian' => '25']))
            ->assertSessionHasNoErrors();
        $this->actingAs($penjual)->post(route('penjual.produk.store'), $this->dataProduk(['nama' => 'Kue PO', 'pre_order' => '1', 'pre_order_hari' => '2']))
            ->assertSessionHasNoErrors();

        $harian = Product::where('nama', 'Roti Harian')->first();
        $po = Product::where('nama', 'Kue PO')->first();

        $this->assertSame(25, $harian->stok_harian);
        $this->assertFalse($harian->pre_order);
        $this->assertTrue($po->pre_order);
        $this->assertSame(2, $po->pre_order_hari);
        $this->assertNull($po->stok_harian);
    }

    public function test_pre_order_hanya_untuk_makanan_dan_wajib_ada_lama_hari(): void
    {
        $penjual = $this->buatPenjual();
        $fashion = $this->buatKategori('Fashion');

        $this->actingAs($penjual)->post(route('penjual.produk.store'), $this->dataProduk(['category_id' => $fashion->id, 'pre_order' => '1', 'pre_order_hari' => '2']))
            ->assertSessionHasErrors('pre_order');

        $this->actingAs($penjual)->post(route('penjual.produk.store'), $this->dataProduk(['pre_order' => '1']))
            ->assertSessionHasErrors('pre_order_hari');

        $this->actingAs($penjual)->post(route('penjual.produk.store'), $this->dataProduk(['pre_order' => '1', 'pre_order_hari' => '30']))
            ->assertSessionHasErrors('pre_order_hari');

        $this->actingAs($penjual)->post(route('penjual.produk.store'), $this->dataProduk(['pre_order' => '1', 'pre_order_hari' => '2', 'stok_harian' => '10']))
            ->assertSessionHasErrors('stok_harian');

        $this->assertSame(0, Product::count());
    }

    public function test_perintah_reset_mengembalikan_stok_harian_saja(): void
    {
        $penjual = $this->buatPenjual();
        $harian = $this->buatProduk($penjual->shop, null, ['stok' => 2, 'stok_harian' => 30]);
        $biasa = $this->buatProduk($penjual->shop, null, ['stok' => 2]);
        $po = $this->buatProduk($penjual->shop, null, ['stok' => 2, 'stok_harian' => 30, 'pre_order' => true, 'pre_order_hari' => 2]);

        $this->artisan('stok:reset-harian')->assertSuccessful();

        $this->assertSame(30, $harian->fresh()->stok);
        $this->assertSame(2, $biasa->fresh()->stok);
        $this->assertSame(2, $po->fresh()->stok);
    }

    public function test_pembatalan_pesanan_lama_tidak_membuat_stok_melebihi_jatah_harian(): void
    {
        $penjual = $this->buatPenjual();
        $produk = $this->buatProduk($penjual->shop, null, ['stok' => 10, 'stok_harian' => 10]);
        $pembeli = $this->buatPembeli();
        $this->isiKeranjang($pembeli, $produk, 5);

        $this->actingAs($pembeli)->post(route('checkout.store'), $this->dataCheckout());
        $this->assertSame(5, $produk->fresh()->stok);

        // Pagi berikutnya stok diisi penuh, lalu pesanan kemarin dibatalkan.
        $this->artisan('stok:reset-harian');
        $this->actingAs($pembeli)->patch(route('pesanan.batal', Order::first()->id));

        $this->assertSame(10, $produk->fresh()->stok);
    }

    public function test_pembatalan_biasa_tetap_mengembalikan_stok(): void
    {
        $penjual = $this->buatPenjual();
        $produk = $this->buatProduk($penjual->shop, null, ['stok' => 10]);
        $pembeli = $this->buatPembeli();
        $this->isiKeranjang($pembeli, $produk, 4);

        $this->actingAs($pembeli)->post(route('checkout.store'), $this->dataCheckout());
        $this->actingAs($pembeli)->patch(route('pesanan.batal', Order::first()->id));

        $this->assertSame(10, $produk->fresh()->stok);
    }

    public function test_katalog_dan_halaman_produk_menampilkan_label_pre_order(): void
    {
        $penjual = $this->buatPenjual();
        $po = $this->buatProduk($penjual->shop, null, ['nama' => 'Brownies PO', 'pre_order' => true, 'pre_order_hari' => 2]);

        $this->get(route('katalog'))->assertOk()->assertSee('Pre-order 2 hari');
        $this->get(route('produk.show', $po->id))->assertOk()->assertSee('siap sekitar 2 hari');
    }
}
