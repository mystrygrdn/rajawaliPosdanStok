<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->must_change_password) {
            // Kecualikan rute ganti password, rute submit ganti password, logout, atau request API/JSON
            if (!$request->routeIs('password.change', 'password.update', 'logout') && !$request->expectsJson()) {
                return redirect()->route('password.change')
                    ->with('warning', 'Demi keamanan, Anda wajib mengganti password bawaan terlebih dahulu.');
            }
        }

        return $next($request);
    }
}
