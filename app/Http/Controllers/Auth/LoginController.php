<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request, OtpService $otp)
    {
        $cred = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = DB::table('users')->where('email', $cred['email'])->first();

        if (! $user || ! Hash::check($cred['password'], $user->password)) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
        }

        // Belum verifikasi email -> kirim OTP baru & arahkan ke halaman OTP
        if (! $user->email_verified_at) {
            session(['otp_user_id' => $user->id]);
            $otp->send($user);

            return redirect()->route('otp.form')
                ->with('status', 'Email belum diverifikasi. Kode OTP baru telah dikirim.');
        }

        if (! $user->is_active) {
            return back()->withErrors(['email' => 'Akun Anda tidak aktif.'])->onlyInput('email');
        }

        Auth::loginUsingId($user->id, $request->boolean('remember'));
        $request->session()->regenerate();

        return match ($user->role) {
            'merchant' => redirect()->route('merchant.dashboard'),
            'customer' => redirect()->route('customer.katering.index'),
            'admin' => redirect()->route('admin.dashboard'),
            default    => redirect('/'),
        };
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}