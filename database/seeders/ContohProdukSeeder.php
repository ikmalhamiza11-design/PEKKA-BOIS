<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Kelompok;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContohProdukSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $kelompok = Kelompok::orderBy('id')->first();

        if (! $kelompok) {
            $this->command?->warn('Belum ada kelompok PEKKA, data contoh dilewati.');

            return;
        }

        foreach ($this->data() as $indeks => $usaha) {
            $user = User::firstOrNew(['email' => 'penjual'.($indeks + 1).'@contoh.test']);

            if (! $user->exists) {
                $user->name = $usaha['pemilik'];
                $user->phone = '62812000000'.($indeks + 1);
                $user->password = Str::random(32);
                $user->role = User::PENJUAL;
                $user->email_verified_at = now();
                $user->save();
            }

            $toko = $user->shop()->updateOrCreate([], [
                'nama' => $usaha['toko'],
                'kelompok_id' => $kelompok->id,
                'status' => Shop::TERVERIFIKASI,
            ]);

            foreach ($usaha['produk'] as $produk) {
                $kategori = Category::where('slug', $produk['kategori'])->first();

                if (! $kategori) {
                    continue;
                }

                Product::updateOrCreate(
                    ['shop_id' => $toko->id, 'nama' => $produk['nama']],
                    [
                        'category_id' => $kategori->id,
                        'deskripsi' => $produk['deskripsi'],
                        'harga' => $produk['harga'],
                        'stok' => $produk['stok'],
                        'aktif' => true,
                        'unggulan' => $produk['unggulan'] ?? false,
                    ]
                );
            }
        }
    }

    private function data(): array
    {
        return [
            [
                'pemilik' => 'Penjual Contoh 1',
                'toko' => 'Dapur Mawar',
                'produk' => [
                    ['nama' => 'Keripik Singkong Balado', 'unggulan' => true, 'kategori' => 'kuliner', 'harga' => 18000, 'stok' => 40, 'deskripsi' => 'Keripik singkong renyah dengan bumbu balado.'],
                    ['nama' => 'Kue Kering Nastar', 'kategori' => 'oleh-oleh', 'harga' => 55000, 'stok' => 25, 'deskripsi' => 'Nastar isi selai nanas dalam toples.'],
                    ['nama' => 'Sambal Roa', 'kategori' => 'kuliner', 'harga' => 30000, 'stok' => 18, 'deskripsi' => 'Sambal roa pedas gurih, botol 150 gram.'],
                    ['nama' => 'Kacang Bawang Gurih', 'kategori' => 'kuliner', 'harga' => 15000, 'stok' => 50, 'deskripsi' => 'Kacang tanah goreng dengan bawang putih renyah.'],
                    ['nama' => 'Dendeng Balado', 'kategori' => 'kuliner', 'harga' => 48000, 'stok' => 0, 'deskripsi' => 'Dendeng sapi balado pedas, kemasan 200 gram.'],
                ],
            ],
            [
                'pemilik' => 'Penjual Contoh 2',
                'toko' => 'Anyaman Melati',
                'produk' => [
                    ['nama' => 'Tas Anyaman Pandan', 'unggulan' => true, 'kategori' => 'kerajinan', 'harga' => 85000, 'stok' => 12, 'deskripsi' => 'Tas anyaman pandan dengan tali kulit sintetis.'],
                    ['nama' => 'Tempat Tisu Rotan', 'kategori' => 'kerajinan', 'harga' => 35000, 'stok' => 20, 'deskripsi' => 'Tempat tisu dari rotan anyaman tangan.'],
                    ['nama' => 'Keranjang Serbaguna', 'kategori' => 'kerajinan', 'harga' => 65000, 'stok' => 8, 'deskripsi' => 'Keranjang anyaman untuk penyimpanan di rumah.'],
                ],
            ],
            [
                'pemilik' => 'Penjual Contoh 3',
                'toko' => 'Batik Kenanga',
                'produk' => [
                    ['nama' => 'Kerudung Batik Cap', 'unggulan' => true, 'kategori' => 'fashion', 'harga' => 70000, 'stok' => 15, 'deskripsi' => 'Kerudung segi empat batik cap motif parang.'],
                    ['nama' => 'Dompet Batik', 'kategori' => 'fashion', 'harga' => 45000, 'stok' => 22, 'deskripsi' => 'Dompet kecil berbahan kain batik.'],
                    ['nama' => 'Selendang Batik Tulis', 'kategori' => 'fashion', 'harga' => 120000, 'stok' => 5, 'deskripsi' => 'Selendang batik tulis dengan pewarna alami.'],
                ],
            ],
            [
                'pemilik' => 'Penjual Contoh 4',
                'toko' => 'Kebun Anggrek',
                'produk' => [
                    ['nama' => 'Madu Hutan 250 ml', 'unggulan' => true, 'kategori' => 'pertanian', 'harga' => 60000, 'stok' => 30, 'deskripsi' => 'Madu hutan murni dalam botol kaca 250 ml.'],
                    ['nama' => 'Jahe Merah Bubuk', 'kategori' => 'pertanian', 'harga' => 25000, 'stok' => 35, 'deskripsi' => 'Jahe merah bubuk siap seduh, kemasan 100 gram.'],
                    ['nama' => 'Teh Daun Kelor', 'kategori' => 'pertanian', 'harga' => 20000, 'stok' => 40, 'deskripsi' => 'Teh daun kelor kering, isi 20 kantong celup.'],
                ],
            ],
            [
                'pemilik' => 'Penjual Contoh 5',
                'toko' => 'Jahit Sari',
                'produk' => [
                    ['nama' => 'Jasa Permak Pakaian', 'kategori' => 'jasa', 'harga' => 20000, 'stok' => 99, 'deskripsi' => 'Permak panjang, lebar, dan perbaikan jahitan.'],
                ],
            ],
            [
                'pemilik' => 'Penjual Contoh 6',
                'toko' => 'Dodol Cempaka',
                'produk' => [
                    ['nama' => 'Dodol Nanas', 'kategori' => 'oleh-oleh', 'harga' => 28000, 'stok' => 28, 'deskripsi' => 'Dodol nanas lembut dalam kotak isi 10 potong.'],
                ],
            ],
        ];
    }
}
