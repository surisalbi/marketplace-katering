@extends('layouts.app')
@section('title', $alamat ? 'Edit Alamat' : 'Tambah Alamat')
@section('content')
@php
    $input  = 'w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400';
    $action = $alamat ? route('customer.alamat.update', $alamat->id) : route('customer.alamat.store');
@endphp
<main class="max-w-full mx-auto p-4">
    <a href="{{ route('customer.alamat.index') }}" class="text-sm text-gray-500">&larr; Kembali</a>
    <div class="bg-white rounded-2xl shadow p-8 mt-3">
        <h1 class="text-xl font-bold mb-6">{{ $alamat ? 'Edit Alamat' : 'Tambah Alamat' }}</h1>
        @include('partials.alerts')

        <form method="POST" action="{{ $action }}" class="space-y-4">
            @csrf
            @if ($alamat) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium mb-1">Label Alamat</label>
                <input type="text" name="label" placeholder="Contoh: Kantor Pusat, Gudang"
                       value="{{ old('label', $alamat->label ?? '') }}" class="{{ $input }}" required>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Nama Penerima</label>
                    <input type="text" name="nama_penerima" value="{{ old('nama_penerima', $alamat->nama_penerima ?? '') }}" class="{{ $input }}" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">No. Telepon Penerima</label>
                    <input type="text" name="telp" value="{{ old('telp', $alamat->telp ?? '') }}" class="{{ $input }}" required>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Kota</label>
                <input type="text" name="kota" value="{{ old('kota', $alamat->kota ?? '') }}" class="{{ $input }}" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Alamat Lengkap</label>
                <textarea name="alamat" rows="3" class="{{ $input }}" required>{{ old('alamat', $alamat->alamat ?? '') }}</textarea>
            </div>

            @if (! $alamat || ! $alamat->is_default)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_default" value="1" @checked(old('is_default'))>
                    Jadikan alamat utama
                </label>
            @else
                <p class="text-xs text-gray-500">Ini alamat utama. Untuk menggantinya, jadikan alamat lain sebagai utama.</p>
            @endif

            <button class="bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg px-6 py-2.5">Simpan</button>
        </form>
    </div>
</main>
@endsection