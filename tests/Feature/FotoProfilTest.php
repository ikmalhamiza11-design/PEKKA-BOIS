<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\MembuatDataContoh;
use Tests\TestCase;

class FotoProfilTest extends TestCase
{
    use MembuatDataContoh;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('Ekstensi GD belum aktif.');
        }

        Storage::fake('public');
    }

    public function test_pembeli_bisa_mengunggah_foto_profil(): void
    {
        $pembeli = $this->buatPembeli();

        $this->actingAs($pembeli)
            ->put(route('profil.foto'), ['foto' => UploadedFile::fake()->image('saya.jpg', 800, 800)])
            ->assertRedirect(route('profil'))
            ->assertSessionHas('pesan');

        $foto = $pembeli->fresh()->foto;

        $this->assertStringStartsWith('profil/', $foto);
        Storage::disk('public')->assertExists($foto);
    }

    public function test_penjual_bisa_mengunggah_foto_profil_dan_tampil_di_halaman_profil(): void
    {
        $penjual = $this->buatPenjual();

        $this->actingAs($penjual)->put(route('profil.foto'), ['foto' => UploadedFile::fake()->image('saya.png', 500, 500)]);

        $this->actingAs($penjual->fresh())
            ->get(route('profil'))
            ->assertOk()
            ->assertSee('storage/'.$penjual->fresh()->foto, false);
    }

    public function test_foto_baru_menggantikan_dan_menghapus_foto_lama(): void
    {
        $pembeli = $this->buatPembeli();

        $this->actingAs($pembeli)->put(route('profil.foto'), ['foto' => UploadedFile::fake()->image('a.jpg')]);
        $lama = $pembeli->fresh()->foto;

        $this->actingAs($pembeli->fresh())->put(route('profil.foto'), ['foto' => UploadedFile::fake()->image('b.jpg')]);
        $baru = $pembeli->fresh()->foto;

        $this->assertNotSame($lama, $baru);
        Storage::disk('public')->assertMissing($lama);
        Storage::disk('public')->assertExists($baru);
    }

    public function test_foto_profil_bisa_dihapus(): void
    {
        $pembeli = $this->buatPembeli();

        $this->actingAs($pembeli)->put(route('profil.foto'), ['foto' => UploadedFile::fake()->image('a.jpg')]);
        $foto = $pembeli->fresh()->foto;

        $this->actingAs($pembeli->fresh())->delete(route('profil.foto.hapus'))->assertRedirect(route('profil'));

        $this->assertNull($pembeli->fresh()->foto);
        Storage::disk('public')->assertMissing($foto);
    }

    public function test_berkas_bukan_foto_atau_terlalu_besar_ditolak(): void
    {
        $pembeli = $this->buatPembeli();

        $this->actingAs($pembeli)
            ->put(route('profil.foto'), ['foto' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf')])
            ->assertSessionHasErrors('foto');

        $this->actingAs($pembeli)
            ->put(route('profil.foto'), ['foto' => UploadedFile::fake()->image('besar.jpg')->size(4000)])
            ->assertSessionHasErrors('foto');

        $this->assertNull($pembeli->fresh()->foto);
    }

    public function test_tamu_tidak_bisa_mengunggah_foto(): void
    {
        $this->put(route('profil.foto'), [])->assertRedirect(route('login'));
        $this->delete(route('profil.foto.hapus'))->assertRedirect(route('login'));
    }
}
