@extends('layouts.admin')

@section('judul', 'Kelompok PEKKA')

@section('isi')
    <div>
        <h1 class="text-2xl font-extrabold">Kelompok PEKKA</h1>
        <p class="mt-1 text-sm text-ink/70">{{ $total }} kelompok terdaftar. Penjual memilih kelompoknya dari daftar ini saat mendaftar.</p>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <form method="POST" action="{{ route('admin.kelompok.store') }}" class="card flex flex-col gap-3" novalidate>
            @csrf
            <h2 class="font-extrabold">Tambah satu kelompok</h2>
            <x-field name="nama" label="Nama kelompok" maxlength="255" />
            <x-field name="desa" label="Desa/Kelurahan" :wajib="false" maxlength="255" />
            <x-field name="kecamatan" label="Kecamatan" :wajib="false" maxlength="255" />
            <button type="submit" class="btn-primary">Tambah Kelompok</button>
        </form>

        <form method="POST" action="{{ route('admin.kelompok.impor') }}" class="card flex flex-col gap-3" novalidate>
            @csrf
            <h2 class="font-extrabold">Tempel banyak kelompok sekaligus</h2>
            <p class="text-sm text-ink/70">Satu kelompok per baris, dengan format <span class="font-bold">nama; desa; kecamatan</span>. Salin langsung dari kolom Excel juga bisa. Desa dan kecamatan boleh dikosongkan, dan kelompok yang sudah ada dilewati.</p>
            <div>
                <label for="daftar" class="mb-1.5 block text-sm font-bold">Daftar kelompok<span class="text-danger" aria-hidden="true"> *</span></label>
                <textarea id="daftar" name="daftar" rows="6" placeholder="PEKKA Melati; Cibinong; Cibinong&#10;PEKKA Mawar; Ciawi; Ciawi" class="input @error('daftar') input-galat @enderror">{{ old('daftar') }}</textarea>
                @error('daftar')
                    <p class="mt-1.5 text-sm font-semibold text-danger">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="btn-secondary">Tambahkan Semua</button>
        </form>
    </div>

    <form method="GET" action="{{ route('admin.kelompok.index') }}" class="card !p-4 flex flex-col gap-3 sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="q" class="mb-1.5 block text-sm font-bold">Cari kelompok</label>
            <input id="q" name="q" type="search" value="{{ $kata }}" maxlength="100" placeholder="Nama kelompok, desa, atau kecamatan" class="input">
        </div>
        <button type="submit" class="btn-primary sm:w-auto">Cari</button>
    </form>

    @forelse ($kelompok as $item)
        <article class="card !p-4 flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="truncate font-extrabold">{{ $item->nama }}</p>
                <p class="truncate text-sm text-ink/70">{{ collect([$item->desa, $item->kecamatan])->filter()->implode(', ') ?: 'Wilayah belum diisi' }} &middot; {{ $item->shops_count }} toko</p>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <a href="{{ route('admin.kelompok.edit', $item->id) }}" class="btn-secondary min-h-11 w-auto !px-4 !py-2 text-sm">Ubah</a>
                <form method="POST" action="{{ route('admin.kelompok.destroy', $item->id) }}" onsubmit="return confirm('Hapus kelompok {{ addslashes($item->nama) }}?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-secondary min-h-11 w-auto !border-danger !px-4 !py-2 text-sm !text-danger hover:!bg-danger/10">Hapus</button>
                </form>
            </div>
        </article>
    @empty
        <section class="card text-center">
            <p class="font-extrabold">Belum ada kelompok yang cocok</p>
        </section>
    @endforelse

    {{ $kelompok->links() }}
@endsection
