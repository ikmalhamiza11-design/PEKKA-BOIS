@extends('layouts.auth')

@php $ubah = $produk !== null; @endphp

@section('judul', $ubah ? 'Ubah Produk' : 'Tambah Produk')

@section('appbar')
    <x-app-bar :judul="$ubah ? 'Ubah Produk' : 'Tambah Produk'" :kembali="route('penjual.produk.index')" />
@endsection

@section('isi')
    <form method="POST" action="{{ $ubah ? route('penjual.produk.update', $produk->id) : route('penjual.produk.store') }}" enctype="multipart/form-data" class="card flex flex-col gap-4" novalidate>
        @csrf
        @if ($ubah)
            @method('PUT')
        @endif

        <x-field name="nama" label="Nama produk" placeholder="Contoh: Keripik Singkong Pedas" maxlength="150" :nilai="$produk?->nama" />

        @php $galatKategori = $errors->first('category_id'); @endphp
        <div>
            <label for="category_id" class="mb-1.5 block text-sm font-bold">Kategori<span class="text-danger" aria-hidden="true"> *</span></label>
            <select id="category_id" name="category_id" @class(['input', 'input-galat' => $galatKategori !== '']) aria-invalid="{{ $galatKategori !== '' ? 'true' : 'false' }}">
                <option value="">Pilih kategori...</option>
                @foreach ($kategori as $item)
                    <option value="{{ $item->id }}" @selected((string) old('category_id', $produk?->category_id) === (string) $item->id)>{{ $item->nama }}</option>
                @endforeach
            </select>
            @if ($galatKategori !== '')
                <p class="mt-1.5 text-sm font-semibold text-danger">{{ $galatKategori }}</p>
            @endif
        </div>

        <x-field name="harga" label="Harga (Rupiah)" awalan="Rp" inputmode="numeric" placeholder="15000" hint="Tulis angka saja, contoh: 15000." :nilai="$produk?->harga" />

        <x-field name="stok" label="Stok" type="number" inputmode="numeric" min="0" hint="Isi 0 jika stok sedang habis." :nilai="$produk?->stok ?? 0" />

        <div>
            <label for="deskripsi" class="mb-1.5 block text-sm font-bold">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" rows="5" maxlength="2000" placeholder="Ceritakan bahan, rasa, ukuran, atau keunggulan produkmu." @class(['input', 'input-galat' => $errors->has('deskripsi')])>{{ old('deskripsi', $produk?->deskripsi) }}</textarea>
            @error('deskripsi')
                <p class="mt-1.5 text-sm font-semibold text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="foto" class="mb-1.5 block text-sm font-bold">Foto produk</label>

            @if ($ubah && $produk->foto)
                <div class="mb-3 flex items-center gap-3 rounded-xl bg-cream/70 p-3">
                    <img src="{{ asset('storage/'.$produk->foto) }}" alt="Foto {{ $produk->nama }}" class="h-20 w-20 rounded-lg object-cover">
                    <div>
                        <p class="text-sm font-bold">Foto saat ini</p>
                        <input type="hidden" name="hapus_foto" value="0">
                        <label class="mt-1 flex items-center gap-2 text-sm">
                            <input type="checkbox" name="hapus_foto" value="1" class="h-5 w-5 accent-olive" @checked(old('hapus_foto'))>
                            Hapus foto ini
                        </label>
                    </div>
                </div>
            @endif

            <div data-foto-pratinjau class="mb-3 hidden rounded-xl bg-cream/70 p-3">
                <img src="" alt="Pratinjau foto baru" class="max-h-56 w-full rounded-lg object-contain">
                <p data-foto-info class="mt-2 text-xs text-ink/70"></p>
            </div>

            <input id="foto" type="file" name="foto" accept="image/jpeg,image/png,image/webp" data-foto-input @class(['input', 'input-galat' => $errors->has('foto')])>
            @error('foto')
                <p class="mt-1.5 text-sm font-semibold text-danger">{{ $message }}</p>
            @else
                <p class="mt-1.5 text-xs text-ink/60">JPG, PNG, atau WebP, maksimal 10 MB. Foto dikecilkan otomatis agar halaman cepat dibuka.</p>
            @enderror
        </div>

        <div>
            <input type="hidden" name="aktif" value="0">
            <label class="flex items-center gap-3 text-sm font-bold">
                <input type="checkbox" name="aktif" value="1" class="h-5 w-5 accent-olive" @checked(old('aktif', $produk?->aktif ?? true))>
                Tampilkan produk di katalog
            </label>
        </div>

        <button type="submit" class="btn-primary">{{ $ubah ? 'Simpan Perubahan' : 'Simpan Produk' }}</button>
        <a href="{{ route('penjual.produk.index') }}" class="btn-secondary">Batal</a>
    </form>
@endsection
