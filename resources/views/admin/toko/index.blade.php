@extends('layouts.admin')

@section('judul', 'Verifikasi Toko')

@section('isi')
    @php
        $tab = [
            'menunggu' => ['Menunggu', $hitung['menunggu'] ?? 0],
            'terverifikasi' => ['Terverifikasi', $hitung['terverifikasi'] ?? 0],
            'ditolak' => ['Ditolak', $hitung['ditolak'] ?? 0],
            'semua' => ['Semua', $hitung->sum()],
        ];
        $lencana = [
            'menunggu' => 'bg-lime/30 text-olive',
            'terverifikasi' => 'bg-green-100 text-success',
            'ditolak' => 'bg-red-100 text-danger',
        ];
    @endphp

    <h1 class="text-2xl font-extrabold">Verifikasi Toko</h1>

    <nav class="gulir-x -mx-1 flex gap-2 px-1" aria-label="Status toko">
        @foreach ($tab as $kunci => [$nama, $jumlah])
            <a href="{{ route('admin.toko.index', array_filter(['status' => $kunci, 'q' => $kata, 'kelompok' => $kelompokId ?: null])) }}" @class(['btn-primary' => $status === $kunci, 'btn-secondary' => $status !== $kunci, 'min-h-11 w-auto shrink-0 !px-4 !py-2 text-sm']) @if ($status === $kunci) aria-current="page" @endif>{{ $nama }} ({{ $jumlah }})</a>
        @endforeach
    </nav>

    <form method="GET" action="{{ route('admin.toko.index') }}" class="card !p-4 flex flex-col gap-3 sm:flex-row sm:items-end">
        <input type="hidden" name="status" value="{{ $status }}">
        <div class="flex-1">
            <label for="q" class="mb-1.5 block text-sm font-bold">Cari toko atau penjual</label>
            <input id="q" name="q" type="search" value="{{ $kata }}" maxlength="100" placeholder="Nama toko, nama penjual, atau email" class="input">
        </div>
        @if ($kelompok->isNotEmpty())
            <div class="sm:w-64">
                <label for="kelompok" class="mb-1.5 block text-sm font-bold">Kelompok</label>
                <select id="kelompok" name="kelompok" class="input">
                    <option value="">Semua kelompok</option>
                    @foreach ($kelompok as $item)
                        <option value="{{ $item->id }}" @selected($kelompokId === $item->id)>{{ $item->nama }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <button type="submit" class="btn-primary sm:w-auto">Cari</button>
    </form>

    @forelse ($toko as $item)
        <a href="{{ route('admin.toko.show', $item->id) }}" class="card !p-4 flex items-center justify-between gap-3 hover:bg-lime/10">
            <span class="min-w-0">
                <span class="block truncate font-extrabold">{{ $item->nama }}</span>
                <span class="block truncate text-sm text-ink/70">{{ $item->user->name }} &middot; {{ $item->user->email }}</span>
                <span class="block truncate text-xs text-ink/60">{{ $item->kelompok->label() }} &middot; {{ $item->products_count }} produk &middot; daftar {{ $item->created_at->format('d/m/Y') }}</span>
            </span>
            <span class="shrink-0 rounded-full px-3 py-1 text-xs font-bold {{ $lencana[$item->status] ?? $lencana['menunggu'] }}">{{ $item->labelStatus() }}</span>
        </a>
    @empty
        <section class="card text-center">
            <p class="font-extrabold">Tidak ada toko yang cocok</p>
            <p class="mt-1 text-sm text-ink/70">Ubah status atau kata pencarian untuk melihat toko lain.</p>
        </section>
    @endforelse

    {{ $toko->links() }}
@endsection
