<?php

namespace App\Http\Controllers\Penjual;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KetersediaanController extends Controller
{
    public function edit(Request $request): View
    {
        return view('penjual.ketersediaan.edit', ['toko' => $request->user()->shop]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->merge([
            'jam_buka' => $this->rapikanJam($request->input('jam_buka')),
            'jam_tutup' => $this->rapikanJam($request->input('jam_tutup')),
        ]);

        $otomatis = $request->boolean('jadwal_otomatis');

        $data = $request->validate([
            'libur' => ['nullable', 'boolean'],
            'jadwal_otomatis' => ['nullable', 'boolean'],
            'jam_buka' => [Rule::requiredIf($otomatis), 'nullable', 'date_format:H:i'],
            'jam_tutup' => [Rule::requiredIf($otomatis), 'nullable', 'date_format:H:i', 'after:jam_buka'],
            'hari_buka' => ['nullable', 'array'],
            'hari_buka.*' => ['integer', 'between:1,7'],
        ], [
            'jam_buka.required' => 'Isi jam buka.',
            'jam_tutup.required' => 'Isi jam tutup.',
            'jam_buka.date_format' => 'Tulis jam dengan format seperti 08:00.',
            'jam_tutup.date_format' => 'Tulis jam dengan format seperti 17:00.',
            'jam_tutup.after' => 'Jam tutup harus setelah jam buka (dalam hari yang sama).',
        ]);

        $hari = collect($data['hari_buka'] ?? [])->map(fn ($h) => (int) $h)->unique()->sort()->values();

        if ($otomatis && $hari->isEmpty()) {
            return back()->withInput()->withErrors(['hari_buka' => 'Pilih minimal satu hari buka.']);
        }

        $request->user()->shop->update([
            'libur' => $request->boolean('libur'),
            'jadwal_otomatis' => $otomatis,
            'jam_buka' => $otomatis ? $data['jam_buka'] : null,
            'jam_tutup' => $otomatis ? $data['jam_tutup'] : null,
            'hari_buka' => $otomatis ? $hari->implode(',') : null,
        ]);

        return redirect()->route('penjual.ketersediaan.edit')->with('pesan', 'Pengaturan ketersediaan toko disimpan.');
    }

    private function rapikanJam(mixed $jam): ?string
    {
        $jam = trim((string) $jam);

        if ($jam === '') {
            return null;
        }

        // Peramban kadang mengirim 08:00:00; kita simpan hanya jam dan menit.
        return preg_match('/^(\d{2}:\d{2}):\d{2}$/', $jam, $cocok) ? $cocok[1] : $jam;
    }
}
