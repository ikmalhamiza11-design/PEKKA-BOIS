<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KegiatanController extends Controller
{
    public function index(): View
    {
        return view('admin.kegiatan.index', [
            'kegiatan' => Kegiatan::orderByDesc('mulai_at')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.kegiatan.form', ['kegiatan' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $kegiatan = Kegiatan::create($this->validasi($request));

        return redirect()->route('admin.kegiatan.index')->with('pesan', 'Kegiatan "'.$kegiatan->judul.'" ditambahkan.');
    }

    public function edit(int $id): View
    {
        return view('admin.kegiatan.form', ['kegiatan' => Kegiatan::findOrFail($id)]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        Kegiatan::findOrFail($id)->update($this->validasi($request));

        return redirect()->route('admin.kegiatan.index')->with('pesan', 'Kegiatan diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $kegiatan = Kegiatan::findOrFail($id);
        $kegiatan->delete();

        return redirect()->route('admin.kegiatan.index')->with('pesan', 'Kegiatan "'.$kegiatan->judul.'" dihapus.');
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'deskripsi' => ['required', 'string', 'max:5000'],
            'lokasi' => ['nullable', 'string', 'max:150'],
            'mulai_at' => ['required', 'date'],
            'selesai_at' => ['nullable', 'date', 'after_or_equal:mulai_at'],
        ], [], [
            'judul' => 'judul',
            'deskripsi' => 'deskripsi',
            'lokasi' => 'lokasi',
            'mulai_at' => 'waktu mulai',
            'selesai_at' => 'waktu selesai',
        ]);

        $data['dipublikasikan'] = $request->boolean('dipublikasikan');
        $data['lokasi'] = filled($data['lokasi'] ?? null) ? trim($data['lokasi']) : null;
        $data['selesai_at'] = filled($data['selesai_at'] ?? null) ? $data['selesai_at'] : null;

        return $data;
    }
}
