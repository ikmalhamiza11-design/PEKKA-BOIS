@props(['name', 'label', 'type' => 'text', 'hint' => null, 'galat' => false, 'wajib' => true, 'awalan' => null, 'nilai' => null])

@php
    $pesan = $errors->first($name);
    $adaGalat = $pesan !== '' || $galat;
@endphp

<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-bold">
        {{ $label }}@if ($wajib)<span class="text-danger" aria-hidden="true"> *</span>@endif
    </label>
    <div class="relative">
        @if ($awalan)
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-base font-semibold text-ink/60">{{ $awalan }}</span>
        @endif
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="{{ $type }}"
            value="{{ old($name, $nilai) }}"
            aria-invalid="{{ $adaGalat ? 'true' : 'false' }}"
            @if ($pesan) aria-describedby="{{ $name }}-galat" @endif
            {{ $attributes->class(['input', 'input-galat' => $adaGalat, 'pl-16' => $awalan]) }}
        >
    </div>
    @if ($hint && ! $pesan)
        <p class="mt-1.5 text-xs text-ink/60">{{ $hint }}</p>
    @endif
    @if ($pesan)
        <p id="{{ $name }}-galat" class="mt-1.5 text-sm font-semibold text-danger">{{ $pesan }}</p>
    @endif
</div>
