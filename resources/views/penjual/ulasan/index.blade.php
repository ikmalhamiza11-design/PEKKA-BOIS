@extends('layouts.auth')

@section('judul', 'Ulasan Pembeli')

@section('appbar')
    <x-app-bar judul="Ulasan Pembeli" :kembali="route('dashboard')" />
@endsection

@section('isi')
    @if (session('pesan'))
        <x-alert tipe="sukses">{{ session('pesan') }}</x-alert>
    @endif

    <section class="card !p-4">
        @if ($ringkasan['jumlah'] > 0)
            <p class="text-xs font-bold uppercase tracking-wide text-olive/70">Penilaian toko</p>
            <div class="mt-1 flex items-center gap-3">
                <p class="text-4xl font-extrabold text-olive">{{ number_format($ringkasan['rata'], 1, ',', '.') }}</p>
                <div>
                    <x-bintang :nilai="$ringkasan['rata']" :angka="false" />
                    <p class="text-sm text-ink/70">dari {{ $ringkasan['jumlah'] }} ulasan</p>
                </div>
            </div>
        @else
            <p class="font-extrabold">Belum ada ulasan</p>
            <p class="mt-1 text-sm text-ink/70">Ulasan muncul setelah pembeli menyelesaikan pesanan dan menilai produkmu.</p>
        @endif
    </section>

    <div class="grid grid-cols-2 gap-2">
        <a href="{{ route('penjual.ulasan.index') }}" class="{{ $filter === '' ? 'btn-primary' : 'btn-secondary' }} min-h-11 !px-2 !py-2 text-sm">Semua</a>
        <a href="{{ route('penjual.ulasan.index', ['filter' => 'belum_dibalas']) }}" class="{{ $filter === 'belum_dibalas' ? 'btn-primary' : 'btn-secondary' }} min-h-11 !px-2 !py-2 text-sm">Belum Dibalas ({{ $belumDibalas }})</a>
    </div>

    @forelse ($ulasan as $item)
        <article class="card !p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="truncate font-extrabold">{{ $item->orderItem?->nama ?? 'Produk' }}</p>
                    <p class="text-xs text-ink/70">{{ $item->namaSamaran() }} &middot; {{ $item->created_at->format('d/m/Y') }}</p>
                </div>
                <x-bintang :nilai="$item->rating" :angka="false" :kecil="true" />
            </div>

            @if ($item->komentar)
                <p class="mt-2 whitespace-pre-line text-sm text-ink/80">{{ $item->komentar }}</p>
            @else
                <p class="mt-2 text-sm text-ink/50">Pembeli tidak menulis komentar.</p>
            @endif

            <form method="POST" action="{{ route('penjual.ulasan.balas', $item->id) }}" class="mt-3 flex flex-col gap-2 border-t border-olive/10 pt-3" novalidate>
                @csrf
                @method('PUT')
                <label for="balasan-{{ $item->id }}" class="text-sm font-bold">{{ $item->balasan ? 'Balasanmu' : 'Balas ulasan' }}</label>
                <textarea id="balasan-{{ $item->id }}" name="balasan" rows="3" maxlength="500" placeholder="Terima kasih sudah berbelanja..." class="input">{{ $item->balasan }}</textarea>
                <button type="submit" class="btn-secondary min-h-11 !py-2 text-sm">{{ $item->balasan ? 'Perbarui Balasan' : 'Kirim Balasan' }}</button>
            </form>
        </article>
    @empty
        @if ($ringkasan['jumlah'] > 0)
            <section class="card text-center">
                <p class="font-extrabold">Semua ulasan sudah dibalas</p>
            </section>
        @endif
    @endforelse

    {{ $ulasan->links() }}
@endsection
