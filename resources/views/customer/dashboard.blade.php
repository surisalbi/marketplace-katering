@extends('layouts.app')
@section('title', 'Dashboard Customer')
@section('content')
<main class="max-w-full mx-auto space-y-6">
    @include('partials.alerts')

    <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-gray-500">Selamat datang,</p>
                <h1 class="text-xl font-bold">{{ $customer->nama_kantor }}</h1>
                <p class="text-sm text-gray-600 mt-1">PIC: {{ $customer->nama_pic }} &middot; {{ $customer->telp }}</p>
            </div>
            <a href="{{ route('customer.profile.edit') }}" class="text-sm text-orange-600">Edit</a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex items-start justify-between mb-3">
            <h2 class="font-semibold">Alamat Pengiriman <span class="text-gray-400 font-normal">({{ $alamatCount }})</span></h2>
            <a href="{{ route('customer.alamat.index') }}" class="text-sm text-orange-600">Kelola</a>
        </div>

        @if ($alamatDefault)
            <div class="text-sm">
                <span class="text-xs font-medium bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full">Utama</span>
                <span class="font-medium ml-1">{{ $alamatDefault->label }}</span>
                <p class="mt-2">{{ $alamatDefault->nama_penerima }} &middot; {{ $alamatDefault->telp }}</p>
                <p class="text-gray-600">{{ $alamatDefault->alamat }}, {{ $alamatDefault->kota }}</p>
            </div>
        @else
            <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-4 text-sm text-yellow-800">
                Anda belum punya alamat pengiriman.
                <a href="{{ route('customer.alamat.create') }}" class="font-semibold underline">Tambah alamat</a>
            </div>
        @endif
    </div>
</main>
@endsection