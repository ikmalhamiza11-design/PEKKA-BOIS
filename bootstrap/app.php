<?php

use App\Http\Middleware\HanyaAdmin;
use App\Http\Middleware\HanyaAdminDinas;
use App\Http\Middleware\HanyaPenjual;
use App\Http\Middleware\WajibGantiSandi;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [WajibGantiSandi::class]);
        $middleware->alias([
            'penjual' => HanyaPenjual::class,
            'admin' => HanyaAdmin::class,
            'admin.dinas' => HanyaAdminDinas::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
