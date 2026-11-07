<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HanyaAdminDinas
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->adalahAdminDinas()) {
            abort(403, 'Halaman ini hanya untuk admin dinas.');
        }

        return $next($request);
    }
}
