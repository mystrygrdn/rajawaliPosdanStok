<?php

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
        // Mengarahkan tamu yang belum login ke rute login bernama 'login'
        $middleware->redirectGuestsTo(fn () => route('login'));
        
        // Mengarahkan pengguna jika sudah login tetapi mencoba mengakses halaman login kembali
        $middleware->redirectUsersTo(fn () => '/');

        $middleware->web(append: [
            \App\Http\Middleware\ForcePasswordChange::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();