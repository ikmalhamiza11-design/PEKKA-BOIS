<?php

namespace Tests\Feature;

use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatUlasan;
use Tests\TestCase;

class UlasanProdukTest extends TestCase
{
    use MembuatUlasan;
    use RefreshDatabase;

    public function test_produk_tanpa_ulasan_menampilkan_pesan_kosong(): void
    {
        $produk = $this->buatProduk($this->buatPenjual()->shop);

        $this->get(route('produk.show', $produk->id))
            ->assertOk()
            ->assertSee('Belum ada ulasan')
            ->assertSee('Ulasan (0)');
    }

    public function test_ringkasan_menghitung_rata_rata_jumlah_dan_sebaran(): void
    {
        $produk = $this->buatProduk($this->buatPenjual()->shop);
        $this->buatUlasan($produk, null, 5);
        $this->buatUlasan($produk, null, 4);
        $this->buatUlasan($produk, null, 3);

        $this->get(route('produk.show', $produk->id))
            ->assertOk()
            ->assertViewHas('ringkasan', ['jumlah' => 3, 'rata' => 4.0, 'sebaran' => [5 => 1, 4 => 1, 3 => 1, 2 => 0, 1 => 0]])
            ->assertSee('Ulasan (3)')
            ->assertSee('4,0')
            ->assertSee('dari 3 ulasan');
    }

    public function test_rata_rata_dibulatkan_satu_desimal(): void
    {
        $produk = $this->buatProduk($this->buatPenjual()->shop);
        $this->buatUlasan($produk, null, 5);
        $this->buatUlasan($produk, null, 5);
        $this->buatUlasan($produk, null, 4);

        $this->get(route('produk.show', $produk->id))->assertViewHas('ringkasan', fn ($r) => $r['rata'] === 4.7 && $r['jumlah'] === 3);
    }

    public function test_ulasan_produk_lain_tidak_ikut_dihitung(): void
    {
        $toko = $this->buatPenjual()->shop;
        $produk = $this->buatProduk($toko);
        $lain = $this->buatProduk($toko);
        $this->buatUlasan($produk, null, 5);
        $this->buatUlasan($lain, null, 1);

        $this->get(route('produk.show', $produk->id))
            ->assertViewHas('ringkasan', fn ($r) => $r['jumlah'] === 1 && $r['rata'] === 5.0)
            ->assertViewHas('ringkasanToko', fn ($r) => $r['jumlah'] === 2 && $r['rata'] === 3.0);
    }

    public function test_nama_pembeli_disamarkan(): void
    {
        $produk = $this->buatProduk($this->buatPenjual()->shop);
        $pembeli = $this->buatPembeli(['name' => 'Siti Rohmah Lestari']);
        $this->buatUlasan($produk, $pembeli, 5);

        $this->get(route('produk.show', $produk->id))
            ->assertSee('Siti R.')
            ->assertDontSee('Rohmah')
            ->assertDontSee('Lestari')
            ->assertDontSee($pembeli->email);
    }

    public function test_komentar_dan_balasan_penjual_tampil(): void
    {
        $produk = $this->buatProduk($this->buatPenjual()->shop);
        $this->buatUlasan($produk, null, 5, ['komentar' => 'Gurih dan renyah', 'balasan' => 'Terima kasih sudah belanja']);

        $this->get(route('produk.show', $produk->id))
            ->assertSee('Gurih dan renyah')
            ->assertSee('Balasan penjual')
            ->assertSee('Terima kasih sudah belanja');
    }

    public function test_isi_ulasan_di_escape_untuk_mencegah_xss(): void
    {
        $produk = $this->buatProduk($this->buatPenjual()->shop);
        $pembeli = $this->buatPembeli(['name' => '<b>Jahat</b> Sekali']);
        $this->buatUlasan($produk, $pembeli, 5, ['komentar' => '<script>alert(1)</script>']);

        $this->get(route('produk.show', $produk->id))
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<b>Jahat</b>', false);
    }

    public function test_ulasan_dibagi_lima_per_halaman(): void
    {
        $produk = $this->buatProduk($this->buatPenjual()->shop);

        foreach (range(1, 7) as $nomor) {
            $this->buatUlasan($produk, null, 5, ['komentar' => 'Komentar nomor '.$nomor.'.']);
        }

        $this->get(route('produk.show', $produk->id))
            ->assertSee('Komentar nomor 7.')
            ->assertSee('Komentar nomor 3.')
            ->assertDontSee('Komentar nomor 2.')
            ->assertSee('ulasan=2#ulasan', false);

        $this->get(route('produk.show', ['id' => $produk->id, 'ulasan' => 2]))
            ->assertSee('Komentar nomor 2.')
            ->assertSee('Komentar nomor 1.')
            ->assertDontSee('Komentar nomor 7.');
    }

    public function test_kartu_katalog_menampilkan_rating_hanya_jika_ada_ulasan(): void
    {
        $toko = $this->buatPenjual()->shop;
        $dinilai = $this->buatProduk($toko, null, ['nama' => 'Produk Dinilai']);
        $this->buatProduk($toko, null, ['nama' => 'Produk Polos']);
        $this->buatUlasan($dinilai, null, 4);
        $this->buatUlasan($dinilai, null, 5);

        $html = $this->get(route('katalog'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'Penilaian 4,5 dari 5, 2 ulasan'));
        $this->assertStringContainsString('Produk Polos', $html);
    }

    public function test_beranda_menampilkan_rating_pada_kartu(): void
    {
        $produk = $this->buatProduk($this->buatPenjual()->shop);
        $this->buatUlasan($produk, null, 5);

        $this->get(route('beranda'))->assertOk()->assertSee('Penilaian 5,0 dari 5, 1 ulasan', false);
    }

    public function test_urut_penilaian_terbaik(): void
    {
        $toko = $this->buatPenjual()->shop;
        $polos = $this->buatProduk($toko, null, ['nama' => 'Zzz Tanpa Ulasan']);
        $rendah = $this->buatProduk($toko, null, ['nama' => 'Zzz Rating Rendah']);
        $tinggi = $this->buatProduk($toko, null, ['nama' => 'Zzz Rating Tinggi']);
        $this->buatUlasan($rendah, null, 2);
        $this->buatUlasan($tinggi, null, 5);

        $this->get(route('katalog', ['urut' => 'terbaik']))
            ->assertOk()
            ->assertViewHas('produk', fn ($daftar) => $daftar->pluck('id')->all() === [$tinggi->id, $rendah->id, $polos->id])
            ->assertViewHas('total', 3)
            ->assertSee('Penilaian terbaik');
    }

    public function test_urut_terbaik_memakai_jumlah_ulasan_sebagai_penentu_seri(): void
    {
        $toko = $this->buatPenjual()->shop;
        $sedikit = $this->buatProduk($toko);
        $banyak = $this->buatProduk($toko);
        $this->buatUlasan($sedikit, null, 5);
        $this->buatUlasan($banyak, null, 5);
        $this->buatUlasan($banyak, null, 5);

        $this->get(route('katalog', ['urut' => 'terbaik']))
            ->assertViewHas('produk', fn ($daftar) => $daftar->pluck('id')->all() === [$banyak->id, $sedikit->id]);
    }

    public function test_menghapus_produk_tidak_menghapus_ulasan_dan_nilai_toko(): void
    {
        $penjual = $this->buatPenjual();
        $produk = $this->buatProduk($penjual->shop);
        $sisa = $this->buatProduk($penjual->shop);
        $this->buatUlasan($produk, null, 1);
        $this->buatUlasan($sisa, null, 5);

        $this->actingAs($penjual)->delete(route('penjual.produk.destroy', $produk->id));

        $this->assertSame(2, Review::count());
        $this->assertNull(Review::where('rating', 1)->first()->product_id);

        $this->get(route('produk.show', $sisa->id))
            ->assertViewHas('ringkasanToko', fn ($r) => $r['jumlah'] === 2 && $r['rata'] === 3.0);
    }

    public function test_satu_pesanan_hanya_bisa_memiliki_satu_ulasan_per_item(): void
    {
        $produk = $this->buatProduk($this->buatPenjual()->shop);
        $ulasan = $this->buatUlasan($produk);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Review::create([
            'order_item_id' => $ulasan->order_item_id,
            'user_id' => $ulasan->user_id,
            'product_id' => $produk->id,
            'shop_id' => $produk->shop_id,
            'rating' => 1,
        ]);
    }
}
