@props(['nilai', 'jumlah' => null, 'kecil' => false, 'angka' => true])

@php
    $bulat = max(0, min(5, (int) round($nilai)));
    $teks = number_format((float) $nilai, 1, ',', '.');
@endphp

<span class="inline-flex items-center gap-1.5" role="img" aria-label="Penilaian {{ $teks }} dari 5{{ $jumlah !== null ? ', '.$jumlah.' ulasan' : '' }}">
    <span @class(['leading-none tracking-tight text-amber-500', 'text-sm' => $kecil, 'text-lg' => ! $kecil]) aria-hidden="true">{{ str_repeat('★', $bulat) }}<span class="text-ink/20">{{ str_repeat('★', 5 - $bulat) }}</span></span>
    @if ($angka)
        <span class="text-xs font-extrabold text-ink/80" aria-hidden="true">{{ $teks }}</span>
    @endif
    @if ($jumlah !== null)
        <span class="text-xs text-ink/60" aria-hidden="true">{{ '('.$jumlah.')' }}</span>
    @endif
</span>
