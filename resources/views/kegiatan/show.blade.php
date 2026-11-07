@extends('layouts.app')

@section('judul', $kegiatan->judul)

@section('isi')
    <div class="mx-auto flex max-w-3xl flex-col gap-4 px-4 py-6">
        <a href="{{ route('kegiatan') }}" class="text-sm font-bold text-olive underline">&larr; Semua kegiatan</a>

        <article class="card flex flex-col gap-3">
            <h1 class="text-2xl font-extrabold">{{ $kegiatan->judul }}</h1>
            <p class="font-bold text-olive">{{ $kegiatan->waktuTeks() }}</p>
            @if ($kegiatan->lokasi)
                <p class="text-sm text-ink/70">Lokasi: {{ $kegiatan->lokasi }}</p>
            @endif
            <p class="whitespace-pre-line text-ink/80">{{ $kegiatan->deskripsi }}</p>
        </article>
    </div>
@endsection
