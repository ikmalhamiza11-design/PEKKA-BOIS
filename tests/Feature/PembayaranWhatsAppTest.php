<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Support\PesanWa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\MembuatPesananBayar;
use Tests\TestCase;

class PembayaranWhatsAppTest extends TestCase
{
    use MembuatPesananBayar;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    private function beriNomorPenjual(Order $pesanan): void
    {
        $pesanan->shop->user->forceFill(['phone' => '6281234567890'])->save();
    }

    public function test_halaman_bayar_memberi_catatan_ongkir_dan_belum_ada_tombol_wa(): void
    {
        $pembeli = $this->buatPembeli();
        $pesanan = $this->buatPesanan($pembeli);

        $this->actingAs($pembeli)
            ->get(route('pesanan.show', $pesanan->id))
            ->assertOk()
            ->assertSee('Transfer hanya untuk harga produk. Ongkir dibicarakan lewat WhatsApp setelah bayar.')
            ->assertDontSee('Chat Penjual di WhatsApp')
            ->assertDontSee('wa.me');
    }

    public function test_total_pesanan_tetap_sama_dengan_subtotal(): void
    {
        $pesanan = $this->buatPesanan($this->buatPembeli());

        $this->assertSame($pesanan->subtotal, $pesanan->total);
    }

    public function test_tombol_wa_muncul_setelah_bukti_terkirim(): void
    {
        $pembeli = $this->buatPembeli();
        $pesanan = $this->buatPesanan($pembeli);
        $this->beriNomorPenjual($pesanan);
        $this->kirimBukti($pembeli, $pesanan);

        $this->actingAs($pembeli)
            ->get(route('pesanan.show', $pesanan->id))
            ->assertOk()
            ->assertSee('Chat Penjual di WhatsApp')
            ->assertSee('https://wa.me/6281234567890?text=', false);
    }

    public function test_setelah_unggah_bukti_pembeli_diberi_tahu_soal_wa(): void
    {
        $pembeli = $this->buatPembeli();
        $pesanan = $this->buatPesanan($pembeli);

        $this->actingAs($pembeli)
            ->post(route('pembayaran.store', $pesanan->id), [
                'nama_pengirim' => 'Budi Santoso',
                'bukti' => \Illuminate\Http\UploadedFile::fake()->image('bukti.jpg', 600, 800),
            ])
            ->assertSessionHas('pesan', fn ($pesan) => str_contains($pesan, 'WhatsApp'));
    }

    public function test_pesan_wa_berisi_data_pesanan_dan_nomor_penjual(): void
    {
        $pembeli = $this->buatPembeli();
        $pesanan = $this->buatPesanan($pembeli);
        $this->kirimBukti($pembeli, $pesanan);

        $pesanan = Order::with(['items', 'shop.user'])->find($pesanan->id);
        $pesanan->shop->user->update(['phone' => '081234567890']);
        $pesanan->refresh()->load(['items', 'shop.user']);

        $url = PesanWa::untukPenjual($pesanan);

        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $url);

        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $pesan = $query['text'];

        $this->assertStringContainsString($pesanan->kode, $pesan);
        $this->assertStringContainsString($pesanan->items->first()->nama, $pesan);
        $this->assertStringContainsString('Rp 20.000', $pesan);
        $this->assertStringContainsString($pesanan->nama_penerima, $pesan);
        $this->assertStringContainsString('bukti', strtolower($pesan));
        $this->assertStringContainsString('harga produk saja', $pesan);
    }

    public function test_tanpa_nomor_penjual_tampil_pemberitahuan_bukan_tautan_rusak(): void
    {
        $pembeli = $this->buatPembeli();
        $pesanan = $this->buatPesanan($pembeli);
        $this->kirimBukti($pembeli, $pesanan);

        $pesanan->shop->user->forceFill(['phone' => null])->save();

        $this->actingAs($pembeli)
            ->get(route('pesanan.show', $pesanan->id))
            ->assertOk()
            ->assertSee('Nomor WhatsApp penjual belum tersedia')
            ->assertDontSee('wa.me');
    }

    public function test_tombol_wa_tetap_ada_setelah_pembayaran_diterima(): void
    {
        [$pembeli, $penjual, $pesanan] = $this->buatPesananDibayar();
        $penjual->forceFill(['phone' => '6281234567890'])->save();

        $this->actingAs($pembeli)
            ->get(route('pesanan.show', $pesanan->id))
            ->assertOk()
            ->assertSee('Chat Penjual di WhatsApp');
    }

    public function test_pembeli_lain_tidak_bisa_melihat_halaman_pesanan_ini(): void
    {
        [, , $pesanan] = $this->buatPesananDibayar();

        $this->actingAs($this->buatPembeli())
            ->get(route('pesanan.show', $pesanan->id))
            ->assertNotFound();
    }
}
