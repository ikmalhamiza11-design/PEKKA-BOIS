@extends('layouts.admin')

@section('judul', 'Admin Kelompok')

@section('isi')
    <div>
        <h1 class="text-2xl font-extrabold">Akun Admin Kelompok</h1>
        <p class="mt-1 text-sm text-ink/70">Admin kelompok hanya bisa memverifikasi toko dan memoderasi ulasan milik kelompoknya sendiri.</p>
    </div>

    @if ($kelompok->isEmpty())
        <x-alert judul="Belum ada kelompok">
            Tambahkan kelompok PEKKA dulu di menu <a href="{{ route('admin.kelompok.index') }}" class="font-extrabold underline">Kelompok</a> sebelum membuat akun admin kelompok.
        </x-alert>
    @else
        <form method="POST" action="{{ route('admin.admin-kelompok.store') }}" class="card flex max-w-xl flex-col gap-3" novalidate autocomplete="off">
            @csrf
            <h2 class="font-extrabold">Buat akun baru</h2>
            <x-field name="name" label="Nama lengkap" maxlength="100" />
            <x-field name="email" label="Email" type="email" maxlength="255" />
            <x-field name="phone" label="Nomor HP/WhatsApp" type="tel" :wajib="false" hint="Boleh dikosongkan. Contoh: 0812-3456-7890." />
            <div>
                <label for="kelompok_id" class="mb-1.5 block text-sm font-bold">Kelompok PEKKA<span class="text-danger" aria-hidden="true"> *</span></label>
                <select id="kelompok_id" name="kelompok_id" class="input @error('kelompok_id') input-galat @enderror">
                    <option value="">Pilih kelompok</option>
                    @foreach ($kelompok as $item)
                        <option value="{{ $item->id }}" @selected((int) old('kelompok_id') === $item->id)>{{ $item->label() }}</option>
                    @endforeach
                </select>
                @error('kelompok_id')
                    <p class="mt-1.5 text-sm font-semibold text-danger">{{ $message }}</p>
                @enderror
            </div>
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
            <button type="submit" class="btn-primary">Buat Akun</button>
        </form>
    @endif

    <section class="flex flex-col gap-3">
        <h2 class="font-extrabold">Akun yang ada ({{ $akun->count() }})</h2>

        @forelse ($akun as $item)
            <article class="card !p-4 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="truncate font-extrabold">{{ $item->name }}</p>
                    <p class="truncate text-sm text-ink/70">{{ $item->email }}</p>
                    <p class="truncate text-xs text-ink/60">{{ $item->kelompok?->label() ?? 'Belum terhubung ke kelompok' }}</p>
                </div>
                <form method="POST" action="{{ route('admin.admin-kelompok.destroy', $item->id) }}" onsubmit="return confirm('Hapus akun {{ addslashes($item->name) }}?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-secondary min-h-11 w-auto !border-danger !px-4 !py-2 text-sm !text-danger hover:!bg-danger/10">Hapus</button>
                </form>
            </article>
        @empty
            <section class="card text-center">
                <p class="font-extrabold">Belum ada admin kelompok</p>
            </section>
        @endforelse
    </section>
@endsection
