@extends('layouts.app')
@section('title', $menu ? 'Edit Menu' : 'Tambah Menu')
@section('content')
@php
    $input = 'w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400';
    $action = $menu ? route('merchant.menu.update', $menu->id) : route('merchant.menu.store');
@endphp
<main class="max-w-full mx-auto">
    <a href="{{ route('merchant.menu.index') }}" class="text-sm text-gray-500">&larr; Kembali</a>
    <div class="bg-white rounded-2xl shadow p-8 mt-3">
        <h1 class="text-xl font-bold mb-6">{{ $menu ? 'Edit Menu' : 'Tambah Menu' }}</h1>
        @include('partials.alerts')

        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @if ($menu) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium mb-1">Nama Menu</label>
                <input type="text" name="nama" value="{{ old('nama', $menu->nama ?? '') }}" class="{{ $input }}" required>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Kategori</label>
                    <select name="kategori_menu_id" class="{{ $input }}" required>
                        <option value="">Pilih kategori</option>
                        @foreach ($kategori as $k)
                            <option value="{{ $k->id }}" @selected(old('kategori_menu_id', $menu->kategori_menu_id ?? '') == $k->id)>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Harga (Rp)</label>
                    <input type="number" name="harga" min="0" step="500" value="{{ old('harga', isset($menu) ? (int) $menu->harga : '') }}" class="{{ $input }}" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Deskripsi</label>
                <textarea name="deskripsi" rows="4" class="{{ $input }}">{{ old('deskripsi', $menu->deskripsi ?? '') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Foto Menu (maks 2MB)</label>
                @if ($menu && $menu->foto)
                    <img src="{{ asset($menu->foto) }}" class="w-24 h-24 rounded-lg object-cover mb-2" alt="">
                @endif
                <input type="file" name="foto" accept="image/*" class="block w-full text-sm">
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_available" value="1" @checked(old('is_available', $menu->is_available ?? 1))>
                Tersedia untuk dipesan
            </label>

            <button class="bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg px-6 py-2.5">Simpan</button>
        </form>
    </div>
</main>
@endsection