<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OtpController extends Controller
{
    public function form()
    {
        if (! session('otp_user_id')) {
            return redirect()->route('login');
        }

        $user = DB::table('users')->find(session('otp_user_id'));

        return view('auth.verify-otp', ['email' => $user->email ?? '']);
    }

    public function verify(Request $request)
    {
        $request->validate(['otp' => 'required|digits:6']);

        $userId = session('otp_user_id');
        if (! $userId) {
            return redirect()->route('login');
        }

        $row = DB::table('email_otps')->where('user_id', $userId)->first();

        if (! $row || Carbon::parse($row->expires_at)->isPast()) {
            return back()->withErrors(['otp' => 'Kode kedaluwarsa. Silakan kirim ulang kode.']);
        }

        if ($row->attempts >= 5) {
            return back()->withErrors(['otp' => 'Terlalu banyak percobaan. Silakan kirim ulang kode.']);
        }

        if (! Hash::check($request->otp, $row->code)) {
            DB::table('email_otps')->where('id', $row->id)->increment('attempts');
            return back()->withErrors(['otp' => 'Kode OTP salah.']);
        }

        DB::transaction(function () use ($userId) {
            DB::table('users')->where('id', $userId)->update([
                'email_verified_at' => now(),
                'is_active'         => 1,
                'updated_at'        => now(),
            ]);
            DB::table('email_otps')->where('user_id', $userId)->delete();
        });

        session()->forget('otp_user_id');

        return redirect()->route('login')
            ->with('status', 'Email berhasil diverifikasi. Silakan login.');
    }

    public function resend(OtpService $otp)
    {
        $userId = session('otp_user_id');
        if (! $userId) {
            return redirect()->route('login');
        }

        $last = DB::table('email_otps')->where('user_id', $userId)->first();
        if ($last && Carbon::parse($last->created_at)->gt(now()->subSeconds(60))) {
            return back()->withErrors(['otp' => 'Tunggu 60 detik sebelum mengirim ulang kode.']);
        }

        $otp->send(DB::table('users')->find($userId));

        return back()->with('status', 'Kode OTP baru telah dikirim.');
    }
}