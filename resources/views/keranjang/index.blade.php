@extends('layouts.app')
@use('App\Support\Rupiah')

@section('judul', 'Keranjang')

@section('isi')
    <div class="mx-auto max-w-6xl px-4 pb-8 pt-4 lg:pt-8">
        <h1 class="text-3xl font-extrabold">Keranjang Belanja</h1>

        @if ($items->isEmpty())
            <section class="card mt-4 flex flex-col items-center gap-3 py-10 text-center">
                <span class="flex h-16 w-16 items-center justify-center rounded-full bg-lime/30 text-olive" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
                </span>
                <p class="text-lg font-extrabold">Keranjangmu masih kosong</p>
                <p class="max-w-sm text-sm text-ink/70">Yuk, pilih produk usaha anggota PEKKA di katalog.</p>
                <a href="{{ route('katalog') }}" class="btn-primary max-w-xs">Lihat Katalog</a>
            </section>
        @else
            <div class="mt-4 grid gap-6 lg:grid-cols-[1fr_22rem] lg:items-start">
                <div class="flex flex-col gap-4">
                    @foreach ($perToko as $baris)
                        @php $toko = $baris->first()->product->shop; @endphp
                        <section class="card !p-4">
                            <p class="font-extrabold">{{ $toko->nama }}</p>
                            <p class="text-xs text-ink/70">{{ $toko->kelompok->nama }}</p>

                            @foreach ($baris as $item)
                                @php
                                    $produk = $item->product;
                                    $masalah = $item->masalah();
                                @endphp
                                <div class="mt-3 flex gap-3 border-t border-olive/10 pt-3">
                                    <a href="{{ route('produk.show', $produk->id) }}" class="foto-{{ $produk->id % 4 }} flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl text-olive/50">
                                        @if ($produk->foto)
                                            <img src="{{ asset('storage/'.$produk->foto) }}" alt="{{ $produk->nama }}" class="h-full w-full object-cover" loading="lazy">
                                        @else
                                            <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                                        @endif
                                    </a>
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('produk.show', $produk->id) }}" class="block truncate font-extrabold hover:underline">{{ $produk->nama }}</a>
                                        <p class="text-sm font-bold text-olive">{{ $produk->harga_rupiah }}</p>

                                        @if ($masalah)
                                            <p class="mt-1 text-sm font-bold text-danger">{{ $masalah }}</p>
                                        @endif

                                        <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                                            <div class="inline-flex items-center rounded-full border border-olive/25 bg-cream/60">
                                                <form method="POST" action="{{ route('keranjang.ubah', $item->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="jumlah" value="{{ max($item->jumlah - 1, 1) }}">
                                                    <button type="submit" aria-label="Kurangi jumlah {{ $produk->nama }}" @disabled($item->jumlah <= 1) class="flex h-11 w-11 items-center justify-center text-xl font-extrabold text-olive disabled:opacity-40">-</button>
                                                </form>
                                                <span class="w-10 text-center font-extrabold" aria-label="Jumlah">{{ $item->jumlah }}</span>
                                                <form method="POST" action="{{ route('keranjang.ubah', $item->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="jumlah" value="{{ $item->jumlah + 1 }}">
                                                    <button type="submit" aria-label="Tambah jumlah {{ $produk->nama }}" @disabled($item->jumlah >= $produk->stok) class="flex h-11 w-11 items-center justify-center text-xl font-extrabold text-olive disabled:opacity-40">+</button>
                                                </form>
                                            </div>

                                            <form method="POST" action="{{ route('keranjang.hapus', $item->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="min-h-11 px-2 text-sm font-bold text-danger underline">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                    <p class="shrink-0 text-right text-sm font-extrabold text-olive">{{ Rupiah::format($item->subtotal) }}</p>
                                </div>
                            @endforeach
                        </section>
                    @endforeach
                </div>

                <aside class="card lg:sticky lg:top-24">
                    <h2 class="text-lg font-extrabold">Ringkasan</h2>
                    <div class="mt-3 flex items-center justify-between text-sm">
                        <span class="text-ink/70">{{ $items->sum('jumlah') }} item dari {{ $perToko->count() }} toko</span>
                    </div>
                    <div class="mt-2 flex items-center justify-between border-t border-olive/10 pt-3">
                        <span class="font-bold">Total</span>
                        <span class="text-xl font-extrabold text-olive">{{ Rupiah::format($total) }}</span>
                    </div>
                    <p class="mt-2 text-xs text-ink/60">Total belum termasuk ongkir. Setelah bayar, ongkir dan cara penerimaan dibicarakan langsung dengan penjual lewat WhatsApp.</p>

                    @if ($adaMasalah)
                        <p class="mt-3 text-sm font-bold text-danger">Perbaiki produk bertanda merah dulu, lalu lanjut ke checkout.</p>
                        <button type="button" disabled aria-disabled="true" class="btn-primary mt-3">Lanjut ke Checkout</button>
                    @else
                        <a href="{{ route('checkout') }}" class="btn-primary mt-3">Lanjut ke Checkout</a>
                    @endif
                    <a href="{{ route('katalog') }}" class="btn-secondary mt-2">Lanjut Belanja</a>
                </aside>
            </div>
        @endif
    </div>
@endsection
