<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoGunadarmaTest extends TestCase
{
    use RefreshDatabase;

    public function test_berkas_logo_ada_di_folder_publik(): void
    {
        $this->assertFileExists(public_path('images/logo-gunadarma.png'));
    }

    public function test_halaman_masuk_dan_daftar_menampilkan_logo_gunadarma(): void
    {
        foreach (['login', 'daftar', 'daftar.pembeli', 'daftar.penjual'] as $rute) {
            $this->get(route($rute))
                ->assertOk()
                ->assertSee('images/logo-gunadarma.png', false)
                ->assertSee('Logo Universitas Gunadarma');
        }
    }
}
