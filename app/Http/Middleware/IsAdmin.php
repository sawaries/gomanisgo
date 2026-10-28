<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin {
    public function handle($request, Closure $next){
        if(!auth('admin')->check()){
            return redirect()->route('admin.login')->with('error','Silakan login sebagai admin');
        }
        return $next($request);
    }
}
