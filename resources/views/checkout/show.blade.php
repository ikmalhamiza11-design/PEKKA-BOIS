@extends('layouts.app')
@use('App\Support\Rupiah')

@section('judul', 'Checkout')

@section('isi')
    <div class="mx-auto max-w-6xl px-4 pb-8 pt-4 lg:pt-8">
        <a href="{{ route('keranjang') }}" class="mb-3 inline-flex items-center gap-1 text-sm font-bold text-olive">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            Kembali ke keranjang
        </a>
        <h1 class="text-3xl font-extrabold">Checkout</h1>

        <div class="mt-4 grid gap-6 lg:grid-cols-[1fr_22rem] lg:items-start">
            <form id="form-checkout" method="POST" action="{{ route('checkout.store') }}" class="card flex flex-col gap-4" novalidate>
                @csrf
                <h2 class="text-lg font-extrabold">Alamat pengiriman</h2>

                <x-field name="nama_penerima" label="Nama penerima" autocomplete="name" maxlength="100" :nilai="$user->name" />
                <x-field name="telepon" label="Nomor HP/WhatsApp" type="tel" inputmode="tel" autocomplete="tel" placeholder="0812-3456-7890" :nilai="\App\Support\Telepon::lokal($user->phone)" />

                <div>
                    <label for="alamat" class="mb-1.5 block text-sm font-bold">Alamat lengkap<span class="text-danger" aria-hidden="true"> *</span></label>
                    <textarea id="alamat" name="alamat" rows="4" maxlength="500" placeholder="Jalan, RT/RW, desa, kecamatan, kode pos" @class(['input', 'input-galat' => $errors->has('alamat')])>{{ old('alamat') }}</textarea>
                    @error('alamat')
                        <p class="mt-1.5 text-sm font-semibold text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="catatan" class="mb-1.5 block text-sm font-bold">Catatan untuk penjual</label>
                    <textarea id="catatan" name="catatan" rows="2" maxlength="300" placeholder="Opsional" @class(['input', 'input-galat' => $errors->has('catatan')])>{{ old('catatan') }}</textarea>
                    @error('catatan')
                        <p class="mt-1.5 text-sm font-semibold text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <x-alert>
                    Pembayaran dilakukan lewat transfer bank ke rekening penjual, lalu kamu mengunggah bukti transfernya di halaman pesanan. Stok produkmu sudah ditahan begitu pesanan dibuat.
                </x-alert>
            </form>

            <aside class="card lg:sticky lg:top-24">
                <h2 class="text-lg font-extrabold">Ringkasan pesanan</h2>

                @foreach ($perToko as $baris)
                    @php $toko = $baris->first()->product->shop; @endphp
                    <div class="mt-3 border-t border-olive/10 pt-3">
                        <p class="text-sm font-extrabold">{{ $toko->nama }}</p>
                        @foreach ($baris as $item)
                            <div class="mt-1 flex justify-between gap-3 text-sm">
                                <span class="min-w-0 text-ink/80">{{ $item->product->nama }} <span class="text-ink/60">&times; {{ $item->jumlah }}</span></span>
                                <span class="shrink-0 font-bold">{{ Rupiah::format($item->subtotal) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endforeach

                @if ($perToko->count() > 1)
                    <p class="mt-3 text-xs text-ink/60">Pesananmu akan dipecah menjadi {{ $perToko->count() }} pesanan, satu untuk setiap toko.</p>
                @endif

                <div class="mt-3 flex items-center justify-between border-t border-olive/10 pt-3">
                    <span class="font-bold">Total ({{ $jumlahItem }} item)</span>
                    <span class="text-xl font-extrabold text-olive">{{ Rupiah::format($total) }}</span>
                </div>
                <p class="mt-2 text-xs text-ink/60">Ongkos kirim belum termasuk dan akan dikonfirmasi oleh penjual.</p>

                <button type="submit" form="form-checkout" class="btn-primary mt-4">Buat Pesanan</button>
            </aside>
        </div>
    </div>
@endsection
