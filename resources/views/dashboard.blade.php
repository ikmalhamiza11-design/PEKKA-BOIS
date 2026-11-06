@extends('layouts.auth')

@section('judul', 'Beranda')

@section('isi')
    <div>
        <h1 class="text-3xl font-extrabold leading-tight">Halo, {{ $user->name }}</h1>
        <p class="mt-2 text-ink/70">Anda masuk sebagai {{ $user->labelPeran() }}.</p>
    </div>

    @if (session('status') === 'email-terverifikasi')
        <x-alert tipe="sukses">Email berhasil diverifikasi.</x-alert>
    @endif

    @if ($shop)
        <section class="card">
            <p class="font-extrabold">{{ $shop->nama }}</p>
            <p class="text-sm text-ink/70">Kelompok {{ $shop->kelompok->label() }}</p>
            <p class="mt-2 inline-flex rounded-full bg-lime/30 px-3 py-1 text-xs font-bold text-olive">Status toko: {{ ucfirst($shop->status) }}</p>
        </section>
    @endif

    <form method="POST" action="{{ route('logout') }}" class="mt-auto">
        @csrf
        <button type="submit" class="btn-secondary">Keluar</button>
    </form>
@endsection
