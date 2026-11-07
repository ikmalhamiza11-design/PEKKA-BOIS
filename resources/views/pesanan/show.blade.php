@extends('layouts.app')
@use('App\Support\Rupiah')

@section('judul', 'Pesanan '.$pesanan->kode)

@section('isi')
    <div class="mx-auto max-w-3xl px-4 pb-8 pt-4 lg:pt-8">
        <a href="{{ route('pesanan') }}" class="mb-3 inline-flex items-center gap-1 text-sm font-bold text-olive">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            Semua pesanan
        </a>

        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-extrabold">{{ $pesanan->kode }}</h1>
                <p class="text-sm text-ink/70">Dibuat {{ $pesanan->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <span @class(['rounded-full px-3 py-1 text-sm font-extrabold', 'bg-lime/30 text-olive' => $pesanan->status !== \App\Models\Order::DIBATALKAN, 'bg-ink/10 text-ink/70' => $pesanan->status === \App\Models\Order::DIBATALKAN])>{{ $pesanan->labelStatus() }}</span>
        </div>

        @include('pesanan.pembayaran')
        @include('pesanan.pengiriman')

        <section class="card mt-4 !p-4">
            <p class="font-extrabold">{{ $pesanan->shop->nama }}</p>
            <p class="text-xs text-ink/70">{{ $pesanan->shop->kelompok->nama }}</p>

            @foreach ($pesanan->items as $item)
                <div class="mt-3 flex justify-between gap-3 border-t border-olive/10 pt-3 text-sm">
                    <div class="min-w-0">
                        @if ($item->product_id)
                            <a href="{{ route('produk.show', $item->product_id) }}" class="font-bold hover:underline">{{ $item->nama }}</a>
                        @else
                            <span class="font-bold">{{ $item->nama }}</span>
                        @endif
                        <p class="text-ink/70">{{ Rupiah::format($item->harga) }} &times; {{ $item->jumlah }}</p>
                    </div>
                    <span class="shrink-0 font-extrabold">{{ Rupiah::format($item->subtotal) }}</span>
                </div>
            @endforeach

            <div class="mt-3 flex items-center justify-between border-t border-olive/10 pt-3">
                <span class="font-bold">Total</span>
                <span class="text-xl font-extrabold text-olive">{{ Rupiah::format($pesanan->total) }}</span>
            </div>
            <p class="mt-1 text-xs text-ink/60">Ongkos kirim belum termasuk dan akan dikonfirmasi oleh penjual.</p>
        </section>

        <section class="card mt-4 !p-4 text-sm">
            <h2 class="font-extrabold">Pengiriman</h2>
            <p class="mt-2 font-bold">{{ $pesanan->nama_penerima }}</p>
            <p class="text-ink/70">{{ \App\Support\Telepon::lokal($pesanan->telepon) }}</p>
            <p class="mt-1 whitespace-pre-line">{{ $pesanan->alamat }}</p>
            @if ($pesanan->catatan)
                <p class="mt-2 text-ink/70">Catatan: {{ $pesanan->catatan }}</p>
            @endif
        </section>

        @if ($pesanan->bisaDibatalkan())
            <form method="POST" action="{{ route('pesanan.batal', $pesanan->id) }}" class="mt-4" onsubmit="return confirm('Batalkan pesanan ini?')">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn-secondary !border-danger !text-danger hover:!bg-red-50">Batalkan Pesanan</button>
            </form>
        @endif
    </div>
@endsection
