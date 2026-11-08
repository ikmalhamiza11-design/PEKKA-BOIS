<?php

use App\Http\Middleware\HanyaAdmin;
use App\Http\Middleware\HanyaAdminDinas;
use App\Http\Middleware\HanyaPenjual;
use App\Http\Middleware\WajibGantiSandi;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->validateCsrfTokens(except: ['keluar']);
        $middleware->web(append: [WajibGantiSandi::class]);
        $middleware->alias([
            'penjual' => HanyaPenjual::class,
            'admin' => HanyaAdmin::class,
            'admin.dinas' => HanyaAdminDinas::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return null;
            }

            return redirect()->back(fallback: route('beranda'))
                ->withInput($request->except(['_token', 'password', 'password_confirmation']))
                ->with('galat', 'Sesi halaman sudah berakhir. Silakan coba lagi.');
        });
    })->create();
