@props(['href', 'label', 'aktif' => false, 'tengah' => false])

<a href="{{ $href }}" @if ($aktif) aria-current="page" @endif @class(['flex flex-1 flex-col items-center justify-end gap-1 pb-2 pt-1 text-[0.7rem] font-bold', 'text-olive' => $aktif, 'text-ink/60' => ! $aktif])>
    <span @class(['flex items-center justify-center', 'h-6 w-6' => ! $tengah, '-mt-7 h-14 w-14 rounded-full bg-lime text-ink shadow-lg ring-4 ring-cream' => $tengah])>
        <svg viewBox="0 0 24 24" width="{{ $tengah ? 28 : 24 }}" height="{{ $tengah ? 28 : 24 }}" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{{ $slot }}</svg>
    </span>
    <span>{{ $label }}</span>
</a>
