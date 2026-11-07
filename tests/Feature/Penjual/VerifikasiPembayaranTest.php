<?php

namespace Tests\Feature\Penjual;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\MembuatPesananBayar;
use Tests\TestCase;

class VerifikasiPembayaranTest extends TestCase
{
    use MembuatPesananBayar;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_pembeli_tidak_bisa_membuka_halaman_penjual(): void
    {
        $pembeli = $this->buatPembeli();

        $this->actingAs($pembeli)->get(route('penjual.pesanan.index'))->assertForbidden();
        $this->actingAs($pembeli)->get(route('penjual.rekening.edit'))->assertForbidden();
        $this->actingAs($pembeli)->patch(route('penjual.pembayaran.terima', 1))->assertForbidden();
    }

    public function test_penjual_melihat_pesanan_masuk_tokonya_saja(): void
    {
        [, $penjual, $pesanan] = $this->buatPesananDenganBukti();
        [, , $pesananLain] = $this->buatPesananDenganBukti();

        $this->actingAs($penjual)
            ->get(route('penjual.pesanan.index'))
            ->assertOk()
            ->assertSee($pesanan->kode)
            ->assertDontSee($pesananLain->kode);
    }

    public function test_filter_perlu_verifikasi_menyaring_pesanan(): void
    {
        [$pembeli, $penjual, $pesanan] = $this->buatPesananDenganBukti();
        $lain = $this->buatPesanan($pembeli);
        $lain->update(['shop_id' => $penjual->shop->id]);

        $this->actingAs($penjual)
            ->get(route('penjual.pesanan.index', ['status' => 'perlu_verifikasi']))
            ->assertSee($pesanan->kode)
            ->assertDontSee($lain->kode);
    }

    public function test_penjual_tidak_bisa_membuka_pesanan_toko_lain(): void
    {
        [, , $pesanan] = $this->buatPesananDenganBukti();

        $this->actingAs($this->buatPenjual())
            ->get(route('penjual.pesanan.show', $pesanan->id))
            ->assertNotFound();
    }

    public function test_penjual_menerima_pembayaran(): void
    {
        [, $penjual, $pesanan, $pembayaran] = $this->buatPesananDenganBukti();

        $this->actingAs($penjual)
            ->patch(route('penjual.pembayaran.terima', $pembayaran->id))
            ->assertRedirect(route('penjual.pesanan.show', $pesanan->id))
            ->assertSessionHas('pesan');

        $this->assertSame(Payment::DITERIMA, $pembayaran->fresh()->status);
        $this->assertNotNull($pembayaran->fresh()->diverifikasi_at);
        $this->assertSame(Order::DIBAYAR, $pesanan->fresh()->status);
    }

    public function test_penjual_menolak_pembayaran_dengan_alasan(): void
    {
        [, $penjual, $pesanan, $pembayaran] = $this->buatPesananDenganBukti();

        $this->actingAs($penjual)
            ->patch(route('penjual.pembayaran.tolak', $pembayaran->id), ['alasan' => 'Jumlah transfer kurang.'])
            ->assertRedirect(route('penjual.pesanan.show', $pesanan->id))
            ->assertSessionHas('pesan');

        $this->assertSame(Payment::DITOLAK, $pembayaran->fresh()->status);
        $this->assertSame('Jumlah transfer kurang.', $pembayaran->fresh()->alasan_penolakan);
        $this->assertSame(Order::MENUNGGU_PEMBAYARAN, $pesanan->fresh()->status);
    }

    public function test_penolakan_tanpa_alasan_ditolak_sistem(): void
    {
        [, $penjual, $pesanan, $pembayaran] = $this->buatPesananDenganBukti();

        $this->actingAs($penjual)
            ->patch(route('penjual.pembayaran.tolak', $pembayaran->id), ['alasan' => ''])
            ->assertSessionHasErrors('alasan');

        $this->assertSame(Payment::MENUNGGU, $pembayaran->fresh()->status);
        $this->assertSame(Order::MENUNGGU_VERIFIKASI, $pesanan->fresh()->status);
    }

    public function test_pembeli_bisa_unggah_ulang_setelah_ditolak(): void
    {
        [$pembeli, $penjual, $pesanan, $pembayaran] = $this->buatPesananDenganBukti();

        $this->actingAs($penjual)
            ->patch(route('penjual.pembayaran.tolak', $pembayaran->id), ['alasan' => 'Bukti buram.']);

        $this->actingAs($pembeli)
            ->get(route('pesanan.show', $pesanan->id))
            ->assertSee('Bukti buram.')
            ->assertSee('Kirim Bukti Transfer');

        $this->kirimBukti($pembeli, $pesanan);

        $this->assertSame(2, Payment::count());
        $this->assertSame(Order::MENUNGGU_VERIFIKASI, $pesanan->fresh()->status);
    }

    public function test_pembayaran_yang_sudah_diproses_tidak_bisa_diproses_lagi(): void
    {
        [, $penjual, , $pembayaran] = $this->buatPesananDenganBukti();

        $this->actingAs($penjual)->patch(route('penjual.pembayaran.terima', $pembayaran->id));

        $this->actingAs($penjual)
            ->patch(route('penjual.pembayaran.tolak', $pembayaran->id), ['alasan' => 'Salah.'])
            ->assertSessionHas('galat');

        $this->assertSame(Payment::DITERIMA, $pembayaran->fresh()->status);
    }

    public function test_penjual_lain_tidak_bisa_memproses_pembayaran(): void
    {
        [, , $pesanan, $pembayaran] = $this->buatPesananDenganBukti();
        $penjualLain = $this->buatPenjual();

        $this->actingAs($penjualLain)->patch(route('penjual.pembayaran.terima', $pembayaran->id))->assertNotFound();
        $this->actingAs($penjualLain)->patch(route('penjual.pembayaran.tolak', $pembayaran->id), ['alasan' => 'x'])->assertNotFound();

        $this->assertSame(Order::MENUNGGU_VERIFIKASI, $pesanan->fresh()->status);
    }

    public function test_dasbor_menampilkan_jumlah_pembayaran_yang_perlu_diverifikasi(): void
    {
        [, $penjual] = $this->buatPesananDenganBukti();

        $this->actingAs($penjual)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('perluVerifikasi', 1)
            ->assertSee('Pesanan Masuk')
            ->assertSee('Rekening Pembayaran');
    }
}
