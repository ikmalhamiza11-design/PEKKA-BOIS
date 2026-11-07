@extends('layouts.auth')

@section('judul', 'Verifikasi Email')

@section('isi')
    <section class="card flex flex-col items-center gap-4 text-center">
        <span class="flex h-20 w-20 items-center justify-center rounded-full bg-lime/30 text-olive" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
        </span>
        <h1 class="text-3xl font-extrabold">Cek email kamu</h1>
        <p class="text-ink/70">Kami mengirim tautan verifikasi ke email kamu untuk memastikan keamanan akun.</p>
        <p class="max-w-full break-all rounded-full bg-cream px-4 py-1.5 text-sm font-semibold">{{ auth()->user()->email }}</p>

        @if (session('status') === 'tautan-terkirim')
            <x-alert tipe="sukses">Tautan verifikasi baru sudah dikirim.</x-alert>
        @endif

        @if (session('status') === 'email-gagal')
            <x-alert tipe="galat" judul="Email belum bisa dikirim">Ada gangguan saat mengirim email. Tunggu sebentar lalu tekan "Kirim ulang email", atau lapor ke operator.</x-alert>
        @endif

        @if (session('status') === 'email-diganti')
            <x-alert tipe="sukses">Email diganti. Tautan verifikasi dikirim ke alamat yang baru.</x-alert>
        @endif

        <div class="w-full rounded-xl bg-cream/70 p-4 text-left text-sm">
            <p class="mb-3 font-extrabold">Langkah cepat verifikasi</p>
            <ol class="space-y-3">
                <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-lime text-xs font-extrabold">1</span>Buka kotak masuk pada aplikasi email kamu.</li>
                <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-lime text-xs font-extrabold">2</span>Klik tombol bertuliskan "Verifikasi Akun PEKKA".</li>
                <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-lime text-xs font-extrabold">3</span>Kembali ke aplikasi untuk melanjutkan.</li>
            </ol>
            <p class="mt-4 text-xs text-ink/60">Belum menerima email? Periksa folder Spam atau tab Promosi kamu.</p>
        </div>

        <form method="POST" action="{{ route('verification.send') }}" class="w-full">
            @csrf
            <button type="submit" class="btn-primary">Kirim ulang email</button>
        </form>

        <details class="w-full text-sm" @if ($errors->has('email')) open @endif>
            <summary class="cursor-pointer py-2 font-bold text-olive">Ganti alamat email</summary>
            <form method="POST" action="{{ route('verification.change') }}" class="mt-2 flex flex-col gap-3 text-left" novalidate>
                @csrf
                <x-field name="email" label="Email baru" type="email" placeholder="nama@email.com" autocomplete="email" inputmode="email" />
                <button type="submit" class="btn-secondary">Simpan dan kirim ulang</button>
            </form>
        </details>

        <a href="{{ route('bantuan.masuk') }}" class="text-sm font-bold text-olive underline">Email tidak bisa dibuka? Lapor ke operator</a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm font-semibold text-ink/60 underline">Keluar</button>
        </form>
    </section>
@endsection
