@extends('layouts.auth')

@section('judul', 'Beranda')

@section('isi')
    <div>
        <h1 class="text-3xl font-extrabold leading-tight">Halo, {{ $user->name }}</h1>
        <p class="mt-2 text-ink/70">Anda masuk sebagai {{ $user->labelPeran() }}.</p>
    </div>

    @if (session('pesan'))
        <x-alert tipe="sukses">{{ session('pesan') }}</x-alert>
    @endif

    @if (session('status') === 'email-terverifikasi')
        <x-alert tipe="sukses">Email berhasil diverifikasi.</x-alert>
    @endif

    @if ($shop)
        <section class="card">
            <p class="font-extrabold">{{ $shop->nama }}</p>
            <p class="text-sm text-ink/70">Kelompok {{ $shop->kelompok->label() }}</p>
            <p class="mt-2 inline-flex rounded-full bg-lime/30 px-3 py-1 text-xs font-bold text-olive">Status toko: {{ ucfirst($shop->status) }}</p>
        </section>

        @if ($shop->status === \App\Models\Shop::MENUNGGU)
            <x-alert judul="Toko menunggu verifikasi">Produkmu belum tampil di katalog sampai operator PEKKA memverifikasi tokomu.</x-alert>
        @elseif ($shop->status === \App\Models\Shop::DITOLAK)
            <x-alert tipe="galat" judul="Toko belum disetujui">
                @if ($shop->alasan_penolakan)
                    Alasan: {{ $shop->alasan_penolakan }}
                @else
                    Hubungi operator PEKKA untuk menanyakan alasannya.
                @endif
            </x-alert>
        @endif

        @unless ($shop->punyaRekening())
            <x-alert judul="Rekening pembayaran belum diisi">
                Pembeli belum bisa membayar pesanan ke tokomu sebelum rekening terisi.
                <a href="{{ route('penjual.rekening.edit') }}" class="font-extrabold underline">Isi rekening sekarang</a>
            </x-alert>
        @endunless

        @if ($shop->libur)
            <x-alert judul="Tokomu sedang libur">
                Pembeli belum bisa memesan.
                <a href="{{ route('penjual.ketersediaan.edit') }}" class="font-extrabold underline">Atur ketersediaan toko</a>
            </x-alert>
        @elseif (! $shop->sesuaiJadwal())
            <x-alert judul="Tokomu sedang di luar jam buka">
                Hanya produk pre-order yang bisa dipesan sekarang.
                <a href="{{ route('penjual.ketersediaan.edit') }}" class="font-extrabold underline">Atur ketersediaan toko</a>
            </x-alert>
        @endif

        @if ($ringkasan)
            <section class="grid grid-cols-3 gap-3 text-center">
                <div class="card !p-3">
                    <p class="text-2xl font-extrabold text-olive">{{ $ringkasan['total'] }}</p>
                    <p class="text-xs text-ink/70">Produk</p>
                </div>
                <div class="card !p-3">
                    <p class="text-2xl font-extrabold text-olive">{{ $ringkasan['aktif'] }}</p>
                    <p class="text-xs text-ink/70">Aktif</p>
                </div>
                <div class="card !p-3">
                    <p class="text-2xl font-extrabold text-danger">{{ $ringkasan['habis'] }}</p>
                    <p class="text-xs text-ink/70">Stok habis</p>
                </div>
            </section>
        @endif

        @if ($ringkasanUlasan && $ringkasanUlasan['jumlah'] > 0)
            <section class="card !p-3">
                <p class="text-xs text-ink/70">Penilaian toko</p>
                <x-bintang :nilai="$ringkasanUlasan['rata']" :jumlah="$ringkasanUlasan['jumlah']" />
            </section>
        @endif

        <div class="grid gap-3">
            <a href="{{ route('penjual.produk.index') }}" class="btn-primary">Kelola Produk</a>
            <a href="{{ route('penjual.produk.create') }}" class="btn-secondary">+ Tambah Produk</a>
            @php $perluTindakan = $perluVerifikasi + $perluDiproses; @endphp
            <a href="{{ route('penjual.pesanan.index') }}" class="btn-secondary">
                Pesanan Masuk
                @if ($perluTindakan > 0)
                    <span class="ml-2 rounded-full bg-danger px-2 py-0.5 text-xs font-extrabold text-white" aria-label="{{ $perluTindakan }} pesanan perlu tindakan">{{ $perluTindakan }}</span>
                @endif
            </a>
            <a href="{{ route('penjual.ulasan.index') }}" class="btn-secondary">
                Ulasan Pembeli
                @if ($ulasanBelumDibalas > 0)
                    <span class="ml-2 rounded-full bg-danger px-2 py-0.5 text-xs font-extrabold text-white" aria-label="{{ $ulasanBelumDibalas }} ulasan belum dibalas">{{ $ulasanBelumDibalas }}</span>
                @endif
            </a>
            <a href="{{ route('penjual.ketersediaan.edit') }}" class="btn-secondary">Ketersediaan Toko</a>
            <a href="{{ route('penjual.rekening.edit') }}" class="btn-secondary">Rekening Pembayaran</a>
        </div>
    @endif

    @unless ($shop)
        <div class="grid gap-3">
            <a href="{{ route('beranda') }}" class="btn-primary">Ke Beranda</a>
            <a href="{{ route('katalog') }}" class="btn-secondary">Lihat Katalog</a>
            <a href="{{ route('pesanan') }}" class="btn-secondary">Pesanan Saya</a>
            <a href="{{ route('keranjang') }}" class="btn-secondary">Keranjang</a>
        </div>
    @else
        <a href="{{ route('beranda') }}" class="btn-secondary">Ke Beranda</a>
    @endunless

    <a href="{{ route('profil') }}" class="btn-secondary mt-auto">Profil Saya</a>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn-secondary">Keluar</button>
    </form>
@endsection
