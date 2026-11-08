@extends('layouts.auth')

@section('judul', 'Daftar Pembeli')

@section('appbar')
    <x-app-bar judul="Daftar Pembeli" :kembali="route('daftar')" />
@endsection

@section('isi')
    <x-logo-gunadarma />

    <div>
        <p class="mb-3 inline-flex rounded-full bg-lime/25 px-3 py-1 text-xs font-bold text-olive">Langkah 2 dari 2</p>
        <h1 class="text-3xl font-extrabold leading-tight">Lengkapi data dirimu</h1>
        <p class="mt-2 text-ink/70">Belanja produk buatan perempuan kepala keluarga di Kabupaten Bogor langsung dari penjualnya.</p>
    </div>

    <form method="POST" action="{{ route('daftar.pembeli.simpan') }}" class="card flex flex-col gap-4" novalidate>
        @csrf
        <x-field name="name" label="Nama lengkap" placeholder="Contoh: Siti Aminah" autocomplete="name" hint="Nama akan tertera pada faktur dan tiket pesanan." />
        <x-field name="email" label="Email" type="email" placeholder="contoh@email.com" autocomplete="email" inputmode="email" />
        <x-field name="phone" label="Nomor HP/WhatsApp" type="tel" awalan="+62" placeholder="812-3456-7890" autocomplete="tel-national" inputmode="tel" hint="Untuk konfirmasi pesanan dan koordinasi pengiriman." />
        <x-password-field name="password" label="Kata sandi" placeholder="Buat kata sandi" autocomplete="new-password" :meter="true" />
        <x-password-field name="password_confirmation" label="Konfirmasi kata sandi" placeholder="Ulangi kata sandi" autocomplete="new-password" />

        <div>
            <label class="flex cursor-pointer items-start gap-3 text-sm">
                <input type="checkbox" name="terms" value="1" class="mt-0.5 h-5 w-5 shrink-0 accent-olive" @checked(old('terms'))>
                <span>Saya menyetujui Syarat &amp; Ketentuan serta Kebijakan Privasi {{ config('app.name') }}.</span>
            </label>
            @error('terms')
                <p class="mt-1.5 text-sm font-semibold text-danger">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-primary">Daftar Akun Pembeli</button>
        <p class="text-center text-sm text-ink/70">Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-olive underline">Masuk di sini</a></p>
    </form>
@endsection
