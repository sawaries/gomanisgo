<?php

namespace App\Http\Middleware;

use Closure;

class IsGudang
{
    // Akses: petugas gudang (guard gudang) atau admin (guard admin)
    public function handle($request, Closure $next)
    {
        if (! auth('gudang')->check() && ! auth('admin')->check()) {
            return redirect()->route('gudang.login')->with('error', 'Silakan login sebagai petugas gudang');
        }

        return $next($request);
    }
}