@extends('layouts.auth')

@section('judul', 'Masuk')

@section('isi')
    <div>
        <p class="mb-3 inline-flex rounded-full bg-lime/25 px-3 py-1 text-xs font-bold text-olive">Etalase usaha perempuan kepala keluarga</p>
        <h1 class="text-3xl font-extrabold leading-tight">Selamat datang kembali</h1>
        <p class="mt-2 text-ink/70">Masuk untuk berbelanja produk usaha PEKKA atau mengelola toko Anda.</p>
    </div>

    @if (session('status') === 'kata-sandi-diubah')
        <x-alert tipe="sukses">Kata sandi berhasil diubah. Silakan masuk dengan kata sandi baru.</x-alert>
    @endif

    @if (session('login_gagal'))
        <x-alert tipe="galat" judul="Email atau kata sandi salah.">Periksa kembali penulisan huruf besar, huruf kecil, atau spasi, lalu coba lagi.</x-alert>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="card flex flex-col gap-4" novalidate>
        @csrf
        <x-field name="email" label="Alamat email" type="email" placeholder="contoh@email.com" autocomplete="email" inputmode="email" :galat="session('login_gagal')" />
        <x-password-field name="password" label="Kata sandi" placeholder="Masukkan kata sandi Anda" autocomplete="current-password" :galat="session('login_gagal')" :lupa="true" />
        <button type="submit" class="btn-primary">Masuk Sekarang</button>
    </form>

    <div class="flex items-center gap-3 text-xs font-bold text-ink/50" aria-hidden="true">
        <span class="h-px flex-1 bg-olive/15"></span>ATAU<span class="h-px flex-1 bg-olive/15"></span>
    </div>

    <a href="{{ route('daftar') }}" class="btn-secondary">Daftar Akun Baru</a>
@endsection
