@extends('layouts.app')

@section('judul', 'Beranda')

@section('isi')
    @php
        $ikon = [
            'kuliner' => '<path d="M3 2v7c0 1.1.9 2 2 2h2a2 2 0 0 0 2-2V2"/><path d="M6 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>',
            'fashion' => '<path d="M20.4 6.7 16 4l-4 2-4-2-4.4 2.7a1 1 0 0 0-.4 1.2l1.4 3.4L6 11v9h12v-9l2-1.7 1.4-3.4a1 1 0 0 0-.4-1.2z"/>',
            'kerajinan' => '<path d="m12 2 10 5-10 5L2 7z"/><path d="m2 17 10 5 10-5"/><path d="m2 12 10 5 10-5"/>',
            'pertanian' => '<path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.5 19 2c1 2 2 4.2 2 8 0 5.5-4.8 10-10 10z"/><path d="M2 21c0-3 1.9-5.6 5-7"/>',
            'jasa' => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.8-3.8a6 6 0 0 1-7.9 7.9L6.6 19.4a2 2 0 0 1-2.8-2.8l7.1-7.1A6 6 0 0 1 18.8 1.6z"/>',
            'oleh-oleh' => '<rect x="3" y="8" width="18" height="4"/><path d="M12 8v13"/><path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"/><path d="M7.5 8a2.5 2.5 0 0 1 0-5C11 3 12 8 12 8s1-5 4.5-5a2.5 2.5 0 0 1 0 5"/>',
        ];
        $keterangan = ['Wilayah Kabupaten Bogor', 'Diperiksa admin kelompok', 'Dari kuliner hingga jasa'];
        $ikonStatistik = [
            '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9"/><path d="M16 3.1a4 4 0 0 1 0 7.8"/>',
            '<path d="M12 3 4 6v6c0 4.5 3.2 8 8 9 4.8-1 8-4.5 8-9V6z"/><path d="m9 12 2 2 4-4"/>',
            '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>',
        ];
    @endphp

    <section class="mx-auto grid max-w-6xl grid-cols-1 gap-4 px-4 pt-5 lg:grid-cols-[1.5fr_1fr] lg:pt-6">
        <div class="flex min-w-0 flex-col gap-5 lg:rounded-3xl lg:bg-lime/15 lg:p-8">
            <div>
                <p class="mb-3 inline-flex rounded-full bg-lime/30 px-3 py-1 text-xs font-bold text-olive">Marketplace perempuan kepala keluarga</p>
                <h1 class="text-3xl font-extrabold leading-snug lg:text-4xl">Belanja &amp; Dukung <br class="lg:hidden"><span class="rounded-md bg-lime px-2 py-0.5 leading-[1.6] box-decoration-clone">Usaha PEKKA Bogor</span></h1>
                <p class="mt-3 max-w-xl text-ink/70">Marketplace produk Kelompok Perempuan Kepala Keluarga, Kabupaten Bogor.</p>
            </div>

            <form method="GET" action="{{ route('katalog') }}" role="search" class="flex max-w-xl items-center gap-2 rounded-full border border-olive/15 bg-paper p-1.5 focus-within:ring-2 focus-within:ring-olive/40">
                <label for="cari" class="sr-only">Cari produk, usaha, atau kelompok PEKKA</label>
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="ml-3 shrink-0 text-olive/60"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input id="cari" name="q" type="search" placeholder="Cari produk, usaha, atau kelompok PEKKA..." class="min-w-0 flex-1 bg-transparent px-2 py-2 text-ink placeholder:text-ink/50 focus:outline-none">
                <button type="submit" class="rounded-full bg-lime px-5 py-2.5 font-extrabold text-ink hover:brightness-95">Cari</button>
            </form>

            <div id="kategori">
                <div class="mb-2 flex items-center justify-between">
                    <h2 class="text-sm font-extrabold text-olive">Kategori Produk</h2>
                    <a href="{{ route('katalog') }}" class="text-xs font-bold text-olive underline">Lihat produk</a>
                </div>
                <div class="gulir-x flex gap-2 lg:flex-wrap">
                    @foreach ($kategori as $item)
                        <a href="{{ route('katalog', ['kategori' => $item->slug]) }}" class="inline-flex shrink-0 items-center gap-2 rounded-full border border-olive/20 bg-paper px-4 py-2 text-sm font-bold text-olive hover:bg-lime/30">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $ikon[$item->slug] ?? $ikon['kerajinan'] !!}</svg>
                            {{ $item->nama }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <aside class="hidden rounded-3xl bg-paper p-4 shadow-sm lg:block" aria-label="Produk unggulan PEKKA">
            <div class="mb-3 flex items-center justify-between gap-2">
                <p class="text-sm font-extrabold text-olive">Produk Unggulan PEKKA</p>
                @if ($sorotan)
                    <span class="rounded-full bg-lime px-3 py-0.5 text-xs font-extrabold text-ink">{{ $sorotan->category->nama }}</span>
                @endif
            </div>
            @if ($sorotan)
                <div class="foto-{{ $sorotan->id % 4 }} flex aspect-[4/3] items-center justify-center rounded-2xl text-olive/50">
                    <svg viewBox="0 0 24 24" width="56" height="56" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                </div>
                <p class="mt-3 text-lg font-extrabold"><a href="{{ route('produk.show', $sorotan->id) }}" class="hover:underline">{{ $sorotan->nama }}</a></p>
                <p class="font-extrabold text-olive">{{ $sorotan->harga_rupiah }}</p>
                <div class="mt-2 rounded-lg bg-lime/15 px-3 py-2 text-xs text-ink/70">
                    <p class="font-bold text-ink/80">{{ $sorotan->shop->nama }}</p>
                    <p>{{ $sorotan->shop->kelompok->nama }} &middot; Stok: {{ $sorotan->stok }}</p>
                </div>
                <div class="mt-3 flex gap-2">
                    <x-tombol-keranjang :produk="$sorotan" />
                    <button type="button" disabled aria-disabled="true" aria-label="Simpan ke favorit" title="Favorit segera hadir" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-olive/25 text-olive opacity-60">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21l7.8-7.5 1-1.1a5.5 5.5 0 0 0 0-7.8z"/></svg>
                    </button>
                </div>
            @else
                <p class="text-ink/70">Produk unggulan akan tampil di sini setelah ada toko yang terverifikasi.</p>
            @endif
        </aside>
    </section>

    <section class="mx-auto max-w-6xl px-4 pt-5">
        <div class="flex flex-col gap-4 rounded-2xl border border-olive/10 bg-paper p-5 shadow-sm lg:flex-row lg:items-center lg:justify-between lg:border-l-4 lg:border-l-lime">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-lime text-ink" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="m12 6 1 2.5 2.5 1-2.5 1L12 13l-1-2.5-2.5-1 2.5-1z"/></svg>
                </span>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-lg font-extrabold">Sahabat PEKKA</h2>
                        <span class="rounded-full bg-lime/40 px-2.5 py-0.5 text-[0.7rem] font-extrabold text-olive">Asisten otomatis (AI)</span>
                    </div>
                    <p class="mt-1 max-w-xl text-sm text-ink/70">Menjawab pertanyaan seputar cara belanja, cara berjualan, NIB, dan label halal.</p>
                </div>
            </div>
            <div class="flex flex-col gap-3 lg:items-end">
                <div class="hidden flex-wrap gap-2 lg:flex">
                    <a href="{{ route('sahabat-pekka', ['tanya' => 'Bagaimana cara mengurus NIB untuk usaha kecil?']) }}" class="rounded-full border border-olive/20 px-3 py-1.5 text-xs font-bold text-olive hover:bg-lime/30">Tanya NIB Usaha</a>
                    <a href="{{ route('sahabat-pekka', ['tanya' => 'Bagaimana cara belanja di PEKKA BOIS?']) }}" class="rounded-full border border-olive/20 px-3 py-1.5 text-xs font-bold text-olive hover:bg-lime/30">Cara Belanja</a>
                    <a href="{{ route('sahabat-pekka', ['tanya' => 'Bagaimana cara mengurus label halal untuk produk makanan?']) }}" class="rounded-full border border-olive/20 px-3 py-1.5 text-xs font-bold text-olive hover:bg-lime/30">Label Halal</a>
                </div>
                <a href="{{ route('sahabat-pekka') }}" class="btn-primary lg:w-auto">Tanya Sahabat PEKKA</a>
            </div>
        </div>
    </section>

    @if ($unggulan->isNotEmpty())
        <section id="unggulan" class="mx-auto max-w-6xl px-4 pt-8">
            <div class="mb-4 flex items-end justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-extrabold"><span class="lg:hidden">Produk Unggulan</span><span class="hidden lg:inline">Produk Unggulan Kelompok PEKKA</span></h2>
                    <p class="text-sm text-ink/60">Pilihan produk dari usaha anggota PEKKA.</p>
                </div>
                <div class="hidden items-center gap-2 lg:flex">
                    <button type="button" data-gulir="#daftar-unggulan" data-arah="-1" aria-label="Geser ke kiri" class="flex h-10 w-10 items-center justify-center rounded-full border border-olive/20 bg-paper text-olive hover:bg-lime/30">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                    <button type="button" data-gulir="#daftar-unggulan" data-arah="1" aria-label="Geser ke kanan" class="flex h-10 w-10 items-center justify-center rounded-full border border-olive/20 bg-paper text-olive hover:bg-lime/30">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                    <a href="{{ route('katalog') }}" class="rounded-full border border-olive/20 bg-paper px-4 py-2 text-sm font-bold text-olive hover:bg-lime/30">Lihat semua</a>
                </div>
                <a href="{{ route('katalog') }}" class="shrink-0 text-sm font-bold text-olive underline lg:hidden">Semua</a>
            </div>
            <div id="daftar-unggulan" class="gulir-x -mx-4 flex snap-x snap-mandatory gap-3 px-4 pb-2 lg:mx-0 lg:px-0">
                @foreach ($unggulan as $item)
                    <div class="w-[68%] shrink-0 snap-start sm:w-[40%] lg:w-[calc(25%-0.57rem)]">
                        <x-produk-kartu :produk="$item" />
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <section id="produk" class="mx-auto max-w-6xl px-4 py-8">
        <div class="mb-4 flex items-end justify-between gap-3">
            <div>
                <h2 class="text-2xl font-extrabold">Usaha &amp; Produk Pilihan</h2>
                <p class="text-sm text-ink/60">Produk usaha anggota kelompok PEKKA di Kabupaten Bogor.</p>
            </div>
            <a href="{{ route('katalog') }}" class="shrink-0 text-sm font-bold text-olive underline">Lihat semua<span class="hidden lg:inline"> katalog</span></a>
        </div>

        @if ($produk->isEmpty())
            <x-alert>Belum ada produk yang tampil. Produk muncul setelah toko diverifikasi admin kelompok.</x-alert>
        @else
            <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                @foreach ($produk as $item)
                    <x-produk-kartu :produk="$item" />
                @endforeach
            </div>
        @endif
    </section>

    <section class="mx-auto max-w-6xl px-4">
        <div class="rounded-2xl bg-olive p-4 text-white sm:p-6">
            <div class="grid gap-3 lg:grid-cols-3">
                @foreach ($statistik as $indeks => $item)
                    <div class="flex items-center gap-4 rounded-xl bg-white/5 p-4">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-lime text-ink" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $ikonStatistik[$indeks] !!}</svg>
                        </span>
                        <div>
                            <p class="text-2xl font-extrabold leading-none text-lime">{{ $item['nilai'] }}</p>
                            <p class="mt-1 text-sm font-bold">{{ $item['label'] }}</p>
                            <p class="text-xs text-white/70">{{ $keterangan[$indeks] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
