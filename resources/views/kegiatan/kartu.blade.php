<a href="{{ route('kegiatan.show', $item->id) }}" class="card block !p-4 hover:border-olive/30">
    <p class="font-extrabold">{{ $item->judul }}</p>
    <p class="mt-1 text-sm font-bold text-olive">{{ $item->waktuTeks() }}</p>
    @if ($item->lokasi)
        <p class="text-sm text-ink/70">{{ $item->lokasi }}</p>
    @endif
    <p class="mt-2 text-sm text-ink/70">{{ \Illuminate\Support\Str::limit($item->deskripsi, 140) }}</p>
</a>
