@extends('layouts.auth')

@section('judul', 'Produk Saya')

@section('appbar')
    <x-app-bar judul="Produk Saya" :kembali="route('dashboard')" />
@endsection

@section('isi')
    @if (session('pesan'))
        <x-alert tipe="sukses">{{ session('pesan') }}</x-alert>
    @endif

    @if ($toko->status !== \App\Models\Shop::TERVERIFIKASI)
        <x-alert judul="Produk belum tampil di katalog">
            Produk baru tampil setelah tokomu diverifikasi admin kelompok. Kamu tetap bisa menyiapkan produk dari sekarang.
        </x-alert>
    @endif

    <a href="{{ route('penjual.produk.create') }}" class="btn-primary">+ Tambah Produk</a>

    @forelse ($produk as $item)
        <article class="card flex flex-col gap-3 !p-3">
            <div class="flex gap-3">
                <div class="foto-{{ $item->id % 4 }} flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl text-olive/50">
                    @if ($item->foto)
                        <img src="{{ asset('storage/'.$item->foto) }}" alt="{{ $item->nama }}" class="h-full w-full object-cover" loading="lazy">
                    @else
                        <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate font-extrabold">{{ $item->nama }}</p>
                    <p class="font-extrabold text-olive">{{ $item->harga_rupiah }}</p>
                    <p class="text-xs text-ink/70">{{ $item->category->nama }} &middot; Stok: {{ $item->stok }}</p>
                    @if ($item->preOrder())
                        <p class="text-xs font-bold text-amber-900">Pre-order {{ $item->pre_order_hari }} hari</p>
                    @elseif ($item->stok_harian)
                        <p class="text-xs text-ink/70">Stok harian: {{ $item->stok_harian }} per hari</p>
                    @endif
                    <div class="mt-1 flex flex-wrap gap-1.5">
                        @if ($item->aktif)
                            <span class="rounded-full bg-lime/30 px-2.5 py-0.5 text-[0.7rem] font-extrabold text-olive">Aktif</span>
                        @else
                            <span class="rounded-full bg-ink/10 px-2.5 py-0.5 text-[0.7rem] font-extrabold text-ink/70">Nonaktif</span>
                        @endif
                        @if ($item->stok < 1)
                            <span class="rounded-full bg-red-50 px-2.5 py-0.5 text-[0.7rem] font-extrabold text-danger">Stok habis</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <a href="{{ route('penjual.produk.edit', $item->id) }}" class="btn-secondary min-h-11 !px-2 !py-2 text-sm">Ubah</a>

                <form method="POST" action="{{ route('penjual.produk.status', $item->id) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn-secondary min-h-11 !px-2 !py-2 text-sm">{{ $item->aktif ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                </form>

                <form method="POST" action="{{ route('penjual.produk.destroy', $item->id) }}" data-konfirmasi="Hapus produk ini? Tindakan ini tidak bisa dibatalkan." data-konfirmasi-judul="Hapus data ini?" data-konfirmasi-tombol="Ya, Hapus">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-secondary min-h-11 !border-danger !px-2 !py-2 text-sm !text-danger hover:!bg-red-50">Hapus</button>
                </form>
            </div>
        </article>
    @empty
        <section class="card text-center">
            <p class="font-extrabold">Belum ada produk</p>
            <p class="mt-1 text-sm text-ink/70">Tambahkan produk pertamamu supaya bisa dilihat pembeli.</p>
        </section>
    @endforelse

    {{ $produk->links() }}
@endsection
