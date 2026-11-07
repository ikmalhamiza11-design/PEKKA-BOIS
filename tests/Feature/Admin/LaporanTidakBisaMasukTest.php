<?php

namespace Tests\Feature\Admin;

use App\Models\LaporanAkun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\MembuatAdmin;
use Tests\TestCase;

class LaporanTidakBisaMasukTest extends TestCase
{
    use MembuatAdmin;
    use RefreshDatabase;

    private function kirimLaporan(array $ganti = []): void
    {
        $this->post(route('bantuan.masuk.simpan'), array_merge([
            'nama' => 'Ibu Siti',
            'phone' => '0812-3456-7890',
            'email' => '',
            'keterangan' => 'Lupa sandi email',
        ], $ganti));
    }

    public function test_formulir_bisa_dibuka_tanpa_login(): void
    {
        $this->get(route('bantuan.masuk'))->assertOk()->assertSee('Tidak bisa masuk?');
    }

    public function test_laporan_dicatat_dan_dicocokkan_lewat_email(): void
    {
        $akun = $this->buatPembeli(['email' => 'siti@contoh.test']);

        $this->kirimLaporan(['email' => 'SITI@contoh.test']);

        $laporan = LaporanAkun::firstOrFail();
        $this->assertSame('6281234567890', $laporan->phone);
        $this->assertSame($akun->id, $laporan->user_id);
        $this->assertSame(LaporanAkun::BARU, $laporan->status);
    }

    public function test_laporan_dicocokkan_lewat_nomor_wa_jika_email_kosong(): void
    {
        $akun = $this->buatPembeli(['phone' => '6281234567890']);

        $this->kirimLaporan();

        $this->assertSame($akun->id, LaporanAkun::firstOrFail()->user_id);
    }

    public function test_akun_admin_tidak_pernah_dicocokkan(): void
    {
        $this->buatAdminDinas(['email' => 'admin@contoh.test']);

        $this->kirimLaporan(['email' => 'admin@contoh.test']);

        $this->assertNull(LaporanAkun::firstOrFail()->user_id);
    }

    public function test_laporan_ganda_dari_nomor_sama_dalam_sehari_tidak_dicatat_dua_kali(): void
    {
        $this->kirimLaporan();
        $this->kirimLaporan();

        $this->assertSame(1, LaporanAkun::count());
    }

    public function test_formulir_menolak_data_tidak_lengkap(): void
    {
        $this->post(route('bantuan.masuk.simpan'), ['nama' => '', 'phone' => '123'])
            ->assertSessionHasErrors(['nama', 'phone']);

        $this->assertSame(0, LaporanAkun::count());
    }

    public function test_hanya_operator_yang_bisa_melihat_laporan(): void
    {
        $this->get(route('admin.laporan.index'))->assertRedirect(route('login'));
        $this->actingAs($this->buatPembeli())->get(route('admin.laporan.index'))->assertForbidden();
        $this->actingAs($this->buatAdminKelompok())->get(route('admin.laporan.index'))->assertForbidden();
        $this->actingAs($this->buatAdminDinas())->get(route('admin.laporan.index'))->assertOk();
    }

    public function test_operator_membuat_sandi_sementara_dan_akun_wajib_ganti_sandi(): void
    {
        $akun = $this->buatPembeli(['email' => 'siti@contoh.test']);
        $operator = $this->buatAdminDinas();
        $this->kirimLaporan(['email' => 'siti@contoh.test']);
        $laporan = LaporanAkun::firstOrFail();

        $this->actingAs($operator)->post(route('admin.laporan.sandi', $laporan->id))->assertSessionHas('sandi_sementara');

        $sandi = session('sandi_sementara')['sandi'];
        $akun = $akun->fresh();

        $this->assertSame(8, strlen($sandi));
        $this->assertTrue(Hash::check($sandi, $akun->password));
        $this->assertFalse(Hash::check('password', $akun->password));
        $this->assertTrue($akun->wajib_ganti_sandi);

        $laporan = $laporan->fresh();
        $this->assertSame(LaporanAkun::SELESAI, $laporan->status);
        $this->assertSame($operator->id, $laporan->ditangani_oleh);
    }

    public function test_sandi_sementara_tidak_dibuat_untuk_laporan_tanpa_akun(): void
    {
        $this->kirimLaporan();
        $laporan = LaporanAkun::firstOrFail();

        $this->actingAs($this->buatAdminDinas())
            ->post(route('admin.laporan.sandi', $laporan->id))
            ->assertSessionHas('galat')
            ->assertSessionMissing('sandi_sementara');

        $this->assertSame(LaporanAkun::BARU, $laporan->fresh()->status);
    }

    public function test_operator_bisa_mencocokkan_laporan_secara_manual(): void
    {
        $akun = $this->buatPenjual('terverifikasi', ['email' => 'toko@contoh.test']);
        $this->kirimLaporan();
        $laporan = LaporanAkun::firstOrFail();

        $this->actingAs($this->buatAdminDinas())
            ->post(route('admin.laporan.cocokkan', $laporan->id), ['email_akun' => 'toko@contoh.test'])
            ->assertSessionHas('pesan');

        $this->assertSame($akun->id, $laporan->fresh()->user_id);
    }

    public function test_operator_bisa_menandai_email_terverifikasi(): void
    {
        $akun = $this->buatPembeli(['email' => 'siti@contoh.test', 'email_verified_at' => null]);
        $this->kirimLaporan(['email' => 'siti@contoh.test']);
        $laporan = LaporanAkun::firstOrFail();

        $this->actingAs($this->buatAdminDinas())
            ->post(route('admin.laporan.verifikasi', $laporan->id))
            ->assertSessionHas('pesan');

        $this->assertNotNull($akun->fresh()->email_verified_at);
        $this->assertSame(LaporanAkun::SELESAI, $laporan->fresh()->status);
    }

    public function test_akun_wajib_ganti_sandi_dialihkan_sampai_sandi_baru_dibuat(): void
    {
        $akun = $this->buatPembeli();
        $akun->forceFill(['wajib_ganti_sandi' => true])->save();

        $this->actingAs($akun)->get(route('beranda'))->assertRedirect(route('sandi.wajib'));
        $this->actingAs($akun)->get(route('dashboard'))->assertRedirect(route('sandi.wajib'));
        $this->actingAs($akun)->get(route('sandi.wajib'))->assertOk();
    }

    public function test_sandi_baru_menghapus_kewajiban_dan_harus_beda_dari_sandi_sementara(): void
    {
        $akun = $this->buatPembeli();
        $akun->forceFill(['wajib_ganti_sandi' => true])->save();

        $this->actingAs($akun)
            ->put(route('sandi.wajib.simpan'), ['password' => 'password', 'password_confirmation' => 'password'])
            ->assertSessionHasErrors('password');
        $this->assertTrue($akun->fresh()->wajib_ganti_sandi);

        $this->actingAs($akun)
            ->put(route('sandi.wajib.simpan'), ['password' => 'SandiBaru123', 'password_confirmation' => 'SandiBaru123'])
            ->assertRedirect(route('beranda'));

        $akun = $akun->fresh();
        $this->assertFalse($akun->wajib_ganti_sandi);
        $this->assertTrue(Hash::check('SandiBaru123', $akun->password));
        $this->actingAs($akun)->get(route('beranda'))->assertOk();
    }

    public function test_akun_wajib_ganti_sandi_tetap_bisa_keluar(): void
    {
        $akun = $this->buatPembeli();
        $akun->forceFill(['wajib_ganti_sandi' => true])->save();

        $this->actingAs($akun)->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_halaman_sandi_wajib_dialihkan_jika_tidak_diwajibkan(): void
    {
        $this->actingAs($this->buatPembeli())->get(route('sandi.wajib'))->assertRedirect(route('dashboard'));
    }
}
