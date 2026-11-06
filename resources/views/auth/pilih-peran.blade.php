@extends('layouts.auth')

@section('judul', 'Daftar')

@section('isi')
    <div>
        <p class="mb-3 inline-flex rounded-full bg-lime/25 px-3 py-1 text-xs font-bold text-olive">Langkah 1 dari 2</p>
        <h1 class="text-3xl font-extrabold leading-tight">Daftar sebagai</h1>
        <p class="mt-2 text-ink/70">Pilih jenis akun yang sesuai dengan kebutuhan Anda di PEKKA Bogor Istimewa.</p>
    </div>

    <form method="GET" action="{{ route('daftar.lanjut') }}" class="flex flex-1 flex-col gap-4">
        <label class="block cursor-pointer">
            <input type="radio" name="peran" value="pembeli" class="peer sr-only" checked>
            <span class="flex gap-4 rounded-2xl border-2 border-olive/15 bg-paper p-4 transition peer-checked:border-olive peer-checked:bg-lime/20 peer-focus-visible:ring-2 peer-focus-visible:ring-olive">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-olive text-white" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
                </span>
                <span>
                    <span class="block text-lg font-extrabold">Pembeli</span>
                    <span class="block text-sm text-ink/70">Belanja produk buatan perempuan kepala keluarga di Kabupaten Bogor.</span>
                </span>
            </span>
        </label>

        <label class="block cursor-pointer">
            <input type="radio" name="peran" value="penjual" class="peer sr-only">
            <span class="flex gap-4 rounded-2xl border-2 border-olive/15 bg-paper p-4 transition peer-checked:border-olive peer-checked:bg-lime/20 peer-focus-visible:ring-2 peer-focus-visible:ring-olive">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-olive text-white" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 2-5h14l2 5"/><path d="M4 9v11h16V9"/><path d="M9 20v-6h6v6"/></svg>
                </span>
                <span>
                    <span class="block text-lg font-extrabold">Penjual</span>
                    <span class="block text-sm text-ink/70">Khusus anggota kelompok PEKKA. Buka toko digital gratis untuk usahamu.</span>
                </span>
            </span>
        </label>

        <div class="mt-auto flex flex-col gap-4 pt-4">
            <button type="submit" class="btn-primary">Lanjut</button>
            <p class="text-center text-sm text-ink/70">Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-olive underline">Masuk</a></p>
        </div>
    </form>
@endsection
