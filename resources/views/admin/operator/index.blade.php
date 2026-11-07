@extends('layouts.admin')

@section('judul', 'Operator')

@section('isi')
    <div>
        <h1 class="text-2xl font-extrabold">Akun Operator</h1>
        <p class="mt-1 text-sm text-ink/70">Semua operator memiliki hak yang sama: memverifikasi toko, memoderasi ulasan, menangani laporan tidak bisa masuk, dan mengelola kelompok, kegiatan, serta panduan.</p>
    </div>

    <form method="POST" action="{{ route('admin.operator.store') }}" class="card flex max-w-xl flex-col gap-3" novalidate autocomplete="off">
        @csrf
        <h2 class="font-extrabold">Buat akun operator baru</h2>
        <x-field name="name" label="Nama lengkap" maxlength="100" />
        <x-field name="email" label="Email" type="email" maxlength="255" />
        <x-field name="phone" label="Nomor HP/WhatsApp" type="tel" :wajib="false" hint="Boleh dikosongkan. Contoh: 0812-3456-7890." />
        <div>
            <label for="password" class="mb-1.5 block text-sm font-bold">Kata sandi<span class="text-danger" aria-hidden="true"> *</span></label>
            <input id="password" name="password" type="password" autocomplete="new-password" class="input @error('password') input-galat @enderror">
            @error('password')
                <p class="mt-1.5 text-sm font-semibold text-danger">{{ $message }}</p>
            @else
                <p class="mt-1.5 text-xs text-ink/60">Minimal 8 karakter dengan huruf besar, huruf kecil, dan angka.</p>
            @enderror
        </div>
        <div>
            <label for="password_confirmation" class="mb-1.5 block text-sm font-bold">Ulangi kata sandi<span class="text-danger" aria-hidden="true"> *</span></label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="input">
        </div>
        <button type="submit" class="btn-primary">Buat Akun Operator</button>
    </form>

    <section class="flex flex-col gap-3">
        <h2 class="font-extrabold">Operator yang ada ({{ $akun->count() }})</h2>

        @foreach ($akun as $item)
            <article class="card !p-4 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="truncate font-extrabold">{{ $item->name }}@if ($item->id === auth()->id()) <span class="text-xs font-bold text-olive">(Anda)</span>@endif</p>
                    <p class="truncate text-sm text-ink/70">{{ $item->email }}</p>
                </div>
                @if ($item->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.operator.destroy', $item->id) }}" onsubmit="return confirm('Hapus akun {{ addslashes($item->name) }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-secondary min-h-11 w-auto !border-danger !px-4 !py-2 text-sm !text-danger hover:!bg-danger/10">Hapus</button>
                    </form>
                @endif
            </article>
        @endforeach
    </section>
@endsection
