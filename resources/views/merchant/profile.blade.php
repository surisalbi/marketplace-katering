@extends('layouts.app')
@section('title', 'Lengkapi Data Merchant')
@section('content')
<main class="max-w-full mx-auto p-4">
    <a href="{{ route('merchant.dashboard') }}" class="text-sm text-gray-500">&larr; Kembali</a>
    <div class="bg-white rounded-2xl shadow p-8 mt-3">
        <h1 class="text-xl font-bold mb-6">Data Merchant</h1>
        @include('partials.alerts')

        <form method="POST" action="{{ route('merchant.profile.update') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @php $input = 'w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400'; @endphp

            <div>
                <label class="block text-sm font-medium mb-1">Nama Usaha</label>
                <input type="text" name="nama_usaha" value="{{ old('nama_usaha', $merchant->nama_usaha) }}" class="{{ $input }}" required>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">No. Telepon</label>
                    <input type="text" name="no_telepon" value="{{ old('no_telepon', $merchant->no_telepon) }}" class="{{ $input }}" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Email Usaha</label>
                    <input type="email" name="email" value="{{ old('email', $merchant->email) }}" class="{{ $input }}" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Provinsi</label>
                    <input type="text" name="provinsi" value="{{ old('provinsi', $merchant->provinsi) }}" class="{{ $input }}" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Kabupaten/Kota</label>
                    <input type="text" name="kab_kota" value="{{ old('kab_kota', $merchant->kab_kota) }}" class="{{ $input }}" required>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Alamat Lengkap</label>
                <input type="text" name="alamat" value="{{ old('alamat', $merchant->alamat) }}" class="{{ $input }}" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Deskripsi Usaha</label>
                <textarea name="deskripsi" rows="4" class="{{ $input }}">{{ old('deskripsi', $merchant->deskripsi) }}</textarea>
            </div>
            <div class="rounded-xl border border-gray-200 p-4 space-y-3">
                <div>
                    <p class="text-sm font-medium">Rekening untuk menerima transfer</p>
                    <p class="text-xs text-gray-500">Ditampilkan ke customer saat memilih pembayaran transfer.</p>
                </div>
                <div class="grid sm:grid-cols-3 gap-3">
                    <input type="text" name="bank_nama" value="{{ old('bank_nama', $merchant->bank_nama) }}" placeholder="Nama bank (BCA, Mandiri...)" class="{{ $input }}">
                    <input type="text" name="bank_no_rekening" value="{{ old('bank_no_rekening', $merchant->bank_no_rekening) }}" placeholder="No. rekening" class="{{ $input }}">
                    <input type="text" name="bank_atas_nama" value="{{ old('bank_atas_nama', $merchant->bank_atas_nama) }}" placeholder="Atas nama" class="{{ $input }}">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Foto Usaha (maks 2MB)</label>
                @if ($merchant->foto)
                    <img src="{{ asset($merchant->foto) }}" class="w-28 h-28 rounded-xl object-cover mb-2" alt="Foto usaha">
                @else
                    <div class="w-28 h-28 rounded-xl bg-gray-100 flex items-center justify-center text-xs text-gray-400 mb-2">Belum ada foto</div>
                @endif
                <input type="file" name="foto" accept="image/*" class="block w-full text-sm">
            </div>
            <button class="bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg px-6 py-2.5">Simpan</button>
        </form>
    </div>
</main>
@endsection