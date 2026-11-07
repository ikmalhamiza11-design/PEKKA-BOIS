<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatUlasan;
use Tests\TestCase;

class UlasanTest extends TestCase
{
    use MembuatUlasan;
    use RefreshDatabase;

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->get(route('ulasan.form', [1, 1]))->assertRedirect(route('login'));
        $this->post(route('ulasan.simpan', [1, 1]), $this->dataUlasan())->assertRedirect(route('login'));
    }

    public function test_formulir_tampil_untuk_pesanan_selesai(): void
    {
        [$pembeli, $pesanan, $item] = $this->buatPesananSelesai();

        $this->actingAs($pembeli)
            ->get(route('ulasan.form', [$pesanan->id, $item->id]))
            ->assertOk()
            ->assertSee($item->nama)
            ->assertSee('Kirim Ulasan');
    }

    public function test_formulir_ubah_menampilkan_isi_lama(): void
    {
        [$pembeli, $pesanan, $item] = $this->buatPesananSelesai();
        $this->actingAs($pembeli)->post(route('ulasan.simpan', [$pesanan->id, $item->id]), $this->dataUlasan(['rating' => '4', 'komentar' => 'Cukup enak']));

        $this->actingAs($pembeli)
            ->get(route('ulasan.form', [$pesanan->id, $item->id]))
            ->assertSee('Cukup enak')
            ->assertSee('Simpan Perubahan');
    }

    public function test_ulasan_ditolak_jika_pesanan_belum_selesai(): void
    {
        $pembeli = $this->buatPembeli();
        $pesanan = $this->buatPesanan($pembeli);
        $item = $pesanan->items()->first();

        $this->actingAs($pembeli)->get(route('ulasan.form', [$pesanan->id, $item->id]))->assertRedirect(route('pesanan.show', $pesanan->id))->assertSessionHas('galat');
        $this->actingAs($pembeli)->post(route('ulasan.simpan', [$pesanan->id, $item->id]), $this->dataUlasan())->assertSessionHas('galat');

        $this->assertSame(0, Review::count());
    }

    public function test_ulasan_ditolak_untuk_pesanan_dibatalkan(): void
    {
        [$pembeli, $pesanan, $item] = $this->buatPesananSelesai();
        $pesanan->update(['status' => Order::DIBATALKAN]);

        $this->actingAs($pembeli)->post(route('ulasan.simpan', [$pesanan->id, $item->id]), $this->dataUlasan())->assertSessionHas('galat');

        $this->assertSame(0, Review::count());
    }

    public function test_pesanan_orang_lain_tidak_bisa_diulas(): void
    {
        [, $pesanan, $item] = $this->buatPesananSelesai();
        $orangLain = $this->buatPembeli();

        $this->actingAs($orangLain)->get(route('ulasan.form', [$pesanan->id, $item->id]))->assertNotFound();
        $this->actingAs($orangLain)->post(route('ulasan.simpan', [$pesanan->id, $item->id]), $this->dataUlasan())->assertNotFound();

        $this->assertSame(0, Review::count());
    }

    public function test_item_dari_pesanan_lain_tidak_bisa_dipakai(): void
    {
        [$pembeli, $pesanan] = $this->buatPesananSelesai();
        [, , $itemLain] = $this->buatPesananSelesai();

        $this->actingAs($pembeli)->post(route('ulasan.simpan', [$pesanan->id, $itemLain->id]), $this->dataUlasan())->assertNotFound();

        $this->assertSame(0, Review::count());
    }

    public function test_produk_yang_sudah_dihapus_tidak_bisa_diulas(): void
    {
        [$pembeli, $pesanan, $item] = $this->buatPesananSelesai();
        $item->product->delete();

        $this->actingAs($pembeli)->post(route('ulasan.simpan', [$pesanan->id, $item->id]), $this->dataUlasan())->assertSessionHas('galat');

        $this->assertSame(0, Review::count());
    }

    public function test_ulasan_berhasil_disimpan_dengan_data_yang_benar(): void
    {
        [$pembeli, $pesanan, $item] = $this->buatPesananSelesai();

        $this->actingAs($pembeli)
            ->post(route('ulasan.simpan', [$pesanan->id, $item->id]), $this->dataUlasan(['rating' => '4', 'komentar' => 'Enak banget']))
            ->assertRedirect(route('pesanan.show', $pesanan->id))
            ->assertSessionHas('pesan');

        $ulasan = Review::first();
        $this->assertSame($pembeli->id, $ulasan->user_id);
        $this->assertSame($item->id, $ulasan->order_item_id);
        $this->assertSame($item->product_id, $ulasan->product_id);
        $this->assertSame($pesanan->shop_id, $ulasan->shop_id);
        $this->assertSame(4, $ulasan->rating);
        $this->assertSame('Enak banget', $ulasan->komentar);
        $this->assertNull($ulasan->balasan);
    }

    public function test_komentar_boleh_kosong(): void
    {
        [$pembeli, $pesanan, $item] = $this->buatPesananSelesai();

        $this->actingAs($pembeli)
            ->post(route('ulasan.simpan', [$pesanan->id, $item->id]), ['rating' => '3', 'komentar' => ''])
            ->assertSessionHasNoErrors();

        $this->assertNull(Review::first()->komentar);
    }

    public function test_rating_tidak_valid_ditolak(): void
    {
        [$pembeli, $pesanan, $item] = $this->buatPesananSelesai();

        foreach (['', '0', '6', '-1', 'bagus', '4.5'] as $nilai) {
            $this->actingAs($pembeli)
                ->post(route('ulasan.simpan', [$pesanan->id, $item->id]), $this->dataUlasan(['rating' => $nilai]))
                ->assertSessionHasErrors('rating');
        }

        $this->assertSame(0, Review::count());
    }

    public function test_rating_wajib_diisi(): void
    {
        [$pembeli, $pesanan, $item] = $this->buatPesananSelesai();

        $this->actingAs($pembeli)
            ->post(route('ulasan.simpan', [$pesanan->id, $item->id]), ['komentar' => 'Tanpa bintang'])
            ->assertSessionHasErrors('rating');
    }

    public function test_komentar_terlalu_panjang_ditolak(): void
    {
        [$pembeli, $pesanan, $item] = $this->buatPesananSelesai();

        $this->actingAs($pembeli)
            ->post(route('ulasan.simpan', [$pesanan->id, $item->id]), $this->dataUlasan(['komentar' => str_repeat('a', 1001)]))
            ->assertSessionHasErrors('komentar');
    }

    public function test_mengulas_dua_kali_memperbarui_tanpa_baris_baru(): void
    {
        [$pembeli, $pesanan, $item] = $this->buatPesananSelesai();

        $this->actingAs($pembeli)->post(route('ulasan.simpan', [$pesanan->id, $item->id]), $this->dataUlasan(['rating' => '2', 'komentar' => 'Kurang']));
        $this->actingAs($pembeli)
            ->post(route('ulasan.simpan', [$pesanan->id, $item->id]), $this->dataUlasan(['rating' => '5', 'komentar' => 'Ternyata enak']))
            ->assertSessionHas('pesan', 'Ulasanmu sudah diperbarui.');

        $this->assertSame(1, Review::count());
        $this->assertSame(5, Review::first()->rating);
        $this->assertSame('Ternyata enak', Review::first()->komentar);
    }

    public function test_memperbarui_ulasan_tidak_menghapus_balasan_penjual(): void
    {
        [$pembeli, $pesanan, $item] = $this->buatPesananSelesai();
        $this->actingAs($pembeli)->post(route('ulasan.simpan', [$pesanan->id, $item->id]), $this->dataUlasan());
        Review::first()->update(['balasan' => 'Terima kasih ya', 'dibalas_at' => now()]);

        $this->actingAs($pembeli)->post(route('ulasan.simpan', [$pesanan->id, $item->id]), $this->dataUlasan(['rating' => '3']));

        $this->assertSame('Terima kasih ya', Review::first()->balasan);
    }

    public function test_isian_tambahan_tidak_bisa_memalsukan_data(): void
    {
        [$pembeli, $pesanan, $item] = $this->buatPesananSelesai();
        $orangLain = $this->buatPembeli();
        $tokoLain = $this->buatPenjual()->shop;

        $this->actingAs($pembeli)->post(route('ulasan.simpan', [$pesanan->id, $item->id]), $this->dataUlasan([
            'user_id' => $orangLain->id,
            'shop_id' => $tokoLain->id,
            'balasan' => 'Dibalas sendiri',
            'product_id' => 9999,
        ]));

        $ulasan = Review::first();
        $this->assertSame($pembeli->id, $ulasan->user_id);
        $this->assertSame($pesanan->shop_id, $ulasan->shop_id);
        $this->assertSame($item->product_id, $ulasan->product_id);
        $this->assertNull($ulasan->balasan);
    }

    public function test_pesanan_selesai_menampilkan_tombol_beri_dan_ubah_ulasan(): void
    {
        [$pembeli, $pesanan, $item] = $this->buatPesananSelesai();

        $this->actingAs($pembeli)->get(route('pesanan.show', $pesanan->id))->assertSee('Beri ulasan')->assertDontSee('Ubah ulasan');

        $this->actingAs($pembeli)->post(route('ulasan.simpan', [$pesanan->id, $item->id]), $this->dataUlasan());

        $this->actingAs($pembeli)->get(route('pesanan.show', $pesanan->id))->assertSee('Ubah ulasan')->assertDontSee('Beri ulasan');
    }

    public function test_pesanan_belum_selesai_tidak_menampilkan_tombol_ulasan(): void
    {
        $pembeli = $this->buatPembeli();
        $pesanan = $this->buatPesanan($pembeli);

        $this->actingAs($pembeli)->get(route('pesanan.show', $pesanan->id))->assertDontSee('Beri ulasan');
    }

    public function test_pengiriman_ulasan_dibatasi_dua_puluh_per_menit(): void
    {
        [$pembeli, $pesanan, $item] = $this->buatPesananSelesai();

        foreach (range(1, 20) as $ke) {
            $this->actingAs($pembeli)->post(route('ulasan.simpan', [$pesanan->id, $item->id]), $this->dataUlasan())->assertRedirect();
        }

        $this->actingAs($pembeli)->post(route('ulasan.simpan', [$pesanan->id, $item->id]), $this->dataUlasan())->assertStatus(429);
    }
}
