<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TrustProxies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\TrustProxies as BaseTrustProxies;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SecurityHeaders::class,
        ]);

        // Di produksi aplikasi biasanya berada di belakang Nginx/load
        // balancer yang mengakhiri TLS. Tanpa daftar proxy tepercaya,
        // Laravel menganggap request masih HTTP: HSTS tidak dikirim dan
        // cookie sesi bisa kehilangan atribut Secure. Isi TRUSTED_PROXIES
        // di .env (satu IP, daftar dipisah koma, atau "*" bila aplikasi
        // tidak dapat diakses langsung). Nilainya dibaca dari config oleh
        // middleware pengganti di bawah supaya tetap berlaku saat
        // konfigurasi di-cache.
        $middleware->replace(BaseTrustProxies::class, TrustProxies::class);

        $middleware->alias([
            'admin' => EnsureAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
