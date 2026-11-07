<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WajibGantiSandi
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && $user->wajib_ganti_sandi
            && $request->route() !== null
            && ! $request->routeIs('sandi.wajib', 'sandi.wajib.simpan', 'logout')
        ) {
            return redirect()->route('sandi.wajib');
        }

        return $next($request);
    }
}
