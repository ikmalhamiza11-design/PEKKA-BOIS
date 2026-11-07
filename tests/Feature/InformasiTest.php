<?php

namespace Tests\Feature;

use App\Models\Kegiatan;
use App\Models\Kelompok;
use App\Models\Panduan;
use App\Models\Shop;
use App\Models\User;
use Database\Seeders\PanduanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatDataContoh;
use Tests\TestCase;

class InformasiTest extends TestCase
{
    use MembuatDataContoh;
    use RefreshDatabase;

    protected function buatAdminDinas(): User
    {
        $user = User::factory()->create();
        $user->role = User::ADMIN_DINAS;
        $user->save();

        return $user;
    }

    protected function buatAdminKelompok(): User
    {
        $user = User::factory()->create();
        $user->role = User::ADMIN_KELOMPOK;
        $user->kelompok_id = ($this->buatKelompok(['nama' => 'Kelompok Admin']))->id;
        $user->save();

        return $user;
    }

    protected function dataKegiatan(array $atribut = []): array
    {
        return array_merge([
            'judul' => 'Pelatihan Foto Produk',
            'deskripsi' => 'Belajar memotret produk dengan ponsel.',
            'lokasi' => 'Balai Desa Cibinong',
            'mulai_at' => now()->addDays(5)->format('Y-m-d\TH:i'),
            'selesai_at' => now()->addDays(5)->addHours(3)->format('Y-m-d\TH:i'),
            'dipublikasikan' => '1',
        ], $atribut);
    }

    public function test_peta_hanya_menampilkan_kelompok_dengan_toko_terverifikasi(): void
    {
        $tampil = $this->buatKelompok(['nama' => 'PEKKA Melati', 'kecamatan' => 'Cibinong', 'latitude' => -6.48, 'longitude' => 106.85]);
        $this->buatPenjual()->shop->update(['kelompok_id' => $tampil->id]);

        $tersembunyi = $this->buatKelompok(['nama' => 'PEKKA Mawar', 'kecamatan' => 'Ciawi']);
        $this->buatPenjual(Shop::MENUNGGU)->shop->update(['kelompok_id' => $tersembunyi->id]);

        $this->get(route('peta'))
            ->assertOk()
            ->assertSee('PEKKA Melati')
            ->assertDontSee('PEKKA Mawar')
            ->assertSee('Lokasi ditampilkan per desa atau kecamatan');
    }

    public function test_peta_mengirim_titik_hanya_untuk_kelompok_berkoordinat(): void
    {
        $dengan = $this->buatKelompok(['nama' => 'PEKKA Bertitik', 'latitude' => -6.48, 'longitude' => 106.85]);
        $tanpa = $this->buatKelompok(['nama' => 'PEKKA Tanpa Titik']);
        $this->buatPenjual()->shop->update(['kelompok_id' => $dengan->id]);
        $this->buatPenjual()->shop->update(['kelompok_id' => $tanpa->id]);

        $this->get(route('peta'))
            ->assertOk()
            ->assertViewHas('titik', fn ($titik) => $titik->count() === 1 && $titik->first()['nama'] === 'PEKKA Bertitik')
            ->assertSee('PEKKA Tanpa Titik');
    }

    public function test_kegiatan_tayang_dipisah_antara_mendatang_dan_berlalu_dan_draf_disembunyikan(): void
    {
        Kegiatan::create($this->dataKegiatan(['judul' => 'Acara Mendatang', 'mulai_at' => now()->addDays(3), 'selesai_at' => null]));
        Kegiatan::create($this->dataKegiatan(['judul' => 'Acara Lama', 'mulai_at' => now()->subDays(10), 'selesai_at' => null]));
        Kegiatan::create($this->dataKegiatan(['judul' => 'Acara Draf', 'dipublikasikan' => false]));

        $this->get(route('kegiatan'))
            ->assertOk()
            ->assertSee('Acara Mendatang')
            ->assertSee('Acara Lama')
            ->assertDontSee('Acara Draf');
    }

    public function test_halaman_kegiatan_kosong_menampilkan_pesan_ramah(): void
    {
        $this->get(route('kegiatan'))->assertOk()->assertSee('Belum ada kegiatan yang dijadwalkan');
    }

    public function test_detail_kegiatan_dan_draf_tidak_bisa_dibuka(): void
    {
        $tayang = Kegiatan::create($this->dataKegiatan(['mulai_at' => now()->addDay()]));
        $draf = Kegiatan::create($this->dataKegiatan(['judul' => 'Draf', 'dipublikasikan' => false]));

        $this->get(route('kegiatan.show', $tayang->id))->assertOk()->assertSee('Pelatihan Foto Produk');
        $this->get(route('kegiatan.show', $draf->id))->assertNotFound();
    }

    public function test_panduan_ditampilkan_per_kategori_dan_draf_disembunyikan(): void
    {
        Panduan::create(['judul' => 'Cara Belanja', 'kategori' => 'pembeli', 'isi' => 'Langkah satu.', 'urutan' => 1, 'dipublikasikan' => true]);
        Panduan::create(['judul' => 'Panduan Draf', 'kategori' => 'penjual', 'isi' => 'Rahasia.', 'urutan' => 1, 'dipublikasikan' => false]);

        $this->get(route('panduan'))
            ->assertOk()
            ->assertSee('Untuk Pembeli')
            ->assertSee('Cara Belanja')
            ->assertDontSee('Panduan Draf');
    }

    public function test_seeder_panduan_tidak_menggandakan_data(): void
    {
        $this->seed(PanduanSeeder::class);
        $jumlah = Panduan::count();
        $this->seed(PanduanSeeder::class);

        $this->assertGreaterThan(5, $jumlah);
        $this->assertSame($jumlah, Panduan::count());
    }

    public function test_admin_dinas_mengelola_kegiatan(): void
    {
        $admin = $this->buatAdminDinas();

        $this->actingAs($admin)->get(route('admin.kegiatan.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.kegiatan.create'))->assertOk();

        $this->actingAs($admin)
            ->post(route('admin.kegiatan.store'), $this->dataKegiatan())
            ->assertRedirect(route('admin.kegiatan.index'))
            ->assertSessionHas('pesan');

        $kegiatan = Kegiatan::first();
        $this->assertSame('Pelatihan Foto Produk', $kegiatan->judul);
        $this->assertTrue($kegiatan->dipublikasikan);

        $this->actingAs($admin)
            ->put(route('admin.kegiatan.update', $kegiatan->id), $this->dataKegiatan(['judul' => 'Judul Baru', 'dipublikasikan' => null]))
            ->assertRedirect(route('admin.kegiatan.index'));

        $this->assertSame('Judul Baru', $kegiatan->fresh()->judul);
        $this->assertFalse($kegiatan->fresh()->dipublikasikan);

        $this->actingAs($admin)->delete(route('admin.kegiatan.destroy', $kegiatan->id))->assertRedirect(route('admin.kegiatan.index'));
        $this->assertSame(0, Kegiatan::count());
    }

    public function test_kegiatan_ditolak_jika_waktu_selesai_sebelum_mulai(): void
    {
        $this->actingAs($this->buatAdminDinas())
            ->post(route('admin.kegiatan.store'), $this->dataKegiatan([
                'mulai_at' => now()->addDays(5)->format('Y-m-d\TH:i'),
                'selesai_at' => now()->addDays(4)->format('Y-m-d\TH:i'),
            ]))
            ->assertSessionHasErrors('selesai_at');

        $this->assertSame(0, Kegiatan::count());
    }

    public function test_judul_dan_deskripsi_kegiatan_wajib(): void
    {
        $this->actingAs($this->buatAdminDinas())
            ->post(route('admin.kegiatan.store'), $this->dataKegiatan(['judul' => '', 'deskripsi' => '']))
            ->assertSessionHasErrors(['judul', 'deskripsi']);
    }

    public function test_admin_dinas_mengelola_panduan(): void
    {
        $admin = $this->buatAdminDinas();

        $this->actingAs($admin)
            ->post(route('admin.panduan.store'), ['judul' => 'Cara Baru', 'kategori' => 'umum', 'isi' => 'Isi panduan.', 'urutan' => 2, 'dipublikasikan' => '1'])
            ->assertRedirect(route('admin.panduan.index'));

        $panduan = Panduan::first();
        $this->assertSame('umum', $panduan->kategori);

        $this->actingAs($admin)
            ->put(route('admin.panduan.update', $panduan->id), ['judul' => 'Cara Revisi', 'kategori' => 'penjual', 'isi' => 'Isi baru.', 'urutan' => 1])
            ->assertRedirect(route('admin.panduan.index'));

        $this->assertSame('Cara Revisi', $panduan->fresh()->judul);
        $this->assertFalse($panduan->fresh()->dipublikasikan);

        $this->actingAs($admin)->delete(route('admin.panduan.destroy', $panduan->id));
        $this->assertSame(0, Panduan::count());
    }

    public function test_kategori_panduan_harus_valid(): void
    {
        $this->actingAs($this->buatAdminDinas())
            ->post(route('admin.panduan.store'), ['judul' => 'X', 'kategori' => 'sembarang', 'isi' => 'Isi.'])
            ->assertSessionHasErrors('kategori');
    }

    public function test_admin_kelompok_pembeli_dan_penjual_tidak_bisa_mengelola_konten(): void
    {
        $this->get(route('admin.kegiatan.index'))->assertRedirect(route('login'));
        $adminKelompok = $this->buatAdminKelompok();
        $this->actingAs($adminKelompok)->get(route('admin.kegiatan.index'))->assertForbidden();
        $this->actingAs($adminKelompok)->post(route('admin.panduan.store'), ['judul' => 'X', 'kategori' => 'umum', 'isi' => 'Y'])->assertForbidden();
        $this->actingAs($this->buatPembeli())->get(route('admin.kegiatan.index'))->assertForbidden();
        $this->actingAs($this->buatPenjual())->get(route('admin.panduan.index'))->assertForbidden();
    }

    public function test_admin_dinas_mengisi_koordinat_kelompok(): void
    {
        $kelompok = $this->buatKelompok(['nama' => 'PEKKA Titik']);

        $this->actingAs($this->buatAdminDinas())
            ->put(route('admin.kelompok.update', $kelompok->id), ['nama' => 'PEKKA Titik', 'latitude' => '-6.4817', 'longitude' => '106.8540'])
            ->assertRedirect(route('admin.kelompok.index'));

        $kelompok = $kelompok->fresh();
        $this->assertTrue($kelompok->punyaKoordinat());
        $this->assertEqualsWithDelta(-6.4817, $kelompok->latitude, 0.0001);
    }

    public function test_koordinat_di_luar_batas_ditolak(): void
    {
        $kelompok = $this->buatKelompok(['nama' => 'PEKKA Salah']);

        $this->actingAs($this->buatAdminDinas())
            ->put(route('admin.kelompok.update', $kelompok->id), ['nama' => 'PEKKA Salah', 'latitude' => '999', 'longitude' => 'abc'])
            ->assertSessionHasErrors(['latitude', 'longitude']);

        $this->assertFalse($kelompok->fresh()->punyaKoordinat());
    }
}
