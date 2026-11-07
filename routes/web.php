<?php

use App\Http\Controllers\Admin\AdminKelompokController;
use App\Http\Controllers\Admin\DasborController;
use App\Http\Controllers\Admin\KegiatanController as KegiatanAdminController;
use App\Http\Controllers\Admin\KelompokController;
use App\Http\Controllers\Admin\LaporanAkunController;
use App\Http\Controllers\Admin\OperatorController;
use App\Http\Controllers\Admin\PanduanController as PanduanAdminController;
use App\Http\Controllers\Admin\TokoController;
use App\Http\Controllers\Admin\UlasanController as UlasanAdminController;
use App\Http\Controllers\Auth\BantuanMasukController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SandiWajibController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\PanduanController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\Penjual\PembayaranController as PembayaranPenjualController;
use App\Http\Controllers\Penjual\PesananController as PesananPenjualController;
use App\Http\Controllers\Penjual\ProdukController as ProdukPenjualController;
use App\Http\Controllers\Penjual\RekeningController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\PetaController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\Penjual\UlasanController as UlasanPenjualController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\SahabatPekkaController;
use App\Http\Controllers\UlasanController;
use Illuminate\Support\Facades\Route;

Route::get('/', BerandaController::class)->name('beranda');

Route::get('/katalog', KatalogController::class)->name('katalog');
Route::get('/produk/{id}', [ProdukController::class, 'show'])->whereNumber('id')->name('produk.show');

Route::get('/peta', PetaController::class)->name('peta');
Route::get('/kegiatan', [KegiatanController::class, 'index'])->name('kegiatan');
Route::get('/kegiatan/{id}', [KegiatanController::class, 'show'])->whereNumber('id')->name('kegiatan.show');
Route::get('/panduan', PanduanController::class)->name('panduan');

Route::get('/sahabat-pekka', [SahabatPekkaController::class, 'index'])->name('sahabat-pekka');
Route::post('/sahabat-pekka/kirim', [SahabatPekkaController::class, 'kirim'])->middleware('throttle:12,1,sahabat.kirim.')->name('sahabat-pekka.kirim');

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [LoginController::class, 'create'])->name('login');
    Route::post('/masuk', [LoginController::class, 'store'])->middleware('throttle:10,1,login.store.')->name('login.store');

    Route::get('/daftar', [RegisterController::class, 'pilihPeran'])->name('daftar');
    Route::get('/daftar/lanjut', [RegisterController::class, 'lanjut'])->name('daftar.lanjut');
    Route::get('/daftar/pembeli', [RegisterController::class, 'formPembeli'])->name('daftar.pembeli');
    Route::post('/daftar/pembeli', [RegisterController::class, 'simpanPembeli'])->middleware('throttle:10,1,daftar.pembeli.simpan.')->name('daftar.pembeli.simpan');
    Route::get('/daftar/penjual', [RegisterController::class, 'formPenjual'])->name('daftar.penjual');
    Route::post('/daftar/penjual', [RegisterController::class, 'simpanPenjual'])->middleware('throttle:10,1,daftar.penjual.simpan.')->name('daftar.penjual.simpan');

    Route::get('/lupa-kata-sandi', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/lupa-kata-sandi', [PasswordResetController::class, 'store'])->middleware('throttle:6,1,password.email.')->name('password.email');
    Route::get('/atur-ulang-kata-sandi/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/atur-ulang-kata-sandi', [PasswordResetController::class, 'update'])->middleware('throttle:6,1,password.update.')->name('password.update');
});

Route::get('/bantuan-masuk', [BantuanMasukController::class, 'create'])->name('bantuan.masuk');
Route::post('/bantuan-masuk', [BantuanMasukController::class, 'store'])->middleware('throttle:5,1,bantuan.masuk.simpan.')->name('bantuan.masuk.simpan');

Route::middleware('auth')->group(function () {
    Route::post('/keluar', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/sandi-baru', [SandiWajibController::class, 'create'])->name('sandi.wajib');
    Route::put('/sandi-baru', [SandiWajibController::class, 'update'])->middleware('throttle:6,1,sandi.wajib.simpan.')->name('sandi.wajib.simpan');

    Route::get('/email/verifikasi', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verifikasi/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');
    Route::post('/email/verifikasi/kirim-ulang', [EmailVerificationController::class, 'send'])->middleware('throttle:6,1,verification.send.')->name('verification.send');
    Route::post('/email/ganti', [EmailVerificationController::class, 'change'])->middleware('throttle:6,1,verification.change.')->name('verification.change');

    Route::middleware('verified')->group(function () {
        Route::get('/pendaftaran-berhasil', [RegisterController::class, 'berhasil'])->name('pendaftaran.berhasil');
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::get('/profil', [ProfilController::class, 'show'])->name('profil');
        Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
        Route::put('/profil/foto', [ProfilController::class, 'foto'])->middleware('throttle:10,1,profil.foto.')->name('profil.foto');
        Route::delete('/profil/foto', [ProfilController::class, 'hapusFoto'])->name('profil.foto.hapus');
        Route::put('/profil/email', [ProfilController::class, 'email'])->middleware('throttle:6,1,profil.email.')->name('profil.email');
        Route::put('/profil/kata-sandi', [ProfilController::class, 'sandi'])->middleware('throttle:6,1,profil.sandi.')->name('profil.sandi');

        Route::get('/keranjang', [KeranjangController::class, 'index'])->name('keranjang');
        Route::post('/keranjang', [KeranjangController::class, 'tambah'])->middleware('throttle:60,1,keranjang.tambah.')->name('keranjang.tambah');
        Route::patch('/keranjang/{id}', [KeranjangController::class, 'ubah'])->whereNumber('id')->name('keranjang.ubah');
        Route::delete('/keranjang/{id}', [KeranjangController::class, 'hapus'])->whereNumber('id')->name('keranjang.hapus');

        Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
        Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1,checkout.store.')->name('checkout.store');

        Route::get('/pesanan', [PesananController::class, 'index'])->name('pesanan');
        Route::get('/pesanan/{id}', [PesananController::class, 'show'])->whereNumber('id')->name('pesanan.show');
        Route::patch('/pesanan/{id}/batal', [PesananController::class, 'batal'])->whereNumber('id')->name('pesanan.batal');

        Route::patch('/pesanan/{id}/diterima', [PesananController::class, 'diterima'])->whereNumber('id')->name('pesanan.diterima');
        Route::get('/pesanan/{id}/ulasan/{itemId}', [UlasanController::class, 'form'])->whereNumber(['id', 'itemId'])->name('ulasan.form');
        Route::post('/pesanan/{id}/ulasan/{itemId}', [UlasanController::class, 'simpan'])->whereNumber(['id', 'itemId'])->middleware('throttle:20,1,ulasan.simpan.')->name('ulasan.simpan');

        Route::post('/pesanan/{id}/pembayaran', [PembayaranController::class, 'store'])->whereNumber('id')->middleware('throttle:10,1,pembayaran.store.')->name('pembayaran.store');
        Route::get('/pembayaran/{id}/bukti', [PembayaranController::class, 'bukti'])->whereNumber('id')->name('pembayaran.bukti');
    });
});

Route::middleware(['auth', 'verified', 'penjual'])->prefix('penjual')->name('penjual.')->group(function () {
    Route::get('/produk', [ProdukPenjualController::class, 'index'])->name('produk.index');
    Route::get('/produk/tambah', [ProdukPenjualController::class, 'create'])->name('produk.create');
    Route::post('/produk', [ProdukPenjualController::class, 'store'])->middleware('throttle:30,1,produk.store.')->name('produk.store');
    Route::get('/produk/{id}/ubah', [ProdukPenjualController::class, 'edit'])->whereNumber('id')->name('produk.edit');
    Route::put('/produk/{id}', [ProdukPenjualController::class, 'update'])->whereNumber('id')->name('produk.update');
    Route::patch('/produk/{id}/status', [ProdukPenjualController::class, 'status'])->whereNumber('id')->name('produk.status');
    Route::delete('/produk/{id}', [ProdukPenjualController::class, 'destroy'])->whereNumber('id')->name('produk.destroy');

    Route::get('/rekening', [RekeningController::class, 'edit'])->name('rekening.edit');
    Route::put('/rekening', [RekeningController::class, 'update'])->middleware('throttle:10,1,rekening.update.')->name('rekening.update');

    Route::get('/pesanan', [PesananPenjualController::class, 'index'])->name('pesanan.index');
    Route::get('/pesanan/{id}', [PesananPenjualController::class, 'show'])->whereNumber('id')->name('pesanan.show');

    Route::patch('/pesanan/{id}/proses', [PesananPenjualController::class, 'proses'])->whereNumber('id')->name('pesanan.proses');
    Route::patch('/pesanan/{id}/kirim', [PesananPenjualController::class, 'kirim'])->whereNumber('id')->name('pesanan.kirim');

    Route::get('/ulasan', [UlasanPenjualController::class, 'index'])->name('ulasan.index');
    Route::put('/ulasan/{id}/balasan', [UlasanPenjualController::class, 'balas'])->whereNumber('id')->middleware('throttle:30,1,ulasan.balas.')->name('ulasan.balas');

    Route::patch('/pembayaran/{id}/terima', [PembayaranPenjualController::class, 'terima'])->whereNumber('id')->name('pembayaran.terima');
    Route::patch('/pembayaran/{id}/tolak', [PembayaranPenjualController::class, 'tolak'])->whereNumber('id')->name('pembayaran.tolak');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DasborController::class)->name('dasbor');

    Route::get('/toko', [TokoController::class, 'index'])->name('toko.index');
    Route::get('/toko/{id}', [TokoController::class, 'show'])->whereNumber('id')->name('toko.show');
    Route::patch('/toko/{id}/setujui', [TokoController::class, 'setujui'])->whereNumber('id')->name('toko.setujui');
    Route::patch('/toko/{id}/tolak', [TokoController::class, 'tolak'])->whereNumber('id')->name('toko.tolak');

    Route::get('/ulasan', [UlasanAdminController::class, 'index'])->name('ulasan.index');
    Route::patch('/ulasan/{id}/sembunyikan', [UlasanAdminController::class, 'sembunyikan'])->whereNumber('id')->name('ulasan.sembunyikan');
    Route::patch('/ulasan/{id}/tampilkan', [UlasanAdminController::class, 'tampilkan'])->whereNumber('id')->name('ulasan.tampilkan');

    Route::middleware('admin.dinas')->group(function () {
        Route::get('/laporan', [LaporanAkunController::class, 'index'])->name('laporan.index');
        Route::post('/laporan/{id}/cocokkan', [LaporanAkunController::class, 'cocokkan'])->whereNumber('id')->name('laporan.cocokkan');
        Route::post('/laporan/{id}/sandi-sementara', [LaporanAkunController::class, 'sandiSementara'])->whereNumber('id')->middleware('throttle:20,1,laporan.sandi.')->name('laporan.sandi');
        Route::post('/laporan/{id}/verifikasi-email', [LaporanAkunController::class, 'verifikasiEmail'])->whereNumber('id')->name('laporan.verifikasi');
        Route::patch('/laporan/{id}/selesai', [LaporanAkunController::class, 'selesai'])->whereNumber('id')->name('laporan.selesai');

        Route::get('/operator', [OperatorController::class, 'index'])->name('operator.index');
        Route::post('/operator', [OperatorController::class, 'store'])->middleware('throttle:20,1,operator.store.')->name('operator.store');
        Route::delete('/operator/{id}', [OperatorController::class, 'destroy'])->whereNumber('id')->name('operator.destroy');

        Route::get('/kelompok', [KelompokController::class, 'index'])->name('kelompok.index');
        Route::post('/kelompok', [KelompokController::class, 'store'])->name('kelompok.store');
        Route::post('/kelompok/impor', [KelompokController::class, 'impor'])->name('kelompok.impor');
        Route::get('/kelompok/{id}/ubah', [KelompokController::class, 'edit'])->whereNumber('id')->name('kelompok.edit');
        Route::put('/kelompok/{id}', [KelompokController::class, 'update'])->whereNumber('id')->name('kelompok.update');
        Route::delete('/kelompok/{id}', [KelompokController::class, 'destroy'])->whereNumber('id')->name('kelompok.destroy');

        Route::get('/admin-kelompok', [AdminKelompokController::class, 'index'])->name('admin-kelompok.index');
        Route::post('/admin-kelompok', [AdminKelompokController::class, 'store'])->name('admin-kelompok.store');
        Route::delete('/admin-kelompok/{id}', [AdminKelompokController::class, 'destroy'])->whereNumber('id')->name('admin-kelompok.destroy');

        Route::get('/kegiatan', [KegiatanAdminController::class, 'index'])->name('kegiatan.index');
        Route::get('/kegiatan/tambah', [KegiatanAdminController::class, 'create'])->name('kegiatan.create');
        Route::post('/kegiatan', [KegiatanAdminController::class, 'store'])->name('kegiatan.store');
        Route::get('/kegiatan/{id}/ubah', [KegiatanAdminController::class, 'edit'])->whereNumber('id')->name('kegiatan.edit');
        Route::put('/kegiatan/{id}', [KegiatanAdminController::class, 'update'])->whereNumber('id')->name('kegiatan.update');
        Route::delete('/kegiatan/{id}', [KegiatanAdminController::class, 'destroy'])->whereNumber('id')->name('kegiatan.destroy');

        Route::get('/panduan', [PanduanAdminController::class, 'index'])->name('panduan.index');
        Route::get('/panduan/tambah', [PanduanAdminController::class, 'create'])->name('panduan.create');
        Route::post('/panduan', [PanduanAdminController::class, 'store'])->name('panduan.store');
        Route::get('/panduan/{id}/ubah', [PanduanAdminController::class, 'edit'])->whereNumber('id')->name('panduan.edit');
        Route::put('/panduan/{id}', [PanduanAdminController::class, 'update'])->whereNumber('id')->name('panduan.update');
        Route::delete('/panduan/{id}', [PanduanAdminController::class, 'destroy'])->whereNumber('id')->name('panduan.destroy');
    });
});
