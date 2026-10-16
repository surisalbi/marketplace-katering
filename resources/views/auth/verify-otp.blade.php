@extends('layouts.app_auth')
@section('title', 'Verifikasi OTP')
@section('content')
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow p-8">
        <h1 class="text-2xl font-bold mb-1">Verifikasi Email</h1>
        <p class="text-sm text-gray-500 mb-6">Masukkan 6 digit kode yang dikirim ke <b>{{ $email }}</b>.</p>
        @include('partials.alerts')
        <form method="POST" action="{{ route('otp.verify') }}" class="space-y-4">
            @csrf
            <input type="text" name="otp" inputmode="numeric" maxlength="6" autocomplete="one-time-code" autofocus required
                   class="w-full text-center text-3xl tracking-[0.5em] rounded-lg border border-gray-300 px-3 py-3 focus:outline-none focus:ring-2 focus:ring-orange-400">
            <button class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg py-2.5">Verifikasi</button>
        </form>
        <form method="POST" action="{{ route('otp.resend') }}" class="text-center mt-4">
            @csrf
            <button class="text-sm text-orange-600 hover:underline">Kirim ulang kode</button>
        </form>
    </div>
</div>
@endsection