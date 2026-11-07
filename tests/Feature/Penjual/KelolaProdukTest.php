<?php

namespace Tests\Feature\Penjual;

use App\Models\Product;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\MembuatDataContoh;
use Tests\TestCase;

class KelolaProdukTest extends TestCase
{
    use MembuatDataContoh;
    use RefreshDatabase;

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->get(route('penjual.produk.index'))->assertRedirect(route('login'));
        $this->get(route('penjual.produk.create'))->assertRedirect(route('login'));
    }

    public function test_pembeli_tidak_boleh_masuk_area_penjual(): void
    {
        $pembeli = $this->buatPembeli();

        $this->actingAs($pembeli)->get(route('penjual.produk.index'))->assertForbidden();
        $this->actingAs($pembeli)->post(route('penjual.produk.store'), $this->dataProduk())->assertForbidden();
        $this->assertSame(0, Product::count());
    }

    public function test_penjual_yang_belum_verifikasi_email_diarahkan_ke_verifikasi(): void
    {
        $penjual = $this->buatPenjual(Shop::MENUNGGU, ['email_verified_at' => null]);

        $this->actingAs($penjual)
            ->get(route('penjual.produk.index'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_daftar_hanya_menampilkan_produk_milik_sendiri(): void
    {
        $saya = $this->buatPenjual();
        $lain = $this->buatPenjual();
        $this->buatProduk($saya->shop, null, ['nama' => 'Produk Milik Saya']);
        $this->buatProduk($lain->shop, null, ['nama' => 'Produk Milik Orang Lain']);

        $this->actingAs($saya)
            ->get(route('penjual.produk.index'))
            ->assertOk()
            ->assertSee('Produk Milik Saya')
            ->assertDontSee('Produk Milik Orang Lain');
    }

    public function test_daftar_kosong_menampilkan_ajakan_menambah_produk(): void
    {
        $this->actingAs($this->buatPenjual())
            ->get(route('penjual.produk.index'))
            ->assertOk()
            ->assertSee('Belum ada produk');
    }

    public function test_toko_menunggu_melihat_pemberitahuan_di_daftar_produk(): void
    {
        $this->actingAs($this->buatPenjual(Shop::MENUNGGU))
            ->get(route('penjual.produk.index'))
            ->assertSee('Produk belum tampil di katalog');
    }

    public function test_formulir_tambah_produk_tampil(): void
    {
        $this->buatKategori('Kuliner');

        $this->actingAs($this->buatPenjual())
            ->get(route('penjual.produk.create'))
            ->assertOk()
            ->assertSee('Simpan Produk')
            ->assertSee('Kuliner');
    }

    public function test_penjual_berhasil_menambah_produk_tanpa_foto(): void
    {
        $penjual = $this->buatPenjual();

        $this->actingAs($penjual)
            ->post(route('penjual.produk.store'), $this->dataProduk())
            ->assertRedirect(route('penjual.produk.index'))
            ->assertSessionHas('pesan');

        $produk = Product::first();
        $this->assertSame($penjual->shop->id, $produk->shop_id);
        $this->assertSame('Keripik Singkong', $produk->nama);
        $this->assertSame(15000, $produk->harga);
        $this->assertSame(20, $produk->stok);
        $this->assertTrue($produk->aktif);
        $this->assertNull($produk->foto);
    }

    public function test_harga_dengan_titik_dan_rp_dibersihkan(): void
    {
        $this->actingAs($this->buatPenjual())
            ->post(route('penjual.produk.store'), $this->dataProduk(['harga' => 'Rp 15.000']))
            ->assertSessionHasNoErrors();

        $this->assertSame(15000, Product::first()->harga);
    }

    public function test_produk_toko_terverifikasi_tampil_di_katalog(): void
    {
        $penjual = $this->buatPenjual(Shop::TERVERIFIKASI);

        $this->actingAs($penjual)->post(route('penjual.produk.store'), $this->dataProduk(['nama' => 'Sambal Roa']));

        $this->get(route('katalog'))->assertSee('Sambal Roa');
    }

    public function test_produk_toko_menunggu_tidak_tampil_di_katalog(): void
    {
        $penjual = $this->buatPenjual(Shop::MENUNGGU);

        $this->actingAs($penjual)->post(route('penjual.produk.store'), $this->dataProduk(['nama' => 'Sambal Roa']));

        $this->assertSame(1, Product::count());
        $this->get(route('katalog'))->assertDontSee('Sambal Roa');
    }

    public function test_produk_nonaktif_tidak_tampil_di_katalog(): void
    {
        $penjual = $this->buatPenjual(Shop::TERVERIFIKASI);

        $this->actingAs($penjual)->post(route('penjual.produk.store'), $this->dataProduk(['nama' => 'Sambal Roa', 'aktif' => '0']));

        $this->assertFalse(Product::first()->aktif);
        $this->get(route('katalog'))->assertDontSee('Sambal Roa');
    }

    public function test_penjual_tidak_bisa_menjadikan_produk_unggulan_sendiri(): void
    {
        $this->actingAs($this->buatPenjual())
            ->post(route('penjual.produk.store'), $this->dataProduk(['unggulan' => '1']));

        $this->assertFalse((bool) Product::first()->unggulan);
    }

    public function test_penjual_tidak_bisa_menyetel_toko_lain_lewat_isian(): void
    {
        $saya = $this->buatPenjual();
        $lain = $this->buatPenjual();

        $this->actingAs($saya)
            ->post(route('penjual.produk.store'), $this->dataProduk(['shop_id' => $lain->shop->id]));

        $this->assertSame($saya->shop->id, Product::first()->shop_id);
    }

    public function test_nama_wajib_diisi(): void
    {
        $this->actingAs($this->buatPenjual())
            ->post(route('penjual.produk.store'), $this->dataProduk(['nama' => '']))
            ->assertSessionHasErrors('nama');

        $this->assertSame(0, Product::count());
    }

    public function test_nama_terlalu_panjang_ditolak(): void
    {
        $this->actingAs($this->buatPenjual())
            ->post(route('penjual.produk.store'), $this->dataProduk(['nama' => str_repeat('a', 151)]))
            ->assertSessionHasErrors('nama');
    }

    public function test_kategori_yang_tidak_ada_ditolak(): void
    {
        $this->actingAs($this->buatPenjual())
            ->post(route('penjual.produk.store'), $this->dataProduk(['category_id' => 9999]))
            ->assertSessionHasErrors('category_id');
    }

    public function test_harga_bukan_angka_ditolak(): void
    {
        $this->actingAs($this->buatPenjual())
            ->post(route('penjual.produk.store'), $this->dataProduk(['harga' => 'lima belas ribu']))
            ->assertSessionHasErrors('harga');
    }

    public function test_harga_terlalu_kecil_atau_negatif_ditolak(): void
    {
        $penjual = $this->buatPenjual();

        $this->actingAs($penjual)
            ->post(route('penjual.produk.store'), $this->dataProduk(['harga' => '0']))
            ->assertSessionHasErrors('harga');

        $this->actingAs($penjual)
            ->post(route('penjual.produk.store'), $this->dataProduk(['harga' => '-5000']))
            ->assertSessionHasErrors('harga');

        $this->assertSame(0, Product::count());
    }

    public function test_stok_negatif_ditolak(): void
    {
        $this->actingAs($this->buatPenjual())
            ->post(route('penjual.produk.store'), $this->dataProduk(['stok' => '-1']))
            ->assertSessionHasErrors('stok');
    }

    public function test_stok_nol_diperbolehkan(): void
    {
        $this->actingAs($this->buatPenjual())
            ->post(route('penjual.produk.store'), $this->dataProduk(['stok' => '0']))
            ->assertSessionHasNoErrors();

        $this->assertSame(0, Product::first()->stok);
    }

    public function test_berkas_bukan_gambar_ditolak(): void
    {
        Storage::fake('public');

        $this->actingAs($this->buatPenjual())
            ->post(route('penjual.produk.store'), $this->dataProduk([
                'foto' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
            ]))
            ->assertSessionHasErrors('foto');

        $this->assertSame(0, Product::count());
    }

    public function test_foto_lebih_dari_10_mb_ditolak(): void
    {
        Storage::fake('public');

        $this->actingAs($this->buatPenjual())
            ->post(route('penjual.produk.store'), $this->dataProduk([
                'foto' => UploadedFile::fake()->create('besar.jpg', 10241, 'image/jpeg'),
            ]))
            ->assertSessionHasErrors('foto');
    }

    public function test_foto_besar_dikompres_ke_lebar_maksimal_1200(): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('Ekstensi GD belum aktif.');
        }

        Storage::fake('public');

        $this->actingAs($this->buatPenjual())
            ->post(route('penjual.produk.store'), $this->dataProduk([
                'foto' => UploadedFile::fake()->image('besar.jpg', 3000, 2000),
            ]))
            ->assertSessionHasNoErrors();

        $foto = Product::first()->foto;
        $this->assertNotNull($foto);
        Storage::disk('public')->assertExists($foto);

        [$lebar, $tinggi] = getimagesize(Storage::disk('public')->path($foto));
        $this->assertSame(1200, $lebar);
        $this->assertSame(800, $tinggi);
    }

    public function test_foto_kecil_tidak_diperbesar(): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('Ekstensi GD belum aktif.');
        }

        Storage::fake('public');

        $this->actingAs($this->buatPenjual())
            ->post(route('penjual.produk.store'), $this->dataProduk([
                'foto' => UploadedFile::fake()->image('kecil.png', 400, 300),
            ]));

        [$lebar] = getimagesize(Storage::disk('public')->path(Product::first()->foto));
        $this->assertSame(400, $lebar);
    }

    public function test_penjual_berhasil_mengubah_produk_sendiri(): void
    {
        $penjual = $this->buatPenjual();
        $produk = $this->buatProduk($penjual->shop);

        $this->actingAs($penjual)
            ->put(route('penjual.produk.update', $produk->id), $this->dataProduk(['nama' => 'Nama Baru', 'harga' => '25000', 'stok' => '3']))
            ->assertRedirect(route('penjual.produk.index'));

        $produk->refresh();
        $this->assertSame('Nama Baru', $produk->nama);
        $this->assertSame(25000, $produk->harga);
        $this->assertSame(3, $produk->stok);
    }

    public function test_formulir_ubah_menampilkan_data_lama(): void
    {
        $penjual = $this->buatPenjual();
        $produk = $this->buatProduk($penjual->shop, null, ['nama' => 'Dodol Betawi']);

        $this->actingAs($penjual)
            ->get(route('penjual.produk.edit', $produk->id))
            ->assertOk()
            ->assertSee('Dodol Betawi')
            ->assertSee('Simpan Perubahan');
    }

    public function test_penjual_tidak_bisa_membuka_atau_mengubah_produk_toko_lain(): void
    {
        $saya = $this->buatPenjual();
        $lain = $this->buatPenjual();
        $produkLain = $this->buatProduk($lain->shop, null, ['nama' => 'Milik Lain']);

        $this->actingAs($saya)->get(route('penjual.produk.edit', $produkLain->id))->assertNotFound();
        $this->actingAs($saya)->put(route('penjual.produk.update', $produkLain->id), $this->dataProduk(['nama' => 'Diretas']))->assertNotFound();
        $this->actingAs($saya)->patch(route('penjual.produk.status', $produkLain->id))->assertNotFound();
        $this->actingAs($saya)->delete(route('penjual.produk.destroy', $produkLain->id))->assertNotFound();

        $produkLain->refresh();
        $this->assertSame('Milik Lain', $produkLain->nama);
        $this->assertTrue($produkLain->aktif);
    }

    public function test_mengganti_foto_menghapus_foto_lama(): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('Ekstensi GD belum aktif.');
        }

        Storage::fake('public');
        $penjual = $this->buatPenjual();

        $this->actingAs($penjual)->post(route('penjual.produk.store'), $this->dataProduk([
            'foto' => UploadedFile::fake()->image('a.jpg', 800, 800),
        ]));
        $produk = Product::first();
        $lama = $produk->foto;

        $this->actingAs($penjual)->put(route('penjual.produk.update', $produk->id), $this->dataProduk([
            'foto' => UploadedFile::fake()->image('b.jpg', 800, 800),
        ]));

        $produk->refresh();
        $this->assertNotSame($lama, $produk->foto);
        Storage::disk('public')->assertMissing($lama);
        Storage::disk('public')->assertExists($produk->foto);
    }

    public function test_foto_lama_tetap_jika_tidak_ada_foto_baru(): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('Ekstensi GD belum aktif.');
        }

        Storage::fake('public');
        $penjual = $this->buatPenjual();

        $this->actingAs($penjual)->post(route('penjual.produk.store'), $this->dataProduk([
            'foto' => UploadedFile::fake()->image('a.jpg', 800, 800),
        ]));
        $produk = Product::first();
        $lama = $produk->foto;

        $this->actingAs($penjual)->put(route('penjual.produk.update', $produk->id), $this->dataProduk(['nama' => 'Ganti Nama']));

        $this->assertSame($lama, $produk->fresh()->foto);
        Storage::disk('public')->assertExists($lama);
    }

    public function test_penjual_bisa_menghapus_foto_saja(): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('Ekstensi GD belum aktif.');
        }

        Storage::fake('public');
        $penjual = $this->buatPenjual();

        $this->actingAs($penjual)->post(route('penjual.produk.store'), $this->dataProduk([
            'foto' => UploadedFile::fake()->image('a.jpg', 800, 800),
        ]));
        $produk = Product::first();
        $lama = $produk->foto;

        $this->actingAs($penjual)->put(route('penjual.produk.update', $produk->id), $this->dataProduk(['hapus_foto' => '1']));

        $this->assertNull($produk->fresh()->foto);
        Storage::disk('public')->assertMissing($lama);
    }

    public function test_penjual_bisa_menonaktifkan_dan_mengaktifkan_produk(): void
    {
        $penjual = $this->buatPenjual();
        $produk = $this->buatProduk($penjual->shop);

        $this->actingAs($penjual)->patch(route('penjual.produk.status', $produk->id))->assertRedirect();
        $this->assertFalse($produk->fresh()->aktif);

        $this->actingAs($penjual)->patch(route('penjual.produk.status', $produk->id))->assertRedirect();
        $this->assertTrue($produk->fresh()->aktif);
    }

    public function test_penjual_bisa_menghapus_produk_sendiri_beserta_fotonya(): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('Ekstensi GD belum aktif.');
        }

        Storage::fake('public');
        $penjual = $this->buatPenjual();

        $this->actingAs($penjual)->post(route('penjual.produk.store'), $this->dataProduk([
            'foto' => UploadedFile::fake()->image('a.jpg', 800, 800),
        ]));
        $produk = Product::first();
        $foto = $produk->foto;

        $this->actingAs($penjual)
            ->delete(route('penjual.produk.destroy', $produk->id))
            ->assertRedirect(route('penjual.produk.index'));

        $this->assertSame(0, Product::count());
        Storage::disk('public')->assertMissing($foto);
    }

    public function test_produk_yang_tidak_ada_menghasilkan_404(): void
    {
        $this->actingAs($this->buatPenjual())
            ->get(route('penjual.produk.edit', 9999))
            ->assertNotFound();
    }

    public function test_daftar_produk_dibagi_per_halaman(): void
    {
        $penjual = $this->buatPenjual();

        foreach (range(1, 12) as $nomor) {
            $this->buatProduk($penjual->shop, null, ['nama' => 'Produk Nomor '.$nomor]);
        }

        $this->actingAs($penjual)->get(route('penjual.produk.index'))
            ->assertSee('Produk Nomor 12')
            ->assertDontSee('Produk Nomor 1<', false);

        $this->actingAs($penjual)->get(route('penjual.produk.index', ['page' => 2]))
            ->assertSee('Produk Nomor 1')
            ->assertDontSee('Produk Nomor 12');
    }
}
