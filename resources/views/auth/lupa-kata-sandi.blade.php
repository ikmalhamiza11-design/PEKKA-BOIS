@extends('layouts.auth')

@section('judul', 'Lupa Kata Sandi')

@section('appbar')
    <x-app-bar judul="Lupa Kata Sandi" :kembali="route('login')" />
@endsection

@section('isi')
    <div class="flex flex-col items-center text-center">
        <span class="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-lime/30 text-olive" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/><path d="M12 8v4l3 2"/></svg>
        </span>
        <h1 class="text-3xl font-extrabold">Lupa kata sandi?</h1>
        <p class="mt-2 text-ink/70">Jangan khawatir, Bu/Pak. Masukkan email terdaftar Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi.</p>
    </div>

    @if (session('status') === 'tautan-reset-terkirim')
        <x-alert tipe="sukses">Jika email terdaftar, tautan untuk mengatur ulang kata sandi sudah kami kirim. Periksa kotak masuk dan folder Spam.</x-alert>
    @endif

    @if (session('status') === 'email-gagal')
        <x-alert tipe="galat" judul="Email belum bisa dikirim">Ada gangguan saat mengirim email. Coba lagi beberapa saat lagi, atau lapor ke operator lewat tautan di bawah.</x-alert>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="card flex flex-col gap-4" novalidate>
        @csrf
        <x-field name="email" label="Alamat email" type="email" placeholder="contoh@email.com" autocomplete="email" inputmode="email" hint="Pastikan email aktif dan bisa dibuka langsung di perangkat ini." />
        <button type="submit" class="btn-primary">Kirim tautan reset</button>
    </form>

    <a href="{{ route('bantuan.masuk') }}" class="text-center text-sm font-bold text-olive underline">Email tidak bisa dibuka? Lapor ke operator</a>
    <a href="{{ route('login') }}" class="text-center text-sm font-bold text-olive underline">Kembali ke halaman masuk</a>
@endsection
