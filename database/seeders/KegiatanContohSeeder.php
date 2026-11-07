<?php

namespace Database\Seeders;

use App\Models\Kegiatan;
use Illuminate\Database\Seeder;

class KegiatanContohSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $hariIni = now()->startOfDay();

        $daftar = [
            [
                'judul' => '(Contoh) Pelatihan Foto Produk dan Cara Berjualan Online',
                'deskripsi' => "Ini adalah kegiatan contoh untuk melihat tampilan halaman Kegiatan. Hapus atau ganti lewat panel admin sebelum situs dipublikasikan.\n\nMateri yang direncanakan: cara memotret produk dengan ponsel, menulis deskripsi yang jelas, dan mengelola pesanan di PEKKA BOIS.",
                'lokasi' => 'Kantor Kecamatan (contoh)',
                'mulai_at' => $hariIni->copy()->addDays(10)->setTime(9, 0),
                'selesai_at' => $hariIni->copy()->addDays(10)->setTime(12, 0),
            ],
            [
                'judul' => '(Contoh) Pertemuan Rutin Kelompok PEKKA',
                'deskripsi' => "Kegiatan contoh. Pertemuan bulanan anggota untuk berbagi kabar usaha dan membahas kebutuhan pelatihan.\n\nGanti dengan jadwal sebenarnya lewat panel admin.",
                'lokasi' => 'Balai Desa (contoh)',
                'mulai_at' => $hariIni->copy()->addDays(24)->setTime(13, 0),
                'selesai_at' => null,
            ],
            [
                'judul' => '(Contoh) Bazar Produk Anggota PEKKA',
                'deskripsi' => "Kegiatan contoh yang sudah berlalu, untuk melihat tampilan daftar kegiatan lampau.",
                'lokasi' => 'Alun-alun (contoh)',
                'mulai_at' => $hariIni->copy()->subDays(20)->setTime(8, 0),
                'selesai_at' => $hariIni->copy()->subDays(19)->setTime(16, 0),
            ],
        ];

        foreach ($daftar as $data) {
            Kegiatan::firstOrCreate(['judul' => $data['judul']], $data + ['dipublikasikan' => true]);
        }
    }
}
