@extends('layouts.app')
@section('title', 'Dashboard Merchant')
@section('content')

<main class="max-w-full mx-auto p-4 space-y-6">
    @include('partials.alerts')

    {{-- Status verifikasi --}}
    @if ($merchant->is_active == 1)
        <div class="rounded-2xl border border-green-200 bg-green-50 p-5 flex items-start gap-3">
            <span class="text-green-600 text-xl">
                <i class="las la-check-circle"></i>
            </span>
            <div>
                <p class="font-semibold text-green-800">Terverifikasi</p>
                <p class="text-sm text-green-700">Usaha Anda sudah diverifikasi dan dapat tampil di marketplace.</p>
            </div>
        </div>
    @elseif ($percent < 100)
        <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-5 flex items-start gap-3">
            <span class="text-yellow-600 text-xl">
                <i class="las la-exclamation-circle"></i>
            </span>
            <div>
                <p class="font-semibold text-yellow-800">Menunggu Verifikasi</p>
                <p class="text-sm text-yellow-700">Lengkapi data usaha Anda agar dapat kami proses untuk verifikasi.</p>
                <a href="{{ route('merchant.profile.edit') }}"
                   class="inline-block mt-3 bg-orange-500 hover:bg-orange-600 text-white text-sm px-4 py-2 rounded-lg">Lengkapi Data</a>
            </div>
        </div>
    @else
        <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-5 flex items-start gap-3">
            <span class="text-yellow-600 text-xl">
                <i class="las la-exclamation-circle"></i>
            </span>
            <div>
                <p class="font-semibold text-yellow-800">Menunggu Verifikasi</p>
                <p class="text-sm text-yellow-700">Data usaha Anda sudah lengkap dan sedang ditinjau oleh admin.</p>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex items-center justify-between mb-2">
            <h2 class="font-semibold">Kelengkapan Profil Usaha</h2>
            <span class="text-sm font-medium">{{ $percent }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2.5">
            <div class="bg-orange-500 h-2.5 rounded-full" style="width: {{ $percent }}%"></div>
        </div>
        @if ($percent < 100)
            <p class="text-sm text-gray-500 mt-3">Lengkapi data usaha agar toko Anda bisa tampil di marketplace.</p>
            <a href="{{ route('merchant.profile.edit') }}" class="inline-block mt-3 bg-orange-500 text-white text-sm px-4 py-2 rounded-lg">Lengkapi Data</a>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex items-start justify-between">
            <h2 class="font-semibold mb-4">Data Merchant</h2>
            <a href="{{ route('merchant.profile.edit') }}" class="text-sm text-orange-600">Edit</a>
        </div>
        <div class="flex gap-6">
            @if ($merchant->foto)
                <img src="{{ asset($merchant->foto) }}" class="w-24 h-24 rounded-lg object-cover mb-2" alt="Foto">
            @else
                <div class="w-28 h-28 rounded-xl bg-gray-100 flex items-center justify-center text-xs text-gray-400">Belum ada foto</div>
            @endif
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm flex-1">
                <div><dt class="text-gray-500">Nama Usaha</dt><dd>{{ $merchant->nama_usaha ?: '-' }}</dd></div>
                <div><dt class="text-gray-500">No. Telepon</dt><dd>{{ $merchant->no_telepon ?: '-' }}</dd></div>
                <div><dt class="text-gray-500">Email</dt><dd>{{ $merchant->email ?: '-' }}</dd></div>
                <div><dt class="text-gray-500">Lokasi</dt><dd>{{ $merchant->kab_kota ?: '-' }}, {{ $merchant->provinsi ?: '-' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-gray-500">Alamat</dt><dd>{{ $merchant->alamat ?: '-' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-gray-500">Deskripsi</dt><dd>{{ $merchant->deskripsi ?: '-' }}</dd></div>
            </dl>
        </div>
    </div>
</main>
@endsection