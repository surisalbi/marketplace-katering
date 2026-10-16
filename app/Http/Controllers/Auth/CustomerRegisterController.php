<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerRegisterController extends Controller
{
    public function create()
    {
        return view('auth.customer-register');
    }

    public function store(Request $request, OtpService $otp)
    {
        $data = $request->validate([
            'nama_kantor' => 'required|string|max:150',
            'nama_pic'    => 'required|string|max:100',
            'telp'        => ['required', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'email'       => 'required|email|max:255|unique:users,email',
            'password'    => 'required|string|min:8|confirmed',
        ]);

        $userId = DB::transaction(function () use ($data) {
            $now = now();

            $userId = DB::table('users')->insertGetId([
                'name'       => $data['nama_pic'],
                'email'      => $data['email'],
                'password'   => Hash::make($data['password']),
                'role'       => 'customer',
                'is_active'  => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('customer')->insert([
                'user_id'     => $userId,
                'nama_kantor' => $data['nama_kantor'],
                'nama_pic'    => $data['nama_pic'],
                'telp'        => $data['telp'],
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