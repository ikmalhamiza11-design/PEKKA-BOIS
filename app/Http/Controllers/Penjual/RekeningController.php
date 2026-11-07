<?php

namespace App\Http\Controllers\Penjual;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RekeningController extends Controller
{
    public function edit(Request $request): View
    {
        return view('penjual.rekening.edit', ['toko' => $request->user()->shop]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->merge([
            'nomor_rekening' => preg_replace('/[\s\-]/', '', (string) $request->input('nomor_rekening')),
        ]);

        $data = $request->validate([
            'nama_bank' => ['required', 'string', 'max:50'],
            'nomor_rekening' => ['required', 'regex:/^[0-9]{5,25}$/'],
            'atas_nama' => ['required', 'string', 'max:100'],
        ], [
            'nomor_rekening.regex' => 'Nomor rekening hanya boleh berisi angka, antara 5 sampai 25 digit.',
        ]);

        $request->user()->shop->update($data);

        return redirect()->route('penjual.rekening.edit')->with('pesan', 'Rekening pembayaran disimpan.');
    }
}
