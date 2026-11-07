<?php

namespace App\Services;

use App\Exceptions\SahabatPekkaGalat;
use App\Models\Product;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class SahabatPekkaService
{
    private const KATA_UMUM = [
        'yang', 'dan', 'atau', 'untuk', 'dengan', 'dari', 'ini', 'itu', 'saya', 'aku', 'kamu', 'anda',
        'mau', 'ingin', 'bisa', 'tidak', 'bukan', 'ada', 'adalah', 'agar', 'supaya', 'pada', 'dalam',
        'cara', 'bagaimana', 'gimana', 'apa', 'apakah', 'kenapa', 'mengapa', 'dimana', 'kapan', 'berapa',
        'siapa', 'tolong', 'minta', 'bantu', 'cari', 'carikan', 'beli', 'belanja', 'pesan', 'pesanan',
        'bayar', 'pembayaran', 'kirim', 'pengiriman', 'daftar', 'jual', 'jualan', 'berjualan', 'produk',
        'toko', 'pekka', 'situs', 'akun', 'usaha', 'nib', 'halal', 'label', 'sertifikat', 'urus', 'mengurus',
        'katalog', 'harga', 'murah', 'terbaik', 'rekomendasi', 'sebutkan', 'tampilkan', 'lihat', 'punya',
        'sudah', 'belum', 'akan', 'lagi', 'juga', 'saja', 'nya', 'dong', 'ya', 'kah', 'apa',
    ];

    public function jawab(string $pesan, array $riwayat = []): array
    {
        $kunci = config('services.gemini.key');

        if (blank($kunci)) {
            throw new SahabatPekkaGalat('Sahabat PEKKA belum diaktifkan. Silakan coba lagi nanti.', 503);
        }

        $batas = (int) config('services.gemini.batas_per_menit', 10);

        if (RateLimiter::tooManyAttempts('sahabat-pekka-global', $batas)) {
            throw new SahabatPekkaGalat('Sahabat PEKKA sedang ramai. Coba lagi sebentar lagi ya.', 429);
        }

        RateLimiter::hit('sahabat-pekka-global', 60);

        $kata = $this->kataKunci($pesan);
        $produk = $this->cariProduk($kata);

        $konfigurasi = [
            'temperature' => 0.4,
            'maxOutputTokens' => 1024,
        ];

        $anggaran = config('services.gemini.thinking_budget');

        if ($anggaran !== null && $anggaran !== '') {
            $konfigurasi['thinkingConfig'] = ['thinkingBudget' => (int) $anggaran];
        }

        $alamat = 'https://generativelanguage.googleapis.com/v1beta/models/'.config('services.gemini.model').':generateContent';

        try {
            $respons = Http::withHeaders(['x-goog-api-key' => $kunci])
                ->acceptJson()
                ->timeout((int) config('services.gemini.timeout', 25))
                ->post($alamat, [
                    'systemInstruction' => ['parts' => [['text' => $this->instruksi($kata, $produk)]]],
                    'contents' => $this->susunPercakapan($pesan, $riwayat),
                    'generationConfig' => $konfigurasi,
                ]);
        } catch (ConnectionException $e) {
            Log::warning('Sahabat PEKKA: koneksi ke Gemini gagal', ['pesan' => $e->getMessage()]);

            throw new SahabatPekkaGalat('Sahabat PEKKA sedang tidak bisa dihubungi. Coba lagi sebentar.', 503);
        }

        if ($respons->status() === 429) {
            throw new SahabatPekkaGalat('Sahabat PEKKA sedang ramai. Coba lagi sebentar lagi ya.', 429);
        }

        if ($respons->failed()) {
            Log::warning('Sahabat PEKKA: Gemini menolak permintaan', [
                'status' => $respons->status(),
                'galat' => $respons->json('error.message'),
            ]);

            throw new SahabatPekkaGalat('Sahabat PEKKA sedang bermasalah. Coba lagi nanti.', 502);
        }

        $teks = collect($respons->json('candidates.0.content.parts', []))
            ->pluck('text')
            ->filter()
            ->implode("\n");

        $teks = $this->rapikan($teks);

        if ($teks === '') {
            return [
                'jawaban' => 'Maaf, saya belum bisa menjawab pertanyaan itu. Coba tulis dengan kalimat lain ya.',
                'produk' => [],
            ];
        }

        return [
            'jawaban' => $teks,
            'produk' => $produk->map(fn (Product $item) => [
                'nama' => $item->nama,
                'harga' => $item->harga_rupiah,
                'toko' => $item->shop->nama,
                'url' => route('produk.show', $item->id),
            ])->values()->all(),
        ];
    }

    protected function kataKunci(string $pesan): Collection
    {
        return collect(preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($pesan), -1, PREG_SPLIT_NO_EMPTY))
            ->reject(fn (string $kata) => mb_strlen($kata) < 3 || in_array($kata, self::KATA_UMUM, true))
            ->unique()
            ->take(5)
            ->values();
    }

    protected function cariProduk(Collection $kata): Collection
    {
        if ($kata->isEmpty()) {
            return collect();
        }

        return Product::tampil()
            ->with('shop')
            ->where('stok', '>', 0)
            ->where(function ($query) use ($kata) {
                foreach ($kata as $item) {
                    $query->orWhere('nama', 'like', '%'.addcslashes($item, '%_\\').'%');
                }
            })
            ->orderByDesc('unggulan')
            ->limit(5)
            ->get();
    }

    protected function instruksi(Collection $kata, Collection $produk): string
    {
        $teks = trim(file_get_contents(resource_path('prompts/sahabat-pekka.txt')));

        if ($kata->isEmpty()) {
            return $teks;
        }

        if ($produk->isEmpty()) {
            return $teks."\n\nKONTEKS KATALOG\nTidak ada produk di katalog yang cocok dengan kata kunci pada pesan terakhir.";
        }

        $daftar = $produk->map(fn (Product $item) => '- '.$item->nama.', '.$item->harga_rupiah.', toko '.$item->shop->nama)->implode("\n");

        return $teks."\n\nKONTEKS KATALOG\nProduk di katalog yang cocok dengan pesan terakhir (sebut hanya jika relevan):\n".$daftar;
    }

    protected function susunPercakapan(string $pesan, array $riwayat): array
    {
        $giliran = collect($riwayat)
            ->map(fn (array $item) => ['role' => $item['peran'], 'text' => trim((string) $item['isi'])])
            ->filter(fn (array $item) => $item['text'] !== '')
            ->push(['role' => 'user', 'text' => trim($pesan)])
            ->values();

        $hasil = [];

        foreach ($giliran as $item) {
            if ($hasil === [] && $item['role'] !== 'user') {
                continue;
            }

            $terakhir = array_key_last($hasil);

            if ($terakhir !== null && $hasil[$terakhir]['role'] === $item['role']) {
                $hasil[$terakhir]['text'] .= "\n".$item['text'];

                continue;
            }

            $hasil[] = $item;
        }

        return array_map(fn (array $item) => ['role' => $item['role'], 'parts' => [['text' => $item['text']]]], $hasil);
    }

    protected function rapikan(string $teks): string
    {
        $teks = preg_replace('/\*\*(.+?)\*\*/s', '$1', $teks);
        $teks = preg_replace('/^\s{0,3}#{1,6}\s*/m', '', $teks);
        $teks = preg_replace('/^\s*[\*\+]\s+/m', '- ', $teks);
        $teks = str_replace('`', '', $teks);

        return trim(preg_replace("/\n{3,}/", "\n\n", $teks));
    }
}
