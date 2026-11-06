@props(['tipe' => 'info', 'judul' => null])

@php
    $kelas = match ($tipe) {
        'galat' => 'border-danger/30 bg-red-50 text-danger',
        'sukses' => 'border-success/30 bg-green-50 text-success',
        default => 'border-olive/20 bg-lime/15 text-olive',
    };
@endphp

<div role="{{ $tipe === 'galat' ? 'alert' : 'status' }}" class="rounded-xl border p-4 text-sm {{ $kelas }}">
    @if ($judul)
        <p class="font-extrabold">{{ $judul }}</p>
    @endif
    <div @class(['font-medium', 'mt-1' => $judul])>{{ $slot }}</div>
</div>
