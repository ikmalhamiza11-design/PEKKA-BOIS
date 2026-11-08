@props(['href', 'label', 'aktif' => false, 'tengah' => false])

<a href="{{ $href }}" @if ($aktif) aria-current="page" @endif @class(['flex min-w-0 flex-col items-center justify-end gap-1 pb-2 pt-1 text-[0.68rem] font-bold leading-none', 'flex-1' => ! $tengah, 'flex-[1.5]' => $tengah, 'text-olive' => $aktif, 'text-ink/60' => ! $aktif])>
    <span @class(['flex items-center justify-center', 'h-6 w-6 shrink-0' => ! $tengah, '-mt-6 h-14 w-14 shrink-0 rounded-full bg-lime text-ink shadow-lg ring-4 ring-cream' => $tengah])>
        <svg viewBox="0 0 24 24" width="{{ $tengah ? 28 : 24 }}" height="{{ $tengah ? 28 : 24 }}" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{{ $slot }}</svg>
    </span>
    <span class="whitespace-nowrap">{{ $label }}</span>
</a>
