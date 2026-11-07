@extends('layouts.auth')

@section('judul', 'Bantuan Tidak Bisa Masuk')

@section('appbar')
    <x-app-bar judul="Bantuan Tidak Bisa Masuk" :kembali="auth()->check() ? route('verification.notice') : route('login')" />
@endsection

@section('isi')
    @if (session('status') === 'laporan-terkirim')
        <section class="card flex flex-col items-center gap-3 text-center">
            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-success text-white" aria-hidden="true">&#10003;</span>
            <h1 class="text-2xl font-extrabold">Laporan terkirim</h1>
            <p class="text-ink/70">Operator PEKKA akan menghubungi Ibu/Bapak lewat WhatsApp ke nomor yang tadi ditulis. Mohon ditunggu ya.</p>
        </section>
    @else
        <div>
            <h1 class="text-3xl font-extrabold leading-tight">Tidak bisa masuk?</h1>
            <p class="mt-2 text-ink/70">Kalau Ibu/Bapak lupa kata sandi dan email tidak bisa dibuka, isi data di bawah. Operator akan menghubungi lewat WhatsApp dan membantu membuatkan kata sandi sementara.</p>
        </div>

        <form method="POST" action="{{ route('bantuan.masuk.simpan') }}" class="card flex flex-col gap-4" novalidate>
            @csrf
            <x-field name="nama" label="Nama lengkap" maxlength="100" autocomplete="name" hint="Tulis sesuai nama saat mendaftar." />
            <x-field name="phone" label="Nomor WhatsApp" type="tel" inputmode="tel" autocomplete="tel" placeholder="0812-3456-7890" hint="Operator akan menghubungi nomor ini." />
            <x-field name="email" label="Email yang dipakai saat mendaftar" type="email" inputmode="email" :wajib="false" hint="Boleh dikosongkan kalau lupa." />

            <div>
                <label for="keterangan" class="mb-1.5 block text-sm font-bold">Keterangan tambahan</label>
                <textarea id="keterangan" name="keterangan" rows="3" maxlength="500" class="input @error('keterangan') input-galat @enderror" placeholder="Contoh: nama toko, nama kelompok PEKKA">{{ old('keterangan') }}</textarea>
                @error('keterangan')
                    <p class="mt-1.5 text-sm font-semibold text-danger">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-primary">Kirim Laporan</button>
        </form>
    @endif

    @guest
        <a href="{{ route('login') }}" class="text-center text-sm font-bold text-olive underline">Kembali ke halaman masuk</a>
    @endguest
@endsection
