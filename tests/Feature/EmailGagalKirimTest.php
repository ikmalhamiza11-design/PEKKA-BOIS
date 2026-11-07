<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatDataContoh;
use Tests\TestCase;

class EmailGagalKirimTest extends TestCase
{
    use MembuatDataContoh;
    use RefreshDatabase;

    private function rusakkanPengirimEmail(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => '127.0.0.1',
            'mail.mailers.smtp.port' => 1,
            'mail.mailers.smtp.timeout' => 2,
        ]);
    }

    public function test_daftar_tetap_berhasil_walau_email_gagal_terkirim(): void
    {
        $this->rusakkanPengirimEmail();

        $this->post(route('daftar.pembeli.simpan'), [
            'name' => 'Ibu Contoh',
            'email' => 'ibu@contoh.test',
            'phone' => '081234567890',
            'password' => 'KataSandi123',
            'password_confirmation' => 'KataSandi123',
            'terms' => '1',
        ])
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('status', 'email-gagal');

        $this->assertDatabaseHas('users', ['email' => 'ibu@contoh.test']);
    }

    public function test_kirim_ulang_verifikasi_tidak_error_saat_email_gagal(): void
    {
        $this->rusakkanPengirimEmail();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertSessionHas('status', 'email-gagal');
    }

    public function test_lupa_kata_sandi_tidak_error_saat_email_gagal(): void
    {
        $this->rusakkanPengirimEmail();

        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status', 'email-gagal');
    }
}
