@extends('layouts.auth')

@section('judul', 'Profil Saya')

@section('appbar')
    <x-app-bar judul="Profil Saya" :kembali="route('dashboard')" />
@endsection

@section('isi')
    @if (session('pesan'))
        <x-alert tipe="sukses">{{ session('pesan') }}</x-alert>
    @endif

    <section class="card flex items-center gap-4">
        @if ($user->fotoUrl())
            <img src="{{ $user->fotoUrl() }}" alt="Foto profil {{ $user->name }}" class="h-16 w-16 shrink-0 rounded-full object-cover">
        @else
            <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-olive text-2xl font-extrabold text-lime" aria-hidden="true">{{ $inisial }}</span>
        @endif
        <div class="min-w-0">
            <p class="truncate text-lg font-extrabold">{{ $user->name }}</p>
            <p class="break-all text-sm text-ink/70">{{ $user->email }}</p>
            <p class="mt-1 inline-flex rounded-full bg-lime/30 px-3 py-0.5 text-xs font-bold text-olive">{{ $user->labelPeran() }}</p>
        </div>
    </section>

    @if ($shop)
        <section class="card">
            <p class="text-xs font-bold uppercase tracking-wide text-olive/70">Toko saya</p>
            <p class="font-extrabold">{{ $shop->nama }}</p>
            <p class="text-sm text-ink/70">Kelompok {{ $shop->kelompok->nama }}</p>
            <p class="mt-2 inline-flex rounded-full bg-lime/30 px-3 py-1 text-xs font-bold text-olive">Status toko: {{ ucfirst($shop->status) }}</p>
        </section>
    @endif

    <form method="POST" action="{{ route('profil.foto') }}" enctype="multipart/form-data" class="card flex flex-col gap-3" novalidate>
        @csrf
        @method('PUT')
        <h2 class="text-lg font-extrabold">Foto profil</h2>
        <div>
            <label for="foto" class="mb-1.5 block text-sm font-bold">Pilih foto</label>
            <input id="foto" type="file" name="foto" accept="image/jpeg,image/png,image/webp" @class(['input', 'input-galat' => $errors->has('foto')])>
            @error('foto')
                <p class="mt-1.5 text-sm font-semibold text-danger">{{ $message }}</p>
            @else
                <p class="mt-1.5 text-xs text-ink/60">JPG, PNG, atau WebP, maksimal 3 MB.</p>
            @enderror
        </div>
        <button type="submit" class="btn-primary">Simpan Foto</button>
    </form>

    @if ($user->foto)
        <form method="POST" action="{{ route('profil.foto.hapus') }}" data-konfirmasi="Hapus foto profil?" data-konfirmasi-judul="Hapus data ini?" data-konfirmasi-tombol="Ya, Hapus">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-secondary">Hapus Foto Profil</button>
        </form>
    @endif

    <form method="POST" action="{{ route('profil.update') }}" class="card flex flex-col gap-4" novalidate>
        @csrf
        @method('PUT')
        <h2 class="text-lg font-extrabold">Data diri</h2>
        <x-field name="name" label="Nama lengkap" autocomplete="name" maxlength="100" :nilai="$user->name" />
        <x-field name="phone" label="Nomor HP/WhatsApp" type="tel" inputmode="tel" autocomplete="tel" placeholder="0812-3456-7890" :nilai="$phoneLokal" />
        <button type="submit" class="btn-primary">Simpan Data Diri</button>
    </form>

    <form method="POST" action="{{ route('profil.email') }}" class="card flex flex-col gap-4" novalidate>
        @csrf
        @method('PUT')
        <h2 class="text-lg font-extrabold">Ganti email</h2>
        <x-alert>Setelah email diganti, kamu perlu memverifikasi email yang baru sebelum bisa memakai akun lagi.</x-alert>
        <x-field name="email" label="Email baru" type="email" autocomplete="email" placeholder="nama@email.com" />
        <x-password-field name="sandi_email" label="Kata sandi saat ini" autocomplete="current-password" />
        <button type="submit" class="btn-secondary">Ganti Email</button>
    </form>

    <form method="POST" action="{{ route('profil.sandi') }}" class="card flex flex-col gap-4" novalidate>
        @csrf
        @method('PUT')
        <h2 class="text-lg font-extrabold">Ganti kata sandi</h2>
        <x-password-field name="kata_sandi_lama" label="Kata sandi saat ini" autocomplete="current-password" />
        <x-password-field name="password" label="Kata sandi baru" autocomplete="new-password" :meter="true" />
        <x-password-field name="password_confirmation" label="Ulangi kata sandi baru" autocomplete="new-password" />
        <button type="submit" class="btn-secondary">Ganti Kata Sandi</button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-auto">
        @csrf
        <button type="submit" class="btn-secondary">Keluar</button>
    </form>
@endsection
