<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginForm()
    {
        if (auth('gudang')->check() || auth('admin')->check()) {
            return redirect()->route('gudang.dashboard');
        }

        return view('gudang.login');
    }

    public function login(Request $r)
    {
        $r->validate(['email' => 'required|email', 'password' => 'required']);
        if (Auth::guard('gudang')->attempt(['email' => $r->email, 'password' => $r->password, 'role' => 'gudang'])) {
            $r->session()->regenerate();

            return redirect()->route('gudang.dashboard');
        }

        return back()->with('error', 'Email atau password salah, atau bukan akun gudang')->withInput($r->only('email'));
    }

    public function logout()
    {
        Auth::guard('gudang')->logout();

        return redirect()->route('gudang.login');
    }
}