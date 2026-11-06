@extends('layouts.app')

@section('judul', $judul)

@section('isi')
    <div class="mx-auto flex max-w-md flex-col items-center gap-4 px-4 py-20 text-center">
        <span class="flex h-20 w-20 items-center justify-center rounded-full bg-lime/30 text-olive" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        </span>
        <h1 class="text-3xl font-extrabold">Segera hadir</h1>
        <p class="text-ink/70">Halaman {{ $judul }} sedang kami siapkan.</p>
        <a href="{{ route('beranda') }}" class="btn-primary max-w-xs">Kembali ke Beranda</a>
    </div>
@endsection
