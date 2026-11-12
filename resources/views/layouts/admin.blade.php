<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#412a4d">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('judul', 'Panel Admin') - Admin {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-cream font-sans text-ink antialiased">
    @php
        $admin = auth()->user();
        $menungguVerifikasi = $admin->cakupanToko()->where('status', \App\Models\Shop::MENUNGGU)->count();
        $menu = [
            ['admin.dasbor', 'Dasbor', 'admin.dasbor', 0],
            ['admin.toko.index', 'Verifikasi Toko', 'admin.toko.*', $menungguVerifikasi],
            ['admin.ulasan.index', 'Ulasan', 'admin.ulasan.*', 0],
        ];

        if ($admin->adalahAdminDinas()) {
            $menu[] = ['admin.kelompok.index', 'Kelompok', 'admin.kelompok.*', 0];
            $menu[] = ['admin.laporan.index', 'Laporan Masuk', 'admin.laporan.*', \App\Models\LaporanAkun::baru()->count()];
            $menu[] = ['admin.operator.index', 'Operator', 'admin.operator.*', 0];
            $menu[] = ['admin.kegiatan.index', 'Kegiatan', 'admin.kegiatan.*', 0];
            $menu[] = ['admin.panduan.index', 'Panduan', 'admin.panduan.*', 0];
        }
    @endphp

    <header class="sticky top-0 z-30 border-b border-olive/10 bg-olive text-white">
        <div class="mx-auto flex max-w-5xl items-center gap-3 px-4 py-3">
            <a href="{{ route('admin.dasbor') }}" class="flex items-center gap-2 leading-tight">
                <img src="{{ asset('images/logo-pekka-bois.png') }}" alt="" width="256" height="256" class="h-9 w-9 shrink-0 rounded-full" decoding="async">
                <span>
                <span class="block text-base font-extrabold text-lime">PEKKA BOIS</span>
                <span class="block text-[0.65rem] font-semibold tracking-wide text-white/80">PANEL ADMIN</span>
                </span>
            </a>
            <div class="ml-auto flex items-center gap-3 text-sm">
                <span class="hidden text-right leading-tight sm:block">
                    <span class="block font-bold">{{ $admin->name }}</span>
                    <span class="block text-xs text-white/70">{{ $admin->labelPeran() }}</span>
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="min-h-11 cursor-pointer rounded-full border border-white/40 px-4 py-2 text-sm font-bold hover:bg-white/10">Keluar</button>
                </form>
            </div>
        </div>
        <nav class="gulir-x border-t border-white/10" aria-label="Menu admin">
            <ul class="mx-auto flex max-w-5xl gap-1 px-4 py-2 text-sm font-bold">
                @foreach ($menu as [$rute, $nama, $pola, $lencana])
                    <li class="shrink-0">
                        <a href="{{ route($rute) }}" @class(['flex min-h-11 items-center gap-2 rounded-full px-4', 'bg-lime text-ink' => request()->routeIs($pola), 'text-white hover:bg-white/10' => ! request()->routeIs($pola)]) @if (request()->routeIs($pola)) aria-current="page" @endif>
                            {{ $nama }}
                            @if ($lencana > 0)
                                <span class="rounded-full bg-danger px-2 py-0.5 text-xs font-extrabold text-white" aria-label="{{ $lencana }} menunggu">{{ $lencana }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </header>

    <div class="mx-auto flex max-w-5xl flex-col gap-5 px-4 py-6">
        @if (session('pesan'))
            <x-alert tipe="sukses">{{ session('pesan') }}</x-alert>
        @endif
        @if (session('galat'))
            <x-alert tipe="galat">{{ session('galat') }}</x-alert>
        @endif
        @if ($errors->any())
            <x-alert tipe="galat">{{ $errors->first() }}</x-alert>
        @endif

        <main class="flex flex-col gap-5">
            @yield('isi')
        </main>
    </div>
</body>
</html>
