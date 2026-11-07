@php
    $toko = $pesanan->shop;
    $terakhir = $pesanan->pembayaranTerakhir;
@endphp

@if ($pesanan->status === \App\Models\Order::MENUNGGU_PEMBAYARAN)
    @if ($terakhir && $terakhir->status === \App\Models\Payment::DITOLAK)
        <div class="mt-4">
            <x-alert tipe="galat" judul="Bukti transfer ditolak">
                Alasan: {{ $terakhir->alasan_penolakan }}. Silakan unggah bukti transfer yang benar.
            </x-alert>
        </div>
    @endif

    <section class="card mt-4 !p-4 text-sm">
        <h2 class="font-extrabold">Pembayaran</h2>

        @if ($toko->punyaRekening())
            <p class="mt-2 text-ink/70">Transfer sesuai total pesanan ke rekening berikut, lalu unggah bukti transfernya.</p>

            <div class="mt-3 rounded-xl bg-cream/70 p-3">
                <p class="text-xs text-ink/60">{{ $toko->nama_bank }}</p>
                <p class="text-lg font-extrabold tracking-wide">{{ $toko->nomor_rekening }}</p>
                <p class="font-bold">a.n. {{ $toko->atas_nama }}</p>
            </div>

            <p class="mt-3">Jumlah transfer: <span class="font-extrabold text-olive">{{ \App\Support\Rupiah::format($pesanan->total) }}</span></p>

            <form method="POST" action="{{ route('pembayaran.store', $pesanan->id) }}" enctype="multipart/form-data" class="mt-4 flex flex-col gap-4" novalidate>
                @csrf

                <x-field name="nama_pengirim" label="Nama pemilik rekening pengirim" maxlength="100" :nilai="auth()->user()->name" />

                <div>
                    <label for="bukti" class="mb-1.5 block text-sm font-bold">Bukti transfer<span class="text-danger" aria-hidden="true"> *</span></label>
                    <input id="bukti" type="file" name="bukti" accept="image/jpeg,image/png,image/webp" @class(['input', 'input-galat' => $errors->has('bukti')])>
                    @error('bukti')
                        <p class="mt-1.5 text-sm font-semibold text-danger">{{ $message }}</p>
                    @else
                        <p class="mt-1.5 text-xs text-ink/60">JPG, PNG, atau WebP, maksimal 5 MB.</p>
                    @enderror
                </div>

                <button type="submit" class="btn-primary">Kirim Bukti Transfer</button>
            </form>
        @else
            <div class="mt-3">
                <x-alert judul="Rekening penjual belum tersedia">
                    Penjual belum mengisi rekening pembayaran. Silakan coba lagi nanti. Stok produk ini sudah ditahan untukmu.
                </x-alert>
            </div>
        @endif
    </section>
@elseif ($pesanan->status === \App\Models\Order::MENUNGGU_VERIFIKASI)
    <div class="mt-4">
        <x-alert judul="Menunggu verifikasi penjual">
            Bukti transfermu sudah terkirim dan sedang diperiksa penjual.
        </x-alert>
    </div>

    @if ($terakhir)
        <a href="{{ route('pembayaran.bukti', $terakhir->id) }}" target="_blank" rel="noopener" class="btn-secondary mt-3">Lihat Bukti yang Dikirim</a>
    @endif
@elseif ($pesanan->status === \App\Models\Order::DIBAYAR)
    <div class="mt-4">
        <x-alert tipe="sukses" judul="Pembayaran diterima">
            Penjual sudah memastikan pembayaranmu dan akan memproses pesanan ini.
        </x-alert>
    </div>
@endif
