@extends('layouts.auth')

@section('judul', 'Pesanan '.$pesanan->kode)

@section('appbar')
    <x-app-bar :judul="$pesanan->kode" :kembali="route('penjual.pesanan.index')" />
@endsection

@section('isi')
    @php
        $terakhir = $pesanan->pembayaranTerakhir;
    @endphp

    @if (session('pesan'))
        <x-alert tipe="sukses">{{ session('pesan') }}</x-alert>
    @endif

    @if (session('galat'))
        <x-alert tipe="galat">{{ session('galat') }}</x-alert>
    @endif

    <section class="card !p-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="font-extrabold">{{ $pesanan->kode }}</p>
                <p class="text-sm text-ink/70">Dibuat {{ $pesanan->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <span @class(['shrink-0 rounded-full px-3 py-1 text-xs font-extrabold', 'bg-lime/30 text-olive' => $pesanan->status !== \App\Models\Order::DIBATALKAN, 'bg-ink/10 text-ink/70' => $pesanan->status === \App\Models\Order::DIBATALKAN])>{{ $pesanan->labelStatus() }}</span>
        </div>

        @foreach ($pesanan->items as $item)
            <div class="mt-3 flex justify-between gap-3 border-t border-olive/10 pt-3 text-sm">
                <div class="min-w-0">
                    <p class="font-bold">{{ $item->nama }}</p>
                    <p class="text-ink/70">{{ \App\Support\Rupiah::format($item->harga) }} &times; {{ $item->jumlah }}</p>
                    @if ($item->pre_order_hari)
                        <p class="text-xs font-bold text-amber-900">Pre-order, siap sekitar {{ $item->pre_order_hari }} hari setelah dipesan</p>
                    @endif
                </div>
                <span class="shrink-0 font-extrabold">{{ \App\Support\Rupiah::format($item->subtotal) }}</span>
            </div>
        @endforeach

        <div class="mt-3 flex items-center justify-between border-t border-olive/10 pt-3">
            <span class="font-bold">Total</span>
            <span class="text-xl font-extrabold text-olive">{{ \App\Support\Rupiah::format($pesanan->total) }}</span>
        </div>
    </section>

    <x-linimasa-pesanan :pesanan="$pesanan" />

    <section class="card !p-4 text-sm">
        <h2 class="font-extrabold">Penerima</h2>
        <p class="mt-2 font-bold">{{ $pesanan->nama_penerima }}</p>
        <p class="text-ink/70">{{ \App\Support\Telepon::lokal($pesanan->telepon) }}</p>
        <p class="mt-1 whitespace-pre-line">{{ $pesanan->alamat }}</p>
        @if ($pesanan->catatan)
            <p class="mt-2 text-ink/70">Catatan: {{ $pesanan->catatan }}</p>
        @endif
    </section>

    <section class="card !p-4 text-sm">
        <h2 class="font-extrabold">Pembayaran</h2>

        @if (! $terakhir)
            <p class="mt-2 text-ink/70">Pembeli belum mengirim bukti transfer.</p>
        @else
            <p class="mt-2">Pengirim: <span class="font-bold">{{ $terakhir->nama_pengirim }}</span></p>
            <p>Jumlah: <span class="font-extrabold text-olive">{{ \App\Support\Rupiah::format($terakhir->jumlah) }}</span></p>
            <p>Status: <span class="font-bold">{{ $terakhir->labelStatus() }}</span></p>
            @if ($terakhir->alasan_penolakan)
                <p class="text-ink/70">Alasan penolakan: {{ $terakhir->alasan_penolakan }}</p>
            @endif

            <a href="{{ route('pembayaran.bukti', $terakhir->id) }}" target="_blank" rel="noopener" class="mt-3 block rounded-xl bg-cream/70 p-2">
                <img src="{{ route('pembayaran.bukti', $terakhir->id) }}" alt="Bukti transfer {{ $pesanan->kode }}" class="max-h-80 w-full rounded-lg object-contain" loading="lazy">
            </a>

            @if ($terakhir->status === \App\Models\Payment::MENUNGGU)
                <form method="POST" action="{{ route('penjual.pembayaran.terima', $terakhir->id) }}" class="mt-4" onsubmit="return confirm('Terima pembayaran ini? Pastikan dana sudah masuk ke rekeningmu.')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn-primary">Terima Pembayaran</button>
                </form>

                <form method="POST" action="{{ route('penjual.pembayaran.tolak', $terakhir->id) }}" class="mt-4 flex flex-col gap-3" novalidate>
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="alasan" class="mb-1.5 block text-sm font-bold">Alasan jika ditolak<span class="text-danger" aria-hidden="true"> *</span></label>
                        <textarea id="alasan" name="alasan" rows="3" maxlength="300" placeholder="Contoh: Jumlah transfer tidak sesuai." @class(['input', 'input-galat' => $errors->has('alasan')])>{{ old('alasan') }}</textarea>
                        @error('alasan')
                            <p class="mt-1.5 text-sm font-semibold text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="btn-secondary !border-danger !text-danger hover:!bg-red-50">Tolak Pembayaran</button>
                </form>
            @endif
        @endif
    </section>
    @if (in_array($pesanan->status, [\App\Models\Order::DIBAYAR, \App\Models\Order::DIPROSES, \App\Models\Order::DIKIRIM, \App\Models\Order::SELESAI], true))
        <section class="card !p-4 text-sm">
            <h2 class="font-extrabold">Pengiriman</h2>

            @if ($pesanan->status === \App\Models\Order::DIBAYAR)
                <p class="mt-2 text-ink/70">Pembayaran sudah diterima. Mulai proses pesanan setelah kamu menyiapkan barangnya.</p>

                <form method="POST" action="{{ route('penjual.pesanan.proses', $pesanan->id) }}" class="mt-3">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn-primary">Mulai Proses Pesanan</button>
                </form>
            @elseif ($pesanan->status === \App\Models\Order::DIPROSES)
                <p class="mt-2 text-ink/70">Isi kurir dan nomor resi saat pesanan sudah diserahkan untuk dikirim.</p>

                <form method="POST" action="{{ route('penjual.pesanan.kirim', $pesanan->id) }}" class="mt-3 flex flex-col gap-4" novalidate>
                    @csrf
                    @method('PATCH')

                    <x-field name="kurir" label="Kurir atau cara pengiriman" list="daftar-kurir" placeholder="Contoh: JNE" maxlength="50" />
                    <datalist id="daftar-kurir">
                        <option value="Antar sendiri">
                        <option value="JNE">
                        <option value="J&T">
                        <option value="SiCepat">
                        <option value="Pos Indonesia">
                        <option value="Ojek online">
                    </datalist>

                    <x-field name="nomor_resi" label="Nomor resi" :wajib="false" maxlength="60" hint="Boleh dikosongkan jika diantar sendiri." />

                    <button type="submit" class="btn-primary">Tandai Sudah Dikirim</button>
                </form>
            @else
                <p class="mt-2">Kurir: <span class="font-bold">{{ $pesanan->kurir }}</span></p>
                @if ($pesanan->nomor_resi)
                    <p>Nomor resi: <span class="font-bold">{{ $pesanan->nomor_resi }}</span></p>
                @endif
                @if ($pesanan->status === \App\Models\Order::DIKIRIM)
                    <p class="mt-2 text-ink/70">Menunggu pembeli mengonfirmasi bahwa pesanan sudah diterima.</p>
                @endif
            @endif
        </section>
    @endif
@endsection
