@extends('layouts.admin')

@section('judul', 'Dasbor')

@section('isi')
    <div>
        <h1 class="text-2xl font-extrabold">Halo, {{ $admin->name }}</h1>
        <p class="mt-1 text-ink/70">
            @if ($admin->adalahAdminDinas())
                Anda mengelola seluruh kelompok PEKKA di Kabupaten Bogor.
            @else
                Anda mengelola kelompok {{ $admin->kelompok?->label() }}.
            @endif
        </p>
    </div>

    @if ($statistik['menunggu'] > 0)
        <x-alert judul="{{ $statistik['menunggu'] }} toko menunggu verifikasi">
            Produk toko baru belum tampil di katalog sampai Anda menyetujuinya.
            <a href="{{ route('admin.toko.index') }}" class="font-extrabold underline">Periksa sekarang</a>
        </x-alert>
    @endif

    <section class="grid grid-cols-2 gap-3 md:grid-cols-4" aria-label="Ringkasan">
        <div class="card !p-4">
            <p class="text-3xl font-extrabold text-danger">{{ $statistik['menunggu'] }}</p>
            <p class="text-sm text-ink/70">Toko menunggu</p>
        </div>
        <div class="card !p-4">
            <p class="text-3xl font-extrabold text-olive">{{ $statistik['terverifikasi'] }}</p>
            <p class="text-sm text-ink/70">Toko terverifikasi</p>
        </div>
        <div class="card !p-4">
            <p class="text-3xl font-extrabold text-olive">{{ $statistik['produk'] }}</p>
            <p class="text-sm text-ink/70">Produk</p>
        </div>
        <div class="card !p-4">
            <p class="text-3xl font-extrabold text-olive">{{ $statistik['pesanan'] }}</p>
            <p class="text-sm text-ink/70">Pesanan</p>
        </div>
        <div class="card !p-4">
            <p class="text-3xl font-extrabold text-olive">{{ $statistik['ulasan'] }}</p>
            <p class="text-sm text-ink/70">Ulasan tampil</p>
        </div>
        <div class="card !p-4">
            <p class="text-3xl font-extrabold text-ink/70">{{ $statistik['ulasan_disembunyikan'] }}</p>
            <p class="text-sm text-ink/70">Ulasan disembunyikan</p>
        </div>
        <div class="card !p-4">
            <p class="text-3xl font-extrabold text-ink/70">{{ $statistik['ditolak'] }}</p>
            <p class="text-sm text-ink/70">Toko ditolak</p>
        </div>
    </section>

    @if ($statistikDinas)
        <section class="grid grid-cols-2 gap-3 md:grid-cols-4" aria-label="Ringkasan dinas">
            <div class="card !p-4">
                <p class="text-3xl font-extrabold text-olive">{{ $statistikDinas['kelompok'] }}</p>
                <p class="text-sm text-ink/70">Kelompok PEKKA</p>
            </div>
            <div class="card !p-4">
                <p class="text-3xl font-extrabold text-olive">{{ $statistikDinas['penjual'] }}</p>
                <p class="text-sm text-ink/70">Akun penjual</p>
            </div>
            <div class="card !p-4">
                <p class="text-3xl font-extrabold text-olive">{{ $statistikDinas['pembeli'] }}</p>
                <p class="text-sm text-ink/70">Akun pembeli</p>
            </div>
            <div class="card !p-4">
                <p class="text-3xl font-extrabold text-olive">{{ $statistikDinas['admin_kelompok'] }}</p>
                <p class="text-sm text-ink/70">Admin kelompok</p>
            </div>
        </section>
    @endif

    <section class="card">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-extrabold">Antrean verifikasi toko</h2>
            <a href="{{ route('admin.toko.index') }}" class="text-sm font-bold text-olive underline">Lihat semua</a>
        </div>

        @forelse ($antrean as $toko)
            <a href="{{ route('admin.toko.show', $toko->id) }}" class="mt-3 flex items-center justify-between gap-3 rounded-xl border border-olive/10 p-3 hover:bg-lime/10">
                <span class="min-w-0">
                    <span class="block truncate font-bold">{{ $toko->nama }}</span>
                    <span class="block truncate text-xs text-ink/70">{{ $toko->user->name }} &middot; {{ $toko->kelompok->nama }}</span>
                </span>
                <span class="shrink-0 text-xs text-ink/60">{{ $toko->created_at->format('d/m/Y') }}</span>
            </a>
        @empty
            <p class="mt-3 text-sm text-ink/70">Tidak ada toko yang menunggu. Semua sudah diperiksa.</p>
        @endforelse
    </section>
@endsection
