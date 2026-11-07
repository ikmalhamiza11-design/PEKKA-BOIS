<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\MembuatAdmin;
use Tests\TestCase;

class KelolaOperatorTest extends TestCase
{
    use MembuatAdmin;
    use RefreshDatabase;

    private function data(array $ganti = []): array
    {
        return array_merge([
            'name' => 'Operator Dua',
            'email' => 'operator2@contoh.test',
            'phone' => '0812-3456-7890',
            'password' => 'KataSandi123',
            'password_confirmation' => 'KataSandi123',
        ], $ganti);
    }

    public function test_hanya_operator_yang_bisa_mengelola_operator(): void
    {
        $this->get(route('admin.operator.index'))->assertRedirect(route('login'));
        $this->actingAs($this->buatPembeli())->get(route('admin.operator.index'))->assertForbidden();
        $this->actingAs($this->buatAdminKelompok())->get(route('admin.operator.index'))->assertForbidden();
        $this->actingAs($this->buatAdminDinas())->get(route('admin.operator.index'))->assertOk();
    }

    public function test_operator_bisa_membuat_operator_baru_yang_langsung_aktif(): void
    {
        $this->actingAs($this->buatAdminDinas())
            ->post(route('admin.operator.store'), $this->data())
            ->assertRedirect(route('admin.operator.index'))
            ->assertSessionHas('pesan');

        $akun = User::where('email', 'operator2@contoh.test')->firstOrFail();

        $this->assertSame(User::ADMIN_DINAS, $akun->role);
        $this->assertSame('6281234567890', $akun->phone);
        $this->assertNotNull($akun->email_verified_at);
        $this->assertTrue(Hash::check('KataSandi123', $akun->password));
    }

    public function test_operator_baru_bisa_masuk_ke_panel(): void
    {
        $this->actingAs($this->buatAdminDinas())->post(route('admin.operator.store'), $this->data());
        auth()->logout();

        $this->post(route('login.store'), ['email' => 'operator2@contoh.test', 'password' => 'KataSandi123'])
            ->assertRedirect(route('dashboard'));

        $this->get(route('admin.laporan.index'))->assertOk();
    }

    public function test_email_ganda_dan_sandi_lemah_ditolak(): void
    {
        $admin = $this->buatAdminDinas(['email' => 'operator2@contoh.test']);

        $this->actingAs($admin)->post(route('admin.operator.store'), $this->data())->assertSessionHasErrors('email');
        $this->actingAs($admin)
            ->post(route('admin.operator.store'), $this->data(['email' => 'baru@contoh.test', 'password' => 'lemah', 'password_confirmation' => 'lemah']))
            ->assertSessionHasErrors('password');
    }

    public function test_operator_bisa_menghapus_operator_lain_tetapi_tidak_dirinya_sendiri(): void
    {
        $saya = $this->buatAdminDinas();
        $lain = $this->buatAdminDinas();

        $this->actingAs($saya)->delete(route('admin.operator.destroy', $saya->id))->assertSessionHas('galat');
        $this->assertNotNull($saya->fresh());

        $this->actingAs($saya)->delete(route('admin.operator.destroy', $lain->id))->assertSessionHas('pesan');
        $this->assertNull($lain->fresh());
    }

    public function test_menu_admin_menampilkan_laporan_dan_operator_bukan_admin_kelompok(): void
    {
        $this->actingAs($this->buatAdminDinas())
            ->get(route('admin.dasbor'))
            ->assertSee('Laporan Masuk')
            ->assertSee('Operator')
            ->assertDontSee('Admin Kelompok');
    }
}
