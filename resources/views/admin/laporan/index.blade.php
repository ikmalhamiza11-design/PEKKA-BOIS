@extends('layouts.admin')

@section('judul', 'Laporan Masuk')

@section('isi')
    <div>
        <h1 class="text-2xl font-extrabold">Laporan Tidak Bisa Masuk</h1>
        <p class="mt-1 text-sm text-ink/70">Laporan dari ibu/bapak yang lupa kata sandi dan tidak bisa membuka email. Hubungi lewat WhatsApp dulu untuk memastikan orangnya benar sebelum membuat sandi sementara.</p>
    </div>

    @if ($sandiSementara)
        <section class="card border-2 border-olive">
            <p class="text-sm font-bold text-olive">Sandi sementara untuk {{ $sandiSementara['nama'] }}</p>
            <p class="mt-1 break-all text-xs text-ink/60">{{ $sandiSementara['email'] }}</p>
            <p class="mt-2 select-all text-3xl font-extrabold tracking-widest">{{ $sandiSementara['sandi'] }}</p>
            <p class="mt-2 text-sm text-ink/70">Sandi ini hanya tampil sekali. Kirim sekarang lewat WhatsApp. Ibu/bapak akan diminta membuat sandi baru saat masuk.</p>
            <a href="{{ $sandiSementara['wa'] }}" target="_blank" rel="noopener" class="btn-primary mt-3">Kirim lewat WhatsApp</a>
        </section>
    @endif

    <nav class="flex gap-2 text-sm font-bold" aria-label="Saring laporan">
        @foreach (['baru' => 'Baru ('.$hitungBaru.')', 'selesai' => 'Selesai', 'semua' => 'Semua'] as $kunci => $nama)
            <a href="{{ route('admin.laporan.index', ['status' => $kunci]) }}" @class(['rounded-full px-4 py-2', 'bg-olive text-white' => $status === $kunci, 'bg-lime/25 text-olive' => $status !== $kunci])>{{ $nama }}</a>
        @endforeach
    </nav>

    <section class="flex flex-col gap-3">
        @forelse ($laporan as $item)
            <article class="card !p-4 flex flex-col gap-3">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="font-extrabold">{{ $item->nama }}</p>
                        <p class="text-sm text-ink/70">WA: {{ \App\Support\Telepon::lokal($item->phone) }}@if ($item->email) &middot; <span class="break-all">{{ $item->email }}</span>@endif</p>
                        <p class="text-xs text-ink/60">{{ $item->created_at->translatedFormat('d M Y H:i') }}</p>
                    </div>
                    <span @class(['rounded-full px-3 py-1 text-xs font-extrabold', 'bg-lime text-ink' => $item->status === 'baru', 'bg-olive/15 text-olive' => $item->status !== 'baru'])>{{ $item->status === 'baru' ? 'Baru' : 'Selesai' }}</span>
                </div>

                @if ($item->keterangan)
                    <p class="rounded-xl bg-cream/70 p-3 text-sm">{{ $item->keterangan }}</p>
                @endif

                <div class="rounded-xl bg-cream/70 p-3 text-sm">
                    @if ($item->user)
                        <p class="font-bold">Akun cocok: {{ $item->user->name }} ({{ $item->user->labelPeran() }})</p>
                        <p class="break-all text-xs text-ink/60">{{ $item->user->email }}@unless ($item->user->hasVerifiedEmail()) &middot; email belum terverifikasi @endunless</p>
                        @if ($item->user->shop)
                            <p class="text-xs text-ink/60">Toko: {{ $item->user->shop->nama }}</p>
                        @endif
                    @else
                        <p class="font-bold text-danger">Akun belum ditemukan</p>
                        <form method="POST" action="{{ route('admin.laporan.cocokkan', $item->id) }}" class="mt-2 flex flex-wrap gap-2">
                            @csrf
                            <input type="email" name="email_akun" required placeholder="Email akun yang dicari" class="input min-w-0 flex-1">
                            <button type="submit" class="btn-secondary min-h-11 w-auto !px-4 !py-2 text-sm">Cocokkan</button>
                        </form>
                    @endif
                </div>

                @if ($item->status === 'baru')
                    <div class="flex flex-wrap gap-2">
                        @if ($item->user)
                            <form method="POST" action="{{ route('admin.laporan.sandi', $item->id) }}" data-konfirmasi="Buat sandi sementara untuk {{ $item->nama }}? Sandi lama langsung tidak berlaku.">
                                @csrf
                                <button type="submit" class="btn-primary min-h-11 w-auto !px-4 !py-2 text-sm">Buat sandi sementara</button>
                            </form>
                            @unless ($item->user->hasVerifiedEmail())
                                <form method="POST" action="{{ route('admin.laporan.verifikasi', $item->id) }}" data-konfirmasi="Tandai email akun ini terverifikasi tanpa klik tautan?">
                                    @csrf
                                    <button type="submit" class="btn-secondary min-h-11 w-auto !px-4 !py-2 text-sm">Tandai email terverifikasi</button>
                                </form>
                            @endunless
                        @endif
                        <form method="POST" action="{{ route('admin.laporan.selesai', $item->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-secondary min-h-11 w-auto !px-4 !py-2 text-sm">Tandai selesai</button>
                        </form>
                    </div>
                @else
                    <p class="text-xs text-ink/60">Ditangani oleh {{ $item->penangan?->name ?? 'operator' }}@if ($item->ditangani_at), {{ $item->ditangani_at->translatedFormat('d M Y H:i') }}@endif</p>
                @endif
            </article>
        @empty
            <section class="card text-center">
                <p class="font-extrabold">Tidak ada laporan</p>
            </section>
        @endforelse

        {{ $laporan->links() }}
    </section>
@endsection
