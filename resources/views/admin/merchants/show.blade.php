@extends('layouts.app')
@section('title', 'Detail Merchant')
@section('content')
<div class="max-w-full mx-auto py-6">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Detail Merchant</h1>
            <p class="text-sm text-gray-500">{{ $merchant->nama_usaha ?? '-' }}</p>
        </div>
        <a href="{{ route('admin.merchants.index') }}"
           class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
            &larr; Kembali
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-100 border border-green-300 text-green-800 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-100 border border-red-300 text-red-800 px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="space-y-6">

        {{-- Informasi Usaha --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-6 sm:flex-row">
                <div class="shrink-0">
                    @if ($merchant->foto)
                        <img src="{{ asset($merchant->foto) }}" alt="{{ $merchant->nama_usaha }}"
                             class="h-32 w-32 rounded-lg object-cover border border-gray-200">
                    @else
                        <div class="flex h-32 w-32 items-center justify-center rounded-lg bg-gray-100 text-3xl font-bold text-gray-400">
                            {{ strtoupper(substr($merchant->nama_usaha ?? '?', 0, 1)) }}
                        </div>
                    @endif
                </div>

                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="text-xl font-semibold text-gray-800">{{ $merchant->nama_usaha ?? '-' }}</h2>
                        @if ($merchant->is_active)
                            <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span> Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> Nonaktif
                            </span>
                        @endif
                    </div>

                    <p class="mt-3 text-sm text-gray-600 whitespace-pre-line">{{ $merchant->deskripsi ?: 'Belum ada deskripsi.' }}</p>

                    <form method="POST" action="{{ route('admin.merchants.toggle', $merchant->id) }}" class="mt-4"
                          onsubmit="return confirm('Yakin ingin {{ $merchant->is_active ? 'menonaktifkan' : 'mengaktifkan' }} merchant ini?')">
                        @csrf
                        @method('PATCH')
                        @if ($merchant->is_active)
                            <button type="submit"
                                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                                Nonaktifkan Merchant
                            </button>
                        @else
                            <button type="submit"
                                    class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                                Aktifkan Merchant
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        {{-- Kontak & Alamat --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-base font-semibold text-gray-800">Kontak & Alamat</h3>
            <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-gray-500">Pemilik (Akun)</dt>
                    <dd class="font-medium text-gray-800">{{ $merchant->pemilik }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Email Akun</dt>
                    <dd class="font-medium text-gray-800">{{ $merchant->email_akun }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Email Usaha</dt>
                    <dd class="font-medium text-gray-800">{{ $merchant->email ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">No. Telepon</dt>
                    <dd class="font-medium text-gray-800">{{ $merchant->no_telepon ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Provinsi</dt>
                    <dd class="font-medium text-gray-800">{{ $merchant->provinsi ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Kabupaten/Kota</dt>
                    <dd class="font-medium text-gray-800">{{ $merchant->kab_kota ?? '-' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-gray-500">Alamat</dt>
                    <dd class="font-medium text-gray-800">{{ $merchant->alamat ?? '-' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Rekening Bank --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-base font-semibold text-gray-800">Rekening Bank</h3>
            <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-3">
                <div>
                    <dt class="text-gray-500">Nama Bank</dt>
                    <dd class="font-medium text-gray-800">{{ $merchant->bank_nama ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">No. Rekening</dt>
                    <dd class="font-medium text-gray-800">{{ $merchant->bank_no_rekening ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Atas Nama</dt>
                    <dd class="font-medium text-gray-800">{{ $merchant->bank_atas_nama ?? '-' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Info Sistem --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-base font-semibold text-gray-800">Info Sistem</h3>
            <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-3">
                <div>
                    <dt class="text-gray-500">Status Akun User</dt>
                    <dd class="font-medium {{ $merchant->akun_aktif ? 'text-green-700' : 'text-red-700' }}">
                        {{ $merchant->akun_aktif ? 'Aktif' : 'Nonaktif' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500">Terdaftar</dt>
                    <dd class="font-medium text-gray-800">
                        {{ $merchant->created_at ? \Carbon\Carbon::parse($merchant->created_at)->format('d M Y H:i') : '-' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500">Terakhir Diubah</dt>
                    <dd class="font-medium text-gray-800">
                        {{ $merchant->updated_at ? \Carbon\Carbon::parse($merchant->updated_at)->format('d M Y H:i') : '-' }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection