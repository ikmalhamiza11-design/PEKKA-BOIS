<?php

namespace Tests\Feature\Penjual;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\MembuatPesananBayar;
use Tests\TestCase;

class PengirimanTest extends TestCase
{
    use MembuatPesananBayar;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    protected function pesananDiproses(): array
    {
        [$pembeli, $penjual, $pesanan] = $this->buatPesananDibayar();

        $this->actingAs($penjual)->patch(route('penjual.pesanan.proses', $pesanan->id));

        return [$pembeli, $penjual, $pesanan->fresh()];
    }

    protected function pesananDikirim(): array
    {
        [$pembeli, $penjual, $pesanan] = $this->pesananDiproses();

        $this->actingAs($penjual)->patch(route('penjual.pesanan.kirim', $pesanan->id), [
            'kurir' => 'JNE',
            'nomor_resi' => 'JNE123456',
        ]);

        return [$pembeli, $penjual, $pesanan->fresh()];
    }

    public function test_penjual_memproses_pesanan_yang_sudah_dibayar(): void
    {
        [, $penjual, $pesanan] = $this->buatPesananDibayar();

        $this->actingAs($penjual)
            ->patch(route('penjual.pesanan.proses', $pesanan->id))
            ->assertRedirect(route('penjual.pesanan.show', $pesanan->id))
            ->assertSessionHas('pesan');

        $this->assertSame(Order::DIPROSES, $pesanan->fresh()->status);
        $this->assertNotNull($pesanan->fresh()->diproses_at);
    }

    public function test_pesanan_belum_dibayar_tidak_bisa_diproses(): void
    {
        [, $penjual, $pesanan] = $this->buatPesananDenganBukti();

        $this->actingAs($penjual)
            ->patch(route('penjual.pesanan.proses', $pesanan->id))
            ->assertSessionHas('galat');

        $this->assertSame(Order::MENUNGGU_VERIFIKASI, $pesanan->fresh()->status);
    }

    public function test_penjual_mengirim_pesanan_dengan_kurir_dan_resi(): void
    {
        [, $penjual, $pesanan] = $this->pesananDiproses();

        $this->actingAs($penjual)
            ->patch(route('penjual.pesanan.kirim', $pesanan->id), ['kurir' => 'JNE', 'nomor_resi' => 'JNE123456'])
            ->assertRedirect(route('penjual.pesanan.show', $pesanan->id))
            ->assertSessionHas('pesan');

        $pesanan = $pesanan->fresh();

        $this->assertSame(Order::DIKIRIM, $pesanan->status);
        $this->assertSame('JNE', $pesanan->kurir);
        $this->assertSame('JNE123456', $pesanan->nomor_resi);
        $this->assertNotNull($pesanan->dikirim_at);
    }

    public function test_nomor_resi_boleh_kosong(): void
    {
        [, $penjual, $pesanan] = $this->pesananDiproses();

        $this->actingAs($penjual)
            ->patch(route('penjual.pesanan.kirim', $pesanan->id), ['kurir' => 'Antar sendiri', 'nomor_resi' => ''])
            ->assertSessionHasNoErrors();

        $this->assertSame(Order::DIKIRIM, $pesanan->fresh()->status);
        $this->assertNull($pesanan->fresh()->nomor_resi);
    }

    public function test_kurir_wajib_diisi(): void
    {
        [, $penjual, $pesanan] = $this->pesananDiproses();

        $this->actingAs($penjual)
            ->patch(route('penjual.pesanan.kirim', $pesanan->id), ['kurir' => ''])
            ->assertSessionHasErrors('kurir');

        $this->assertSame(Order::DIPROSES, $pesanan->fresh()->status);
    }

    public function test_pesanan_belum_diproses_tidak_bisa_langsung_dikirim(): void
    {
        [, $penjual, $pesanan] = $this->buatPesananDibayar();

        $this->actingAs($penjual)
            ->patch(route('penjual.pesanan.kirim', $pesanan->id), ['kurir' => 'JNE'])
            ->assertSessionHas('galat');

        $this->assertSame(Order::DIBAYAR, $pesanan->fresh()->status);
    }

    public function test_penjual_lain_tidak_bisa_mengubah_pengiriman(): void
    {
        [, , $pesanan] = $this->buatPesananDibayar();
        $penjualLain = $this->buatPenjual();

        $this->actingAs($penjualLain)->patch(route('penjual.pesanan.proses', $pesanan->id))->assertNotFound();
        $this->actingAs($penjualLain)->patch(route('penjual.pesanan.kirim', $pesanan->id), ['kurir' => 'JNE'])->assertNotFound();

        $this->assertSame(Order::DIBAYAR, $pesanan->fresh()->status);
    }

    public function test_pembeli_tidak_bisa_memakai_rute_penjual(): void
    {
        [$pembeli, , $pesanan] = $this->buatPesananDibayar();

        $this->actingAs($pembeli)->patch(route('penjual.pesanan.proses', $pesanan->id))->assertForbidden();
        $this->actingAs($pembeli)->patch(route('penjual.pesanan.kirim', $pesanan->id), ['kurir' => 'JNE'])->assertForbidden();
    }

    public function test_pembeli_melihat_kurir_dan_resi_saat_pesanan_dikirim(): void
    {
        [$pembeli, , $pesanan] = $this->pesananDikirim();

        $this->actingAs($pembeli)
            ->get(route('pesanan.show', $pesanan->id))
            ->assertOk()
            ->assertSee('JNE123456')
            ->assertSee('Pesanan Sudah Diterima');
    }

    public function test_pembeli_mengonfirmasi_pesanan_diterima(): void
    {
        [$pembeli, , $pesanan] = $this->pesananDikirim();

        $this->actingAs($pembeli)
            ->patch(route('pesanan.diterima', $pesanan->id))
            ->assertRedirect(route('pesanan.show', $pesanan->id))
            ->assertSessionHas('pesan');

        $this->assertSame(Order::SELESAI, $pesanan->fresh()->status);
        $this->assertNotNull($pesanan->fresh()->selesai_at);
    }

    public function test_pesanan_yang_belum_dikirim_tidak_bisa_dikonfirmasi_diterima(): void
    {
        [$pembeli, , $pesanan] = $this->pesananDiproses();

        $this->actingAs($pembeli)
            ->patch(route('pesanan.diterima', $pesanan->id))
            ->assertSessionHas('galat');

        $this->assertSame(Order::DIPROSES, $pesanan->fresh()->status);
    }

    public function test_pembeli_lain_tidak_bisa_mengonfirmasi_pesanan_orang_lain(): void
    {
        [, , $pesanan] = $this->pesananDikirim();

        $this->actingAs($this->buatPembeli())
            ->patch(route('pesanan.diterima', $pesanan->id))
            ->assertNotFound();

        $this->assertSame(Order::DIKIRIM, $pesanan->fresh()->status);
    }

    public function test_filter_perlu_diproses_dan_dikirim(): void
    {
        [, $penjual, $pesanan] = $this->buatPesananDibayar();

        $this->actingAs($penjual)
            ->get(route('penjual.pesanan.index', ['status' => 'perlu_diproses']))
            ->assertOk()
            ->assertSee($pesanan->kode);

        $this->actingAs($penjual)
            ->get(route('penjual.pesanan.index', ['status' => 'dikirim']))
            ->assertOk()
            ->assertDontSee($pesanan->kode);
    }

    public function test_dasbor_menghitung_pesanan_yang_perlu_diproses(): void
    {
        [, $penjual] = $this->buatPesananDibayar();

        $this->actingAs($penjual)
            ->get(route('dashboard'))
            ->assertViewHas('perluDiproses', 1)
            ->assertSee('perlu tindakan');
    }

    public function test_pesanan_belum_dibayar_melewati_batas_waktu_dibatalkan_dan_stok_kembali(): void
    {
        $pembeli = $this->buatPembeli();
        $pesanan = $this->buatPesanan($pembeli);
        $produk = $pesanan->items()->first()->product;
        $this->assertSame(8, $produk->stok);

        DB::table('orders')->where('id', $pesanan->id)->update(['updated_at' => now()->subHours(30)]);

        $this->artisan('pesanan:kedaluwarsa')->assertSuccessful();

        $this->assertSame(Order::DIBATALKAN, $pesanan->fresh()->status);
        $this->assertSame(10, $produk->fresh()->stok);
    }

    public function test_pesanan_baru_dan_yang_sudah_dibayar_tidak_dibatalkan_otomatis(): void
    {
        $baru = $this->buatPesanan($this->buatPembeli());
        [, , $dibayar] = $this->buatPesananDibayar();

        DB::table('orders')->whereIn('id', [$baru->id, $dibayar->id])->update(['updated_at' => now()->subHours(1)]);
        DB::table('orders')->where('id', $dibayar->id)->update(['updated_at' => now()->subHours(48)]);

        $this->artisan('pesanan:kedaluwarsa')->assertSuccessful();

        $this->assertSame(Order::MENUNGGU_PEMBAYARAN, $baru->fresh()->status);
        $this->assertSame(Order::DIBAYAR, $dibayar->fresh()->status);
    }
}
