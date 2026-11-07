<?php

namespace Tests\Feature\Penjual;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatUlasan;
use Tests\TestCase;

class UlasanPenjualTest extends TestCase
{
    use MembuatUlasan;
    use RefreshDatabase;

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->get(route('penjual.ulasan.index'))->assertRedirect(route('login'));
    }

    public function test_pembeli_tidak_boleh_masuk(): void
    {
        $pembeli = $this->buatPembeli();

        $this->actingAs($pembeli)->get(route('penjual.ulasan.index'))->assertForbidden();
        $this->actingAs($pembeli)->put(route('penjual.ulasan.balas', 1), ['balasan' => 'x'])->assertForbidden();
    }

    public function test_daftar_hanya_menampilkan_ulasan_toko_sendiri(): void
    {
        $saya = $this->buatPenjual();
        $lain = $this->buatPenjual();
        $this->buatUlasan($this->buatProduk($saya->shop), null, 5, ['komentar' => 'Ulasan untuk saya']);
        $this->buatUlasan($this->buatProduk($lain->shop), null, 5, ['komentar' => 'Ulasan untuk orang lain']);

        $this->actingAs($saya)
            ->get(route('penjual.ulasan.index'))
            ->assertOk()
            ->assertSee('Ulasan untuk saya')
            ->assertDontSee('Ulasan untuk orang lain');
    }

    public function test_daftar_kosong_menampilkan_pesan(): void
    {
        $this->actingAs($this->buatPenjual())
            ->get(route('penjual.ulasan.index'))
            ->assertOk()
            ->assertSee('Belum ada ulasan');
    }

    public function test_ringkasan_dan_nama_disamarkan(): void
    {
        $penjual = $this->buatPenjual();
        $produk = $this->buatProduk($penjual->shop);
        $this->buatUlasan($produk, $this->buatPembeli(['name' => 'Siti Rohmah']), 5);
        $this->buatUlasan($produk, $this->buatPembeli(['name' => 'Budi Santoso']), 4);

        $this->actingAs($penjual)
            ->get(route('penjual.ulasan.index'))
            ->assertViewHas('ringkasan', fn ($r) => $r['jumlah'] === 2 && $r['rata'] === 4.5)
            ->assertSee('Siti R.')
            ->assertSee('Budi S.')
            ->assertDontSee('Santoso');
    }

    public function test_filter_belum_dibalas(): void
    {
        $penjual = $this->buatPenjual();
        $produk = $this->buatProduk($penjual->shop);
        $this->buatUlasan($produk, null, 5, ['komentar' => 'Sudah dibalas', 'balasan' => 'Terima kasih']);
        $this->buatUlasan($produk, null, 5, ['komentar' => 'Belum dibalas']);

        $this->actingAs($penjual)
            ->get(route('penjual.ulasan.index', ['filter' => 'belum_dibalas']))
            ->assertSee('Belum dibalas', false)
            ->assertDontSee('Sudah dibalas')
            ->assertViewHas('belumDibalas', 1);
    }

    public function test_penjual_berhasil_membalas_ulasan(): void
    {
        $penjual = $this->buatPenjual();
        $ulasan = $this->buatUlasan($this->buatProduk($penjual->shop));

        $this->actingAs($penjual)
            ->put(route('penjual.ulasan.balas', $ulasan->id), ['balasan' => 'Terima kasih sudah berbelanja'])
            ->assertSessionHas('pesan');

        $ulasan->refresh();
        $this->assertSame('Terima kasih sudah berbelanja', $ulasan->balasan);
        $this->assertNotNull($ulasan->dibalas_at);
    }

    public function test_balasan_bisa_diperbarui(): void
    {
        $penjual = $this->buatPenjual();
        $ulasan = $this->buatUlasan($this->buatProduk($penjual->shop));

        $this->actingAs($penjual)->put(route('penjual.ulasan.balas', $ulasan->id), ['balasan' => 'Balasan pertama']);
        $this->actingAs($penjual)->put(route('penjual.ulasan.balas', $ulasan->id), ['balasan' => 'Balasan kedua']);

        $this->assertSame('Balasan kedua', $ulasan->fresh()->balasan);
    }

    public function test_balasan_kosong_atau_terlalu_panjang_ditolak(): void
    {
        $penjual = $this->buatPenjual();
        $ulasan = $this->buatUlasan($this->buatProduk($penjual->shop));

        $this->actingAs($penjual)->put(route('penjual.ulasan.balas', $ulasan->id), ['balasan' => ''])->assertSessionHasErrors('balasan');
        $this->actingAs($penjual)->put(route('penjual.ulasan.balas', $ulasan->id), ['balasan' => str_repeat('a', 501)])->assertSessionHasErrors('balasan');

        $this->assertNull($ulasan->fresh()->balasan);
    }

    public function test_ulasan_toko_lain_tidak_bisa_dibalas(): void
    {
        $saya = $this->buatPenjual();
        $lain = $this->buatPenjual();
        $ulasanLain = $this->buatUlasan($this->buatProduk($lain->shop));

        $this->actingAs($saya)->put(route('penjual.ulasan.balas', $ulasanLain->id), ['balasan' => 'Menyusup'])->assertNotFound();

        $this->assertNull($ulasanLain->fresh()->balasan);
    }

    public function test_penjual_tidak_bisa_mengubah_isi_atau_rating_ulasan(): void
    {
        $penjual = $this->buatPenjual();
        $ulasan = $this->buatUlasan($this->buatProduk($penjual->shop), null, 1, ['komentar' => 'Asli']);

        $this->actingAs($penjual)->put(route('penjual.ulasan.balas', $ulasan->id), [
            'balasan' => 'Terima kasih',
            'rating' => 5,
            'komentar' => 'Diubah penjual',
        ]);

        $ulasan->refresh();
        $this->assertSame(1, $ulasan->rating);
        $this->assertSame('Asli', $ulasan->komentar);
    }

    public function test_dasbor_menampilkan_penilaian_toko_dan_jumlah_belum_dibalas(): void
    {
        $penjual = $this->buatPenjual();
        $produk = $this->buatProduk($penjual->shop);
        $this->buatUlasan($produk, null, 5);
        $this->buatUlasan($produk, null, 4, ['balasan' => 'Terima kasih']);

        $this->actingAs($penjual)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('ulasanBelumDibalas', 1)
            ->assertViewHas('ringkasanUlasan', fn ($r) => $r['jumlah'] === 2 && $r['rata'] === 4.5)
            ->assertSee('Penilaian toko')
            ->assertSee('Ulasan Pembeli');
    }

    public function test_dasbor_pembeli_tidak_menampilkan_ulasan_penjual(): void
    {
        $this->actingAs($this->buatPembeli())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Ulasan Pembeli');
    }
}
