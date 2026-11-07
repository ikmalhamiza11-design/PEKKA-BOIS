<?php

namespace Database\Seeders;

use App\Models\Panduan;
use Illuminate\Database\Seeder;

class PanduanSeeder extends Seeder
{
    public function run(): void
    {
        $daftar = [
            ['pembeli', 1, 'Cara berbelanja', "1. Buka menu Katalog, lalu cari produk dengan kata kunci atau saring menurut kategori, kelompok, dan harga.\n2. Buka halaman produk untuk melihat detail dan ulasan pembeli lain.\n3. Tekan tombol keranjang. Kamu perlu masuk dengan akun yang emailnya sudah diverifikasi.\n4. Buka Keranjang, atur jumlah, lalu lanjut ke Checkout.\n5. Isi nama penerima, nomor telepon, dan alamat pengiriman, lalu buat pesanan.\n\nJika keranjang berisi produk dari beberapa toko, sistem membuat satu pesanan untuk setiap toko."],
            ['pembeli', 2, 'Cara membayar pesanan', "1. Buka Pesanan Saya, lalu pilih pesanan yang belum dibayar.\n2. Lihat rekening penjual dan jumlah yang harus ditransfer.\n3. Transfer sesuai jumlah tersebut lewat bank atau e-wallet pilihanmu.\n4. Kembali ke halaman pesanan, isi nama pengirim, lalu unggah foto bukti transfer.\n5. Tunggu penjual memeriksa bukti transfermu.\n\nJika bukti ditolak, alasannya tampil di halaman pesanan dan kamu bisa mengunggah bukti yang baru. Ongkos kirim belum termasuk dan dikonfirmasi oleh penjual."],
            ['pembeli', 3, 'Memantau pesanan dan menulis ulasan', "1. Status pesanan tampil di halaman pesanan: menunggu pembayaran, dibayar, diproses, dikirim, lalu selesai.\n2. Setelah pesanan dikirim, nama kurir dan nomor resi tampil di halaman pesanan.\n3. Setelah barang sampai, tekan Pesanan Sudah Diterima.\n4. Setelah itu kamu bisa menulis ulasan untuk produk yang kamu beli.\n\nPesanan yang belum dibayar bisa kamu batalkan sendiri, dan stok produk akan kembali."],
            ['penjual', 1, 'Mendaftar dan verifikasi toko', "1. Pilih Daftar, lalu pilih peran Penjual.\n2. Isi data diri, pilih kelompok PEKKA-mu, lalu buat kata sandi.\n3. Buka email dan klik tautan verifikasi.\n4. Toko barumu berstatus menunggu verifikasi dari admin.\n5. Produkmu baru tampil di katalog setelah tokomu disetujui.\n\nJika tokomu ditolak, alasannya tampil di dasbormu."],
            ['penjual', 2, 'Menambah dan mengelola produk', "1. Buka Dasbor, lalu pilih Kelola Produk atau Tambah Produk.\n2. Isi nama, kategori, deskripsi, harga, dan stok.\n3. Unggah foto produk yang terang dan jelas.\n4. Produk bisa kamu nonaktifkan sementara tanpa dihapus.\n\nPastikan stok selalu diperbarui supaya pembeli tidak kecewa."],
            ['penjual', 3, 'Mengisi rekening pembayaran', "1. Buka Dasbor, lalu pilih Rekening Pembayaran.\n2. Isi nama bank atau e-wallet, nomor rekening, dan nama pemilik rekening.\n3. Simpan.\n\nPembeli baru bisa mengirim bukti transfer setelah rekeningmu terisi. Jangan membagikan kata sandi atau kode verifikasi kepada siapa pun."],
            ['penjual', 4, 'Memproses pesanan masuk', "1. Buka Pesanan Masuk dan pilih pesanan yang perlu diverifikasi.\n2. Periksa bukti transfer, lalu pilih Terima atau Tolak. Jika menolak, tulis alasannya.\n3. Setelah pembayaran diterima, tekan Mulai Proses Pesanan.\n4. Setelah barang diserahkan untuk dikirim, isi kurir dan nomor resi, lalu tekan Tandai Sudah Dikirim.\n5. Pembeli akan mengonfirmasi saat barang sampai.\n\nKamu juga bisa membalas ulasan pembeli di menu Ulasan Pembeli."],
            ['pembeli', 4, 'Membatalkan pesanan', "1. Buka Pesanan Saya, lalu pilih pesanan yang belum dibayar.\n2. Tekan Batalkan Pesanan.\n3. Stok produk otomatis kembali.\n\nPesanan yang belum dibayar selama 24 jam juga dibatalkan otomatis oleh sistem. Pesanan yang sudah dibayar tidak bisa dibatalkan sendiri, jadi hubungi penjual jika ada masalah."],
            ['pembeli', 5, 'Mencari usaha lewat Peta Usaha', "1. Buka menu Peta Usaha.\n2. Titik di peta menunjukkan desa atau kecamatan kelompok PEKKA yang punya toko terverifikasi.\n3. Tekan sebuah titik untuk melihat nama kelompok dan tokonya.\n\nDemi privasi anggota, peta hanya menunjukkan wilayah desa atau kecamatan, bukan alamat rumah."],
            ['penjual', 5, 'Membalas ulasan pembeli', "1. Buka Dasbor, lalu pilih Ulasan Pembeli.\n2. Pilih ulasan yang belum dibalas, tulis balasan singkat (maksimal 500 karakter), lalu kirim.\n\nBalas dengan sopan, termasuk untuk ulasan yang kurang baik. Ulasan tidak bisa diubah atau dihapus oleh penjual. Jika ada ulasan yang kasar atau melanggar aturan, admin bisa menyembunyikannya."],
            ['penjual', 6, 'Tips foto dan deskripsi produk', "1. Foto di tempat yang terang, dengan latar polos dan produk di tengah.\n2. Tulis bahan, ukuran atau berat, dan cara penyimpanan di deskripsi.\n3. Tulis harga dan stok yang sebenarnya.\n4. Jangan menulis klaim yang belum bisa dibuktikan, misalnya izin atau sertifikat yang belum kamu miliki."],
            ['umum', 1, 'Tentang PEKKA BOIS', "PEKKA BOIS (PEKKA Bogor Istimewa) adalah marketplace usaha anggota kelompok Perempuan Kepala Keluarga (PEKKA) di Kabupaten Bogor. Di sini pembeli bisa menemukan produk anggota, dan anggota bisa menjual produknya secara online."],
            ['umum', 2, 'Bertanya kepada Sahabat PEKKA', "Sahabat PEKKA adalah asisten otomatis (AI) yang bisa menjawab pertanyaan tentang cara belanja, cara berjualan, dan mencari produk. Jawabannya bisa keliru, jadi untuk urusan penting seperti perizinan dan kehalalan, pastikan ke sumber resmi. Jangan menulis kata sandi, nomor rekening, atau data pribadi di sana."],
            ['umum', 3, 'Menjaga keamanan akun', "1. Gunakan kata sandi yang kuat dan jangan dibagikan.\n2. Jangan mengirim bukti transfer atau data pribadi lewat tempat lain selain halaman pesanan.\n3. Keluar dari akun jika memakai perangkat bersama."],
        ];

        foreach ($daftar as [$kategori, $urutan, $judul, $isi]) {
            Panduan::firstOrCreate(
                ['kategori' => $kategori, 'judul' => $judul],
                ['isi' => $isi, 'urutan' => $urutan, 'dipublikasikan' => true]
            );
        }
    }
}
