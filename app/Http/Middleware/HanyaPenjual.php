<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HanyaPenjual
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== User::PENJUAL || ! $user->shop) {
            abort(403, 'Halaman ini hanya untuk penjual.');
        }

        return $next($request);
    }
}
