<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatDataContoh;
use Tests\TestCase;

class MasukDanDasborPembeliTest extends TestCase
{
    use MembuatDataContoh;
    use RefreshDatabase;

    public function test_pembeli_setelah_masuk_langsung_ke_beranda(): void
    {
        $pembeli = $this->buatPembeli(['email' => 'ibu@contoh.test']);

        $this->post(route('login.store'), ['email' => 'ibu@contoh.test', 'password' => 'password'])
            ->assertRedirect(route('beranda'));

        $this->assertAuthenticatedAs($pembeli);
    }

    public function test_penjual_setelah_masuk_ke_dasbor_toko(): void
    {
        $penjual = $this->buatPenjual();

        $this->post(route('login.store'), ['email' => $penjual->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_pembeli_yang_dialihkan_dari_halaman_lain_kembali_ke_halaman_itu(): void
    {
        $this->buatPembeli(['email' => 'ibu@contoh.test']);

        $this->get(route('keranjang'))->assertRedirect(route('login'));

        $this->post(route('login.store'), ['email' => 'ibu@contoh.test', 'password' => 'password'])
            ->assertRedirect(route('keranjang'));
    }

    public function test_dasbor_pembeli_punya_jalan_menuju_beranda_dan_pesanan(): void
    {
        $this->actingAs($this->buatPembeli())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Ke Beranda')
            ->assertSee('Pesanan Saya')
            ->assertSee('Keranjang')
            ->assertSee('Lihat Katalog');
    }

    public function test_dasbor_penjual_mengingatkan_isi_rekening(): void
    {
        $penjual = $this->buatPenjual();

        $this->actingAs($penjual)
            ->get(route('dashboard'))
            ->assertSee('Rekening pembayaran belum diisi');

        $penjual->shop->update(['nama_bank' => 'BRI', 'nomor_rekening' => '1234567890', 'atas_nama' => 'Ibu Contoh']);

        $this->actingAs($penjual->fresh())
            ->get(route('dashboard'))
            ->assertDontSee('Rekening pembayaran belum diisi');
    }
}
