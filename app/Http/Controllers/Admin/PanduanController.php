<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Panduan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PanduanController extends Controller
{
    public function index(): View
    {
        return view('admin.panduan.index', [
            'panduan' => Panduan::orderBy('kategori')->orderBy('urutan')->orderBy('id')->get()->groupBy('kategori'),
        ]);
    }

    public function create(): View
    {
        return view('admin.panduan.form', ['panduan' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $panduan = Panduan::create($this->validasi($request));

        return redirect()->route('admin.panduan.index')->with('pesan', 'Panduan "'.$panduan->judul.'" ditambahkan.');
    }

    public function edit(int $id): View
    {
        return view('admin.panduan.form', ['panduan' => Panduan::findOrFail($id)]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        Panduan::findOrFail($id)->update($this->validasi($request));

        return redirect()->route('admin.panduan.index')->with('pesan', 'Panduan diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $panduan = Panduan::findOrFail($id);
        $panduan->delete();

        return redirect()->route('admin.panduan.index')->with('pesan', 'Panduan "'.$panduan->judul.'" dihapus.');
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'kategori' => ['required', Rule::in(array_keys(Panduan::KATEGORI))],
            'isi' => ['required', 'string', 'max:8000'],
            'urutan' => ['nullable', 'integer', 'between:0,999'],
        ]);

        $data['urutan'] = (int) ($data['urutan'] ?? 0);
        $data['dipublikasikan'] = $request->boolean('dipublikasikan');

        return $data;
    }
}
