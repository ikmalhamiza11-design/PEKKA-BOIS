@extends('layouts.auth')

@section('judul', 'Ketersediaan Toko')

@section('appbar')
    <x-app-bar judul="Ketersediaan Toko" :kembali="route('dashboard')" />
@endsection

@section('isi')
    @if (session('pesan'))
        <x-alert tipe="sukses">{{ session('pesan') }}</x-alert>
    @endif

    @if ($toko->libur)
        <x-alert judul="Tokomu sedang libur">Pembeli belum bisa memesan produk apa pun dari tokomu, termasuk pre-order.</x-alert>
    @elseif (! $toko->sesuaiJadwal())
        <x-alert judul="Tokomu sedang di luar jam buka">Pembeli hanya bisa memesan produk pre-order. {{ $toko->jadwalTeks() }}</x-alert>
    @endif

    @php
        $otomatis = (bool) old('jadwal_otomatis', $toko->jadwal_otomatis);
        $hariTerpilih = old('hari_buka', $toko->jadwal_otomatis ? $toko->hariBukaDaftar() : [1, 2, 3, 4, 5, 6, 7]);
        $hariTerpilih = array_map('intval', (array) $hariTerpilih);
    @endphp

    <form method="POST" action="{{ route('penjual.ketersediaan.update') }}" class="card flex flex-col gap-5" novalidate>
        @csrf
        @method('PUT')

        <div>
            <input type="hidden" name="libur" value="0">
            <label class="flex items-start gap-3 text-sm font-bold">
                <input type="checkbox" name="libur" value="1" class="mt-0.5 h-5 w-5 accent-olive" @checked(old('libur', $toko->libur))>
                <span>
                    Toko sedang libur
                    <span class="block text-xs font-normal text-ink/60">Saat dicentang, semua produkmu tidak bisa dipesan sampai kamu mematikannya lagi. Produk tetap tampil di katalog.</span>
                </span>
            </label>
        </div>

        <div class="border-t border-olive/10 pt-4">
            <input type="hidden" name="jadwal_otomatis" value="0">
            <label class="flex items-start gap-3 text-sm font-bold">
                <input type="checkbox" name="jadwal_otomatis" value="1" class="mt-0.5 h-5 w-5 accent-olive" @checked($otomatis)>
                <span>
                    Pakai jadwal buka otomatis
                    <span class="block text-xs font-normal text-ink/60">Di luar jadwal, toko otomatis tutup untuk produk biasa. Produk pre-order tetap bisa dipesan. Waktu mengikuti WIB.</span>
                </span>
            </label>

            <div class="mt-4 grid grid-cols-2 gap-3">
                <x-field name="jam_buka" label="Jam buka" type="time" :wajib="false" :nilai="$toko->jam_buka ?? '08:00'" />
                <x-field name="jam_tutup" label="Jam tutup" type="time" :wajib="false" :nilai="$toko->jam_tutup ?? '17:00'" />
            </div>

            <fieldset class="mt-4">
                <legend class="mb-2 text-sm font-bold">Hari buka</legend>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    @foreach (\App\Models\Shop::NAMA_HARI as $angka => $nama)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="hari_buka[]" value="{{ $angka }}" class="h-5 w-5 accent-olive" @checked(in_array($angka, $hariTerpilih, true))>
                            {{ $nama }}
                        </label>
                    @endforeach
                </div>
                @error('hari_buka')
                    <p class="mt-1.5 text-sm font-semibold text-danger">{{ $message }}</p>
                @enderror
            </fieldset>
        </div>

        <button type="submit" class="btn-primary">Simpan Pengaturan</button>
        <a href="{{ route('dashboard') }}" class="btn-secondary">Kembali</a>
    </form>
@endsection
