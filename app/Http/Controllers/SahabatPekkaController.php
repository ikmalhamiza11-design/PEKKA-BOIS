<?php

namespace App\Http\Controllers;

use App\Exceptions\SahabatPekkaGalat;
use App\Services\SahabatPekkaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SahabatPekkaController extends Controller
{
    public function index(Request $request): View
    {
        return view('sahabat.index', [
            'tanya' => Str::limit(trim((string) $request->query('tanya')), 200, ''),
        ]);
    }

    public function kirim(Request $request, SahabatPekkaService $layanan): JsonResponse
    {
        $data = $request->validate([
            'pesan' => ['required', 'string', 'max:500'],
            'riwayat' => ['nullable', 'array', 'max:10'],
            'riwayat.*.peran' => ['required', 'in:user,model'],
            'riwayat.*.isi' => ['required', 'string', 'max:4000'],
        ]);

        try {
            return response()->json($layanan->jawab($data['pesan'], $data['riwayat'] ?? []));
        } catch (SahabatPekkaGalat $e) {
            return response()->json(['galat' => $e->getMessage()], $e->status);
        }
    }
}
