<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            KelompokSeeder::class,
            KategoriSeeder::class,
            AdminDinasSeeder::class,
            PanduanSeeder::class,
            KegiatanContohSeeder::class,
            ContohProdukSeeder::class,
        ]);
    }
}
