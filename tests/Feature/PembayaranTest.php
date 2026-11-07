<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\MembuatPesananBayar;
use Tests\TestCase;

class PembayaranTest extends TestCase
{
    use MembuatPesananBayar;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->post(route('pembayaran.store', 1))->assertRedirect(route('login'));
        $this->get(route('pembayaran.bukti', 1))->assertRedirect(route('login'));
    }

    public function test_halaman_pesanan_menampilkan_rekening_penjual(): void
    {
        $pembeli = $this->buatPembeli();
        $pesanan = $this->buatPesanan($pembeli);

        $this->actingAs($pembeli)
            ->get(route('pesanan.show', $pesanan->id))
            ->assertOk()
            ->assertSee('1234567890')
            ->assertSee('Kirim Bukti Transfer');
    }

    public function test_halaman_pesanan_memberi_tahu_jika_rekening_belum_diisi(): void
    {
        $pembeli = $this->buatPembeli();
        $pesanan = $this->buatPesanan($pembeli, false);

        $this->actingAs($pembeli)
            ->get(route('pesanan.show', $pesanan->id))
            ->assertOk()
            ->assertSee('Rekening penjual belum tersedia')
            ->assertDontSee('Kirim Bukti Transfer');
    }

    public function test_unggah_bukti_mengubah_status_dan_menyimpan_pembayaran(): void
    {
        $pembeli = $this->buatPembeli();
        $pesanan = $this->buatPesanan($pembeli);

        $this->actingAs($pembeli)
            ->post(route('pembayaran.store', $pesanan->id), [
                'nama_pengirim' => 'Budi Santoso',
                'bukti' => UploadedFile::fake()->image('bukti.jpg', 600, 800),
            ])
            ->assertRedirect(route('pesanan.show', $pesanan->id))
            ->assertSessionHas('pesan');

        $pembayaran = Payment::first();

        $this->assertSame(Order::MENUNGGU_VERIFIKASI, $pesanan->fresh()->status);
        $this->assertSame(Payment::MENUNGGU, $pembayaran->status);
        $this->assertSame(20000, $pembayaran->jumlah);
        $this->assertSame('Budi Santoso', $pembayaran->nama_pengirim);
        Storage::disk('local')->assertExists($pembayaran->bukti);
    }

    public function test_bukti_wajib_diisi_dan_harus_berupa_gambar(): void
    {
        $pembeli = $this->buatPembeli();
        $pesanan = $this->buatPesanan($pembeli);

        $this->actingAs($pembeli)
            ->post(route('pembayaran.store', $pesanan->id), ['nama_pengirim' => 'Budi'])
            ->assertSessionHasErrors('bukti');

        $this->actingAs($pembeli)
            ->post(route('pembayaran.store', $pesanan->id), [
                'nama_pengirim' => 'Budi',
                'bukti' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('bukti');

        $this->assertSame(0, Payment::count());
        $this->assertSame(Order::MENUNGGU_PEMBAYARAN, $pesanan->fresh()->status);
    }

    public function test_nama_pengirim_wajib_diisi(): void
    {
        $pembeli = $this->buatPembeli();
        $pesanan = $this->buatPesanan($pembeli);

        $this->actingAs($pembeli)
            ->post(route('pembayaran.store', $pesanan->id), [
                'bukti' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertSessionHasErrors('nama_pengirim');
    }

    public function test_tidak_bisa_unggah_jika_rekening_belum_diisi(): void
    {
        $pembeli = $this->buatPembeli();
        $pesanan = $this->buatPesanan($pembeli, false);

        $this->kirimBukti($pembeli, $pesanan);

        $this->assertSame(0, Payment::count());
        $this->assertSame(Order::MENUNGGU_PEMBAYARAN, $pesanan->fresh()->status);
    }

    public function test_tidak_bisa_unggah_dua_kali_untuk_pesanan_yang_sama(): void
    {
        $pembeli = $this->buatPembeli();
        $pesanan = $this->buatPesanan($pembeli);

        $this->kirimBukti($pembeli, $pesanan);
        $this->kirimBukti($pembeli, $pesanan);

        $this->assertSame(1, Payment::count());
    }

    public function test_tidak_bisa_unggah_untuk_pesanan_dibatalkan(): void
    {
        $pembeli = $this->buatPembeli();
        $pesanan = $this->buatPesanan($pembeli);
        $pesanan->update(['status' => Order::DIBATALKAN]);

        $this->kirimBukti($pembeli, $pesanan);

        $this->assertSame(0, Payment::count());
        $this->assertSame(Order::DIBATALKAN, $pesanan->fresh()->status);
    }

    public function test_tidak_bisa_unggah_untuk_pesanan_milik_orang_lain(): void
    {
        $pemilik = $this->buatPembeli();
        $pesanan = $this->buatPesanan($pemilik);

        $this->actingAs($this->buatPembeli())
            ->post(route('pembayaran.store', $pesanan->id), [
                'nama_pengirim' => 'Orang Lain',
                'bukti' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertNotFound();

        $this->assertSame(0, Payment::count());
    }

    public function test_pesanan_menunggu_verifikasi_tidak_bisa_dibatalkan(): void
    {
        $pembeli = $this->buatPembeli();
        $pesanan = $this->buatPesanan($pembeli);
        $this->kirimBukti($pembeli, $pesanan);

        $this->actingAs($pembeli)
            ->patch(route('pesanan.batal', $pesanan->id))
            ->assertSessionHas('galat');

        $this->assertSame(Order::MENUNGGU_VERIFIKASI, $pesanan->fresh()->status);
    }

    public function test_bukti_hanya_bisa_dilihat_pembeli_dan_penjual_pesanan(): void
    {
        [$pembeli, $penjual, , $pembayaran] = $this->buatPesananDenganBukti();

        $this->actingAs($pembeli)->get(route('pembayaran.bukti', $pembayaran->id))->assertOk();
        $this->actingAs($penjual)->get(route('pembayaran.bukti', $pembayaran->id))->assertOk();
        $this->actingAs($this->buatPembeli())->get(route('pembayaran.bukti', $pembayaran->id))->assertNotFound();
        $this->actingAs($this->buatPenjual())->get(route('pembayaran.bukti', $pembayaran->id))->assertNotFound();
    }
}
