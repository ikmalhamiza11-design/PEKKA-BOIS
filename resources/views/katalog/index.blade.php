@extends('layouts.app')

@section('judul', 'Katalog')

@section('isi')
    @php
        $urutan = ['terbaru' => 'Terbaru', 'termurah' => 'Harga terendah', 'termahal' => 'Harga tertinggi', 'terbaik' => 'Penilaian terbaik'];
        $tanpaKategori = \Illuminate\Support\Arr::except($parameter, ['kategori']);
    @endphp

    <div class="mx-auto max-w-6xl px-4 py-5 lg:py-8">
        <nav class="mb-2 hidden text-sm text-ink/60 lg:block" aria-label="Jejak halaman">
            <a href="{{ route('beranda') }}" class="hover:text-olive">Beranda</a>
            <span aria-hidden="true">/</span>
            <span class="font-bold text-olive">Katalog Produk</span>
        </nav>

        <h1 class="text-3xl font-extrabold">Katalog Produk <span class="hidden lg:inline">Kelompok PEKKA</span></h1>
        <p class="mt-1 max-w-2xl text-ink/70">Jelajahi karya dan produk usaha anggota kelompok PEKKA di Kabupaten Bogor.</p>

        <form method="GET" action="{{ route('katalog') }}" role="search" class="mt-4 flex max-w-2xl items-center gap-2 rounded-full border border-olive/15 bg-paper p-1.5 focus-within:ring-2 focus-within:ring-olive/40">
            <x-parameter-tersembunyi :parameter="\Illuminate\Support\Arr::except($parameter, ['q'])" />
            <label for="cari" class="sr-only">Cari produk, usaha, atau kelompok PEKKA</label>
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="ml-3 shrink-0 text-olive/60"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input id="cari" name="q" type="search" value="{{ $parameter['q'] ?? '' }}" placeholder="Cari produk, usaha, atau kelompok PEKKA..." class="min-w-0 flex-1 bg-transparent px-2 py-2 text-ink placeholder:text-ink/50 focus:outline-none">
            <button type="submit" class="rounded-full bg-lime px-5 py-2.5 font-extrabold text-ink hover:brightness-95">Cari</button>
        </form>

        <div class="gulir-x mt-4 flex gap-2">
            <a href="{{ route('katalog', $tanpaKategori) }}" @class(['shrink-0 rounded-full border px-4 py-2 text-sm font-bold', 'border-olive bg-olive text-white' => $filter['kategori'] === [], 'border-olive/20 bg-paper text-olive hover:bg-lime/30' => $filter['kategori'] !== []])>Semua Produk</a>
            @foreach ($kategori as $item)
                @php $aktif = in_array($item->slug, $filter['kategori'], true); @endphp
                <a href="{{ route('katalog', array_merge($tanpaKategori, ['kategori' => [$item->slug]])) }}" @class(['shrink-0 rounded-full border px-4 py-2 text-sm font-bold', 'border-olive bg-olive text-white' => $aktif, 'border-olive/20 bg-paper text-olive hover:bg-lime/30' => ! $aktif])>{{ $item->nama }}</a>
            @endforeach
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <button type="button" data-buka-dialog="lembar-filter" class="inline-flex items-center gap-2 rounded-full border border-olive/25 bg-paper px-4 py-2 text-sm font-bold text-olive lg:hidden">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"/></svg>
                Filter
                @if ($jumlahFilter > 0)
                    <span class="rounded-full bg-lime px-2 text-xs text-ink">{{ $jumlahFilter }}</span>
                @endif
            </button>

            <form method="GET" action="{{ route('katalog') }}" class="ml-auto flex items-center gap-2">
                <x-parameter-tersembunyi :parameter="\Illuminate\Support\Arr::except($parameter, ['urut'])" />
                <label for="urut" class="text-sm font-bold text-olive">Urutkan:</label>
                <select id="urut" name="urut" data-kirim-otomatis class="input !w-auto !py-2 text-sm">
                    @foreach ($urutan as $kunci => $nama)
                        <option value="{{ $kunci }}" @selected($urut === $kunci)>{{ $nama }}</option>
                    @endforeach
                </select>
                <noscript><button type="submit" class="rounded-full bg-olive px-3 py-2 text-sm font-bold text-white">Terapkan</button></noscript>
            </form>
        </div>

        <p class="mt-3 text-sm text-ink/70" aria-live="polite">Menampilkan <strong>{{ $produk->count() }}</strong> dari {{ $total }} produk</p>

        @if ($chip !== [])
            <div class="mt-3 flex flex-wrap items-center gap-2">
                @foreach ($chip as $item)
                    <a href="{{ $item['url'] }}" aria-label="Hapus filter {{ $item['label'] }}" class="inline-flex items-center gap-1.5 rounded-full bg-lime/30 px-3 py-1 text-xs font-bold text-olive hover:bg-lime/50">
                        {{ $item['label'] }}
                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </a>
                @endforeach
                <a href="{{ route('katalog') }}" class="text-xs font-bold text-olive underline">Hapus semua</a>
            </div>
        @endif

        <div class="mt-4 lg:grid lg:grid-cols-[16rem_1fr] lg:items-start lg:gap-6">
            <aside class="hidden rounded-2xl border border-olive/10 bg-paper p-4 shadow-sm lg:sticky lg:top-20 lg:block" aria-label="Filter produk">
                <x-filter-katalog awalan="samping" :kategori="$kategori" :hitung="$hitung" :kelompok="$kelompok" :filter="$filter" :parameter="$parameter" :jumlah="$jumlahFilter" />
            </aside>

            <section id="hasil" aria-label="Hasil pencarian">
                @if ($produk->isEmpty())
                    <div class="flex flex-col items-center gap-3 rounded-2xl border border-olive/10 bg-paper px-6 py-12 text-center">
                        <span class="flex h-20 w-20 items-center justify-center rounded-full bg-lime/30 text-olive" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/><path d="M8 11h6"/></svg>
                        </span>
                        @if ($parameter === [])
                            <h2 class="text-2xl font-extrabold">Belum ada produk</h2>
                            <p class="max-w-sm text-ink/70">Produk akan tampil setelah toko diverifikasi admin kelompok.</p>
                        @else
                            <h2 class="text-2xl font-extrabold">Produk tidak ditemukan</h2>
                            <p class="max-w-sm text-ink/70">Coba kata kunci lain atau hapus beberapa filter yang aktif agar produk usaha PEKKA dapat ditemukan.</p>
                            <a href="{{ route('katalog') }}" class="btn-primary max-w-xs">Reset Filter &amp; Pencarian</a>
                        @endif
                        <a href="{{ route('beranda') }}" class="text-sm font-bold text-olive underline">Kembali ke Beranda</a>
                        <a href="{{ route('sahabat-pekka') }}" class="text-sm font-bold text-olive underline">Bingung cari apa? Tanya Sahabat PEKKA</a>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($produk as $item)
                            <x-produk-kartu :produk="$item" />
                        @endforeach
                    </div>

                    <div class="mt-6 flex flex-col items-center gap-2">
                        @if ($adaLagi)
                            <a href="{{ $urlLagi }}" class="btn-secondary max-w-xs">Muat lebih banyak</a>
                        @endif
                        <p class="text-xs text-ink/60">Menampilkan {{ $produk->count() }} dari {{ $total }} produk</p>
                    </div>
                @endif
            </section>
        </div>
    </div>

    <dialog id="lembar-filter" aria-label="Filter produk" class="fixed inset-x-0 bottom-0 top-auto m-0 max-h-[88vh] w-full max-w-none overflow-y-auto rounded-t-3xl bg-paper p-5 text-ink backdrop:bg-ink/50 lg:hidden">
        <div class="mb-3 flex justify-end">
            <button type="button" data-tutup-dialog aria-label="Tutup filter" class="flex h-10 w-10 items-center justify-center rounded-full border border-olive/20 text-olive">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <x-filter-katalog awalan="lembar" :kategori="$kategori" :hitung="$hitung" :kelompok="$kelompok" :filter="$filter" :parameter="$parameter" :jumlah="$jumlahFilter" />
    </dialog>
@endsection
