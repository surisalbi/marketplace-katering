@extends('layouts.app')
@section('title', 'Katering')
@section('content')

@php
    $field = 'w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400';
    $activeKategori = request('kategori');
@endphp

<main class="max-w-6xl mx-auto p-4 space-y-5">
    @include('partials.alerts')

    {{-- Header + pencarian --}}
    <section class="bg-gradient-to-r from-orange-500 to-amber-500 rounded-3xl p-6 sm:p-8 text-white">
        <h1 class="text-2xl sm:text-3xl font-bold">Cari katering favorit Anda</h1>
        <p class="text-orange-50 text-sm mt-1">Pilih menu dari katering terverifikasi di kota Anda.</p>

        <form method="GET" action="{{ route('customer.katering.index') }}" class="mt-5 space-y-3">
            @if ($activeKategori)
                <input type="hidden" name="kategori" value="{{ $activeKategori }}">
            @endif

            @if (request('merchant'))
                <input type="hidden" name="merchant" value="{{ request('merchant') }}">
            @endif

            <div class="flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari menu atau nama katering..."
                       class="flex-1 rounded-xl px-4 py-3 text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-white/60">
                <button class="bg-gray-900 hover:bg-black text-white text-sm font-semibold px-6 rounded-xl">Cari</button>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-gray-800">
                <select name="kota" onchange="this.form.submit()" class="{{ $field }}">
                    <option value="">📍 Semua kota</option>
                    @foreach ($kotaList as $kota)
                        <option value="{{ $kota }}" @selected(request('kota') === $kota)>{{ $kota }}</option>
                    @endforeach
                </select>
                <select name="sort" onchange="this.form.submit()" class="{{ $field }}">
                    <option value="terbaru"  @selected(request('sort', 'terbaru') === 'terbaru')>Terbaru</option>
                    <option value="termurah" @selected(request('sort') === 'termurah')>Harga termurah</option>
                    <option value="termahal" @selected(request('sort') === 'termahal')>Harga termahal</option>
                </select>
                <input type="number" name="min" min="0" step="1000" value="{{ request('min') }}" placeholder="Harga min (Rp)" class="{{ $field }}">
                <input type="number" name="max" min="0" step="1000" value="{{ request('max') }}" placeholder="Harga maks (Rp)" class="{{ $field }}">
            </div>
        </form>
    </section>

    {{-- Chip kategori --}}
    <section class="flex gap-2 overflow-x-auto pb-1 -mx-4 px-4 sm:mx-0 sm:px-0">
        @php
            $chip = fn ($on) => 'whitespace-nowrap text-sm px-4 py-2 rounded-full border transition '
                . ($on ? 'bg-orange-500 border-orange-500 text-white font-semibold' : 'bg-white border-gray-200 text-gray-700 hover:border-orange-400');
        @endphp
        <a href="{{ request()->fullUrlWithQuery(['kategori' => null, 'page' => null]) }}" class="{{ $chip(! $activeKategori) }}">Semua</a>
        @foreach ($kategori as $k)
            <a href="{{ request()->fullUrlWithQuery(['kategori' => $k->id, 'page' => null]) }}"
               class="{{ $chip($activeKategori == $k->id) }}">{{ $k->nama }}</a>
        @endforeach
    </section>

    @if ($merchantFilter)
        <div class="rounded-xl bg-orange-50 border border-orange-200 text-sm px-4 py-3 flex items-center justify-between">
            <span>Menampilkan menu dari <b>{{ $merchantFilter }}</b></span>
            <a href="{{ request()->fullUrlWithQuery(['merchant' => null, 'page' => null]) }}" class="text-orange-600 hover:underline">Lihat semua katering</a>
        </div>
    @endif

    {{-- Ringkasan hasil --}}
    <div class="flex items-center justify-between text-sm text-gray-600">
        <p>Menampilkan <b>{{ $menus->total() }}</b> menu</p>
        @if ($hasFilter)
            <a href="{{ route('customer.katering.index') }}" class="text-orange-600 hover:underline">Reset filter</a>
        @endif
    </div>

    {{-- Grid menu --}}
    @if ($menus->count())
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach ($menus as $m)
                @include('customer.katering._card', ['m' => $m])
            @endforeach
        </section>

        <div>{{ $menus->links() }}</div>
    @else
        <section class="bg-white rounded-2xl shadow-sm p-12 text-center">
            <div class="text-5xl mb-3">🔍</div>
            <h2 class="font-semibold text-lg">Menu tidak ditemukan</h2>
            <p class="text-sm text-gray-500 mt-1">Coba ubah kata kunci, kategori, atau rentang harga Anda.</p>
            <a href="{{ route('customer.katering.index') }}"
               class="inline-block mt-5 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-5 py-2.5 rounded-xl">Lihat semua menu</a>
        </section>
    @endif
</main>
@endsection