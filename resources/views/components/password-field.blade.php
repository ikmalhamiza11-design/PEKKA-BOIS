@props(['name', 'label', 'galat' => false, 'lupa' => false, 'meter' => false, 'hint' => null])

@php
    $pesan = $errors->first($name);
    $adaGalat = $pesan !== '' || $galat;
@endphp

<div>
    <div class="mb-1.5 flex items-baseline justify-between gap-3 text-sm">
        <label for="{{ $name }}" class="font-bold">{{ $label }}<span class="text-danger" aria-hidden="true"> *</span></label>
        @if ($lupa)
            <a href="{{ route('password.request') }}" class="font-semibold text-olive underline">Lupa kata sandi?</a>
        @endif
    </div>
    <div class="relative">
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="password"
            aria-invalid="{{ $adaGalat ? 'true' : 'false' }}"
            @if ($pesan) aria-describedby="{{ $name }}-galat" @endif
            {{ $attributes->class(['input', 'pr-12', 'input-galat' => $adaGalat]) }}
        >
        <button type="button" data-toggle-password="{{ $name }}" aria-pressed="false" aria-label="Tampilkan kata sandi" class="absolute inset-y-0 right-0 z-10 flex w-12 touch-manipulation items-center justify-center rounded-r-xl text-olive">
            <svg data-ikon-lihat viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="pointer-events-none"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg data-ikon-sembunyi viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="pointer-events-none hidden"><path d="M9.9 4.2A10 10 0 0 1 12 4c6.5 0 10 8 10 8a17 17 0 0 1-2.2 3.2"/><path d="M6.6 6.6A16.5 16.5 0 0 0 2 12s3.5 8 10 8a9.7 9.7 0 0 0 5.4-1.6"/><path d="M14.1 14.1a3 3 0 1 1-4.2-4.2"/><path d="m2 2 20 20"/></svg>
        </button>
    </div>

    @if ($meter)
        <div class="mt-3 rounded-xl bg-cream/70 p-3 text-sm" data-strength-for="{{ $name }}">
            <div class="flex gap-1.5" aria-hidden="true">
                @foreach (range(1, 4) as $i)
                    <span data-bar class="h-1.5 flex-1 rounded-full bg-olive/15"></span>
                @endforeach
            </div>
            <p class="mt-2 font-semibold">Kekuatan kata sandi: <span data-strength-label>-</span></p>
            <ul class="mt-2 space-y-1 text-ink/70">
                <li data-rule="panjang">Minimal 8 karakter</li>
                <li data-rule="kapital">Kombinasi huruf besar &amp; kecil</li>
                <li data-rule="angka">Mengandung angka (0-9)</li>
            </ul>
        </div>
    @elseif ($hint && ! $pesan)
        <p class="mt-1.5 text-xs text-ink/60">{{ $hint }}</p>
    @endif

    @if ($pesan)
        <p id="{{ $name }}-galat" class="mt-1.5 text-sm font-semibold text-danger">{{ $pesan }}</p>
    @endif
</div>
