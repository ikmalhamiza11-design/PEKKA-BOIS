<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\MembuatAdmin;
use Tests\TestCase;

class KelolaAdminKelompokTest extends TestCase
{
    use MembuatAdmin;
    use RefreshDatabase;

    private ?int $kelompokId = null;

    private function data(array $atribut = []): array
    {
        $this->kelompokId ??= $this->buatKelompok(['nama' => 'Melati'])->id;

        return array_merge([
            'name' => 'Ibu Ketua Melati',
            'email' => 'ketua.melati@contoh.test',
            'phone' => '0812-3456-7890',
            'kelompok_id' => $this->kelompokId,
            'password' => 'KataSandi123',
            'password_confirmation' => 'KataSandi123',
        ], $atribut);
    }

    public function test_halaman_menampilkan_akun_yang_ada(): void
    {
        $kelompok = $this->buatKelompok(['nama' => 'Melati']);
        $this->buatAdminKelompok($kelompok, ['name' => 'Ibu Ani', 'email' => 'ani@contoh.test']);

        $this->actingAs($this->buatAdminDinas())
            ->get(route('admin.admin-kelompok.index'))
            ->assertOk()
            ->assertSee('Ibu Ani')
            ->assertSee('ani@contoh.test');
    }

    public function test_halaman_meminta_kelompok_dulu_jika_belum_ada(): void
    {
        $this->actingAs($this->buatAdminDinas())
            ->get(route('admin.admin-kelompok.index'))
            ->assertOk()
            ->assertSee('Belum ada kelompok');
    }

    public function test_admin_dinas_membuat_akun_admin_kelompok(): void
    {
        $data = $this->data();

        $this->actingAs($this->buatAdminDinas())
            ->post(route('admin.admin-kelompok.store'), $data)
            ->assertRedirect(route('admin.admin-kelompok.index'))
            ->assertSessionHas('pesan');

        $akun = User::where('email', 'ketua.melati@contoh.test')->firstOrFail();
        $this->assertSame(User::ADMIN_KELOMPOK, $akun->role);
        $this->assertSame((int) $data['kelompok_id'], $akun->kelompok_id);
        $this->assertSame('6281234567890', $akun->phone);
        $this->assertNotNull($akun->email_verified_at);
        $this->assertTrue(Hash::check('KataSandi123', $akun->password));
    }

    public function test_akun_baru_bisa_langsung_masuk_panel_admin(): void
    {
        $this->actingAs($this->buatAdminDinas())->post(route('admin.admin-kelompok.store'), $this->data());
        auth()->logout();

        $this->post(route('login.store'), ['email' => 'ketua.melati@contoh.test', 'password' => 'KataSandi123'])
            ->assertRedirect(route('dashboard'));

        $this->get(route('admin.dasbor'))->assertOk();
    }

    public function test_nomor_hp_boleh_dikosongkan_tetapi_harus_benar_jika_diisi(): void
    {
        $admin = $this->buatAdminDinas();

        $this->actingAs($admin)->post(route('admin.admin-kelompok.store'), $this->data(['phone' => '']))->assertSessionHasNoErrors();
        $this->assertNull(User::where('email', 'ketua.melati@contoh.test')->first()->phone);

        $this->actingAs($admin)
            ->post(route('admin.admin-kelompok.store'), $this->data(['email' => 'lain@contoh.test', 'phone' => '123']))
            ->assertSessionHasErrors('phone');
    }

    public function test_validasi_akun_baru(): void
    {
        $admin = $this->buatAdminDinas();
        $this->buatPembeli(['email' => 'dipakai@contoh.test']);

        foreach ([
            ['name' => ''],
            ['email' => 'bukan-email'],
            ['email' => 'dipakai@contoh.test'],
            ['kelompok_id' => ''],
            ['kelompok_id' => 99999],
            ['password' => 'Pendek1', 'password_confirmation' => 'Pendek1'],
            ['password' => 'tanpaangka', 'password_confirmation' => 'tanpaangka'],
            ['password' => 'KataSandi123', 'password_confirmation' => 'Berbeda123'],
        ] as $salah) {
            $this->actingAs($admin)
                ->post(route('admin.admin-kelompok.store'), $this->data($salah))
                ->assertSessionHasErrors(array_key_first($salah));
        }

        $this->assertSame(0, User::where('role', User::ADMIN_KELOMPOK)->count());
    }

    public function test_peran_tidak_bisa_dipalsukan_lewat_isian(): void
    {
        $this->actingAs($this->buatAdminDinas())
            ->post(route('admin.admin-kelompok.store'), $this->data(['role' => User::ADMIN_DINAS]));

        $this->assertSame(User::ADMIN_KELOMPOK, User::where('email', 'ketua.melati@contoh.test')->first()->role);
    }

    public function test_menghapus_akun_admin_kelompok(): void
    {
        $akun = $this->buatAdminKelompok();

        $this->actingAs($this->buatAdminDinas())
            ->delete(route('admin.admin-kelompok.destroy', $akun->id))
            ->assertRedirect(route('admin.admin-kelompok.index'))
            ->assertSessionHas('pesan');

        $this->assertDatabaseMissing('users', ['id' => $akun->id]);
    }

    public function test_hanya_akun_admin_kelompok_yang_bisa_dihapus_lewat_halaman_ini(): void
    {
        $admin = $this->buatAdminDinas();
        $penjual = $this->buatPenjual();
        $pembeli = $this->buatPembeli();

        foreach ([$admin, $penjual, $pembeli] as $target) {
            $this->actingAs($admin)->delete(route('admin.admin-kelompok.destroy', $target->id))->assertNotFound();
            $this->assertDatabaseHas('users', ['id' => $target->id]);
        }
    }
}
