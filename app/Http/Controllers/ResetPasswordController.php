<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    private const TTL_MENIT = 10;      // masa berlaku OTP
    private const MAX_PERCOBAAN = 5;   // batas salah input OTP
    private const JEDA_KIRIM = 60;     // detik antar pengiriman ulang
    private const TTL_RESET_MENIT = 10; // masa berlaku sesi ganti password setelah OTP valid

    public function forgotForm()
    {
        return view('resetpassword.email');
    }

    public function sendOtp(Request $r)
    {
        $r->merge(['email' => strtolower(trim((string) $r->email))]);
        $r->validate(['email' => 'required|email|max:100']);
        $email = $r->email;

        $row = DB::table('resetpassword_otps')->where('email', $email)->first();
        if ($row && now()->lt(\Carbon\Carbon::parse($row->last_sent_at)->addSeconds(self::JEDA_KIRIM))) {
            $r->session()->put('reset_email', $email);

            return redirect()->route('resetpassword.otp')->with('error', 'Tunggu 1 menit sebelum meminta OTP lagi.');
        }

        // hanya akun pelanggan (admin memakai fitur ubah password sendiri)
        $user = User::where('email', $email)->where(fn ($q) => $q->where('role', '!=', 'admin')->orWhereNull('role'))->first();
        if ($user) {
            $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            DB::table('resetpassword_otps')->updateOrInsert(['email' => $email], [
                'otp_hash' => Hash::make($otp),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(self::TTL_MENIT),
                'last_sent_at' => now(),
                'reset_token_hash' => null,
                'verified_at' => null,
            ]);
            try {
                Mail::raw(
                    "Halo {$user->nama_user},\n\nKode OTP reset password GoManisGo Anda: {$otp}\n"
                    .'Berlaku '.self::TTL_MENIT." menit. Jangan bagikan kode ini kepada siapa pun.\n"
                    ."Abaikan email ini jika Anda tidak merasa memintanya.",
                    fn ($m) => $m->to($email)->subject('Kode OTP Reset Password - GoManisGo')
                );
            } catch (\Throwable $e) {
                Log::error('Gagal kirim OTP: '.$e->getMessage());
                DB::table('resetpassword_otps')->where('email', $email)->delete();

                return back()->withInput()->with('error', 'Gagal mengirim email. Coba lagi beberapa saat.');
            }
        }

        // respons sama untuk email terdaftar/tidak (cegah enumerasi akun)
        $r->session()->put('reset_email', $email);

        return redirect()->route('resetpassword.otp')
            ->with('success', 'Jika email terdaftar, kode OTP telah dikirim.');
    }

    public function verifyForm(Request $r)
    {
        if (! $r->session()->has('reset_email')) {
            return redirect()->route('resetpassword.email');
        }

        return view('resetpassword.otp', ['email' => $r->session()->get('reset_email')]);
    }

    public function verifyOtp(Request $r)
    {
        $email = $r->session()->get('reset_email');
        if (! $email) {
            return redirect()->route('resetpassword.email');
        }
        $r->validate(['otp' => 'required|digits:6']);

        $row = DB::table('resetpassword_otps')->where('email', $email)->first();
        $gagal = fn ($m) => back()->withErrors(['otp' => $m]);

        if (! $row || now()->gt($row->expires_at)) {
            DB::table('resetpassword_otps')->where('email', $email)->delete();

            return $gagal('OTP tidak valid atau sudah kedaluwarsa. Silakan minta OTP baru.');
        }
        if ($row->attempts >= self::MAX_PERCOBAAN) {
            DB::table('resetpassword_otps')->where('email', $email)->delete();

            return $gagal('Percobaan terlalu banyak. Silakan minta OTP baru.');
        }
        if (! Hash::check($r->otp, $row->otp_hash)) {
            DB::table('resetpassword_otps')->where('email', $email)->increment('attempts');
            $sisa = self::MAX_PERCOBAAN - $row->attempts - 1;

            return $gagal("OTP salah. Sisa percobaan: {$sisa}.");
        }

        // OTP valid sekali pakai -> terbitkan token reset (disimpan di sesi, hash di DB)
        $token = Str::random(64);
        DB::table('resetpassword_otps')->where('email', $email)->update([
            'otp_hash' => Hash::make(Str::random(32)),
            'reset_token_hash' => hash('sha256', $token),
            'verified_at' => now(),
            'expires_at' => now()->addMinutes(self::TTL_RESET_MENIT),
        ]);
        $r->session()->put('reset_token', $token);

        return redirect()->route('resetpassword.password');
    }

    public function resetForm(Request $r)
    {
        if (! $this->tokenValid($r)) {
            return redirect()->route('resetpassword.email')->with('error', 'Verifikasi OTP diperlukan.');
        }

        return view('resetpassword.password');
    }

    public function reset(Request $r)
    {
        if (! $this->tokenValid($r)) {
            return redirect()->route('resetpassword.email')->with('error', 'Sesi reset berakhir. Ulangi dari awal.');
        }
        $r->validate(['password' => 'required|min:6|confirmed']);

        $email = $r->session()->get('reset_email');
        User::where('email', $email)->where(fn ($q) => $q->where('role', '!=', 'admin')->orWhereNull('role'))
            ->update(['password' => Hash::make($r->password)]);

        DB::table('resetpassword_otps')->where('email', $email)->delete();
        $r->session()->forget(['reset_email', 'reset_token']);

        return redirect()->route('login')->with('success', 'Password berhasil diubah, silakan login.');
    }

    private function tokenValid(Request $r): bool
    {
        $email = $r->session()->get('reset_email');
        $token = $r->session()->get('reset_token');
        if (! $email || ! $token) {
            return false;
        }
        $row = DB::table('resetpassword_otps')->where('email', $email)->first();

        return $row && $row->verified_at && now()->lte($row->expires_at)
            && hash_equals((string) $row->reset_token_hash, hash('sha256', $token));
    }
}