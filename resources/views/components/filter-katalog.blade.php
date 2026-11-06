@props(['awalan', 'kategori', 'hitung', 'kelompok', 'filter', 'parameter', 'jumlah'])

@php
    $penelusuran = \Illuminate\Support\Arr::only($parameter, ['q', 'urut']);
    $dasar = \Illuminate\Support\Arr::except($parameter, ['harga_min', 'harga_maks']);
    $cepat = [
        ['label' => 'Di bawah Rp 25 rb', 'data' => ['harga_maks' => 25000]],
        ['label' => 'Rp 25 rb - 50 rb', 'data' => ['harga_min' => 25000, 'harga_maks' => 50000]],
        ['label' => 'Di atas Rp 50 rb', 'data' => ['harga_min' => 50000]],
    ];
@endphp

<form method="GET" action="{{ route('katalog') }}" class="flex flex-col gap-5">
    <x-parameter-tersembunyi :parameter="$penelusuran" />

    <div class="flex items-center justify-between gap-2">
        <p class="font-extrabold text-olive">
            Filter Produk
            @if ($jumlah > 0)
                <span class="ml-1 rounded-full bg-lime px-2 py-0.5 text-xs text-ink">{{ $jumlah }} aktif</span>
            @endif
        </p>
        <a href="{{ route('katalog', $penelusuran) }}" class="text-sm font-bold text-olive underline">Reset</a>
    </div>

    <fieldset>
        <legend class="mb-2 text-sm font-extrabold">Kategori Usaha</legend>
        <div class="flex flex-col gap-1">
            @foreach ($kategori as $item)
                <label class="flex cursor-pointer items-center gap-3 rounded-xl px-2 py-2 hover:bg-lime/15">
                    <input type="checkbox" name="kategori[]" value="{{ $item->slug }}" class="h-5 w-5 shrink-0 accent-olive" @checked(in_array($item->slug, $filter['kategori'], true))>
                    <span class="flex-1 text-sm font-semibold">{{ $item->nama }}</span>
                    <span class="text-xs text-ink/50">{{ $hitung[$item->id] ?? 0 }}</span>
                </label>
            @endforeach
        </div>
    </fieldset>

    <div>
        <label for="{{ $awalan }}-kelompok" class="mb-2 block text-sm font-extrabold">Kelompok PEKKA</label>
        <select id="{{ $awalan }}-kelompok" name="kelompok" class="input">
            <option value="">Semua kelompok</option>
            @foreach ($kelompok as $item)
                <option value="{{ $item->id }}" @selected($filter['kelompok'] === $item->id)>{{ $item->nama }}</option>
            @endforeach
        </select>
    </div>

    <fieldset>
        <legend class="mb-2 text-sm font-extrabold">Rentang Harga (Rp)</legend>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label for="{{ $awalan }}-min" class="mb-1 block text-xs text-ink/60">Minimum</label>
                <input id="{{ $awalan }}-min" name="harga_min" type="number" inputmode="numeric" min="0" step="1000" value="{{ $filter['harga_min'] }}" placeholder="0" class="input">
            </div>
            <div>
                <label for="{{ $awalan }}-maks" class="mb-1 block text-xs text-ink/60">Maksimum</label>
                <input id="{{ $awalan }}-maks" name="harga_maks" type="number" inputmode="numeric" min="0" step="1000" value="{{ $filter['harga_maks'] }}" placeholder="Tanpa batas" class="input">
            </div>
        </div>
        <div class="mt-2 flex flex-wrap gap-2">
            @foreach ($cepat as $item)
                <a href="{{ route('katalog', array_merge($dasar, $item['data'])) }}" class="rounded-full border border-olive/20 px-3 py-1 text-xs font-bold text-olive hover:bg-lime/30">{{ $item['label'] }}</a>
            @endforeach
        </div>
    </fieldset>

    <button type="submit" class="btn-primary">Terapkan Filter</button>
</form>
