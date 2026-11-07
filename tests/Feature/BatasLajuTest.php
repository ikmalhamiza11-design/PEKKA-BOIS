<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatPesananBayar;
use Tests\TestCase;

class BatasLajuTest extends TestCase
{
    use MembuatPesananBayar;
    use RefreshDatabase;

    protected function tambahBanyak($pembeli, int $kali): void
    {
        $penjual = $this->buatPenjual();
        $this->isiRekening($penjual);
        $produk = $this->buatProduk($penjual->shop, null, ['stok' => 100]);

        foreach (range(1, $kali) as $ke) {
            $this->actingAs($pembeli)->post(route('keranjang.tambah'), ['product_id' => $produk->id])->assertSessionHas('pesan');
        }
    }

    public function test_banyak_menambah_keranjang_tidak_menghalangi_checkout(): void
    {
        $pembeli = $this->buatPembeli();
        $this->tambahBanyak($pembeli, 12);

        $this->actingAs($pembeli)
            ->post(route('checkout.store'), $this->dataCheckout())
            ->assertRedirect()
            ->assertSessionHas('pesan');

        $this->assertSame(1, Order::count());
    }

    public function test_banyak_menambah_keranjang_tidak_menghalangi_ganti_kata_sandi(): void
    {
        $pembeli = $this->buatPembeli();
        $this->tambahBanyak($pembeli, 8);

        $this->actingAs($pembeli)
            ->put(route('profil.sandi'), [])
            ->assertSessionHasErrors('kata_sandi_lama');
    }

    public function test_batas_checkout_tetap_berlaku_sendiri(): void
    {
        $pembeli = $this->buatPembeli();

        foreach (range(1, 10) as $ke) {
            $this->actingAs($pembeli)->post(route('checkout.store'), $this->dataCheckout())->assertRedirect(route('keranjang'));
        }

        $this->actingAs($pembeli)->post(route('checkout.store'), $this->dataCheckout())->assertStatus(429);
    }

    public function test_batas_masuk_tidak_dibagi_dengan_pendaftaran(): void
    {
        foreach (range(1, 10) as $ke) {
            $this->post(route('login.store'), ['email' => 'tidakada@contoh.test', 'password' => 'salah'])->assertRedirect();
        }

        $this->post(route('login.store'), ['email' => 'tidakada@contoh.test', 'password' => 'salah'])->assertStatus(429);

        $this->post(route('daftar.pembeli.simpan'), [])->assertSessionHasErrors();
    }
}
