@extends('layouts.app')
@use('App\Support\Rupiah')

@section('judul', 'Pesanan Saya')

@section('isi')
    <div class="mx-auto max-w-3xl px-4 pb-8 pt-4 lg:pt-8">
        <h1 class="text-3xl font-extrabold">Pesanan Saya</h1>

        <div class="mt-4 flex flex-col gap-3">
            @forelse ($pesanan as $item)
                <a href="{{ route('pesanan.show', $item->id) }}" class="card block !p-4 hover:border-olive/30">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-extrabold">{{ $item->kode }}</p>
                            <p class="truncate text-sm text-ink/70">{{ $item->shop->nama }} &middot; {{ $item->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <span @class(['shrink-0 rounded-full px-3 py-1 text-xs font-extrabold', 'bg-lime/30 text-olive' => $item->status !== \App\Models\Order::DIBATALKAN, 'bg-ink/10 text-ink/70' => $item->status === \App\Models\Order::DIBATALKAN])>{{ $item->labelStatus() }}</span>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-sm">
                        <span class="text-ink/70">{{ $item->items->sum('jumlah') }} item</span>
                        <span class="font-extrabold text-olive">{{ Rupiah::format($item->total) }}</span>
                    </div>
                </a>
            @empty
                <section class="card flex flex-col items-center gap-3 py-10 text-center">
                    <p class="text-lg font-extrabold">Belum ada pesanan</p>
                    <p class="max-w-sm text-sm text-ink/70">Pesanan yang kamu buat akan muncul di sini.</p>
                    <a href="{{ route('katalog') }}" class="btn-primary max-w-xs">Lihat Katalog</a>
                </section>
            @endforelse
        </div>

        <div class="mt-4">{{ $pesanan->links() }}</div>
    </div>
@endsection
