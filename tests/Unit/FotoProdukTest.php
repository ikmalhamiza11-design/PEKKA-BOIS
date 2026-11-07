<?php

namespace Tests\Unit;

use App\Support\FotoProduk;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FotoProdukTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('Ekstensi GD belum aktif.');
        }

        Storage::fake('public');
    }

    public function test_foto_png_diubah_menjadi_jpg_dengan_lebar_maksimal(): void
    {
        $path = FotoProduk::simpan(UploadedFile::fake()->image('a.png', 2400, 1200));

        $this->assertStringEndsWith('.jpg', $path);
        Storage::disk('public')->assertExists($path);

        [$lebar, $tinggi, $tipe] = getimagesize(Storage::disk('public')->path($path));
        $this->assertSame(1200, $lebar);
        $this->assertSame(600, $tinggi);
        $this->assertSame(IMAGETYPE_JPEG, $tipe);
    }

    public function test_hapus_menghapus_berkas_dan_aman_untuk_nilai_kosong(): void
    {
        $path = FotoProduk::simpan(UploadedFile::fake()->image('a.jpg', 300, 300));

        FotoProduk::hapus($path);
        Storage::disk('public')->assertMissing($path);

        FotoProduk::hapus(null);
        FotoProduk::hapus('produk/tidak-ada.jpg');
        $this->assertTrue(true);
    }
}
