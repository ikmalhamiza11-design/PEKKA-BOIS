@if ($pesanan->status === \App\Models\Order::DIPROSES)
    <div class="mt-4">
        <x-alert judul="Pesanan sedang diproses">Penjual sedang menyiapkan pesananmu.</x-alert>
    </div>

    @include('pesanan.wa-penjual')
@elseif ($pesanan->status === \App\Models\Order::DIKIRIM)
    <div class="mt-4">
        <x-alert judul="Pesananmu sedang dikirim">
            Kurir: {{ $pesanan->kurir }}@if ($pesanan->nomor_resi), nomor resi: {{ $pesanan->nomor_resi }}@endif. Setelah barang sampai, tekan tombol di bawah.
        </x-alert>
    </div>

    <form method="POST" action="{{ route('pesanan.diterima', $pesanan->id) }}" class="mt-3" onsubmit="return confirm('Pastikan barang sudah kamu terima. Lanjutkan?')">
        @csrf
        @method('PATCH')
        <button type="submit" class="btn-primary">Pesanan Sudah Diterima</button>
    </form>
@elseif ($pesanan->status === \App\Models\Order::SELESAI)
    <div class="mt-4">
        <x-alert tipe="sukses" judul="Pesanan selesai">Terima kasih sudah berbelanja produk anggota PEKKA.</x-alert>
    </div>
@endif

<div class="mt-4">
    <x-linimasa-pesanan :pesanan="$pesanan" />
</div>
