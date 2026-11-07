<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use Illuminate\View\View;

class KegiatanController extends Controller
{
    public function index(): View
    {
        return view('kegiatan.index', [
            'mendatang' => Kegiatan::tayang()->mendatang()->orderBy('mulai_at')->get(),
            'berlalu' => Kegiatan::tayang()->berlalu()->orderByDesc('mulai_at')->paginate(6),
        ]);
    }

    public function show(int $id): View
    {
        return view('kegiatan.show', ['kegiatan' => Kegiatan::tayang()->findOrFail($id)]);
    }
}
