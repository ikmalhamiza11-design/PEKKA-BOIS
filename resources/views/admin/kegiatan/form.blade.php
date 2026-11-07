@extends('layouts.admin')

@section('judul', $kegiatan ? 'Ubah Kegiatan' : 'Tambah Kegiatan')

@section('isi')
    <a href="{{ route('admin.kegiatan.index') }}" class="text-sm font-bold text-olive underline">&larr; Kembali ke daftar kegiatan</a>

    <form method="POST" action="{{ $kegiatan ? route('admin.kegiatan.update', $kegiatan->id) : route('admin.kegiatan.store') }}" class="card flex max-w-xl flex-col gap-3" novalidate>
        @csrf
        @if ($kegiatan)
            @method('PUT')
        @endif

        <h1 class="text-xl font-extrabold">{{ $kegiatan ? 'Ubah kegiatan' : 'Tambah kegiatan' }}</h1>

        <x-field name="judul" label="Judul" :nilai="$kegiatan?->judul" maxlength="150" />

        <div>
            <label for="deskripsi" class="mb-1.5 block text-sm font-bold">Deskripsi<span class="text-danger" aria-hidden="true"> *</span></label>
            <textarea id="deskripsi" name="deskripsi" rows="6" maxlength="5000" class="input @error('deskripsi') input-galat @enderror">{{ old('deskripsi', $kegiatan?->deskripsi) }}</textarea>
            @error('deskripsi')
                <p class="mt-1.5 text-sm font-semibold text-danger">{{ $message }}</p>
            @enderror
        </div>

        <x-field name="lokasi" label="Lokasi" :nilai="$kegiatan?->lokasi" :wajib="false" maxlength="150" />
        <x-field name="mulai_at" label="Waktu mulai" type="datetime-local" :nilai="$kegiatan?->mulai_at?->format('Y-m-d\TH:i')" />
        <x-field name="selesai_at" label="Waktu selesai" type="datetime-local" :nilai="$kegiatan?->selesai_at?->format('Y-m-d\TH:i')" :wajib="false" />

        <label class="flex min-h-11 items-center gap-3 text-sm font-bold">
            <input type="checkbox" name="dipublikasikan" value="1" class="h-5 w-5" @checked(old('dipublikasikan', $kegiatan?->dipublikasikan ?? true))>
            Tampilkan di halaman Kegiatan
        </label>

        <button type="submit" class="btn-primary">Simpan</button>
    </form>
@endsection
