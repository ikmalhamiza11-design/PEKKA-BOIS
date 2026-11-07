<?php

namespace Tests\Feature\Penjual;

use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatDataContoh;
use Tests\TestCase;

class DasborPenjualTest extends TestCase
{
    use MembuatDataContoh;
    use RefreshDatabase;

    public function test_dasbor_penjual_menampilkan_ringkasan_produk(): void
    {
        $penjual = $this->buatPenjual();
        $this->buatProduk($penjual->shop, null, ['stok' => 5]);
        $this->buatProduk($penjual->shop, null, ['stok' => 0]);
        $this->buatProduk($penjual->shop, null, ['aktif' => false]);

        $this->actingAs($penjual)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('ringkasan', ['total' => 3, 'aktif' => 2, 'habis' => 1])
            ->assertSee('Kelola Produk')
            ->assertSee('Tambah Produk');
    }

    public function test_ringkasan_tidak_menghitung_produk_toko_lain(): void
    {
        $saya = $this->buatPenjual();
        $lain = $this->buatPenjual();
        $this->buatProduk($lain->shop);
        $this->buatProduk($lain->shop);

        $this->actingAs($saya)
            ->get(route('dashboard'))
            ->assertViewHas('ringkasan', ['total' => 0, 'aktif' => 0, 'habis' => 0]);
    }

    public function test_dasbor_toko_menunggu_menampilkan_pemberitahuan(): void
    {
        $this->actingAs($this->buatPenjual(Shop::MENUNGGU))
            ->get(route('dashboard'))
            ->assertSee('Toko menunggu verifikasi');
    }

    public function test_dasbor_toko_ditolak_menampilkan_pemberitahuan(): void
    {
        $this->actingAs($this->buatPenjual(Shop::DITOLAK))
            ->get(route('dashboard'))
            ->assertSee('Toko belum disetujui');
    }

    public function test_dasbor_pembeli_tidak_menampilkan_menu_penjual(): void
    {
        $this->actingAs($this->buatPembeli())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Kelola Produk');
    }
}
