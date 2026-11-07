<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HanyaAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->adalahAdmin()) {
            abort(403, 'Halaman ini hanya untuk admin.');
        }

        if ($user->adalahAdminKelompok() && ! $user->kelompok_id) {
            abort(403, 'Akun admin kelompok ini belum terhubung ke kelompok PEKKA.');
        }

        return $next($request);
    }
}
