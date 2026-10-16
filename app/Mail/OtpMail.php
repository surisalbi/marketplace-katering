<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class OtpMail extends Mailable
{
    public function __construct(public string $name, public string $code) {}

    public function build()
    {
        return $this->subject('Kode Verifikasi Akun Merchant')
            ->view('emails.otp');
    }
}