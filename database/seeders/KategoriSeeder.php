<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $daftar = [
            ['nama' => 'Kuliner', 'slug' => 'kuliner'],
            ['nama' => 'Fashion', 'slug' => 'fashion'],
            ['nama' => 'Kerajinan', 'slug' => 'kerajinan'],
            ['nama' => 'Pertanian', 'slug' => 'pertanian'],
            ['nama' => 'Jasa', 'slug' => 'jasa'],
            ['nama' => 'Oleh-oleh', 'slug' => 'oleh-oleh'],
        ];

        foreach ($daftar as $urutan => $kategori) {
            Category::updateOrCreate(
                ['slug' => $kategori['slug']],
                ['nama' => $kategori['nama'], 'urutan' => $urutan + 1]
            );
        }
    }
}
