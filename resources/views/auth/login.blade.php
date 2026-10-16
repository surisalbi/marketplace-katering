@extends('layouts.app_auth')
@section('title', 'Login')
@section('content')
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow p-8">
        <h1 class="text-2xl font-bold mb-6">Login</h1>
        @include('partials.alerts')
        <form method="POST" action="{{ url('/login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400">
            </div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember"> Ingat saya</label>
            <button class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg py-2.5">Masuk</button>
        </form>
        <p class="text-sm text-center text-gray-500 mt-6">Belum punya akun?
          <a href="{{ route('merchant.register') }}" class="text-orange-600 font-medium">Daftar sebagai merchant</a>
        </p>
        <p class="text-sm text-center text-gray-500 mt-2">Ingin memesan katering?
          <a href="{{ route('customer.register') }}" class="text-orange-600 font-medium">Daftar sebagai customer</a>
        </p>
    </div>
</div>
@endsection