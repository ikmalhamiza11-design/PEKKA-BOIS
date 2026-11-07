@extends('layouts.admin')

@section('judul', $toko->nama)

@section('isi')
    @php
        $lencana = [
            'menunggu' => 'bg-lime/30 text-olive',
            'terverifikasi' => 'bg-green-100 text-success',
            'ditolak' => 'bg-red-100 text-danger',
        ];
    @endphp

    <a href="{{ route('admin.toko.index') }}" class="text-sm font-bold text-olive underline">&larr; Kembali ke daftar toko</a>

    <section class="card">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-2xl font-extrabold">{{ $toko->nama }}</h1>
                <p class="text-sm text-ink/70">{{ $toko->kelompok->label() }}</p>
            </div>
            <span class="shrink-0 rounded-full px-3 py-1 text-xs font-bold {{ $lencana[$toko->status] ?? $lencana['menunggu'] }}">{{ $toko->labelStatus() }}</span>
        </div>

        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
            <div>
                <dt class="font-bold text-olive/80">Nama penjual</dt>
                <dd>{{ $toko->user->name }}</dd>
            </div>
            <div>
                <dt class="font-bold text-olive/80">Email</dt>
                <dd class="break-all">{{ $toko->user->email }}</dd>
            </div>
            <div>
                <dt class="font-bold text-olive/80">Nomor HP/WhatsApp</dt>
                <dd>{{ \App\Support\Telepon::lokal($toko->user->phone) ?: '-' }}</dd>
            </div>
            <div>
                <dt class="font-bold text-olive/80">Terdaftar</dt>
                <dd>{{ $toko->created_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div>
                <dt class="font-bold text-olive/80">Jumlah produk</dt>
                <dd>{{ $toko->products_count }}</dd>
            </div>
            <div>
                <dt class="font-bold text-olive/80">Rekening pembayaran</dt>
                <dd>
                    @if ($toko->punyaRekening())
                        {{ $toko->nama_bank }} &middot; {{ $toko->nomor_rekening }} &middot; a.n. {{ $toko->atas_nama }}
                    @else
                        <span class="font-bold text-danger">Belum diisi</span>
                    @endif
                </dd>
            </div>
        </dl>

        @if ($toko->diverifikasi_at)
            <p class="mt-4 border-t border-olive/10 pt-3 text-xs text-ink/60">
                Keputusan terakhir pada {{ $toko->diverifikasi_at->format('d/m/Y H:i') }}
                @if ($toko->diverifikasiOleh)
                    oleh {{ $toko->diverifikasiOleh->name }}
                @endif
            </p>
        @endif

        @if ($toko->status === \App\Models\Shop::DITOLAK && $toko->alasan_penolakan)
            <div class="mt-3">
                <x-alert tipe="galat" judul="Alasan penolakan">{{ $toko->alasan_penolakan }}</x-alert>
            </div>
        @endif
    </section>

    @if (! $toko->punyaRekening() && $toko->status !== \App\Models\Shop::DITOLAK)
        <x-alert judul="Rekening belum diisi">Toko tetap bisa disetujui, tetapi belum bisa menerima pesanan sampai penjual mengisi rekening pembayaran.</x-alert>
    @endif

    <div class="grid gap-5 md:grid-cols-2">
        @if ($toko->status !== \App\Models\Shop::TERVERIFIKASI)
            <section class="card flex flex-col gap-3">
                <h2 class="font-extrabold">Setujui toko</h2>
                <p class="text-sm text-ink/70">Produk aktif toko akan tampil di katalog dan penjual menerima email pemberitahuan.</p>
                <form method="POST" action="{{ route('admin.toko.setujui', $toko->id) }}" class="mt-auto">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn-primary">Setujui Toko</button>
                </form>
            </section>
        @endif

        @if ($toko->status !== \App\Models\Shop::DITOLAK)
            <section class="card flex flex-col gap-3">
                <h2 class="font-extrabold">{{ $toko->status === \App\Models\Shop::TERVERIFIKASI ? 'Cabut verifikasi' : 'Tolak toko' }}</h2>
                <p class="text-sm text-ink/70">Tulis alasan yang jelas. Penjual akan membacanya di dasbor dan lewat email.</p>
                <form method="POST" action="{{ route('admin.toko.tolak', $toko->id) }}" class="flex flex-col gap-3" novalidate>
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="alasan" class="mb-1.5 block text-sm font-bold">Alasan<span class="text-danger" aria-hidden="true"> *</span></label>
                        <textarea id="alasan" name="alasan" rows="3" maxlength="300" placeholder="Contoh: Nama usaha belum sesuai dengan data kelompok." class="input @error('alasan') input-galat @enderror">{{ old('alasan') }}</textarea>
                        @error('alasan')
                            <p class="mt-1.5 text-sm font-semibold text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="btn-secondary !border-danger !text-danger hover:!bg-danger/10">{{ $toko->status === \App\Models\Shop::TERVERIFIKASI ? 'Cabut Verifikasi' : 'Tolak Toko' }}</button>
                </form>
            </section>
        @endif
    </div>
@endsection
