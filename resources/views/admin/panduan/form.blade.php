@extends('layouts.admin')

@section('judul', $panduan ? 'Ubah Panduan' : 'Tambah Panduan')

@section('isi')
    <a href="{{ route('admin.panduan.index') }}" class="text-sm font-bold text-olive underline">&larr; Kembali ke daftar panduan</a>

    <form method="POST" action="{{ $panduan ? route('admin.panduan.update', $panduan->id) : route('admin.panduan.store') }}" class="card flex max-w-xl flex-col gap-3" novalidate>
        @csrf
        @if ($panduan)
            @method('PUT')
        @endif

        <h1 class="text-xl font-extrabold">{{ $panduan ? 'Ubah panduan' : 'Tambah panduan' }}</h1>

        <x-field name="judul" label="Judul" :nilai="$panduan?->judul" maxlength="150" />

        <div>
            <label for="kategori" class="mb-1.5 block text-sm font-bold">Kategori<span class="text-danger" aria-hidden="true"> *</span></label>
            <select id="kategori" name="kategori" class="input @error('kategori') input-galat @enderror">
                @foreach (\App\Models\Panduan::KATEGORI as $kunci => $nama)
                    <option value="{{ $kunci }}" @selected(old('kategori', $panduan?->kategori) === $kunci)>{{ $nama }}</option>
                @endforeach
            </select>
            @error('kategori')
                <p class="mt-1.5 text-sm font-semibold text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="isi" class="mb-1.5 block text-sm font-bold">Isi panduan<span class="text-danger" aria-hidden="true"> *</span></label>
            <textarea id="isi" name="isi" rows="10" maxlength="8000" class="input @error('isi') input-galat @enderror">{{ old('isi', $panduan?->isi) }}</textarea>
            @error('isi')
                <p class="mt-1.5 text-sm font-semibold text-danger">{{ $message }}</p>
            @enderror
        </div>

        <x-field name="urutan" label="Urutan tampil" type="number" :nilai="$panduan?->urutan ?? 0" :wajib="false" hint="Angka kecil tampil lebih dulu." min="0" max="999" />

        <label class="flex min-h-11 items-center gap-3 text-sm font-bold">
            <input type="checkbox" name="dipublikasikan" value="1" class="h-5 w-5" @checked(old('dipublikasikan', $panduan?->dipublikasikan ?? true))>
            Tampilkan di halaman Panduan
        </label>

        <button type="submit" class="btn-primary">Simpan</button>
    </form>
@endsection
