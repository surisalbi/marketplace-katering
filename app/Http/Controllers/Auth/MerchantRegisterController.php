<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MerchantRegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request, OtpService $otp)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:50|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $userId = DB::transaction(function () use ($data) {
            $now = now();

            $userId = DB::table('users')->insertGetId([
                'name'       => $data['name'],
                'email'      => $data['email'],
                'password'   => Hash::make($data['password']),
                'role'       => 'merchant',
                'is_active'  => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // baris merchant dibuat kosong, dilengkapi nanti di dashboard
            DB::table('merchant')->insert([
                'user_id' => $userId,
                'email'   => $data['email'],
                'is_active' => 0,
            ]);

            return $userId;
        });

        $user = DB::table('users')->find($userId);
        $otp->send($user);

        session(['otp_user_id' => $userId]);

        return redirect()->route('otp.form')
            ->with('status', 'Kode OTP telah dikirim ke ' . $user->email);
    }
}