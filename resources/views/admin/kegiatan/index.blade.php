@extends('layouts.admin')

@section('judul', 'Kegiatan')

@section('isi')
    <div class="flex items-center justify-between gap-3">
        <h1 class="text-2xl font-extrabold">Kegiatan</h1>
        <a href="{{ route('admin.kegiatan.create') }}" class="btn-primary !w-auto px-5">+ Tambah</a>
    </div>

    @forelse ($kegiatan as $item)
        <div class="card !p-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <p class="font-extrabold">{{ $item->judul }}</p>
                <p class="text-sm text-ink/70">{{ $item->waktuTeks() }}</p>
                <p class="mt-1 inline-flex rounded-full px-3 py-0.5 text-xs font-bold {{ $item->dipublikasikan ? 'bg-lime/30 text-olive' : 'bg-ink/10 text-ink/70' }}">{{ $item->dipublikasikan ? 'Tampil' : 'Draf' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.kegiatan.edit', $item->id) }}" class="btn-secondary min-h-11 !w-auto !px-4 !py-2 text-sm">Ubah</a>
                <form method="POST" action="{{ route('admin.kegiatan.destroy', $item->id) }}" data-konfirmasi="Hapus kegiatan ini?" data-konfirmasi-judul="Hapus data ini?" data-konfirmasi-tombol="Ya, Hapus">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-secondary min-h-11 !w-auto !border-danger !px-4 !py-2 text-sm !text-danger">Hapus</button>
                </form>
            </div>
        </div>
    @empty
        <section class="card py-10 text-center text-sm text-ink/70">Belum ada kegiatan. Tambahkan kegiatan pertama.</section>
    @endforelse

    <div>{{ $kegiatan->links() }}</div>
@endsection
