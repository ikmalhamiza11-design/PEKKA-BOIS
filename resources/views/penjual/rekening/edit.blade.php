@extends('layouts.auth')

@section('judul', 'Rekening Pembayaran')

@section('appbar')
    <x-app-bar judul="Rekening Pembayaran" :kembali="route('dashboard')" />
@endsection

@section('isi')
    @if (session('pesan'))
        <x-alert tipe="sukses">{{ session('pesan') }}</x-alert>
    @endif

    @unless ($toko->punyaRekening())
        <x-alert judul="Rekening belum diisi">
            Pembeli baru bisa mengirim bukti transfer setelah rekening pembayaran tokomu terisi.
        </x-alert>
    @endunless

    <form method="POST" action="{{ route('penjual.rekening.update') }}" class="card flex flex-col gap-4" novalidate>
        @csrf
        @method('PUT')

        <x-field name="nama_bank" label="Nama bank atau e-wallet" placeholder="Contoh: BRI" maxlength="50" :nilai="$toko->nama_bank" />

        <x-field name="nomor_rekening" label="Nomor rekening" inputmode="numeric" placeholder="1234567890" maxlength="30" hint="Tulis angka saja." :nilai="$toko->nomor_rekening" />

        <x-field name="atas_nama" label="Atas nama" placeholder="Nama pemilik rekening" maxlength="100" :nilai="$toko->atas_nama" />

        <button type="submit" class="btn-primary">Simpan Rekening</button>
        <a href="{{ route('dashboard') }}" class="btn-secondary">Kembali</a>
    </form>
@endsection
