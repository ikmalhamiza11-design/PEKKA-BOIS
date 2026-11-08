@props(['produk'])

@php $habis = $produk->stok < 1; @endphp

<article class="relative flex h-full flex-col rounded-2xl border border-olive/10 bg-paper p-2 shadow-sm">
    <div class="foto-{{ $produk->id % 4 }} relative flex aspect-square items-center justify-center overflow-hidden rounded-xl text-olive/50">
        @if ($produk->foto)
            <img src="{{ asset('storage/'.$produk->foto) }}" alt="{{ $produk->nama }}" @class(['h-full w-full object-cover', 'opacity-60 grayscale' => $habis]) loading="lazy">
        @else
            <svg viewBox="0 0 24 24" width="44" height="44" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        @endif
        <span class="absolute left-2 top-2 rounded-full bg-lime px-2.5 py-0.5 text-[0.7rem] font-extrabold text-ink">{{ $produk->category->nama }}</span>
        @if ($habis)
            <span class="absolute inset-0 flex items-center justify-center bg-ink/30">
                <span class="rounded-full bg-paper px-3 py-1 text-xs font-extrabold text-danger">Stok Habis</span>
            </span>
        @endif
    </div>
    <div class="flex flex-1 flex-col gap-1.5 px-1 pb-1 pt-3">
        <h3 class="text-sm font-extrabold leading-snug"><a href="{{ route('produk.show', $produk->id) }}" class="after:absolute after:inset-0 after:rounded-2xl focus-visible:outline-none focus-visible:after:ring-2 focus-visible:after:ring-olive">{{ $produk->nama }}</a></h3>
        <p class="font-extrabold text-olive">{{ $produk->harga_rupiah }}</p>
        @if ($produk->preOrder())
            <p class="w-fit rounded-full bg-amber-100 px-2.5 py-0.5 text-[0.7rem] font-extrabold text-amber-900">Pre-order {{ $produk->pre_order_hari }} hari</p>
        @endif
        @if ($produk->shop->libur)
            <p class="w-fit rounded-full bg-ink/10 px-2.5 py-0.5 text-[0.7rem] font-extrabold text-ink/70">Toko libur</p>
        @elseif ($produk->kendalaToko())
            <p class="w-fit rounded-full bg-ink/10 px-2.5 py-0.5 text-[0.7rem] font-extrabold text-ink/70">Toko tutup</p>
        @endif
        @if (($produk->reviews_count ?? 0) > 0)
            <x-bintang :nilai="$produk->reviews_avg_rating" :jumlah="$produk->reviews_count" :kecil="true" />
        @endif
        <div class="rounded-lg bg-lime/15 px-2.5 py-1.5 text-xs text-ink/70">
            <p class="font-bold text-ink/80">{{ $produk->shop->nama }}</p>
            <p>{{ $produk->shop->kelompok->nama }} &middot; {{ $habis ? 'Stok habis' : 'Stok: '.$produk->stok }}</p>
        </div>
        <x-tombol-keranjang :produk="$produk" :kecil="true" />
    </div>
</article>
