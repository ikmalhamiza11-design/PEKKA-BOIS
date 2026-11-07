<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatDataContoh;
use Tests\TestCase;

class PesananTest extends TestCase
{
    use MembuatDataContoh;
    use RefreshDatabase;

    protected function pesananBaru($pembeli, int $jumlah = 2, array $atributProduk = []): Order
    {
        $produk = $this->buatProduk($this->buatPenjual()->shop, null, array_merge(['stok' => 10, 'harga' => 10000], $atributProduk));
        $this->isiKeranjang($pembeli, $produk, $jumlah);

        $this->actingAs($pembeli)->post(route('checkout.store'), $this->dataCheckout());

        return Order::latest('id')->first();
    }

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->get(route('pesanan'))->assertRedirect(route('login'));
        $this->get(route('pesanan.show', 1))->assertRedirect(route('login'));
        $this->patch(route('pesanan.batal', 1))->assertRedirect(route('login'));
    }

    public function test_daftar_pesanan_kosong_menampilkan_pesan(): void
    {
        $this->actingAs($this->buatPembeli())
            ->get(route('pesanan'))
            ->assertOk()
            ->assertSee('Belum ada pesanan');
    }

    public function test_daftar_hanya_menampilkan_pesanan_milik_sendiri(): void
    {
        $saya = $this->buatPembeli();
        $lain = $this->buatPembeli();
        $milikSaya = $this->pesananBaru($saya);
        $milikLain = $this->pesananBaru($lain);

        $this->actingAs($saya)
            ->get(route('pesanan'))
            ->assertSee($milikSaya->kode)
            ->assertDontSee($milikLain->kode);
    }

    public function test_detail_pesanan_menampilkan_rincian(): void
    {
        $pembeli = $this->buatPembeli();
        $pesanan = $this->pesananBaru($pembeli, 2, ['nama' => 'Keripik Detail']);

        $this->actingAs($pembeli)
            ->get(route('pesanan.show', $pesanan->id))
            ->assertOk()
            ->assertSee($pesanan->kode)
            ->assertSee('Keripik Detail')
            ->assertSee('Rp 20.000')
            ->assertSee('Menunggu pembayaran')
            ->assertSee('Siti Rohmah')
            ->assertSee('Batalkan Pesanan');
    }

    public function test_detail_pesanan_orang_lain_tidak_bisa_dibuka(): void
    {
        $milikLain = $this->pesananBaru($this->buatPembeli());

        $this->actingAs($this->buatPembeli())->get(route('pesanan.show', $milikLain->id))->assertNotFound();
    }

    public function test_pesanan_berhasil_dibatalkan_dan_stok_kembali(): void
    {
        $pembeli = $this->buatPembeli();
        $pesanan = $this->pesananBaru($pembeli, 4);
        $produk = $pesanan->items()->first()->product;
        $this->assertSame(6, $produk->stok);

        $this->actingAs($pembeli)
            ->patch(route('pesanan.batal', $pesanan->id))
            ->assertRedirect(route('pesanan.show', $pesanan->id))
            ->assertSessionHas('pesan');

        $this->assertSame(Order::DIBATALKAN, $pesanan->fresh()->status);
        $this->assertSame(10, $produk->fresh()->stok);
    }

    public function test_pesanan_yang_sudah_dibatalkan_tidak_mengembalikan_stok_dua_kali(): void
    {
        $pembeli = $this->buatPembeli();
        $pesanan = $this->pesananBaru($pembeli, 4);
        $produk = $pesanan->items()->first()->product;

        $this->actingAs($pembeli)->patch(route('pesanan.batal', $pesanan->id));
        $this->actingAs($pembeli)->patch(route('pesanan.batal', $pesanan->id))->assertSessionHas('galat');

        $this->assertSame(10, $produk->fresh()->stok);
    }

    public function test_pesanan_orang_lain_tidak_bisa_dibatalkan(): void
    {
        $milikLain = $this->pesananBaru($this->buatPembeli());

        $this->actingAs($this->buatPembeli())->patch(route('pesanan.batal', $milikLain->id))->assertNotFound();

        $this->assertSame(Order::MENUNGGU_PEMBAYARAN, $milikLain->fresh()->status);
    }

    public function test_pesanan_tetap_bisa_dilihat_dan_dibatalkan_setelah_produk_dihapus_penjual(): void
    {
        $pembeli = $this->buatPembeli();
        $pesanan = $this->pesananBaru($pembeli, 2, ['nama' => 'Produk Akan Dihapus']);
        $pesanan->items()->first()->product->delete();

        $this->actingAs($pembeli)
            ->get(route('pesanan.show', $pesanan->id))
            ->assertOk()
            ->assertSee('Produk Akan Dihapus');

        $this->actingAs($pembeli)->patch(route('pesanan.batal', $pesanan->id))->assertSessionHas('pesan');
        $this->assertSame(Order::DIBATALKAN, $pesanan->fresh()->status);
    }

    public function test_pesanan_dibatalkan_tidak_menampilkan_tombol_batal(): void
    {
        $pembeli = $this->buatPembeli();
        $pesanan = $this->pesananBaru($pembeli);
        $this->actingAs($pembeli)->patch(route('pesanan.batal', $pesanan->id));

        $this->actingAs($pembeli)
            ->get(route('pesanan.show', $pesanan->id))
            ->assertSee('Dibatalkan')
            ->assertDontSee('Batalkan Pesanan');
    }

    public function test_daftar_pesanan_dibagi_per_halaman(): void
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        $pembeli = $this->buatPembeli();
        $kode = [];

        foreach (range(1, 12) as $nomor) {
            $kode[$nomor] = $this->pesananBaru($pembeli, 1)->kode;
        }

        $this->actingAs($pembeli)->get(route('pesanan'))
            ->assertSee($kode[12])
            ->assertDontSee($kode[1]);

        $this->actingAs($pembeli)->get(route('pesanan', ['page' => 2]))
            ->assertSee($kode[1])
            ->assertDontSee($kode[12]);
    }
}
