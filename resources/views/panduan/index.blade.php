@extends('layouts.app')

@section('judul', 'Panduan')

@section('isi')
    <div class="mx-auto flex max-w-3xl flex-col gap-5 px-4 py-6">
        <div>
            <h1 class="text-3xl font-extrabold">Panduan</h1>
            <p class="mt-1 text-ink/70">Langkah-langkah memakai PEKKA BOIS. Punya pertanyaan lain? <a href="{{ route('sahabat-pekka') }}" class="font-bold text-olive underline">Tanya Sahabat PEKKA</a>.</p>
        </div>

        @forelse (\App\Models\Panduan::KATEGORI as $kunci => $nama)
            @if (($panduan[$kunci] ?? collect())->isNotEmpty())
                <section>
                    <h2 class="mb-2 text-lg font-extrabold text-olive">{{ $nama }}</h2>
                    <div class="flex flex-col gap-2">
                        @foreach ($panduan[$kunci] as $item)
                            <details class="card group !p-0">
                                <summary class="flex min-h-12 cursor-pointer items-center justify-between gap-3 px-4 py-3 font-bold">
                                    <span>{{ $item->judul }}</span>
                                    <span class="text-olive transition group-open:rotate-180" aria-hidden="true">&#9662;</span>
                                </summary>
                                <div class="whitespace-pre-line border-t border-olive/10 px-4 py-3 text-sm text-ink/80">{{ $item->isi }}</div>
                            </details>
                        @endforeach
                    </div>
                </section>
            @endif
        @empty
        @endforelse

        @if ($panduan->isEmpty())
            <section class="card flex flex-col items-center gap-2 py-10 text-center">
                <p class="text-lg font-extrabold">Panduan belum tersedia</p>
                <p class="max-w-sm text-sm text-ink/70">Panduan sedang disiapkan oleh admin.</p>
            </section>
        @endif
    </div>
@endsection
