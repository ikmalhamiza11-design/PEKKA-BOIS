@extends('layouts.admin')

@section('judul', 'Moderasi Ulasan')

@section('isi')
    @php
        $tab = [
            'semua' => 'Semua',
            'tampil' => 'Tampil',
            'disembunyikan' => 'Disembunyikan ('.$jumlahDisembunyikan.')',
            'rendah' => 'Penilaian rendah',
        ];
    @endphp

    <div>
        <h1 class="text-2xl font-extrabold">Moderasi Ulasan</h1>
        <p class="mt-1 text-sm text-ink/70">Sembunyikan ulasan yang kasar, spam, atau melanggar aturan. Ulasan yang disembunyikan tidak tampil di halaman produk dan tidak dihitung dalam penilaian.</p>
    </div>

    <nav class="gulir-x -mx-1 flex gap-2 px-1" aria-label="Filter ulasan">
        @foreach ($tab as $kunci => $nama)
            <a href="{{ route('admin.ulasan.index', array_filter(['filter' => $kunci === 'semua' ? null : $kunci, 'q' => $kata])) }}" @class(['btn-primary' => $filter === $kunci, 'btn-secondary' => $filter !== $kunci, 'min-h-11 w-auto shrink-0 !px-4 !py-2 text-sm']) @if ($filter === $kunci) aria-current="page" @endif>{{ $nama }}</a>
        @endforeach
    </nav>

    <form method="GET" action="{{ route('admin.ulasan.index') }}" class="card !p-4 flex flex-col gap-3 sm:flex-row sm:items-end">
        @if ($filter !== 'semua')
            <input type="hidden" name="filter" value="{{ $filter }}">
        @endif
        <div class="flex-1">
            <label for="q" class="mb-1.5 block text-sm font-bold">Cari ulasan</label>
            <input id="q" name="q" type="search" value="{{ $kata }}" maxlength="100" placeholder="Isi komentar, nama produk, atau nama toko" class="input">
        </div>
        <button type="submit" class="btn-primary sm:w-auto">Cari</button>
    </form>

    @forelse ($ulasan as $item)
        <article @class(['card !p-4', 'opacity-80' => $item->disembunyikan()])>
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="truncate font-extrabold">{{ $item->orderItem?->nama ?? 'Produk' }}</p>
                    <p class="truncate text-xs text-ink/70">{{ $item->shop->nama }} &middot; oleh {{ $item->user->name }} &middot; {{ $item->created_at->format('d/m/Y') }}</p>
                </div>
                <x-bintang :nilai="$item->rating" :angka="false" :kecil="true" />
            </div>

            @if ($item->komentar)
                <p class="mt-2 whitespace-pre-line text-sm text-ink/80">{{ $item->komentar }}</p>
            @else
                <p class="mt-2 text-sm text-ink/50">Pembeli tidak menulis komentar.</p>
            @endif

            @if ($item->balasan)
                <p class="mt-2 rounded-xl bg-lime/15 p-3 text-sm"><span class="font-bold text-olive">Balasan penjual:</span> {{ $item->balasan }}</p>
            @endif

            <div class="mt-3 border-t border-olive/10 pt-3">
                @if ($item->disembunyikan())
                    <p class="text-sm"><span class="font-bold text-danger">Disembunyikan</span> pada {{ $item->disembunyikan_at->format('d/m/Y H:i') }}. Alasan: {{ $item->alasan_moderasi }}</p>
                    <form method="POST" action="{{ route('admin.ulasan.tampilkan', $item->id) }}" class="mt-2">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn-secondary min-h-11 !py-2 text-sm">Tampilkan Kembali</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.ulasan.sembunyikan', $item->id) }}" class="flex flex-col gap-2" novalidate>
                        @csrf
                        @method('PATCH')
                        <label for="alasan-{{ $item->id }}" class="text-sm font-bold">Sembunyikan ulasan ini</label>
                        <textarea id="alasan-{{ $item->id }}" name="alasan" rows="2" maxlength="300" placeholder="Alasan wajib diisi, contoh: berisi kata kasar." class="input"></textarea>
                        <button type="submit" class="btn-secondary min-h-11 !border-danger !py-2 text-sm !text-danger hover:!bg-danger/10">Sembunyikan</button>
                    </form>
                @endif
            </div>
        </article>
    @empty
        <section class="card text-center">
            <p class="font-extrabold">Tidak ada ulasan yang cocok</p>
            <p class="mt-1 text-sm text-ink/70">Ulasan muncul setelah pembeli menyelesaikan pesanan dan menilai produk.</p>
        </section>
    @endforelse

    {{ $ulasan->links() }}
@endsection
