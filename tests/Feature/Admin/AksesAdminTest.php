<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatAdmin;
use Tests\TestCase;

class AksesAdminTest extends TestCase
{
    use MembuatAdmin;
    use RefreshDatabase;

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->get(route('admin.dasbor'))->assertRedirect(route('login'));
        $this->get(route('admin.toko.index'))->assertRedirect(route('login'));
        $this->get(route('admin.ulasan.index'))->assertRedirect(route('login'));
    }

    public function test_pembeli_dan_penjual_tidak_bisa_masuk_panel_admin(): void
    {
        foreach ([$this->buatPembeli(), $this->buatPenjual()] as $user) {
            $this->actingAs($user)->get(route('admin.dasbor'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.toko.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.ulasan.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.kelompok.index'))->assertForbidden();
        }
    }

    public function test_admin_kelompok_tanpa_kelompok_ditolak(): void
    {
        $admin = $this->buatAdminKelompok();
        $admin->kelompok_id = null;
        $admin->save();

        $this->actingAs($admin)->get(route('admin.dasbor'))->assertForbidden();
    }

    public function test_admin_kelompok_dan_admin_dinas_bisa_membuka_dasbor(): void
    {
        $this->actingAs($this->buatAdminKelompok())->get(route('admin.dasbor'))->assertOk()->assertSee('PANEL ADMIN');
        $this->actingAs($this->buatAdminDinas())->get(route('admin.dasbor'))->assertOk()->assertSee('PANEL ADMIN');
    }

    public function test_admin_dengan_email_belum_terverifikasi_diarahkan_ke_verifikasi(): void
    {
        $admin = $this->buatAdminDinas();
        $admin->email_verified_at = null;
        $admin->save();

        $this->actingAs($admin)->get(route('admin.dasbor'))->assertRedirect(route('verification.notice'));
    }

    public function test_halaman_khusus_dinas_ditolak_untuk_admin_kelompok(): void
    {
        $admin = $this->buatAdminKelompok();
        $kelompok = $this->buatKelompok(['nama' => 'Lain']);

        $this->actingAs($admin)->get(route('admin.kelompok.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.kelompok.edit', $kelompok->id))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.kelompok.store'), ['nama' => 'Baru'])->assertForbidden();
        $this->actingAs($admin)->post(route('admin.kelompok.impor'), ['daftar' => 'Baru'])->assertForbidden();
        $this->actingAs($admin)->delete(route('admin.kelompok.destroy', $kelompok->id))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.admin-kelompok.index'))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.admin-kelompok.store'), [])->assertForbidden();

        $this->assertDatabaseMissing('kelompoks', ['nama' => 'Baru']);
    }

    public function test_menu_kelompok_operator_dan_laporan_hanya_tampil_untuk_dinas(): void
    {
        $this->actingAs($this->buatAdminDinas())
            ->get(route('admin.dasbor'))
            ->assertSee(route('admin.kelompok.index'), false)
            ->assertSee(route('admin.operator.index'), false)
            ->assertSee(route('admin.laporan.index'), false)
            ->assertDontSee(route('admin.admin-kelompok.index'), false);

        $this->actingAs($this->buatAdminKelompok())
            ->get(route('admin.dasbor'))
            ->assertDontSee(route('admin.kelompok.index'), false)
            ->assertDontSee(route('admin.operator.index'), false)
            ->assertDontSee(route('admin.laporan.index'), false);
    }

    public function test_dasbor_umum_mengarahkan_admin_ke_panel_admin(): void
    {
        $this->actingAs($this->buatAdminKelompok())->get(route('dashboard'))->assertRedirect(route('admin.dasbor'));
        $this->actingAs($this->buatAdminDinas())->get(route('dashboard'))->assertRedirect(route('admin.dasbor'));
    }

    public function test_dasbor_umum_untuk_penjual_dan_pembeli_tidak_berubah(): void
    {
        $this->actingAs($this->buatPenjual())->get(route('dashboard'))->assertOk();
        $this->actingAs($this->buatPembeli())->get(route('dashboard'))->assertOk();
    }

    public function test_peran_admin_dikenali_dengan_benar(): void
    {
        $this->assertTrue($this->buatAdminDinas()->adalahAdmin());
        $this->assertTrue($this->buatAdminKelompok()->adalahAdmin());
        $this->assertFalse($this->buatPenjual()->adalahAdmin());
        $this->assertFalse($this->buatPembeli()->adalahAdmin());
        $this->assertFalse(User::factory()->make(['role' => 'sembarang'])->adalahAdmin());
    }
}
