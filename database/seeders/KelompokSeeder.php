<?php

namespace Database\Seeders;

use App\Models\Kelompok;
use Illuminate\Database\Seeder;

class KelompokSeeder extends Seeder
{
    public function run(): void
    {
        $berkas = database_path('data/kelompok_pekka.csv');

        if (is_file($berkas)) {
            $this->muatDariCsv($berkas);

            return;
        }

        if (app()->environment('local')) {
            foreach (range(1, 5) as $nomor) {
                Kelompok::firstOrCreate(['nama' => 'Kelompok PEKKA Contoh '.$nomor, 'desa' => null, 'kecamatan' => null]);
            }

            $this->command?->warn('Berkas database/data/kelompok_pekka.csv belum ada. Diisi 5 kelompok contoh untuk pengembangan.');

            return;
        }

        $this->command?->warn('Berkas database/data/kelompok_pekka.csv belum ada. Daftar kelompok PEKKA tidak diisi.');
    }

    private function muatDariCsv(string $berkas): void
    {
        $buka = fopen($berkas, 'r');

        if ($buka === false) {
            return;
        }

        $barisPertama = (string) fgets($buka);
        rewind($buka);
        $pemisah = substr_count($barisPertama, ';') > substr_count($barisPertama, ',') ? ';' : ',';

        $judul = fgetcsv($buka, 0, $pemisah);

        if (! $judul) {
            fclose($buka);

            return;
        }

        $judul[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $judul[0]);
        $judul = array_map(fn ($kolom) => strtolower(trim((string) $kolom)), $judul);

        while (($baris = fgetcsv($buka, 0, $pemisah)) !== false) {
            $baris = array_slice(array_pad($baris, count($judul), ''), 0, count($judul));
            $data = array_combine($judul, $baris);
            $nama = trim((string) ($data['nama'] ?? ''));

            if ($nama === '') {
                continue;
            }

            $desa = trim((string) ($data['desa'] ?? ''));
            $kecamatan = trim((string) ($data['kecamatan'] ?? ''));

            Kelompok::firstOrCreate([
                'nama' => $nama,
                'desa' => $desa !== '' ? $desa : null,
                'kecamatan' => $kecamatan !== '' ? $kecamatan : null,
            ]);
        }

        fclose($buka);
    }
}
