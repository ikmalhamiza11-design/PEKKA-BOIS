@php
    $urlWa = \App\Support\PesanWa::untukPenjual($pesanan);
@endphp

<section class="card mt-4 !p-4 text-sm">
    <h2 class="font-extrabold">Hubungi penjual lewat WhatsApp</h2>
    <p class="mt-2 text-ink/70">
        Transfermu hanya untuk harga produk. Soal ambil sendiri atau diantar, lokasi, dan biaya tambahan (ongkir), kamu bicarakan langsung dengan penjual lewat WhatsApp.
    </p>

    @if ($urlWa)
        <a href="{{ $urlWa }}" target="_blank" rel="noopener" class="btn-primary mt-3">Chat Penjual di WhatsApp</a>
        <p class="mt-2 text-xs text-ink/60">Pesan sudah terisi otomatis berisi kode pesanan, produk, nominal transfer, alamat, dan tautan foto bukti transfer. Kamu bisa mengubahnya sebelum dikirim.</p>
    @else
        <div class="mt-3">
            <x-alert judul="Nomor WhatsApp penjual belum tersedia">
                Belum ada nomor WhatsApp untuk penjual ini. Silakan hubungi operator PEKKA.
            </x-alert>
        </div>
    @endif
</section>
