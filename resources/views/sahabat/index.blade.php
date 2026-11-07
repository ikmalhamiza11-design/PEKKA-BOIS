@extends('layouts.app')

@section('judul', 'Sahabat PEKKA')

@section('isi')
    <div class="mx-auto flex max-w-2xl flex-col gap-4 px-4 py-6" id="sahabat" data-url="{{ route('sahabat-pekka.kirim') }}" data-token="{{ csrf_token() }}" data-tanya="{{ $tanya }}">
        <div class="flex items-start gap-3">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-lime text-ink" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="m12 6 1 2.5 2.5 1-2.5 1L12 13l-1-2.5-2.5-1 2.5-1z"/></svg>
            </span>
            <div>
                <h1 class="text-2xl font-extrabold">Sahabat PEKKA</h1>
                <p class="text-sm text-ink/70">Asisten otomatis (AI) untuk cara belanja, cara berjualan, NIB, label halal, dan mencari produk.</p>
            </div>
        </div>

        <x-alert judul="Jawaban otomatis bisa keliru">
            Untuk urusan penting seperti perizinan dan kehalalan, pastikan ke sumber resmi. Jangan menulis kata sandi, nomor rekening, atau data pribadi di sini.
        </x-alert>

        <section class="card flex min-h-[22rem] flex-col gap-3 !p-4" aria-live="polite" id="sahabat-percakapan" aria-label="Percakapan">
            <div class="max-w-[85%] self-start rounded-2xl rounded-tl-sm bg-lime/25 px-4 py-3 text-sm">
                Halo, saya Sahabat PEKKA. Ada yang bisa saya bantu? Kamu bisa bertanya cara belanja, cara berjualan, atau mencari produk.
            </div>
        </section>

        <div class="flex flex-wrap gap-2" id="sahabat-saran">
            <button type="button" data-saran="Bagaimana cara belanja di PEKKA BOIS?" class="rounded-full border border-olive/20 px-3 py-1.5 text-xs font-bold text-olive hover:bg-lime/30">Cara belanja</button>
            <button type="button" data-saran="Bagaimana cara berjualan di PEKKA BOIS?" class="rounded-full border border-olive/20 px-3 py-1.5 text-xs font-bold text-olive hover:bg-lime/30">Cara berjualan</button>
            <button type="button" data-saran="Bagaimana cara mengurus NIB untuk usaha kecil?" class="rounded-full border border-olive/20 px-3 py-1.5 text-xs font-bold text-olive hover:bg-lime/30">NIB usaha</button>
            <button type="button" data-saran="Bagaimana cara mengurus label halal untuk produk makanan?" class="rounded-full border border-olive/20 px-3 py-1.5 text-xs font-bold text-olive hover:bg-lime/30">Label halal</button>
        </div>

        <form id="sahabat-form" class="flex items-end gap-2" novalidate>
            <label for="sahabat-pesan" class="sr-only">Pertanyaanmu</label>
            <textarea id="sahabat-pesan" rows="2" maxlength="500" placeholder="Tulis pertanyaanmu di sini" class="input min-h-12 flex-1 resize-none"></textarea>
            <button type="submit" id="sahabat-kirim" class="btn-primary !w-auto shrink-0 px-5">Kirim</button>
        </form>
    </div>
@endsection
