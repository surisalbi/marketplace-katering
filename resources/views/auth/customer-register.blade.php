@extends('layouts.app_auth')
@section('title', 'Daftar Customer')
@section('content')
@php $input = 'w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400'; @endphp
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow p-8">
        <h1 class="text-2xl font-bold mb-1">Daftar Customer</h1>
        <p class="text-sm text-gray-500 mb-6">Pesan katering untuk kantor Anda.</p>
        @include('partials.alerts')
        <form method="POST" action="{{ url('/register') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Nama Kantor / Instansi</label>
                <input type="text" name="nama_kantor" value="{{ old('nama_kantor') }}" class="{{ $input }}" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Nama PIC</label>
                <input type="text" name="nama_pic" value="{{ old('nama_pic') }}" class="{{ $input }}" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">No. Telepon</label>
                <input type="text" name="telp" value="{{ old('telp') }}" class="{{ $input }}" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="{{ $input }}" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" class="{{ $input }}" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="{{ $input }}" required>
            </div>
            <button class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg py-2.5">Daftar</button>
        </form>
        <p class="text-sm text-center text-gray-500 mt-6">Sudah punya akun?
            <a href="{{ route('login') }}" class="text-orange-600 font-medium">Login</a></p>
    </div>
</div>
@endsection