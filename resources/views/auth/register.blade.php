@extends('layouts.app_auth')
@section('title', 'Daftar Merchant')
@section('content')
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow p-8">
        <h1 class="text-2xl font-bold mb-1">Daftar Merchant</h1>
        <p class="text-sm text-gray-500 mb-6">Mulai jual katering Anda.</p>
        @include('partials.alerts')
        <form method="POST" action="{{ url('/merchant/register') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Nama</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" required
                       class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" required
                       class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400">
            </div>
            <button class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg py-2.5">Daftar</button>
        </form>
        <p class="text-sm text-center text-gray-500 mt-6">Sudah punya akun?
            <a href="{{ route('login') }}" class="text-orange-600 font-medium">Login</a></p>
    </div>
</div>
@endsection