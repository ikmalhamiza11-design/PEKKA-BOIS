<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\MembuatDataContoh;
use Tests\TestCase;

class SahabatPekkaTest extends TestCase
{
    use MembuatDataContoh;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        config(['services.gemini.key' => 'kunci-uji', 'services.gemini.batas_per_menit' => 10]);
    }

    protected function balasan(string $teks): array
    {
        return ['candidates' => [['content' => ['parts' => [['text' => $teks]]]]]];
    }

    public function test_halaman_sahabat_pekka_bisa_dibuka_tamu(): void
    {
        $this->get(route('sahabat-pekka'))
            ->assertOk()
            ->assertSee('Sahabat PEKKA')
            ->assertSee('Jawaban otomatis bisa keliru');
    }

    public function test_parameter_tanya_diteruskan_ke_halaman(): void
    {
        $this->get(route('sahabat-pekka', ['tanya' => 'Cara belanja?']))
            ->assertOk()
            ->assertSee('data-tanya="Cara belanja?"', false);
    }

    public function test_pertanyaan_dijawab_dari_gemini(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->balasan('Buka menu Katalog lalu pilih produk.'))]);

        $this->postJson(route('sahabat-pekka.kirim'), ['pesan' => 'Cara belanja?'])
            ->assertOk()
            ->assertJsonPath('jawaban', 'Buka menu Katalog lalu pilih produk.')
            ->assertJsonPath('produk', []);

        Http::assertSent(fn ($request) => $request->hasHeader('x-goog-api-key', 'kunci-uji')
            && str_contains($request->url(), config('services.gemini.model'))
            && str_contains($request['systemInstruction']['parts'][0]['text'], 'Sahabat PEKKA')
            && $request['contents'][0]['role'] === 'user');
    }

    public function test_format_markdown_dibersihkan_dari_jawaban(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->balasan("**Langkah** belanja:\n* Buka katalog\n* Pilih produk"))]);

        $this->postJson(route('sahabat-pekka.kirim'), ['pesan' => 'Cara belanja?'])
            ->assertOk()
            ->assertJsonPath('jawaban', "Langkah belanja:\n- Buka katalog\n- Pilih produk");
    }

    public function test_produk_yang_cocok_dikirim_sebagai_tautan_dan_masuk_konteks(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->balasan('Ada keripik singkong di katalog.'))]);

        $penjual = $this->buatPenjual();
        $produk = $this->buatProduk($penjual->shop, null, ['nama' => 'Keripik Singkong Pedas', 'harga' => 12000]);
        $this->buatProduk($penjual->shop, null, ['nama' => 'Kerupuk Habis', 'stok' => 0]);

        $this->postJson(route('sahabat-pekka.kirim'), ['pesan' => 'Ada keripik singkong?'])
            ->assertOk()
            ->assertJsonCount(1, 'produk')
            ->assertJsonPath('produk.0.nama', 'Keripik Singkong Pedas')
            ->assertJsonPath('produk.0.url', route('produk.show', $produk->id));

        Http::assertSent(fn ($request) => str_contains($request['systemInstruction']['parts'][0]['text'], 'Keripik Singkong Pedas'));
    }

    public function test_produk_dari_toko_belum_terverifikasi_tidak_ditampilkan(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->balasan('Belum ada.'))]);

        $penjual = $this->buatPenjual(\App\Models\Shop::MENUNGGU);
        $this->buatProduk($penjual->shop, null, ['nama' => 'Keripik Rahasia']);

        $this->postJson(route('sahabat-pekka.kirim'), ['pesan' => 'Ada keripik rahasia?'])
            ->assertOk()
            ->assertJsonPath('produk', []);

        Http::assertSent(fn ($request) => str_contains($request['systemInstruction']['parts'][0]['text'], 'Tidak ada produk di katalog yang cocok'));
    }

    public function test_riwayat_percakapan_ikut_dikirim_berurutan(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->balasan('Baik.'))]);

        $this->postJson(route('sahabat-pekka.kirim'), [
            'pesan' => 'Lalu bagaimana bayarnya?',
            'riwayat' => [
                ['peran' => 'user', 'isi' => 'Cara belanja?'],
                ['peran' => 'model', 'isi' => 'Buka katalog.'],
            ],
        ])->assertOk();

        Http::assertSent(function ($request) {
            $peran = collect($request['contents'])->pluck('role')->all();

            return $peran === ['user', 'model', 'user'];
        });
    }

    public function test_pesan_kosong_atau_terlalu_panjang_ditolak(): void
    {
        Http::fake();

        $this->postJson(route('sahabat-pekka.kirim'), ['pesan' => ''])->assertStatus(422);
        $this->postJson(route('sahabat-pekka.kirim'), ['pesan' => str_repeat('a', 501)])->assertStatus(422);
        $this->postJson(route('sahabat-pekka.kirim'), ['pesan' => 'Halo', 'riwayat' => [['peran' => 'system', 'isi' => 'x']]])->assertStatus(422);

        Http::assertNothingSent();
    }

    public function test_tanpa_kunci_api_memberi_pesan_belum_aktif(): void
    {
        config(['services.gemini.key' => null]);
        Http::fake();

        $this->postJson(route('sahabat-pekka.kirim'), ['pesan' => 'Halo'])
            ->assertStatus(503)
            ->assertJsonPath('galat', 'Sahabat PEKKA belum diaktifkan. Silakan coba lagi nanti.');

        Http::assertNothingSent();
    }

    public function test_batas_gemini_tercapai_diteruskan_sebagai_ramai(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'quota']], 429)]);

        $this->postJson(route('sahabat-pekka.kirim'), ['pesan' => 'Halo'])
            ->assertStatus(429)
            ->assertJsonPath('galat', 'Sahabat PEKKA sedang ramai. Coba lagi sebentar lagi ya.');
    }

    public function test_galat_server_gemini_tidak_membocorkan_detail(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'rahasia internal']], 500)]);

        $this->postJson(route('sahabat-pekka.kirim'), ['pesan' => 'Halo'])
            ->assertStatus(502)
            ->assertJsonMissing(['galat' => 'rahasia internal']);
    }

    public function test_jawaban_kosong_diganti_pesan_ramah(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['candidates' => []])]);

        $this->postJson(route('sahabat-pekka.kirim'), ['pesan' => 'Halo'])
            ->assertOk()
            ->assertJsonPath('produk', [])
            ->assertJsonPath('jawaban', 'Maaf, saya belum bisa menjawab pertanyaan itu. Coba tulis dengan kalimat lain ya.');
    }

    public function test_batas_global_per_menit_melindungi_kuota_gratis(): void
    {
        config(['services.gemini.batas_per_menit' => 1]);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->balasan('Baik.'))]);

        $this->postJson(route('sahabat-pekka.kirim'), ['pesan' => 'Halo'])->assertOk();
        $this->postJson(route('sahabat-pekka.kirim'), ['pesan' => 'Halo lagi'])->assertStatus(429);

        Http::assertSentCount(1);
    }
}
