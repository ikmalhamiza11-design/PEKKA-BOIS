@extends('layouts.auth')

@section('judul', 'Daftar Toko PEKKA')

@section('appbar')
    <x-app-bar judul="Daftar Toko PEKKA" :kembali="route('daftar')" />
@endsection

@section('isi')
    <div>
        <p class="mb-3 inline-flex rounded-full bg-lime/25 px-3 py-1 text-xs font-bold text-olive">Langkah 2 dari 2</p>
        <h1 class="text-3xl font-extrabold leading-tight">Buka Toko Digital PEKKA</h1>
        <p class="mt-2 text-ink/70">Khusus anggota kelompok PEKKA di Kabupaten Bogor.</p>
    </div>

    <x-alert>
        <span class="block font-extrabold">Tokomu akan diperiksa admin kelompok PEKKA sebelum tampil di katalog.</span>
    </x-alert>

    <form method="POST" action="{{ route('daftar.penjual.simpan') }}" class="card flex flex-col gap-4" novalidate>
        @csrf
        <x-field name="name" label="Nama lengkap pemilik" placeholder="Contoh: Siti Rohmah" autocomplete="name" />
        <x-field name="shop_name" label="Nama usaha" placeholder="Contoh: Keripik Singkong Barokah" autocomplete="organization" />

        @php $galatKelompok = $errors->first('kelompok_id'); @endphp
        <div>
            <label for="kelompok_id" class="mb-1.5 block text-sm font-bold">Kelompok PEKKA<span class="text-danger" aria-hidden="true"> *</span></label>
            <select id="kelompok_id" name="kelompok_id" @class(['input', 'input-galat' => $galatKelompok !== '']) aria-invalid="{{ $galatKelompok !== '' ? 'true' : 'false' }}">
                <option value="">Pilih kelompok...</option>
                @foreach ($kelompoks as $kelompok)
                    <option value="{{ $kelompok->id }}" @selected(old('kelompok_id') == $kelompok->id)>{{ $kelompok->label() }}</option>
                @endforeach
            </select>
            @if ($galatKelompok !== '')
                <p class="mt-1.5 text-sm font-semibold text-danger">{{ $galatKelompok }}</p>
            @else
                <p class="mt-1.5 text-xs text-ink/60">Kelompokmu tidak ada di daftar? Tanyakan ke pengurus kelompok.</p>
            @endif
        </div>

        <x-field name="email" label="Alamat email" type="email" placeholder="nama@email.com" autocomplete="email" inputmode="email" />
        <x-field name="phone" label="Nomor WhatsApp aktif" type="tel" awalan="+62" placeholder="812-3456-7890" autocomplete="tel-national" inputmode="tel" hint="Pesanan dari pembeli akan dikomunikasikan langsung ke nomor ini." />
        <x-password-field name="password" label="Kata sandi" placeholder="Minimal 8 karakter" autocomplete="new-password" :meter="true" />
        <x-password-field name="password_confirmation" label="Konfirmasi kata sandi" placeholder="Ulangi kata sandi" autocomplete="new-password" />

        <div>
            <label class="flex cursor-pointer items-start gap-3 text-sm">
                <input type="checkbox" name="terms" value="1" class="mt-0.5 h-5 w-5 shrink-0 accent-olive" @checked(old('terms'))>
                <span>Saya menyetujui Syarat &amp; Ketentuan serta kebenaran data usaha ini.</span>
            </label>
            @error('terms')
                <p class="mt-1.5 text-sm font-semibold text-danger">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-primary">Daftar Sekarang</button>
        <p class="text-center text-sm text-ink/70">Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-olive underline">Masuk</a></p>
    </form>
@endsection
