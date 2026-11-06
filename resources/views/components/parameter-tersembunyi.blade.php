@props(['parameter'])

@foreach ($parameter as $nama => $nilai)
    @if (is_array($nilai))
        @foreach ($nilai as $item)
            <input type="hidden" name="{{ $nama }}[]" value="{{ $item }}">
        @endforeach
    @else
        <input type="hidden" name="{{ $nama }}" value="{{ $nilai }}">
    @endif
@endforeach
