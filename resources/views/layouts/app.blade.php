<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#fcf3dd">
    <title>@yield('judul', 'Beranda') - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-cream font-sans text-ink antialiased">
    @php
        $menu = [
            ['beranda', 'Beranda'],
            ['katalog', 'Katalog'],
            ['peta', 'Peta Usaha'],
            ['kegiatan', 'Kegiatan'],
            ['panduan', 'Panduan'],
        ];
        $cocok = fn (string $rute) => request()->routeIs(...($rute === 'katalog' ? ['katalog', 'produk.*'] : [$rute]));
        $jumlahKeranjang = auth()->check() ? auth()->user()->keranjang()->count() : 0;
    @endphp

    <header class="sticky top-0 z-30 border-b border-olive/10 bg-cream/90 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center gap-3 px-4">
            <a href="{{ route('beranda') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-olive text-lime" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                </span>
                <span class="leading-tight">
                    <span class="block text-base font-extrabold text-olive">PEKKA BOIS</span>
                    <span class="block text-[0.6rem] font-semibold tracking-wide text-olive/70">BOGOR ISTIMEWA</span>
                </span>
            </a>

            <nav class="mx-auto hidden items-center gap-1 rounded-full bg-lime/20 p-1 text-sm font-bold lg:flex" aria-label="Menu utama">
                @foreach ($menu as [$rute, $nama])
                    <a href="{{ route($rute) }}" @class(['rounded-full px-4 py-2', 'bg-olive text-white' => $cocok($rute), 'text-olive hover:bg-lime/30' => ! $cocok($rute)]) @if ($cocok($rute)) aria-current="page" @endif>{{ $nama }}</a>
                @endforeach
            </nav>

            <div class="ml-auto flex items-center gap-2 lg:ml-0">
                <a href="{{ route('sahabat-pekka') }}" class="hidden items-center gap-2 rounded-full bg-lime px-4 py-2 text-sm font-extrabold text-ink hover:brightness-95 lg:flex">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="m12 6 1 2.5 2.5 1-2.5 1L12 13l-1-2.5-2.5-1 2.5-1z"/></svg>
                    Tanya Sahabat PEKKA
                </a>
                <a href="{{ route('keranjang') }}" class="relative flex h-11 w-11 items-center justify-center rounded-full text-olive hover:bg-lime/30" aria-label="Keranjang belanja{{ $jumlahKeranjang ? ' ('.$jumlahKeranjang.' produk)' : '' }}">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
                    @if ($jumlahKeranjang > 0)
                        <span class="absolute -right-0.5 -top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-danger px-1 text-[0.65rem] font-extrabold text-white" aria-hidden="true">{{ $jumlahKeranjang }}</span>
                    @endif
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-full bg-olive px-4 py-2 text-sm font-extrabold text-white hover:bg-olive-dark">{{ \Illuminate\Support\Str::limit(auth()->user()->name, 12, '') }}</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-full bg-olive px-4 py-2 text-sm font-extrabold text-white hover:bg-olive-dark">Masuk</a>
                @endauth
            </div>
        </div>
    </header>

    <div class="pb-20 lg:pb-0">
        @if (session('pesan') || session('galat') || $errors->any())
            <div class="mx-auto flex max-w-6xl flex-col gap-2 px-4 pt-4">
                @if (session('pesan'))
                    <x-alert tipe="sukses">{{ session('pesan') }}</x-alert>
                @endif
                @if (session('galat'))
                    <x-alert tipe="galat">{{ session('galat') }}</x-alert>
                @endif
                @if ($errors->any())
                    <x-alert tipe="galat">{{ $errors->first() }}</x-alert>
                @endif
            </div>
        @endif

        <main>
            @yield('isi')
        </main>

        <footer class="mt-10 border-t border-olive/10 bg-paper">
            <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 lg:grid-cols-[1.4fr_1fr_1fr]">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-olive text-lime" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        </span>
                        <span class="font-extrabold text-olive">{{ config('app.name') }}</span>
                    </div>
                    <p class="mt-3 max-w-sm text-sm text-ink/70">Marketplace usaha anggota Kelompok Perempuan Kepala Keluarga (PEKKA) di Kabupaten Bogor.</p>
                </div>
                <div>
                    <p class="mb-3 font-extrabold text-olive">Jelajahi</p>
                    <ul class="space-y-2 text-sm text-ink/70">
                        <li><a href="{{ route('katalog') }}" class="hover:text-olive">Katalog</a></li>
                        <li><a href="{{ route('peta') }}" class="hover:text-olive">Peta Usaha</a></li>
                        <li><a href="{{ route('kegiatan') }}" class="hover:text-olive">Kegiatan</a></li>
                        <li><a href="{{ route('panduan') }}" class="hover:text-olive">Panduan</a></li>
                    </ul>
                </div>
                <div>
                    <p class="mb-3 font-extrabold text-olive">Layanan</p>
                    <ul class="space-y-2 text-sm text-ink/70">
                        <li><a href="{{ route('sahabat-pekka') }}" class="hover:text-olive">Sahabat PEKKA</a></li>
                        <li><a href="{{ route('keranjang') }}" class="hover:text-olive">Keranjang</a></li>
                        <li><a href="{{ route('pesanan') }}" class="hover:text-olive">Pesanan Saya</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-olive/10 px-4 py-4 text-center text-xs text-ink/60">&copy; {{ date('Y') }} {{ config('app.name') }}</div>
        </footer>
    </div>

    <nav class="fixed inset-x-0 bottom-0 z-30 flex h-16 items-end border-t border-olive/15 bg-paper lg:hidden" aria-label="Navigasi bawah">
        <x-tab-bawah :href="route('beranda')" label="Beranda" :aktif="request()->routeIs('beranda')">
            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>
        </x-tab-bawah>
        <x-tab-bawah :href="route('katalog')" label="Katalog" :aktif="$cocok('katalog')">
            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
        </x-tab-bawah>
        <x-tab-bawah :href="route('sahabat-pekka')" label="Sahabat PEKKA" :aktif="request()->routeIs('sahabat-pekka')" :tengah="true">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="m12 6 1 2.5 2.5 1-2.5 1L12 13l-1-2.5-2.5-1 2.5-1z"/>
        </x-tab-bawah>
        <x-tab-bawah :href="route('pesanan')" label="Pesanan" :aktif="request()->routeIs('pesanan', 'pesanan.*')">
            <path d="m7.5 4.3 9 5.2"/><path d="M21 8v8l-9 5-9-5V8l9-5z"/><path d="M3.3 7 12 12l8.7-5"/><path d="M12 22V12"/>
        </x-tab-bawah>
        <x-tab-bawah :href="auth()->check() ? route('dashboard') : route('login')" label="Akun" :aktif="request()->routeIs('dashboard')">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
        </x-tab-bawah>
    </nav>
</body>
</html>
