@extends('layouts.app')

@section('judul', 'Kegiatan')

@section('isi')
    <div class="mx-auto flex max-w-3xl flex-col gap-5 px-4 py-6">
        <div>
            <h1 class="text-3xl font-extrabold">Kegiatan</h1>
            <p class="mt-1 text-ink/70">Pelatihan, pertemuan, dan acara untuk anggota PEKKA dan pembeli.</p>
        </div>

        <section>
            <h2 class="mb-2 text-lg font-extrabold text-olive">Akan datang</h2>
            <div class="flex flex-col gap-3">
                @forelse ($mendatang as $item)
                    @include('kegiatan.kartu', ['item' => $item])
                @empty
                    <div class="card !p-5 text-center text-sm text-ink/70">Belum ada kegiatan yang dijadwalkan.</div>
                @endforelse
            </div>
        </section>

        @if ($berlalu->isNotEmpty())
            <section>
                <h2 class="mb-2 text-lg font-extrabold text-olive">Sudah berlalu</h2>
                <div class="flex flex-col gap-3">
                    @foreach ($berlalu as $item)
                        @include('kegiatan.kartu', ['item' => $item])
                    @endforeach
                </div>
                <div class="mt-3">{{ $berlalu->links() }}</div>
            </section>
        @endif
    </div>
@endsection
