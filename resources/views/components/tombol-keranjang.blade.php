@props(['produk', 'kecil' => false])

@php
    $habis = $produk->stok < 1;
    $kendala = $produk->kendalaToko();
    $milik = auth()->check() && auth()->user()->shop?->id === $produk->shop_id;
    $kelasTombol = $kecil ? 'btn-primary min-h-11 !px-3 !py-2 text-sm' : 'btn-primary text-sm';
    $kelasLuar = $kecil ? 'relative z-10 mt-auto' : 'flex flex-1';
@endphp

@if ($habis)
    <div class="{{ $kelasLuar }}"><button type="button" disabled aria-disabled="true" class="{{ $kelasTombol }}">Habis</button></div>
@elseif ($milik)
    <div class="{{ $kelasLuar }}"><button type="button" disabled aria-disabled="true" class="{{ $kelasTombol }}">Produk Anda</button></div>
@elseif ($kendala)
    <div class="{{ $kelasLuar }}"><button type="button" disabled aria-disabled="true" class="{{ $kelasTombol }}">{{ $produk->shop->libur ? 'Toko Libur' : 'Toko Tutup' }}</button></div>
@elseif (auth()->check())
    <form method="POST" action="{{ route('keranjang.tambah') }}" class="{{ $kelasLuar }}">
        @csrf
        <input type="hidden" name="product_id" value="{{ $produk->id }}">
        <input type="hidden" name="jumlah" value="1">
        <button type="submit" class="{{ $kelasTombol }}">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
            + Keranjang
        </button>
    </form>
@else
    <div class="{{ $kelasLuar }}"><a href="{{ route('login') }}" class="{{ $kelasTombol }}">Masuk untuk membeli</a></div>
@endif
