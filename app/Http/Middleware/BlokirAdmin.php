<?php

namespace App\Http\Middleware;

use Closure;

class BlokirAdmin
{
    // Halaman toko tidak boleh diakses saat login sebagai admin
    public function handle($request, Closure $next)
    {
        if (auth('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        $u = auth('web')->user();
        if ($u && $u->role === 'admin') {
            auth('web')->logout();

            return redirect()->route('admin.login')->with('error', 'Akun admin hanya bisa login lewat halaman admin');
        }

        return $next($request);
    }
}
