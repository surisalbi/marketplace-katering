@extends('layouts.app')
@section('title', 'Profil Customer')
@section('content')
@php $input = 'w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400'; @endphp
<main class="max-w-full mx-auto">
    <div class="bg-white rounded-2xl shadow p-8 mt-3">
        <h1 class="text-xl font-bold mb-6">Profil</h1>
        @include('partials.alerts')
        <form method="POST" action="{{ route('customer.profile.update') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Nama Kantor / Instansi</label>
                <input type="text" name="nama_kantor" value="{{ old('nama_kantor', $customer->nama_kantor) }}" class="{{ $input }}" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Nama PIC</label>
                <input type="text" name="nama_pic" value="{{ old('nama_pic', $customer->nama_pic) }}" class="{{ $input }}" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">No. Telepon</label>
                <input type="text" name="telp" value="{{ old('telp', $customer->telp) }}" class="{{ $input }}" required>
            </div>
            <button class="bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg px-6 py-2.5">Simpan</button>
        </form>
    </div>
</main>
@endsection