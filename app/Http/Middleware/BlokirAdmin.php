<?php

namespace App\Http\Middleware;

use Closure;

class BlokirAdmin
{
    public function handle($request, Closure $next)
    {
        if (auth('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        if (auth('gudang')->check()) {
            return redirect()->route('gudang.dashboard');
        }
        $u = auth('web')->user();
        if ($u && in_array($u->role, ['admin', 'gudang'])) {
            auth('web')->logout();

            return redirect()->route($u->role === 'gudang' ? 'gudang.login' : 'admin.login')
                ->with('error', 'Akun ini hanya bisa login lewat halaman khususnya');
        }

        return $next($request);
    }
}
