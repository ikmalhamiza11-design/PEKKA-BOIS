<?php

namespace Tests\Feature\Admin;

use App\Models\Review;
use App\Services\UlasanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatAdmin;
use Tests\Concerns\MembuatUlasan;
use Tests\TestCase;

class ModerasiUlasanTest extends TestCase
{
    use MembuatAdmin;
    use MembuatUlasan;
    use RefreshDatabase;

    public function test_admin_dinas_melihat_semua_ulasan(): void
    {
        $a = $this->buatProduk($this->buatPenjual()->shop, null, ['nama' => 'Keripik Satu']);
        $b = $this->buatProduk($this->buatPenjual()->shop, null, ['nama' => 'Dodol Dua']);
        $this->buatUlasan($a);
        $this->buatUlasan($b);

        $this->actingAs($this->buatAdminDinas())
            ->get(route('admin.ulasan.index'))
            ->assertOk()
            ->assertSee('Keripik Satu')
            ->assertSee('Dodol Dua');
    }

    public function test_admin_kelompok_hanya_melihat_ulasan_toko_kelompoknya(): void
    {
        $milik = $this->buatKelompok(['nama' => 'Milik']);
        $lain = $this->buatKelompok(['nama' => 'Lain']);
        $produkMilik = $this->buatProduk($this->buatPenjualDiKelompok($milik)->shop, null, ['nama' => 'Produk Milik']);
        $produkLain = $this->buatProduk($this->buatPenjualDiKelompok($lain)->shop, null, ['nama' => 'Produk Lain']);
        $this->buatUlasan($produkMilik);
        $this->buatUlasan($produkLain);

        $this->actingAs($this->buatAdminKelompok($milik))
            ->get(route('admin.ulasan.index'))
            ->assertSee('Produk Milik')
            ->assertDontSee('Produk Lain');
    }

    public function test_admin_kelompok_tidak_bisa_memoderasi_ulasan_kelompok_lain(): void
    {
        $milik = $this->buatKelompok(['nama' => 'Milik']);
        $lain = $this->buatKelompok(['nama' => 'Lain']);
        $ulasan = $this->buatUlasan($this->buatProduk($this->buatPenjualDiKelompok($lain)->shop));
        $admin = $this->buatAdminKelompok($milik);

        $this->actingAs($admin)->patch(route('admin.ulasan.sembunyikan', $ulasan->id), ['alasan' => 'Berisi kata kasar'])->assertNotFound();
        $this->actingAs($admin)->patch(route('admin.ulasan.tampilkan', $ulasan->id))->assertNotFound();

        $this->assertFalse($ulasan->fresh()->disembunyikan());
    }

    public function test_menyembunyikan_ulasan_butuh_alasan(): void
    {
        $ulasan = $this->buatUlasan($this->buatProduk($this->buatPenjual()->shop));
        $admin = $this->buatAdminDinas();

        $this->actingAs($admin)->patch(route('admin.ulasan.sembunyikan', $ulasan->id), [])->assertSessionHasErrors('alasan');
        $this->actingAs($admin)->patch(route('admin.ulasan.sembunyikan', $ulasan->id), ['alasan' => 'abc'])->assertSessionHasErrors('alasan');
        $this->actingAs($admin)->patch(route('admin.ulasan.sembunyikan', $ulasan->id), ['alasan' => str_repeat('a', 301)])->assertSessionHasErrors('alasan');

        $this->assertFalse($ulasan->fresh()->disembunyikan());
    }

    public function test_menyembunyikan_ulasan_mencatat_alasan_dan_admin(): void
    {
        $ulasan = $this->buatUlasan($this->buatProduk($this->buatPenjual()->shop));
        $admin = $this->buatAdminDinas();

        $this->actingAs($admin)
            ->from(route('admin.ulasan.index'))
            ->patch(route('admin.ulasan.sembunyikan', $ulasan->id), ['alasan' => 'Berisi kata kasar'])
            ->assertRedirect(route('admin.ulasan.index'))
            ->assertSessionHas('pesan');

        $baru = $ulasan->fresh();
        $this->assertTrue($baru->disembunyikan());
        $this->assertSame('Berisi kata kasar', $baru->alasan_moderasi);
        $this->assertSame($admin->id, $baru->dimoderasi_oleh);
    }

    public function test_menyembunyikan_ulasan_dua_kali_ditolak_sistem(): void
    {
        $ulasan = $this->buatUlasan($this->buatProduk($this->buatPenjual()->shop));
        $admin = $this->buatAdminDinas();

        $this->actingAs($admin)->patch(route('admin.ulasan.sembunyikan', $ulasan->id), ['alasan' => 'Berisi kata kasar']);
        $this->actingAs($admin)
            ->patch(route('admin.ulasan.sembunyikan', $ulasan->id), ['alasan' => 'Alasan lain yang panjang'])
            ->assertSessionHas('galat');

        $this->assertSame('Berisi kata kasar', $ulasan->fresh()->alasan_moderasi);
    }

    public function test_ulasan_tersembunyi_hilang_dari_halaman_produk_dan_penilaian(): void
    {
        $produk = $this->buatProduk($this->buatPenjual()->shop);
        $bagus = $this->buatUlasan($produk, null, 5, ['komentar' => 'Komentar bagus sekali']);
        $kasar = $this->buatUlasan($produk, null, 1, ['komentar' => 'Komentar kasar sekali']);

        $this->get(route('produk.show', $produk->id))->assertSee('Komentar kasar sekali');

        $this->actingAs($this->buatAdminDinas())
            ->patch(route('admin.ulasan.sembunyikan', $kasar->id), ['alasan' => 'Berisi kata kasar']);

        $this->get(route('produk.show', $produk->id))
            ->assertOk()
            ->assertSee('Komentar bagus sekali')
            ->assertDontSee('Komentar kasar sekali')
            ->assertViewHas('ringkasan', fn ($ringkasan) => $ringkasan['jumlah'] === 1 && $ringkasan['rata'] == 5.0);
    }

    public function test_ulasan_tersembunyi_tidak_dihitung_di_katalog_dan_beranda(): void
    {
        $produk = $this->buatProduk($this->buatPenjual()->shop);
        $this->buatUlasan($produk, null, 5);
        $kasar = $this->buatUlasan($produk, null, 1);
        $kasar->sembunyikan($this->buatAdminDinas(), 'Berisi kata kasar');

        $this->get(route('katalog'))
            ->assertViewHas('produk', fn ($daftar) => $daftar->first()->reviews_count === 1 && (float) $daftar->first()->reviews_avg_rating === 5.0);

        $this->assertSame(1, $produk->reviews()->count());
    }

    public function test_ulasan_tersembunyi_tidak_dihitung_untuk_penjual(): void
    {
        $penjual = $this->buatPenjual();
        $produk = $this->buatProduk($penjual->shop);
        $this->buatUlasan($produk, null, 4);
        $kasar = $this->buatUlasan($produk, null, 1, ['komentar' => 'Komentar kasar sekali']);
        $kasar->sembunyikan($this->buatAdminDinas(), 'Berisi kata kasar');

        $this->actingAs($penjual)
            ->get(route('penjual.ulasan.index'))
            ->assertOk()
            ->assertDontSee('Komentar kasar sekali')
            ->assertViewHas('ringkasan', fn ($ringkasan) => $ringkasan['jumlah'] === 1 && $ringkasan['rata'] == 4.0);

        $this->actingAs($penjual)
            ->put(route('penjual.ulasan.balas', $kasar->id), ['balasan' => 'Terima kasih'])
            ->assertNotFound();
    }

    public function test_menampilkan_kembali_ulasan_memulihkan_semuanya(): void
    {
        $produk = $this->buatProduk($this->buatPenjual()->shop);
        $ulasan = $this->buatUlasan($produk, null, 3, ['komentar' => 'Komentar biasa saja']);
        $admin = $this->buatAdminDinas();
        $ulasan->sembunyikan($admin, 'Salah sembunyi');

        $this->actingAs($admin)
            ->patch(route('admin.ulasan.tampilkan', $ulasan->id))
            ->assertSessionHas('pesan');

        $baru = $ulasan->fresh();
        $this->assertFalse($baru->disembunyikan());
        $this->assertNull($baru->alasan_moderasi);
        $this->get(route('produk.show', $produk->id))->assertSee('Komentar biasa saja');
    }

    public function test_menampilkan_ulasan_yang_sudah_tampil_ditolak_sistem(): void
    {
        $ulasan = $this->buatUlasan($this->buatProduk($this->buatPenjual()->shop));

        $this->actingAs($this->buatAdminDinas())
            ->patch(route('admin.ulasan.tampilkan', $ulasan->id))
            ->assertSessionHas('galat');
    }

    public function test_filter_ulasan_tampil_disembunyikan_dan_rendah(): void
    {
        $produk = $this->buatProduk($this->buatPenjual()->shop);
        $this->buatUlasan($produk, null, 5, ['komentar' => 'Ulasan lima bintang']);
        $rendah = $this->buatUlasan($produk, null, 2, ['komentar' => 'Ulasan dua bintang']);
        $sembunyi = $this->buatUlasan($produk, null, 4, ['komentar' => 'Ulasan tersembunyi']);
        $sembunyi->sembunyikan($this->buatAdminDinas(), 'Spam berulang');
        $admin = $this->buatAdminDinas();

        $this->actingAs($admin)->get(route('admin.ulasan.index', ['filter' => 'disembunyikan']))
            ->assertSee('Ulasan tersembunyi')->assertDontSee('Ulasan lima bintang');

        $this->actingAs($admin)->get(route('admin.ulasan.index', ['filter' => 'tampil']))
            ->assertSee('Ulasan lima bintang')->assertDontSee('Ulasan tersembunyi');

        $this->actingAs($admin)->get(route('admin.ulasan.index', ['filter' => 'rendah']))
            ->assertSee('Ulasan dua bintang')->assertDontSee('Ulasan lima bintang');
    }

    public function test_pencarian_ulasan_berdasarkan_komentar(): void
    {
        $produk = $this->buatProduk($this->buatPenjual()->shop);
        $this->buatUlasan($produk, null, 5, ['komentar' => 'Pengiriman cepat sekali']);
        $this->buatUlasan($produk, null, 5, ['komentar' => 'Rasanya mantap']);

        $this->actingAs($this->buatAdminDinas())
            ->get(route('admin.ulasan.index', ['q' => 'cepat']))
            ->assertSee('Pengiriman cepat sekali')
            ->assertDontSee('Rasanya mantap');
    }

    public function test_pembeli_diberi_tahu_jika_ulasannya_disembunyikan(): void
    {
        [$pembeli, $pesanan, $item] = $this->buatPesananSelesai();
        $ulasan = Review::create([
            'order_item_id' => $item->id,
            'user_id' => $pembeli->id,
            'product_id' => $item->product_id,
            'shop_id' => $pesanan->shop_id,
            'rating' => 1,
            'komentar' => 'Kasar',
        ]);
        $ulasan->sembunyikan($this->buatAdminDinas(), 'Berisi kata kasar');

        $this->actingAs($pembeli)
            ->get(route('ulasan.form', [$pesanan->id, $item->id]))
            ->assertOk()
            ->assertSee('Ulasanmu disembunyikan admin')
            ->assertSee('Berisi kata kasar');
    }

    public function test_pembeli_yang_mengubah_ulasan_tidak_membuatnya_tampil_kembali(): void
    {
        [$pembeli, $pesanan, $item] = $this->buatPesananSelesai();
        $ulasan = Review::create([
            'order_item_id' => $item->id,
            'user_id' => $pembeli->id,
            'product_id' => $item->product_id,
            'shop_id' => $pesanan->shop_id,
            'rating' => 1,
            'komentar' => 'Kasar',
        ]);
        $ulasan->sembunyikan($this->buatAdminDinas(), 'Berisi kata kasar');

        $this->actingAs($pembeli)->post(route('ulasan.simpan', [$pesanan->id, $item->id]), $this->dataUlasan());

        $this->assertTrue($ulasan->fresh()->disembunyikan());
    }

    public function test_ringkasan_layanan_mengabaikan_ulasan_tersembunyi(): void
    {
        $produk = $this->buatProduk($this->buatPenjual()->shop);
        $this->buatUlasan($produk, null, 5);
        $this->buatUlasan($produk, null, 1)->sembunyikan($this->buatAdminDinas(), 'Spam berulang');

        $ringkasan = app(UlasanService::class)->ringkasan(Review::tampil()->where('product_id', $produk->id));

        $this->assertSame(1, $ringkasan['jumlah']);
        $this->assertSame(5.0, (float) $ringkasan['rata']);
    }
}
