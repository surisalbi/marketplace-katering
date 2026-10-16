<?php

namespace App\Services;

use App\Mail\OtpMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    public function send(object $user): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('email_otps')->where('user_id', $user->id)->delete();
        DB::table('email_otps')->insert([
            'user_id'    => $user->id,
            'code'       => Hash::make($code),   // disimpan ter-hash
            'attempts'   => 0,
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
        ]);

        Mail::to($user->email)->send(new OtpMail($user->name, $code));
    }
}