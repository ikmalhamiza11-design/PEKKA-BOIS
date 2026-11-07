<?php

namespace Tests\Feature\Admin;

use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatAdmin;
use Tests\Concerns\MembuatUlasan;
use Tests\TestCase;

class DasborAdminTest extends TestCase
{
    use MembuatAdmin;
    use MembuatUlasan;
    use RefreshDatabase;

    public function test_statistik_admin_dinas_mencakup_semua_kelompok(): void
    {
        $k1 = $this->buatKelompok(['nama' => 'Satu']);
        $k2 = $this->buatKelompok(['nama' => 'Dua']);
        $this->buatPenjualDiKelompok($k1, Shop::MENUNGGU);
        $this->buatPenjualDiKelompok($k2, Shop::MENUNGGU);
        $setuju = $this->buatPenjualDiKelompok($k1, Shop::TERVERIFIKASI);
        $this->buatPenjualDiKelompok($k2, Shop::DITOLAK);
        $this->buatProduk($setuju->shop);
        $ulasan = $this->buatUlasan($this->buatProduk($setuju->shop));
        $this->buatUlasan($this->buatProduk($setuju->shop))->sembunyikan($this->buatAdminDinas(), 'Spam berulang');

        $this->actingAs($this->buatAdminDinas())
            ->get(route('admin.dasbor'))
            ->assertOk()
            ->assertViewHas('statistik', fn ($s) => $s['menunggu'] === 2
                && $s['terverifikasi'] === 1
                && $s['ditolak'] === 1
                && $s['produk'] === 3
                && $s['pesanan'] === 2
                && $s['ulasan'] === 1
                && $s['ulasan_disembunyikan'] === 1)
            ->assertViewHas('statistikDinas', fn ($s) => $s['kelompok'] === 2 && $s['penjual'] === 4);

        $this->assertNotNull($ulasan);
    }

    public function test_statistik_admin_kelompok_hanya_kelompoknya(): void
    {
        $milik = $this->buatKelompok(['nama' => 'Milik']);
        $lain = $this->buatKelompok(['nama' => 'Lain']);
        $this->buatPenjualDiKelompok($milik, Shop::MENUNGGU);
        $this->buatPenjualDiKelompok($lain, Shop::MENUNGGU);
        $this->buatPenjualDiKelompok($lain, Shop::MENUNGGU);
        $this->buatProduk($this->buatPenjualDiKelompok($lain, Shop::TERVERIFIKASI)->shop);

        $this->actingAs($this->buatAdminKelompok($milik))
            ->get(route('admin.dasbor'))
            ->assertViewHas('statistik', fn ($s) => $s['menunggu'] === 1 && $s['terverifikasi'] === 0 && $s['produk'] === 0)
            ->assertViewHas('statistikDinas', null);
    }

    public function test_antrean_menampilkan_toko_menunggu_terlama_dulu(): void
    {
        $kelompok = $this->buatKelompok();
        $lama = $this->buatPenjualDiKelompok($kelompok);
        $lama->shop->update(['nama' => 'Toko Lama']);
        $baru = $this->buatPenjualDiKelompok($kelompok);
        $baru->shop->update(['nama' => 'Toko Baru']);

        $this->actingAs($this->buatAdminDinas())
            ->get(route('admin.dasbor'))
            ->assertSeeInOrder(['Toko Lama', 'Toko Baru']);
    }

    public function test_peringatan_muncul_jika_ada_toko_menunggu(): void
    {
        $this->actingAs($this->buatAdminDinas())->get(route('admin.dasbor'))->assertDontSee('menunggu verifikasi');

        $this->buatPenjual(Shop::MENUNGGU);

        $this->actingAs($this->buatAdminDinas())->get(route('admin.dasbor'))->assertSee('1 toko menunggu verifikasi');
    }
}
