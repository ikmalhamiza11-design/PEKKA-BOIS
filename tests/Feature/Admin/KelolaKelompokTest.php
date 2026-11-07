<?php

namespace Tests\Feature\Admin;

use App\Models\Kelompok;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatAdmin;
use Tests\TestCase;

class KelolaKelompokTest extends TestCase
{
    use MembuatAdmin;
    use RefreshDatabase;

    public function test_daftar_kelompok_menampilkan_jumlah_toko(): void
    {
        $kelompok = $this->buatKelompok(['nama' => 'Melati', 'desa' => 'Cibinong', 'kecamatan' => 'Cibinong']);
        $this->buatPenjualDiKelompok($kelompok);
        $this->buatPenjualDiKelompok($kelompok);

        $this->actingAs($this->buatAdminDinas())
            ->get(route('admin.kelompok.index'))
            ->assertOk()
            ->assertSee('Melati')
            ->assertSee('2 toko');
    }

    public function test_pencarian_kelompok(): void
    {
        $this->buatKelompok(['nama' => 'Seruni', 'desa' => 'Cibinong']);
        $this->buatKelompok(['nama' => 'Teratai', 'desa' => 'Ciawi']);

        $this->actingAs($this->buatAdminDinas())
            ->get(route('admin.kelompok.index', ['q' => 'Ciawi']))
            ->assertSee('Teratai')
            ->assertDontSee('Seruni');
    }

    public function test_menambah_kelompok(): void
    {
        $this->actingAs($this->buatAdminDinas())
            ->post(route('admin.kelompok.store'), ['nama' => ' PEKKA Melati ', 'desa' => 'Cibinong', 'kecamatan' => ''])
            ->assertRedirect(route('admin.kelompok.index'))
            ->assertSessionHas('pesan');

        $this->assertDatabaseHas('kelompoks', ['nama' => 'PEKKA Melati', 'desa' => 'Cibinong', 'kecamatan' => null]);
    }

    public function test_nama_kelompok_wajib_diisi(): void
    {
        $this->actingAs($this->buatAdminDinas())
            ->post(route('admin.kelompok.store'), ['nama' => ''])
            ->assertSessionHasErrors('nama');

        $this->assertSame(0, Kelompok::count());
    }

    public function test_kelompok_kembar_ditolak_tanpa_membedakan_huruf_besar_kecil(): void
    {
        $admin = $this->buatAdminDinas();
        $this->buatKelompok(['nama' => 'PEKKA Melati', 'desa' => 'Cibinong', 'kecamatan' => null]);

        $this->actingAs($admin)
            ->post(route('admin.kelompok.store'), ['nama' => 'pekka melati', 'desa' => 'CIBINONG'])
            ->assertSessionHas('galat');

        $this->assertSame(1, Kelompok::count());
    }

    public function test_nama_sama_dengan_desa_berbeda_diperbolehkan(): void
    {
        $admin = $this->buatAdminDinas();
        $this->buatKelompok(['nama' => 'PEKKA Melati', 'desa' => 'Cibinong']);

        $this->actingAs($admin)
            ->post(route('admin.kelompok.store'), ['nama' => 'PEKKA Melati', 'desa' => 'Ciawi'])
            ->assertSessionHas('pesan');

        $this->assertSame(2, Kelompok::count());
    }

    public function test_mengubah_kelompok(): void
    {
        $kelompok = $this->buatKelompok(['nama' => 'Lama']);
        $admin = $this->buatAdminDinas();

        $this->actingAs($admin)->get(route('admin.kelompok.edit', $kelompok->id))->assertOk()->assertSee('Lama');

        $this->actingAs($admin)
            ->put(route('admin.kelompok.update', $kelompok->id), ['nama' => 'Baru', 'desa' => 'Desa B', 'kecamatan' => 'Kec B'])
            ->assertRedirect(route('admin.kelompok.index'));

        $this->assertDatabaseHas('kelompoks', ['id' => $kelompok->id, 'nama' => 'Baru', 'desa' => 'Desa B', 'kecamatan' => 'Kec B']);
    }

    public function test_mengubah_kelompok_tanpa_perubahan_nama_tidak_dianggap_kembar(): void
    {
        $kelompok = $this->buatKelompok(['nama' => 'Melati', 'desa' => 'Cibinong', 'kecamatan' => null]);

        $this->actingAs($this->buatAdminDinas())
            ->put(route('admin.kelompok.update', $kelompok->id), ['nama' => 'Melati', 'desa' => 'Cibinong', 'kecamatan' => 'Cibinong'])
            ->assertSessionHas('pesan');
    }

    public function test_mengubah_kelompok_menjadi_kembar_ditolak(): void
    {
        $this->buatKelompok(['nama' => 'Melati', 'desa' => 'Cibinong', 'kecamatan' => null]);
        $lain = $this->buatKelompok(['nama' => 'Mawar', 'desa' => 'Ciawi']);

        $this->actingAs($this->buatAdminDinas())
            ->put(route('admin.kelompok.update', $lain->id), ['nama' => 'Melati', 'desa' => 'Cibinong'])
            ->assertSessionHas('galat');

        $this->assertSame('Mawar', $lain->fresh()->nama);
    }

    public function test_kelompok_kosong_bisa_dihapus(): void
    {
        $kelompok = $this->buatKelompok(['nama' => 'Kosong']);

        $this->actingAs($this->buatAdminDinas())
            ->delete(route('admin.kelompok.destroy', $kelompok->id))
            ->assertSessionHas('pesan');

        $this->assertDatabaseMissing('kelompoks', ['id' => $kelompok->id]);
    }

    public function test_kelompok_yang_punya_toko_tidak_bisa_dihapus(): void
    {
        $kelompok = $this->buatKelompok(['nama' => 'Berpenghuni']);
        $this->buatPenjualDiKelompok($kelompok);

        $this->actingAs($this->buatAdminDinas())
            ->delete(route('admin.kelompok.destroy', $kelompok->id))
            ->assertSessionHas('galat');

        $this->assertDatabaseHas('kelompoks', ['id' => $kelompok->id]);
    }

    public function test_kelompok_yang_punya_admin_tidak_bisa_dihapus(): void
    {
        $kelompok = $this->buatKelompok(['nama' => 'Beradmin']);
        $this->buatAdminKelompok($kelompok);

        $this->actingAs($this->buatAdminDinas())
            ->delete(route('admin.kelompok.destroy', $kelompok->id))
            ->assertSessionHas('galat');

        $this->assertDatabaseHas('kelompoks', ['id' => $kelompok->id]);
    }

    public function test_tempel_banyak_kelompok_dengan_pemisah_titik_koma_tab_dan_garis(): void
    {
        $daftar = "PEKKA Melati; Cibinong; Cibinong\nPEKKA Mawar\tCiawi\tCiawi\nPEKKA Anggrek|Megamendung|Megamendung\nPEKKA Kenanga";

        $this->actingAs($this->buatAdminDinas())
            ->post(route('admin.kelompok.impor'), ['daftar' => $daftar])
            ->assertRedirect(route('admin.kelompok.index'))
            ->assertSessionHas('pesan');

        $this->assertSame(4, Kelompok::count());
        $this->assertDatabaseHas('kelompoks', ['nama' => 'PEKKA Mawar', 'desa' => 'Ciawi', 'kecamatan' => 'Ciawi']);
        $this->assertDatabaseHas('kelompoks', ['nama' => 'PEKKA Kenanga', 'desa' => null, 'kecamatan' => null]);
    }

    public function test_tempel_banyak_melewati_yang_sudah_ada_dan_baris_kosong(): void
    {
        $this->buatKelompok(['nama' => 'PEKKA Melati', 'desa' => 'Cibinong', 'kecamatan' => 'Cibinong']);

        $this->actingAs($this->buatAdminDinas())
            ->post(route('admin.kelompok.impor'), ['daftar' => "PEKKA Melati; Cibinong; Cibinong\n\n   \nPEKKA Baru; Ciawi; Ciawi\nPEKKA Baru; Ciawi; Ciawi"])
            ->assertSessionHas('pesan', '1 kelompok ditambahkan, 2 dilewati karena sudah ada.');

        $this->assertSame(2, Kelompok::count());
    }

    public function test_tempel_banyak_melaporkan_baris_bermasalah(): void
    {
        $this->actingAs($this->buatAdminDinas())
            ->post(route('admin.kelompok.impor'), ['daftar' => "; Desa Tanpa Nama; Kecamatan\nPEKKA Sah; Ciawi; Ciawi\n".str_repeat('x', 256)])
            ->assertSessionHas('pesan');

        $this->assertSame(1, Kelompok::count());
        $this->assertDatabaseHas('kelompoks', ['nama' => 'PEKKA Sah']);
    }

    public function test_tempel_banyak_dibatasi_dua_ratus_baris(): void
    {
        $daftar = collect(range(1, 201))->map(fn ($n) => 'Kelompok '.$n)->implode("\n");

        $this->actingAs($this->buatAdminDinas())
            ->post(route('admin.kelompok.impor'), ['daftar' => $daftar])
            ->assertSessionHas('galat');

        $this->assertSame(0, Kelompok::count());
    }

    public function test_tempel_banyak_wajib_diisi(): void
    {
        $this->actingAs($this->buatAdminDinas())
            ->post(route('admin.kelompok.impor'), ['daftar' => ''])
            ->assertSessionHasErrors('daftar');
    }

    public function test_kelompok_baru_langsung_muncul_di_formulir_daftar_penjual(): void
    {
        $this->actingAs($this->buatAdminDinas())
            ->post(route('admin.kelompok.store'), ['nama' => 'PEKKA Cemara', 'desa' => 'Sukamakmur']);
        auth()->logout();

        $this->get(route('daftar.penjual'))->assertOk()->assertSee('PEKKA Cemara');
    }
}
