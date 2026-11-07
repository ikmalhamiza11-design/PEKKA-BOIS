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
        <x-field name="latitude" label="Lintang (latitude) untuk Peta Usaha" :nilai="$kelompok->latitude" :wajib="false" inputmode="decimal" placeholder="-6.4817" hint="Titik tengah desa atau kecamatan, bukan alamat rumah. Klik kanan lokasi di Google Maps lalu salin angka pertama." />
        <x-field name="longitude" label="Bujur (longitude) untuk Peta Usaha" :nilai="$kelompok->longitude" :wajib="false" inputmode="decimal" placeholder="106.8540" hint="Angka kedua dari Google Maps." />
        <button type="submit" class="btn-primary">Simpan Perubahan</button>
    </form>
@endsection
