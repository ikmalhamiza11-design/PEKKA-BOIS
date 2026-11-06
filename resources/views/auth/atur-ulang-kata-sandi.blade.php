@extends('layouts.auth')

@section('judul', 'Atur Kata Sandi Baru')

@section('appbar')
    <x-app-bar judul="Atur Kata Sandi" :kembali="route('login')" />
@endsection

@section('isi')
    <div class="flex flex-col items-center text-center">
        <span class="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-lime/30 text-olive" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 6v6c0 4.5 3.2 8 8 9 4.8-1 8-4.5 8-9V6z"/><path d="m9 12 2 2 4-4"/></svg>
        </span>
        <h1 class="text-3xl font-extrabold">Buat kata sandi baru</h1>
        <p class="mt-2 text-ink/70">Gunakan kombinasi minimal 8 karakter agar toko dan data usahamu tetap aman terlindungi.</p>
    </div>

    @if ($errors->has('email'))
        <x-alert tipe="galat">{{ $errors->first('email') }}</x-alert>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="card flex flex-col gap-4" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ old('email', $email) }}">
        <x-password-field name="password" label="Kata sandi baru" placeholder="Masukkan kata sandi baru" autocomplete="new-password" :meter="true" />
        <x-password-field name="password_confirmation" label="Konfirmasi kata sandi baru" placeholder="Ulangi kata sandi baru" autocomplete="new-password" />
        <button type="submit" class="btn-primary">Simpan Kata Sandi Baru</button>
    </form>

    <a href="{{ route('login') }}" class="text-center text-sm font-bold text-olive underline">Batal dan kembali ke halaman masuk</a>
@endsection
