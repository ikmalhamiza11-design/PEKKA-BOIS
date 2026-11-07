<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\Penjual\ProdukController as ProdukPenjualController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\ProfilController;
use Illuminate\Support\Facades\Route;

Route::get('/', BerandaController::class)->name('beranda');

Route::get('/katalog', KatalogController::class)->name('katalog');
Route::get('/produk/{id}', [ProdukController::class, 'show'])->whereNumber('id')->name('produk.show');

$segera = [
    'peta' => 'Peta Usaha',
    'kegiatan' => 'Kegiatan',
    'panduan' => 'Panduan',
    'sahabat-pekka' => 'Sahabat PEKKA',
];

foreach ($segera as $alamat => $judul) {
    Route::view('/'.$alamat, 'segera', ['judul' => $judul])->name($alamat);
}

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [LoginController::class, 'create'])->name('login');
    Route::post('/masuk', [LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');

    Route::get('/daftar', [RegisterController::class, 'pilihPeran'])->name('daftar');
    Route::get('/daftar/lanjut', [RegisterController::class, 'lanjut'])->name('daftar.lanjut');
    Route::get('/daftar/pembeli', [RegisterController::class, 'formPembeli'])->name('daftar.pembeli');
    Route::post('/daftar/pembeli', [RegisterController::class, 'simpanPembeli'])->middleware('throttle:10,1')->name('daftar.pembeli.simpan');
    Route::get('/daftar/penjual', [RegisterController::class, 'formPenjual'])->name('daftar.penjual');
    Route::post('/daftar/penjual', [RegisterController::class, 'simpanPenjual'])->middleware('throttle:10,1')->name('daftar.penjual.simpan');

    Route::get('/lupa-kata-sandi', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/lupa-kata-sandi', [PasswordResetController::class, 'store'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/atur-ulang-kata-sandi/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/atur-ulang-kata-sandi', [PasswordResetController::class, 'update'])->middleware('throttle:6,1')->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/keluar', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/email/verifikasi', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verifikasi/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');
    Route::post('/email/verifikasi/kirim-ulang', [EmailVerificationController::class, 'send'])->middleware('throttle:6,1')->name('verification.send');
    Route::post('/email/ganti', [EmailVerificationController::class, 'change'])->middleware('throttle:6,1')->name('verification.change');

    Route::middleware('verified')->group(function () {
        Route::get('/pendaftaran-berhasil', [RegisterController::class, 'berhasil'])->name('pendaftaran.berhasil');
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::get('/profil', [ProfilController::class, 'show'])->name('profil');
        Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
        Route::put('/profil/email', [ProfilController::class, 'email'])->middleware('throttle:6,1')->name('profil.email');
        Route::put('/profil/kata-sandi', [ProfilController::class, 'sandi'])->middleware('throttle:6,1')->name('profil.sandi');

        Route::get('/keranjang', [KeranjangController::class, 'index'])->name('keranjang');
        Route::post('/keranjang', [KeranjangController::class, 'tambah'])->middleware('throttle:60,1')->name('keranjang.tambah');
        Route::patch('/keranjang/{id}', [KeranjangController::class, 'ubah'])->whereNumber('id')->name('keranjang.ubah');
        Route::delete('/keranjang/{id}', [KeranjangController::class, 'hapus'])->whereNumber('id')->name('keranjang.hapus');

        Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
        Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');

        Route::get('/pesanan', [PesananController::class, 'index'])->name('pesanan');
        Route::get('/pesanan/{id}', [PesananController::class, 'show'])->whereNumber('id')->name('pesanan.show');
        Route::patch('/pesanan/{id}/batal', [PesananController::class, 'batal'])->whereNumber('id')->name('pesanan.batal');
    });
});

Route::middleware(['auth', 'verified', 'penjual'])->prefix('penjual')->name('penjual.')->group(function () {
    Route::get('/produk', [ProdukPenjualController::class, 'index'])->name('produk.index');
    Route::get('/produk/tambah', [ProdukPenjualController::class, 'create'])->name('produk.create');
    Route::post('/produk', [ProdukPenjualController::class, 'store'])->middleware('throttle:30,1')->name('produk.store');
    Route::get('/produk/{id}/ubah', [ProdukPenjualController::class, 'edit'])->whereNumber('id')->name('produk.edit');
    Route::put('/produk/{id}', [ProdukPenjualController::class, 'update'])->whereNumber('id')->name('produk.update');
    Route::patch('/produk/{id}/status', [ProdukPenjualController::class, 'status'])->whereNumber('id')->name('produk.status');
    Route::delete('/produk/{id}', [ProdukPenjualController::class, 'destroy'])->whereNumber('id')->name('produk.destroy');
});
