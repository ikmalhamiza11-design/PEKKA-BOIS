@extends('layouts.auth')

@section('judul', 'Pesanan Masuk')

@section('appbar')
    <x-app-bar judul="Pesanan Masuk" :kembali="route('dashboard')" />
@endsection

@section('isi')
    @php
        $tab = [
            '' => 'Semua',
            'perlu_verifikasi' => 'Perlu Verifikasi',
            'perlu_diproses' => 'Perlu Diproses',
            'dikirim' => 'Dikirim',
        ];
    @endphp

    @if (session('pesan'))
        <x-alert tipe="sukses">{{ session('pesan') }}</x-alert>
    @endif

    <div class="grid grid-cols-2 gap-2">
        @foreach ($tab as $kunci => $label)
            <a href="{{ $kunci !== '' ? route('penjual.pesanan.index', ['status' => $kunci]) : route('penjual.pesanan.index') }}" class="{{ (string) $status === (string) $kunci ? 'btn-primary' : 'btn-secondary' }} min-h-11 !px-2 !py-2 text-sm">{{ $label }}</a>
        @endforeach
    </div>

    @forelse ($pesanan as $item)
        <a href="{{ route('penjual.pesanan.show', $item->id) }}" class="card block !p-4 hover:border-olive/30">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-extrabold">{{ $item->kode }}</p>
                    <p class="truncate text-sm text-ink/70">{{ $item->nama_penerima }} &middot; {{ $item->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <span @class(['shrink-0 rounded-full px-3 py-1 text-xs font-extrabold', 'bg-lime/30 text-olive' => $item->status !== \App\Models\Order::DIBATALKAN, 'bg-ink/10 text-ink/70' => $item->status === \App\Models\Order::DIBATALKAN])>{{ $item->labelStatus() }}</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-sm">
                <span class="text-ink/70">{{ $item->items->sum('jumlah') }} item</span>
                <span class="font-extrabold text-olive">{{ \App\Support\Rupiah::format($item->total) }}</span>
            </div>
        </a>
    @empty
        <section class="card flex flex-col items-center gap-2 py-10 text-center">
            <p class="text-lg font-extrabold">Belum ada pesanan</p>
            <p class="max-w-sm text-sm text-ink/70">Pesanan dari pembeli akan muncul di sini.</p>
        </section>
    @endforelse

    <div>{{ $pesanan->links() }}</div>
@endsection
