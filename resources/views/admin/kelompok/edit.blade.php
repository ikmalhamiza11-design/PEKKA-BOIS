@extends('layouts.admin')

@section('judul', 'Ubah Kelompok')

@section('isi')
    <a href="{{ route('admin.kelompok.index') }}" class="text-sm font-bold text-olive underline">&larr; Kembali ke daftar kelompok</a>

    <form method="POST" action="{{ route('admin.kelompok.update', $kelompok->id) }}" class="card flex max-w-xl flex-col gap-3" novalidate>
        @csrf
        @method('PUT')
        <h1 class="text-xl font-extrabold">Ubah kelompok</h1>
        <x-field name="nama" label="Nama kelompok" :nilai="$kelompok->nama" maxlength="255" />
        <x-field name="desa" label="Desa/Kelurahan" :nilai="$kelompok->desa" :wajib="false" maxlength="255" />
        <x-field name="kecamatan" label="Kecamatan" :nilai="$kelompok->kecamatan" :wajib="false" maxlength="255" />
        <button type="submit" class="btn-primary">Simpan Perubahan</button>
    </form>
@endsection
