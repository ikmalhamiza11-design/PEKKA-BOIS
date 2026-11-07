@extends('layouts.auth')

@section('judul', 'Buat Kata Sandi Baru')

@section('isi')
    <div class="flex flex-col items-center text-center">
        <span class="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-lime/30 text-olive" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </span>
        <h1 class="text-3xl font-extrabold">Buat kata sandi baru</h1>
        <p class="mt-2 text-ink/70">Ibu/Bapak masuk memakai sandi sementara dari operator. Demi keamanan, silakan buat kata sandi baru yang hanya Ibu/Bapak yang tahu.</p>
    </div>

    <form method="POST" action="{{ route('sandi.wajib.simpan') }}" class="card flex flex-col gap-4" novalidate>
        @csrf
        @method('PUT')
        <x-password-field name="password" label="Kata sandi baru" autocomplete="new-password" :meter="true" />
        <x-password-field name="password_confirmation" label="Ulangi kata sandi baru" autocomplete="new-password" />
        <button type="submit" class="btn-primary">Simpan Kata Sandi Baru</button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="w-full text-center text-sm font-semibold text-ink/60 underline">Keluar</button>
    </form>
@endsection
