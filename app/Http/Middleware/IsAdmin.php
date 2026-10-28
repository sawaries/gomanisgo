<?php

namespace App\Http\Middleware;

use Closure;

class IsAdmin
{
    public function handle($request, Closure $next)
    {
        if (! auth('admin')->check()) {
            return redirect()->route('admin.login')->with('error', 'Silakan login sebagai admin');
        }

        return $next($request);
    }
}
