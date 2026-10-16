@extends('layouts.app')
@section('title', 'Alamat Saya')
@section('content')
<main class="max-w-full mx-auto space-y-4">
    @include('partials.alerts')

    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold">Alamat Pengiriman</h1>
        <a href="{{ route('customer.alamat.create') }}"
           class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">+ Tambah Alamat</a>
    </div>

    @forelse ($alamat as $a)
        <div class="bg-white rounded-2xl shadow p-5 {{ $a->is_default ? 'ring-2 ring-orange-400' : '' }}">
            <div class="flex items-start justify-between gap-4">
                <div class="text-sm">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold">{{ $a->label }}</span>
                        @if ($a->is_default)
                            <span class="text-xs font-medium bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full">Utama</span>
                        @endif
                    </div>
                    <p class="mt-2">{{ $a->nama_penerima }} &middot; {{ $a->telp }}</p>
                    <p class="text-gray-600">{{ $a->alamat }}, {{ $a->kota }}</p>
                </div>
                <div class="text-sm whitespace-nowrap text-right space-y-1">
                    @unless ($a->is_default)
                        <form method="POST" action="{{ route('customer.alamat.default', $a->id) }}">
                            @csrf @method('PATCH')
                            <button class="text-gray-600 hover:text-orange-600">Jadikan utama</button>
                        </form>
                    @endunless
                    <a href="{{ route('customer.alamat.edit', $a->id) }}" class="block text-orange-600">Edit</a>
                    <form method="POST" action="{{ route('customer.alamat.destroy', $a->id) }}"
                          onsubmit="return confirm('Hapus alamat ini?')">
                        @csrf @method('DELETE')
                        <button class="text-red-600">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl shadow p-10 text-center text-gray-400">Belum ada alamat.</div>
    @endforelse
</main>
@endsection