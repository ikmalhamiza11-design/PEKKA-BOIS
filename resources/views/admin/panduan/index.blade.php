@extends('layouts.admin')

@section('judul', 'Panduan')

@section('isi')
    <div class="flex items-center justify-between gap-3">
        <h1 class="text-2xl font-extrabold">Panduan</h1>
        <a href="{{ route('admin.panduan.create') }}" class="btn-primary !w-auto px-5">+ Tambah</a>
    </div>

    @forelse (\App\Models\Panduan::KATEGORI as $kunci => $nama)
        @if (($panduan[$kunci] ?? collect())->isNotEmpty())
            <section class="flex flex-col gap-2">
                <h2 class="font-extrabold text-olive">{{ $nama }}</h2>
                @foreach ($panduan[$kunci] as $item)
                    <div class="card !p-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="font-extrabold">{{ $item->urutan }}. {{ $item->judul }}</p>
                            <p class="mt-1 inline-flex rounded-full px-3 py-0.5 text-xs font-bold {{ $item->dipublikasikan ? 'bg-lime/30 text-olive' : 'bg-ink/10 text-ink/70' }}">{{ $item->dipublikasikan ? 'Tampil' : 'Draf' }}</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.panduan.edit', $item->id) }}" class="btn-secondary min-h-11 !w-auto !px-4 !py-2 text-sm">Ubah</a>
                            <form method="POST" action="{{ route('admin.panduan.destroy', $item->id) }}" onsubmit="return confirm('Hapus panduan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-secondary min-h-11 !w-auto !border-danger !px-4 !py-2 text-sm !text-danger">Hapus</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </section>
        @endif
    @empty
    @endforelse

    @if ($panduan->isEmpty())
        <section class="card py-10 text-center text-sm text-ink/70">Belum ada panduan.</section>
    @endif
@endsection
