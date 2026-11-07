<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Models\User;
use App\Notifications\VerifyAkunPeka;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\MembuatDataContoh;
use Tests\TestCase;

class ProfilTest extends TestCase
{
    use MembuatDataContoh;
    use RefreshDatabase;

    protected function pembeli(array $atribut = []): User
    {
        return $this->buatPembeli(array_merge([
            'name' => 'Siti Rohmah',
            'email' => 'siti@contoh.test',
            'phone' => '6281234567890',
        ], $atribut));
    }

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->get(route('profil'))->assertRedirect(route('login'));
        $this->put(route('profil.update'), [])->assertRedirect(route('login'));
        $this->put(route('profil.email'), [])->assertRedirect(route('login'));
        $this->put(route('profil.sandi'), [])->assertRedirect(route('login'));
    }

    public function test_email_belum_terverifikasi_diarahkan_ke_verifikasi(): void
    {
        $user = $this->pembeli(['email_verified_at' => null]);

        $this->actingAs($user)->get(route('profil'))->assertRedirect(route('verification.notice'));
    }

    public function test_profil_menampilkan_data_pengguna_dengan_nomor_lokal(): void
    {
        $this->actingAs($this->pembeli())
            ->get(route('profil'))
            ->assertOk()
            ->assertSee('Siti Rohmah')
            ->assertSee('siti@contoh.test')
            ->assertSee('081234567890')
            ->assertSee('Pembeli');
    }

    public function test_profil_menampilkan_inisial_nama(): void
    {
        $this->actingAs($this->pembeli(['name' => 'siti rohmah lestari']))
            ->get(route('profil'))
            ->assertViewHas('inisial', 'SR');
    }

    public function test_penjual_melihat_info_tokonya(): void
    {
        $penjual = $this->buatPenjual(Shop::MENUNGGU);

        $this->actingAs($penjual)
            ->get(route('profil'))
            ->assertOk()
            ->assertSee($penjual->shop->nama)
            ->assertSee('Penjual');
    }

    public function test_pembeli_tidak_melihat_bagian_toko(): void
    {
        $this->actingAs($this->pembeli())
            ->get(route('profil'))
            ->assertDontSee('Toko saya');
    }

    public function test_dasbor_punya_tautan_ke_profil(): void
    {
        $this->actingAs($this->pembeli())
            ->get(route('dashboard'))
            ->assertSee(route('profil'), false);
    }

    public function test_data_diri_berhasil_diperbarui_dan_nomor_dinormalisasi(): void
    {
        $user = $this->pembeli();

        $this->actingAs($user)
            ->put(route('profil.update'), ['name' => 'Siti Aminah', 'phone' => '0857-1111-2222'])
            ->assertRedirect(route('profil'))
            ->assertSessionHas('pesan');

        $user->refresh();
        $this->assertSame('Siti Aminah', $user->name);
        $this->assertSame('6285711112222', $user->phone);
    }

    public function test_nama_kosong_ditolak(): void
    {
        $user = $this->pembeli();

        $this->actingAs($user)
            ->put(route('profil.update'), ['name' => '', 'phone' => '081234567890'])
            ->assertSessionHasErrors('name');

        $this->assertSame('Siti Rohmah', $user->fresh()->name);
    }

    public function test_nama_terlalu_panjang_ditolak(): void
    {
        $this->actingAs($this->pembeli())
            ->put(route('profil.update'), ['name' => str_repeat('a', 101), 'phone' => '081234567890'])
            ->assertSessionHasErrors('name');
    }

    public function test_nomor_hp_kosong_atau_salah_ditolak(): void
    {
        $user = $this->pembeli();

        $this->actingAs($user)
            ->put(route('profil.update'), ['name' => 'Siti', 'phone' => ''])
            ->assertSessionHasErrors('phone');

        $this->actingAs($user)
            ->put(route('profil.update'), ['name' => 'Siti', 'phone' => '12345'])
            ->assertSessionHasErrors('phone');

        $this->actingAs($user)
            ->put(route('profil.update'), ['name' => 'Siti', 'phone' => 'abcdefghij'])
            ->assertSessionHasErrors('phone');

        $this->assertSame('6281234567890', $user->fresh()->phone);
    }

    public function test_data_diri_tidak_bisa_dipakai_mengubah_peran_atau_email(): void
    {
        $user = $this->pembeli();

        $this->actingAs($user)->put(route('profil.update'), [
            'name' => 'Siti Aminah',
            'phone' => '081234567890',
            'role' => User::ADMIN_DINAS,
            'email' => 'jahat@contoh.test',
            'email_verified_at' => null,
        ]);

        $user->refresh();
        $this->assertSame(User::PEMBELI, $user->role);
        $this->assertSame('siti@contoh.test', $user->email);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_email_berhasil_diganti_dengan_kata_sandi_benar(): void
    {
        Notification::fake();
        $user = $this->pembeli();

        $this->actingAs($user)
            ->put(route('profil.email'), ['email' => 'baru@contoh.test', 'sandi_email' => 'password'])
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('status', 'email-diganti');

        $user->refresh();
        $this->assertSame('baru@contoh.test', $user->email);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyAkunPeka::class);
    }

    public function test_email_tidak_berubah_jika_kata_sandi_salah(): void
    {
        Notification::fake();
        $user = $this->pembeli();

        $this->actingAs($user)
            ->put(route('profil.email'), ['email' => 'baru@contoh.test', 'sandi_email' => 'salah-total'])
            ->assertSessionHasErrors('sandi_email');

        $this->assertSame('siti@contoh.test', $user->fresh()->email);
        Notification::assertNothingSent();
    }

    public function test_email_wajib_menyertakan_kata_sandi(): void
    {
        $user = $this->pembeli();

        $this->actingAs($user)
            ->put(route('profil.email'), ['email' => 'baru@contoh.test'])
            ->assertSessionHasErrors('sandi_email');

        $this->assertSame('siti@contoh.test', $user->fresh()->email);
    }

    public function test_email_yang_sudah_dipakai_orang_lain_ditolak(): void
    {
        $this->pembeli(['email' => 'terpakai@contoh.test', 'name' => 'Orang Lain', 'phone' => '6281200000000']);
        $user = $this->pembeli(['email' => 'siti@contoh.test']);

        $this->actingAs($user)
            ->put(route('profil.email'), ['email' => 'terpakai@contoh.test', 'sandi_email' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertSame('siti@contoh.test', $user->fresh()->email);
    }

    public function test_email_sama_dengan_email_sekarang_ditolak(): void
    {
        Notification::fake();
        $user = $this->pembeli();

        $this->actingAs($user)
            ->put(route('profil.email'), ['email' => 'SITI@contoh.test', 'sandi_email' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertNotNull($user->fresh()->email_verified_at);
        Notification::assertNothingSent();
    }

    public function test_format_email_salah_ditolak(): void
    {
        $this->actingAs($this->pembeli())
            ->put(route('profil.email'), ['email' => 'bukan-email', 'sandi_email' => 'password'])
            ->assertSessionHasErrors('email');
    }

    public function test_rute_ganti_email_lama_ditutup_untuk_pengguna_terverifikasi(): void
    {
        $user = $this->pembeli();

        $this->actingAs($user)
            ->post(route('verification.change'), ['email' => 'bajak@contoh.test'])
            ->assertRedirect(route('profil'));

        $this->assertSame('siti@contoh.test', $user->fresh()->email);
    }

    public function test_rute_ganti_email_lama_masih_bisa_untuk_pengguna_belum_verifikasi(): void
    {
        Notification::fake();
        $user = $this->pembeli(['email_verified_at' => null]);

        $this->actingAs($user)
            ->post(route('verification.change'), ['email' => 'benar@contoh.test'])
            ->assertSessionHasNoErrors();

        $this->assertSame('benar@contoh.test', $user->fresh()->email);
    }

    public function test_kata_sandi_berhasil_diganti(): void
    {
        $user = $this->pembeli();

        $this->actingAs($user)
            ->put(route('profil.sandi'), [
                'kata_sandi_lama' => 'password',
                'password' => 'SandiBaru123',
                'password_confirmation' => 'SandiBaru123',
            ])
            ->assertRedirect(route('profil'))
            ->assertSessionHas('pesan');

        $this->assertTrue(Hash::check('SandiBaru123', $user->fresh()->password));
        $this->assertFalse(Hash::check('password', $user->fresh()->password));
    }

    public function test_kata_sandi_lama_salah_ditolak(): void
    {
        $user = $this->pembeli();

        $this->actingAs($user)
            ->put(route('profil.sandi'), [
                'kata_sandi_lama' => 'salah-total',
                'password' => 'SandiBaru123',
                'password_confirmation' => 'SandiBaru123',
            ])
            ->assertSessionHasErrors('kata_sandi_lama');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_konfirmasi_kata_sandi_harus_sama(): void
    {
        $this->actingAs($this->pembeli())
            ->put(route('profil.sandi'), [
                'kata_sandi_lama' => 'password',
                'password' => 'SandiBaru123',
                'password_confirmation' => 'SandiLain123',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_kata_sandi_lemah_ditolak(): void
    {
        $user = $this->pembeli();

        foreach (['Pendek1', 'semuahurufkecil1', 'SEMUAHURUFBESAR1', 'TanpaAngkaSama'] as $lemah) {
            $this->actingAs($user)
                ->put(route('profil.sandi'), [
                    'kata_sandi_lama' => 'password',
                    'password' => $lemah,
                    'password_confirmation' => $lemah,
                ])
                ->assertSessionHasErrors('password');
        }

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_kata_sandi_baru_tidak_boleh_sama_dengan_yang_lama(): void
    {
        $user = $this->pembeli(['password' => Hash::make('SandiLama123')]);

        $this->actingAs($user)
            ->put(route('profil.sandi'), [
                'kata_sandi_lama' => 'SandiLama123',
                'password' => 'SandiLama123',
                'password_confirmation' => 'SandiLama123',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_semua_isian_kata_sandi_wajib(): void
    {
        $this->actingAs($this->pembeli())
            ->put(route('profil.sandi'), [])
            ->assertSessionHasErrors(['kata_sandi_lama', 'password']);
    }

    public function test_pengguna_tidak_bisa_mengubah_data_pengguna_lain(): void
    {
        $lain = $this->pembeli(['email' => 'lain@contoh.test', 'name' => 'Orang Lain', 'phone' => '6281200000000']);
        $saya = $this->pembeli(['email' => 'saya@contoh.test']);

        $this->actingAs($saya)->put(route('profil.update'), [
            'name' => 'Diubah',
            'phone' => '081234567890',
            'id' => $lain->id,
        ]);

        $this->assertSame('Orang Lain', $lain->fresh()->name);
        $this->assertSame('Diubah', $saya->fresh()->name);
    }
}
