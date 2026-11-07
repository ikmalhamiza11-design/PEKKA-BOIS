<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\Shop;
use App\Notifications\HasilVerifikasiToko;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\MembuatAdmin;
use Tests\TestCase;

class VerifikasiTokoTest extends TestCase
{
    use MembuatAdmin;
    use RefreshDatabase;

    public function test_daftar_toko_secara_bawaan_menampilkan_yang_menunggu(): void
    {
        $kelompok = $this->buatKelompok();
        $menunggu = $this->buatPenjualDiKelompok($kelompok, Shop::MENUNGGU);
        $setuju = $this->buatPenjualDiKelompok($kelompok, Shop::TERVERIFIKASI);

        $this->actingAs($this->buatAdminDinas())
            ->get(route('admin.toko.index'))
            ->assertOk()
            ->assertSee($menunggu->shop->nama)
            ->assertDontSee($setuju->shop->nama);
    }

    public function test_filter_status_dan_semua(): void
    {
        $kelompok = $this->buatKelompok();
        $menunggu = $this->buatPenjualDiKelompok($kelompok, Shop::MENUNGGU);
        $setuju = $this->buatPenjualDiKelompok($kelompok, Shop::TERVERIFIKASI);
        $tolak = $this->buatPenjualDiKelompok($kelompok, Shop::DITOLAK);
        $admin = $this->buatAdminDinas();

        $this->actingAs($admin)->get(route('admin.toko.index', ['status' => 'terverifikasi']))
            ->assertSee($setuju->shop->nama)->assertDontSee($menunggu->shop->nama)->assertDontSee($tolak->shop->nama);

        $this->actingAs($admin)->get(route('admin.toko.index', ['status' => 'ditolak']))
            ->assertSee($tolak->shop->nama)->assertDontSee($setuju->shop->nama);

        $this->actingAs($admin)->get(route('admin.toko.index', ['status' => 'semua']))
            ->assertSee($menunggu->shop->nama)->assertSee($setuju->shop->nama)->assertSee($tolak->shop->nama);
    }

    public function test_status_tidak_valid_kembali_ke_menunggu(): void
    {
        $this->actingAs($this->buatAdminDinas())
            ->get(route('admin.toko.index', ['status' => 'sembarang']))
            ->assertOk()
            ->assertViewHas('status', Shop::MENUNGGU);
    }

    public function test_pencarian_berdasarkan_nama_toko_penjual_atau_email(): void
    {
        $kelompok = $this->buatKelompok();
        $a = $this->buatPenjualDiKelompok($kelompok);
        $a->shop->update(['nama' => 'Keripik Bu Ani']);
        $a->update(['name' => 'Ani Suryani', 'email' => 'ani@contoh.test']);
        $b = $this->buatPenjualDiKelompok($kelompok);
        $b->shop->update(['nama' => 'Dodol Bu Ratna']);
        $admin = $this->buatAdminDinas();

        $this->actingAs($admin)->get(route('admin.toko.index', ['q' => 'Keripik']))
            ->assertSee('Keripik Bu Ani')->assertDontSee('Dodol Bu Ratna');
        $this->actingAs($admin)->get(route('admin.toko.index', ['q' => 'Suryani']))
            ->assertSee('Keripik Bu Ani')->assertDontSee('Dodol Bu Ratna');
        $this->actingAs($admin)->get(route('admin.toko.index', ['q' => 'ani@contoh']))
            ->assertSee('Keripik Bu Ani')->assertDontSee('Dodol Bu Ratna');
    }

    public function test_admin_dinas_bisa_menyaring_per_kelompok(): void
    {
        $k1 = $this->buatKelompok(['nama' => 'Melati']);
        $k2 = $this->buatKelompok(['nama' => 'Mawar']);
        $a = $this->buatPenjualDiKelompok($k1);
        $b = $this->buatPenjualDiKelompok($k2);

        $this->actingAs($this->buatAdminDinas())
            ->get(route('admin.toko.index', ['kelompok' => $k2->id]))
            ->assertSee($b->shop->nama)
            ->assertDontSee($a->shop->nama);
    }

    public function test_admin_kelompok_hanya_melihat_toko_kelompoknya(): void
    {
        $milik = $this->buatKelompok(['nama' => 'Milik']);
        $lain = $this->buatKelompok(['nama' => 'Lain']);
        $tokoMilik = $this->buatPenjualDiKelompok($milik);
        $tokoLain = $this->buatPenjualDiKelompok($lain);
        $admin = $this->buatAdminKelompok($milik);

        $this->actingAs($admin)->get(route('admin.toko.index'))
            ->assertSee($tokoMilik->shop->nama)
            ->assertDontSee($tokoLain->shop->nama);

        $this->actingAs($admin)->get(route('admin.toko.index', ['kelompok' => $lain->id]))
            ->assertDontSee($tokoLain->shop->nama);
    }

    public function test_admin_kelompok_tidak_bisa_membuka_atau_memutuskan_toko_kelompok_lain(): void
    {
        $milik = $this->buatKelompok(['nama' => 'Milik']);
        $lain = $this->buatKelompok(['nama' => 'Lain']);
        $tokoLain = $this->buatPenjualDiKelompok($lain);
        $admin = $this->buatAdminKelompok($milik);

        $this->actingAs($admin)->get(route('admin.toko.show', $tokoLain->shop->id))->assertNotFound();
        $this->actingAs($admin)->patch(route('admin.toko.setujui', $tokoLain->shop->id))->assertNotFound();
        $this->actingAs($admin)->patch(route('admin.toko.tolak', $tokoLain->shop->id), ['alasan' => 'Tidak sesuai data'])->assertNotFound();

        $this->assertSame(Shop::MENUNGGU, $tokoLain->shop->fresh()->status);
    }

    public function test_halaman_detail_menampilkan_data_penjual_dan_rekening(): void
    {
        $penjual = $this->buatPenjual(Shop::MENUNGGU);
        $penjual->update(['phone' => '6281234567890']);
        $penjual->shop->update(['nama_bank' => 'BRI', 'nomor_rekening' => '1234567890', 'atas_nama' => 'Siti Aminah']);

        $this->actingAs($this->buatAdminDinas())
            ->get(route('admin.toko.show', $penjual->shop->id))
            ->assertOk()
            ->assertSee($penjual->shop->nama)
            ->assertSee($penjual->email)
            ->assertSee('081234567890')
            ->assertSee('BRI')
            ->assertSee('Siti Aminah');
    }

    public function test_detail_memperingatkan_jika_rekening_belum_diisi(): void
    {
        $penjual = $this->buatPenjual(Shop::MENUNGGU);

        $this->actingAs($this->buatAdminDinas())
            ->get(route('admin.toko.show', $penjual->shop->id))
            ->assertSee('Rekening belum diisi');
    }

    public function test_menyetujui_toko_mengubah_status_dan_mencatat_admin(): void
    {
        Notification::fake();
        $kelompok = $this->buatKelompok();
        $penjual = $this->buatPenjualDiKelompok($kelompok);
        $admin = $this->buatAdminKelompok($kelompok);

        $this->actingAs($admin)
            ->patch(route('admin.toko.setujui', $penjual->shop->id))
            ->assertRedirect(route('admin.toko.show', $penjual->shop->id))
            ->assertSessionHas('pesan');

        $toko = $penjual->shop->fresh();
        $this->assertSame(Shop::TERVERIFIKASI, $toko->status);
        $this->assertSame($admin->id, $toko->diverifikasi_oleh);
        $this->assertNotNull($toko->diverifikasi_at);
        $this->assertNull($toko->alasan_penolakan);
        Notification::assertSentTo($penjual, HasilVerifikasiToko::class);
    }

    public function test_produk_toko_yang_disetujui_tampil_di_katalog(): void
    {
        Notification::fake();
        $penjual = $this->buatPenjual(Shop::MENUNGGU);
        $produk = $this->buatProduk($penjual->shop);

        $this->assertFalse(Product::tampil()->whereKey($produk->id)->exists());

        $this->actingAs($this->buatAdminDinas())->patch(route('admin.toko.setujui', $penjual->shop->id));

        $this->assertTrue(Product::tampil()->whereKey($produk->id)->exists());
        $this->get(route('katalog'))->assertSee($produk->nama);
    }

    public function test_menyetujui_toko_yang_sudah_terverifikasi_ditolak_sistem(): void
    {
        Notification::fake();
        $penjual = $this->buatPenjual(Shop::TERVERIFIKASI);

        $this->actingAs($this->buatAdminDinas())
            ->patch(route('admin.toko.setujui', $penjual->shop->id))
            ->assertSessionHas('galat');

        Notification::assertNothingSent();
    }

    public function test_menolak_toko_butuh_alasan(): void
    {
        Notification::fake();
        $penjual = $this->buatPenjual(Shop::MENUNGGU);
        $admin = $this->buatAdminDinas();

        $this->actingAs($admin)->patch(route('admin.toko.tolak', $penjual->shop->id), [])->assertSessionHasErrors('alasan');
        $this->actingAs($admin)->patch(route('admin.toko.tolak', $penjual->shop->id), ['alasan' => 'abc'])->assertSessionHasErrors('alasan');
        $this->actingAs($admin)->patch(route('admin.toko.tolak', $penjual->shop->id), ['alasan' => str_repeat('a', 301)])->assertSessionHasErrors('alasan');

        $this->assertSame(Shop::MENUNGGU, $penjual->shop->fresh()->status);
        Notification::assertNothingSent();
    }

    public function test_menolak_toko_menyimpan_alasan_dan_memberi_tahu_penjual(): void
    {
        Notification::fake();
        $penjual = $this->buatPenjual(Shop::MENUNGGU);
        $admin = $this->buatAdminDinas();

        $this->actingAs($admin)
            ->patch(route('admin.toko.tolak', $penjual->shop->id), ['alasan' => 'Nama usaha belum sesuai data kelompok.'])
            ->assertRedirect(route('admin.toko.show', $penjual->shop->id))
            ->assertSessionHas('pesan');

        $toko = $penjual->shop->fresh();
        $this->assertSame(Shop::DITOLAK, $toko->status);
        $this->assertSame('Nama usaha belum sesuai data kelompok.', $toko->alasan_penolakan);
        $this->assertSame($admin->id, $toko->diverifikasi_oleh);
        Notification::assertSentTo($penjual, HasilVerifikasiToko::class);
    }

    public function test_toko_yang_ditolak_bisa_disetujui_kemudian_dan_alasan_dihapus(): void
    {
        Notification::fake();
        $penjual = $this->buatPenjual(Shop::DITOLAK);
        $penjual->shop->forceFill(['alasan_penolakan' => 'Data belum lengkap.'])->save();
        $this->assertSame('Data belum lengkap.', $penjual->shop->fresh()->alasan_penolakan);

        $this->actingAs($this->buatAdminDinas())->patch(route('admin.toko.setujui', $penjual->shop->id));

        $toko = $penjual->shop->fresh();
        $this->assertSame(Shop::TERVERIFIKASI, $toko->status);
        $this->assertNull($toko->alasan_penolakan);
    }

    public function test_menolak_toko_yang_sudah_ditolak_ditolak_sistem(): void
    {
        Notification::fake();
        $penjual = $this->buatPenjual(Shop::DITOLAK);

        $this->actingAs($this->buatAdminDinas())
            ->patch(route('admin.toko.tolak', $penjual->shop->id), ['alasan' => 'Alasan baru yang cukup panjang'])
            ->assertSessionHas('galat');

        Notification::assertNothingSent();
    }

    public function test_mencabut_verifikasi_menyembunyikan_produk_dari_katalog(): void
    {
        Notification::fake();
        $penjual = $this->buatPenjual(Shop::TERVERIFIKASI);
        $produk = $this->buatProduk($penjual->shop);

        $this->actingAs($this->buatAdminDinas())
            ->patch(route('admin.toko.tolak', $penjual->shop->id), ['alasan' => 'Ada laporan produk tidak sesuai.']);

        $this->assertFalse(Product::tampil()->whereKey($produk->id)->exists());
        $this->get(route('katalog'))->assertDontSee($produk->nama);
    }

    public function test_dasbor_penjual_menampilkan_alasan_penolakan(): void
    {
        $penjual = $this->buatPenjual(Shop::DITOLAK);
        $penjual->shop->forceFill(['alasan_penolakan' => 'Nama usaha belum sesuai data kelompok.'])->save();

        $this->actingAs($penjual)
            ->get(route('dashboard'))
            ->assertSee('Toko belum disetujui')
            ->assertSee('Nama usaha belum sesuai data kelompok.');
    }

    public function test_gagal_kirim_email_tidak_membatalkan_keputusan(): void
    {
        $penjual = $this->buatPenjual(Shop::MENUNGGU);
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 1]);

        $this->actingAs($this->buatAdminDinas())
            ->patch(route('admin.toko.setujui', $penjual->shop->id))
            ->assertRedirect(route('admin.toko.show', $penjual->shop->id))
            ->assertSessionHas('pesan');

        $this->assertSame(Shop::TERVERIFIKASI, $penjual->shop->fresh()->status);
    }

    public function test_perintah_terminal_tetap_berfungsi_dan_mengosongkan_alasan(): void
    {
        $penjual = $this->buatPenjual(Shop::DITOLAK);
        $penjual->shop->forceFill(['alasan_penolakan' => 'Data belum lengkap.'])->save();
        $this->assertSame('Data belum lengkap.', $penjual->shop->fresh()->alasan_penolakan);

        $this->artisan('toko:setujui', ['email' => $penjual->email])->assertSuccessful();

        $toko = $penjual->shop->fresh();
        $this->assertSame(Shop::TERVERIFIKASI, $toko->status);
        $this->assertNull($toko->alasan_penolakan);
    }
}
