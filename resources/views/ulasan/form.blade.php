@extends('layouts.auth')

@section('judul', $ulasan ? 'Ubah Ulasan' : 'Beri Ulasan')

@section('appbar')
    <x-app-bar :judul="$ulasan ? 'Ubah Ulasan' : 'Beri Ulasan'" :kembali="route('pesanan.show', $pesanan->id)" />
@endsection

@section('isi')
    @php
        $pilihan = [1 => 'Buruk', 2 => 'Kurang', 3 => 'Cukup', 4 => 'Bagus', 5 => 'Sangat bagus'];
        $nilaiAwal = (int) old('rating', $ulasan?->rating);
    @endphp

    @if ($ulasan?->disembunyikan())
        <x-alert tipe="galat" judul="Ulasanmu disembunyikan admin">Ulasan ini tidak tampil di halaman produk. Alasan: {{ $ulasan->alasan_moderasi }}</x-alert>
    @endif

    <section class="card !p-4">
        <p class="text-xs font-bold uppercase tracking-wide text-olive/70">Produk yang kamu beli</p>
        <p class="mt-1 font-extrabold">{{ $item->nama }}</p>
        <p class="text-sm text-ink/70">Pesanan {{ $pesanan->kode }}</p>
    </section>

    <form method="POST" action="{{ route('ulasan.simpan', [$pesanan->id, $item->id]) }}" class="card flex flex-col gap-4" novalidate>
        @csrf

        <fieldset>
            <legend class="mb-2 text-sm font-bold">Penilaian<span class="text-danger" aria-hidden="true"> *</span></legend>
            <div class="grid grid-cols-5 gap-2">
                @foreach ($pilihan as $nilai => $label)
                    <label class="cursor-pointer text-center">
                        <input type="radio" name="rating" value="{{ $nilai }}" class="peer sr-only" @checked($nilaiAwal === $nilai)>
                        <span class="flex min-h-16 flex-col items-center justify-center rounded-xl border border-olive/25 bg-paper px-1 py-2 text-olive peer-checked:border-olive peer-checked:bg-olive peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-olive">
                            <span class="text-lg font-extrabold leading-none">{{ $nilai }}&#9733;</span>
                            <span class="mt-1 text-[0.65rem] font-bold leading-tight">{{ $label }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('rating')
                <p class="mt-1.5 text-sm font-semibold text-danger">{{ $message }}</p>
            @enderror
        </fieldset>

        <div>
            <label for="komentar" class="mb-1.5 block text-sm font-bold">Ceritakan pengalamanmu</label>
            <textarea id="komentar" name="komentar" rows="5" maxlength="1000" placeholder="Bagaimana rasa, kualitas, kemasan, atau pengirimannya? (opsional)" @class(['input', 'input-galat' => $errors->has('komentar')])>{{ old('komentar', $ulasan?->komentar) }}</textarea>
            @error('komentar')
                <p class="mt-1.5 text-sm font-semibold text-danger">{{ $message }}</p>
            @enderror
            <p class="mt-1.5 text-xs text-ink/60">Namamu ditampilkan sebagian saja, misalnya "Siti R.".</p>
        </div>

        <button type="submit" class="btn-primary">{{ $ulasan ? 'Simpan Perubahan' : 'Kirim Ulasan' }}</button>
        <a href="{{ route('pesanan.show', $pesanan->id) }}" class="btn-secondary">Batal</a>
    </form>
@endsection
