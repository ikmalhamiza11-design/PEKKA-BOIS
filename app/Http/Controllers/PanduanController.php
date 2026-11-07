<?php

namespace App\Http\Controllers;

use App\Models\Panduan;
use Illuminate\View\View;

class PanduanController extends Controller
{
    public function __invoke(): View
    {
        $panduan = Panduan::tayang()->orderBy('urutan')->orderBy('id')->get()->groupBy('kategori');

        return view('panduan.index', ['panduan' => $panduan]);
    }
}
