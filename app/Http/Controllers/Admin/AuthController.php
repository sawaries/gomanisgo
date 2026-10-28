<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('admin.login');
    }

    public function login(Request $r)
    {
        $r->validate(['email' => 'required|email', 'password' => 'required']);
        if (Auth::guard('admin')->attempt(['email' => $r->email, 'password' => $r->password, 'role' => 'admin'])) {
            $r->session()->regenerate();

            return redirect()->route('admin.dashboard');
        }

        return back()->with('error', 'Bukan admin / login gagal');
    }

    public function logout()
    {
        Auth::guard('admin')->logout();

        return redirect()->route('admin.login');
    }
}
