@extends('layouts.auth')

@section('judul', 'Pendaftaran Berhasil')

@section('isi')
    <div class="flex flex-col items-center text-center">
        <span class="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-success text-white" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5 9-10"/></svg>
        </span>
        <h1 class="text-3xl font-extrabold">Pendaftaran Berhasil!</h1>
        <p class="mt-2 font-semibold text-success">Tokomu sedang menunggu verifikasi admin kelompok.</p>
    </div>

    <section class="card flex flex-col gap-4">
        <div class="flex items-center justify-between gap-2">
            <span class="rounded-full bg-lime px-3 py-1 text-sm font-extrabold text-ink">Status: Menunggu Tinjauan</span>
            <span class="text-xs text-ink/60">ID: PK-{{ str_pad($shop->id, 5, '0', STR_PAD_LEFT) }}</span>
        </div>

        <div class="rounded-xl bg-cream/70 p-3">
            <p class="font-extrabold">{{ $shop->nama }}</p>
            <p class="text-sm text-ink/70">Kelompok {{ $shop->kelompok->label() }}</p>
        </div>

        <ol class="space-y-4 text-sm">
            <li class="flex gap-3">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-success text-white" aria-hidden="true">&#10003;</span>
                <span><span class="block font-extrabold">Data usaha diterima</span>Data usahamu sudah tercatat di sistem.</span>
            </li>
            <li class="flex gap-3">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-lime font-extrabold" aria-hidden="true">2</span>
                <span><span class="block font-extrabold">Validasi admin kelompok</span>Pengurus kelompok PEKKA memeriksa data usahamu sebelum toko tampil di katalog.</span>
            </li>
            <li class="flex gap-3">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-olive/15 font-extrabold text-olive" aria-hidden="true">3</span>
                <span><span class="block font-extrabold">Kabar lewat email</span>Kamu akan diberi tahu lewat email setelah tokomu terverifikasi.</span>
            </li>
        </ol>
    </section>

    <a href="{{ route('dashboard') }}" class="btn-primary">Masuk Sekarang</a>
@endsection
