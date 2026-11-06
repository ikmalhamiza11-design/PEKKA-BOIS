@props(['judul', 'kembali'])

<header class="flex items-center gap-3 rounded-2xl bg-paper p-2 pr-4 shadow-sm">
    <a href="{{ $kembali }}" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-lime/30 text-olive" aria-label="Kembali">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
    </a>
    <div class="leading-tight">
        <p class="font-extrabold">{{ $judul }}</p>
        <p class="text-xs text-ink/60">{{ config('app.name') }}</p>
    </div>
</header>
