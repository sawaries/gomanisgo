<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\RealEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.login');
    }

    public function login(Request $r)
    {
        $r->validate(['email' => 'required|email', 'password' => 'required']);
        // akun admin tidak bisa login di sini (khusus /admin/login)
        $cred = $r->only('email', 'password') + [fn ($q) => $q->whereNotIn('role', ['admin', 'gudang'])->orWhereNull('role')];
        if (Auth::attempt($cred)) {
            $r->session()->regenerate();

            return redirect()->route('home');
        }

        return back()->with('error', 'Email/password salah');
    }

    public function registerForm()
    {
        return view('auth.register');
    }

    public function register(Request $r)
    {
        $r->merge(['email' => strtolower(trim((string) $r->email))]);
        $data = $r->validate([
            'nama_user' => 'required', 'no_telepon' => 'required',
            'email' => ['required', 'max:100', 'email:rfc,dns', new RealEmail, 'unique:users,email'],
            'password' => 'required|min:6',
        ]);
        $data['password'] = Hash::make($data['password']);
        User::create($data);

        return redirect()->route('login')->with('success', 'Registrasi berhasil, silakan login');
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->route('home');
    }
}