@props(['pesanan'])

@php
    $diterima = $pesanan->payments->firstWhere('status', \App\Models\Payment::DITERIMA);

    $langkah = $pesanan->status === \App\Models\Order::DIBATALKAN
        ? [
            ['Pesanan dibuat', $pesanan->created_at],
            ['Dibatalkan', $pesanan->updated_at],
        ]
        : [
            ['Pesanan dibuat', $pesanan->created_at],
            ['Pembayaran diterima', $diterima?->diverifikasi_at],
            ['Sedang diproses', $pesanan->diproses_at],
            ['Dikirim', $pesanan->dikirim_at],
            ['Selesai', $pesanan->selesai_at],
        ];
@endphp

<section class="card !p-4 text-sm">
    <h2 class="font-extrabold">Status Pesanan</h2>

    <ol class="mt-3 flex flex-col gap-3">
        @foreach ($langkah as [$judul, $waktu])
            <li class="flex items-start gap-3">
                <span @class(['mt-1 h-3 w-3 shrink-0 rounded-full border-2', 'border-olive bg-olive' => $waktu, 'border-ink/25' => ! $waktu])></span>
                <div>
                    <p @class(['font-bold', 'text-ink/50' => ! $waktu])>{{ $judul }}</p>
                    @if ($waktu)
                        <p class="text-xs text-ink/60">{{ $waktu->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</section>
