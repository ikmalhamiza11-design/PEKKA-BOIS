<?php

namespace Tests\Feature;

use App\Models\Kegiatan;
use App\Models\Panduan;
use Database\Seeders\KegiatanContohSeeder;
use Database\Seeders\PanduanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederInformasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_panduan_seeder_mengisi_tiga_kategori_dan_aman_dijalankan_berulang(): void
    {
        $this->seed(PanduanSeeder::class);
        $jumlah = Panduan::count();
        $this->seed(PanduanSeeder::class);

        $this->assertGreaterThanOrEqual(13, $jumlah);
        $this->assertSame($jumlah, Panduan::count());

        foreach (array_keys(Panduan::KATEGORI) as $kategori) {
            $this->assertTrue(Panduan::where('kategori', $kategori)->exists());
        }

        $this->get(route('panduan'))->assertOk()->assertSee('Cara berbelanja')->assertSee('Mencari usaha lewat Peta Usaha');
    }

    public function test_kegiatan_contoh_hanya_dibuat_di_lingkungan_local(): void
    {
        $this->app['env'] = 'production';
        (new KegiatanContohSeeder)->run();
        $this->assertSame(0, Kegiatan::count());

        $this->app['env'] = 'local';
        (new KegiatanContohSeeder)->run();
        (new KegiatanContohSeeder)->run();

        $this->assertSame(3, Kegiatan::count());
        $this->assertSame(2, Kegiatan::tayang()->mendatang()->count());
        $this->assertSame(1, Kegiatan::tayang()->berlalu()->count());
        $this->get(route('kegiatan'))->assertOk()->assertSee('(Contoh) Pelatihan Foto Produk');
    }
}
