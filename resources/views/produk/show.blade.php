@extends('layouts.app')

@section('judul', $produk->nama)

@section('isi')
    @php
        $habis = $produk->stok < 1;
        $maks = max($produk->stok, 1);
        $milikSendiri = auth()->check() && auth()->user()->shop?->id === $produk->shop_id;
    @endphp

    <div class="mx-auto max-w-6xl px-4 pb-36 pt-4 lg:pb-12 lg:pt-8">
        <nav class="mb-4 hidden flex-wrap items-center gap-2 text-sm text-ink/60 lg:flex" aria-label="Jejak halaman">
            <a href="{{ route('beranda') }}" class="hover:text-olive">Beranda</a><span aria-hidden="true">/</span>
            <a href="{{ route('katalog') }}" class="hover:text-olive">Katalog Produk</a><span aria-hidden="true">/</span>
            <a href="{{ route('katalog', ['kategori' => $produk->category->slug]) }}" class="hover:text-olive">{{ $produk->category->nama }}</a><span aria-hidden="true">/</span>
            <span class="font-bold text-olive">{{ $produk->nama }}</span>
        </nav>

        <a href="{{ route('katalog') }}" class="mb-3 inline-flex items-center gap-1 text-sm font-bold text-olive lg:hidden">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            Kembali ke katalog
        </a>

        <div class="grid gap-6 lg:grid-cols-[1.05fr_1fr] lg:gap-10">
            <div class="foto-{{ $produk->id % 4 }} relative flex aspect-square items-center justify-center overflow-hidden rounded-3xl text-olive/50">
                @if ($produk->foto)
                    <img src="{{ asset('storage/'.$produk->foto) }}" alt="{{ $produk->nama }}" @class(['h-full w-full object-cover', 'opacity-60 grayscale' => $habis])>
                @else
                    <svg viewBox="0 0 24 24" width="88" height="88" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                @endif
                @if ($habis)
                    <span class="absolute inset-0 flex items-center justify-center bg-ink/30">
                        <span class="rounded-full bg-paper px-4 py-1.5 text-sm font-extrabold text-danger">Stok Habis</span>
                    </span>
                @endif
            </div>

            <div class="flex flex-col gap-4">
                <div>
                    <span class="rounded-full bg-lime/30 px-3 py-1 text-xs font-extrabold text-olive">{{ $produk->category->nama }}</span>
                    <h1 class="mt-3 text-3xl font-extrabold leading-tight">{{ $produk->nama }}</h1>
                    <p class="mt-2 text-3xl font-extrabold text-olive">{{ $produk->harga_rupiah }}</p>
                    @if ($ringkasan['jumlah'] > 0)
                        <a href="#ulasan" class="mt-2 inline-flex"><x-bintang :nilai="$ringkasan['rata']" :jumlah="$ringkasan['jumlah']" /></a>
                    @endif
                    <p class="mt-2">
                        @if ($habis)
                            <span class="rounded-full bg-red-50 px-3 py-1 text-sm font-extrabold text-danger">Stok habis</span>
                        @else
                            <span class="text-sm font-semibold text-success">Stok tersedia: {{ $produk->stok }}</span>
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-3 rounded-2xl border border-olive/10 bg-paper p-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-olive text-lg font-extrabold text-lime" aria-hidden="true">{{ mb_strtoupper(mb_substr($produk->shop->nama, 0, 1)) }}</span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="truncate font-extrabold">{{ $produk->shop->nama }}</p>
                            <span class="rounded-full bg-lime px-2.5 py-0.5 text-[0.7rem] font-extrabold text-ink">Terverifikasi</span>
                        </div>
                        <p class="text-sm text-ink/70">{{ $produk->shop->kelompok->nama }}</p>
                        @if ($ringkasanToko['jumlah'] > 0)
                            <div class="mt-1"><x-bintang :nilai="$ringkasanToko['rata']" :jumlah="$ringkasanToko['jumlah']" :kecil="true" /></div>
                        @endif
                    </div>
                    <a href="{{ route('katalog', ['q' => $produk->shop->nama]) }}" class="shrink-0 rounded-full border border-olive/25 px-3 py-2 text-sm font-bold text-olive hover:bg-lime/30">Kunjungi Toko</a>
                </div>

                <div class="rounded-2xl border border-olive/10 bg-paper p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-extrabold">Jumlah pesanan</p>
                            <p class="text-xs text-ink/60">Minimal 1 item</p>
                        </div>
                        <div data-stepper data-maks="{{ $maks }}" data-harga="{{ $produk->harga }}" class="inline-flex items-center rounded-full border border-olive/25 bg-cream/60">
                            <button type="button" data-stepper-kurang aria-label="Kurangi jumlah" @disabled($habis) class="flex h-11 w-11 items-center justify-center text-xl font-extrabold text-olive disabled:opacity-40">-</button>
                            <input data-stepper-nilai form="form-keranjang" name="jumlah" type="number" min="1" max="{{ $maks }}" value="{{ $habis ? 0 : 1 }}" aria-label="Jumlah pesanan" @disabled($habis) class="w-14 bg-transparent text-center font-extrabold focus:outline-none">
                            <button type="button" data-stepper-tambah aria-label="Tambah jumlah" @disabled($habis) class="flex h-11 w-11 items-center justify-center text-xl font-extrabold text-olive disabled:opacity-40">+</button>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-between border-t border-olive/10 pt-3 text-sm">
                        <span class="text-ink/70">Estimasi subtotal</span>
                        <span class="font-extrabold text-olive" data-subtotal>{{ $habis ? 'Rp 0' : $produk->harga_rupiah }}</span>
                    </div>
                </div>

                <div class="hidden gap-3 lg:flex">
                    @if ($habis)
                        <button type="button" disabled aria-disabled="true" class="btn-primary flex-1">Stok habis</button>
                    @elseif ($milikSendiri)
                        <button type="button" disabled aria-disabled="true" class="btn-primary flex-1">Produk Anda</button>
                    @elseif (auth()->check())
                        <button type="submit" form="form-keranjang" class="btn-primary flex-1">+ Keranjang</button>
                    @else
                        <a href="{{ route('login') }}" class="btn-primary flex-1">Masuk untuk membeli</a>
                    @endif
                    <button type="button" disabled aria-disabled="true" aria-label="Simpan ke favorit" title="Favorit segera hadir" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-olive/25 text-olive opacity-60">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21l7.8-7.5 1-1.1a5.5 5.5 0 0 0 0-7.8z"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <section class="mt-8 rounded-2xl border border-olive/10 bg-paper p-4 sm:p-6" id="ulasan" data-tab-grup>
            <div class="gulir-x flex gap-2" role="tablist" aria-label="Informasi produk">
                <button type="button" role="tab" data-tab-tombol="deskripsi" aria-selected="true" class="shrink-0 rounded-full bg-olive px-4 py-2 text-sm font-bold text-white">Deskripsi</button>
                <button type="button" role="tab" data-tab-tombol="info" aria-selected="false" class="shrink-0 rounded-full px-4 py-2 text-sm font-bold text-olive">Informasi Produk</button>
                <button type="button" role="tab" data-tab-tombol="ulasan" aria-selected="false" class="shrink-0 rounded-full px-4 py-2 text-sm font-bold text-olive">Ulasan ({{ $ringkasan['jumlah'] }})</button>
            </div>

            <div class="mt-4" role="tabpanel" data-tab-panel="deskripsi">
                <p class="whitespace-pre-line text-ink/80">{{ $produk->deskripsi ?: 'Belum ada deskripsi untuk produk ini.' }}</p>
            </div>

            <div class="mt-4" role="tabpanel" data-tab-panel="info" hidden>
                <dl class="grid gap-4 text-sm sm:grid-cols-2">
                    <div><dt class="text-ink/60">Kategori</dt><dd class="font-bold">{{ $produk->category->nama }}</dd></div>
                    <div><dt class="text-ink/60">Toko</dt><dd class="font-bold">{{ $produk->shop->nama }}</dd></div>
                    <div><dt class="text-ink/60">Kelompok PEKKA</dt><dd class="font-bold">{{ $produk->shop->kelompok->nama }}</dd></div>
                    <div><dt class="text-ink/60">Stok</dt><dd class="font-bold">{{ $habis ? 'Habis' : $produk->stok }}</dd></div>
                </dl>
            </div>

            <div class="mt-4" role="tabpanel" data-tab-panel="ulasan" hidden>
                @if ($ringkasan['jumlah'] === 0)
                    <div class="flex flex-col items-center gap-2 py-6 text-center">
                        <p class="text-lg font-extrabold">Belum ada ulasan</p>
                        <p class="max-w-sm text-sm text-ink/70">Ulasan akan muncul setelah pembeli menyelesaikan pesanan produk ini.</p>
                    </div>
                @else
                    <div class="grid gap-6 sm:grid-cols-[14rem_1fr] sm:items-center">
                        <div class="text-center sm:text-left">
                            <p class="text-5xl font-extrabold text-olive">{{ number_format($ringkasan['rata'], 1, ',', '.') }}</p>
                            <x-bintang :nilai="$ringkasan['rata']" :angka="false" />
                            <p class="mt-1 text-sm text-ink/70">dari {{ $ringkasan['jumlah'] }} ulasan</p>
                        </div>
                        <div class="flex flex-col gap-1.5" aria-label="Sebaran penilaian">
                            @foreach ($ringkasan['sebaran'] as $bintang => $banyak)
                                @php $persen = round($banyak / $ringkasan['jumlah'] * 100); @endphp
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="w-6 shrink-0 font-bold">{{ $bintang }}★</span>
                                    <span class="h-2 flex-1 overflow-hidden rounded-full bg-ink/10"><span class="block h-full rounded-full bg-amber-500" style="width: {{ $persen }}%"></span></span>
                                    <span class="w-6 shrink-0 text-right text-ink/60">{{ $banyak }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-4">
                        @foreach ($ulasan as $item)
                            <article class="border-t border-olive/10 py-4">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="font-bold">{{ $item->namaSamaran() }}</p>
                                    <span class="text-xs text-ink/60">{{ $item->created_at->format('d/m/Y') }}</span>
                                </div>
                                <x-bintang :nilai="$item->rating" :angka="false" :kecil="true" />
                                @if ($item->komentar)
                                    <p class="mt-2 whitespace-pre-line text-ink/80">{{ $item->komentar }}</p>
                                @endif
                                @if ($item->balasan)
                                    <div class="mt-3 rounded-xl bg-lime/15 p-3 text-sm">
                                        <p class="font-bold text-olive">Balasan penjual</p>
                                        <p class="mt-1 whitespace-pre-line text-ink/80">{{ $item->balasan }}</p>
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-2">{{ $ulasan->links() }}</div>
                @endif
            </div>
        </section>

        @if ($lainnya->isNotEmpty())
            <section class="mt-8">
                <div class="mb-3 flex items-end justify-between gap-3">
                    <h2 class="text-xl font-extrabold">Produk Lain dari Toko Ini</h2>
                    <a href="{{ route('katalog', ['q' => $produk->shop->nama]) }}" class="shrink-0 text-sm font-bold text-olive underline">Lihat semua</a>
                </div>
                <div class="gulir-x -mx-4 flex snap-x snap-mandatory gap-3 px-4 pb-2 lg:mx-0 lg:grid lg:grid-cols-4 lg:overflow-visible lg:px-0">
                    @foreach ($lainnya as $item)
                        <div class="w-[60%] shrink-0 snap-start sm:w-[40%] lg:w-auto">
                            <x-produk-kartu :produk="$item" />
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    <div class="fixed inset-x-0 bottom-16 z-20 flex items-center gap-3 border-t border-olive/15 bg-paper px-4 py-3 lg:hidden">
        <div class="min-w-0">
            <p class="text-xs text-ink/60">Total pesanan</p>
            <p class="font-extrabold text-olive" data-subtotal>{{ $habis ? 'Rp 0' : $produk->harga_rupiah }}</p>
            <p class="text-xs text-ink/60" data-jumlah-item>{{ $habis ? '0 item' : '1 item' }}</p>
        </div>
        @if ($habis)
            <button type="button" disabled aria-disabled="true" class="btn-primary ml-auto max-w-[12rem]">Stok habis</button>
        @elseif ($milikSendiri)
            <button type="button" disabled aria-disabled="true" class="btn-primary ml-auto max-w-[12rem]">Produk Anda</button>
        @elseif (auth()->check())
            <button type="submit" form="form-keranjang" class="btn-primary ml-auto max-w-[12rem]">+ Keranjang</button>
        @else
            <a href="{{ route('login') }}" class="btn-primary ml-auto max-w-[12rem]">Masuk untuk membeli</a>
        @endif
    </div>

    @auth
        <form id="form-keranjang" method="POST" action="{{ route('keranjang.tambah') }}" class="hidden">
            @csrf
            <input type="hidden" name="product_id" value="{{ $produk->id }}">
        </form>
    @endauth
@endsection
