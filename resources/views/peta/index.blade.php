@extends('layouts.app')

@section('judul', 'Peta Usaha')

@section('isi')
    <div class="mx-auto flex max-w-5xl flex-col gap-5 px-4 py-6">
        <div>
            <h1 class="text-3xl font-extrabold">Peta Usaha</h1>
            <p class="mt-1 text-ink/70">Wilayah kelompok PEKKA di Kabupaten Bogor yang sudah punya toko di PEKKA BOIS.</p>
        </div>

        <x-alert judul="Lokasi ditampilkan per desa atau kecamatan">
            Demi keamanan anggota, peta hanya menunjukkan wilayah kelompok, bukan alamat rumah atau toko.
        </x-alert>

        @if ($titik->isNotEmpty())
            <div id="peta" data-titik="{{ $titik->toJson() }}" class="h-80 w-full overflow-hidden rounded-2xl border border-olive/15 bg-paper lg:h-[28rem]" role="region" aria-label="Peta wilayah kelompok PEKKA"></div>
        @elseif ($jumlahKelompok > 0)
            <x-alert>Titik peta belum diisi admin. Daftar kelompok tetap bisa dilihat di bawah.</x-alert>
        @endif

        @forelse ($kelompok as $kecamatan => $daftar)
            <section>
                <h2 class="mb-2 text-lg font-extrabold text-olive">{{ $kecamatan }}</h2>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($daftar as $item)
                        <a href="{{ route('katalog', ['kelompok' => $item->id]) }}" class="card block !p-4 hover:border-olive/30">
                            <p class="font-extrabold">{{ $item->nama }}</p>
                            @if ($item->desa)
                                <p class="text-sm text-ink/70">{{ $item->desa }}</p>
                            @endif
                            <p class="mt-2 text-sm font-bold text-olive">{{ $item->toko_count }} toko &middot; Lihat produk</p>
                        </a>
                    @endforeach
                </div>
            </section>
        @empty
            <section class="card flex flex-col items-center gap-2 py-10 text-center">
                <p class="text-lg font-extrabold">Belum ada usaha yang tampil</p>
                <p class="max-w-sm text-sm text-ink/70">Kelompok akan muncul di sini setelah tokonya diverifikasi admin.</p>
            </section>
        @endforelse
    </div>
@endsection
