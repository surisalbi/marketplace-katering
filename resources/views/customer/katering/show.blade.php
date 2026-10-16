@extends('layouts.app')
@section('title', $menu->nama)
@section('content')

<main class="max-w-6xl mx-auto p-4 space-y-8">
    @include('partials.alerts')
    <a href="{{ route('customer.katering.index') }}" class="text-sm text-gray-500 hover:text-orange-600">&larr; Kembali ke Katering</a>

    <section class="bg-white rounded-3xl shadow-sm overflow-hidden grid md:grid-cols-2">
        <div class="aspect-[4/3] md:aspect-auto bg-orange-50">
            @if ($menu->foto)
                <img src="{{ asset($menu->foto) }}" alt="{{ $menu->nama }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full min-h-64 flex items-center justify-center text-7xl">🍱</div>
            @endif
        </div>

        <div class="p-6 sm:p-8 flex flex-col">
            <span class="self-start text-xs font-medium bg-orange-100 text-orange-700 px-3 py-1 rounded-full">{{ $menu->kategori_nama }}</span>
            <h1 class="text-2xl font-bold mt-3">{{ $menu->nama }}</h1>
            <p class="text-3xl font-bold text-orange-600 mt-2">Rp {{ number_format($menu->harga, 0, ',', '.') }}</p>

            <div class="mt-5">
                <h2 class="text-sm font-semibold text-gray-500 mb-1">Deskripsi</h2>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $menu->deskripsi ?: 'Belum ada deskripsi untuk menu ini.' }}</p>
            </div>

            <form method="POST" action="{{ route('customer.keranjang.add') }}" id="order-form"
                data-harga="{{ (float) $menu->harga }}"
                class="mt-6 rounded-2xl border border-orange-200 bg-orange-50 p-4 space-y-3">
                @csrf
                <input type="hidden" name="menu_id" value="{{ $menu->id }}">

                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm font-medium">Jumlah porsi</span>
                    <div class="inline-flex items-center bg-white border rounded-xl overflow-hidden">
                        <button type="button" data-step="-1" class="px-3 py-2 text-lg hover:bg-gray-100">&minus;</button>
                        <input type="number" name="qty" id="qty" value="{{ old('qty', 1) }}" min="1" max="1000" required
                            class="w-16 text-center py-2 focus:outline-none">
                        <button type="button" data-step="1" class="px-3 py-2 text-lg hover:bg-gray-100">+</button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Perkiraan subtotal</span>
                    <span id="subtotal" class="font-bold text-orange-600"></span>
                </div>

                <button class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl py-3">Pesan</button>
            </form>

            <script>
            (function () {
                var f = document.getElementById('order-form'),
                    q = document.getElementById('qty'),
                    s = document.getElementById('subtotal'),
                    h = parseFloat(f.dataset.harga);

                function clamp(v) { return Math.max(1, Math.min(1000, parseInt(v) || 1)); }
                function update() { s.textContent = 'Rp ' + (clamp(q.value) * h).toLocaleString('id-ID'); }

                f.querySelectorAll('[data-step]').forEach(function (b) {
                    b.addEventListener('click', function () {
                        q.value = clamp((parseInt(q.value) || 1) + parseInt(b.dataset.step));
                        update();
                    });
                });
                q.addEventListener('input', update);
                update();
            })();
            </script>

            {{-- Info merchant --}}
            <div class="mt-auto pt-6">
                <div class="rounded-2xl bg-gray-50 border p-4 flex items-center gap-4">
                    @if ($menu->merchant_foto)
                        <img src="{{ asset($menu->merchant_foto) }}" class="w-14 h-14 rounded-xl object-cover" alt="">
                    @else
                        <div class="w-14 h-14 rounded-xl bg-orange-100 flex items-center justify-center text-2xl">🏪</div>
                    @endif
                    <div class="text-sm">
                        <p class="font-semibold">{{ $menu->nama_usaha }}</p>
                        <p class="text-gray-500">📍 {{ $menu->kab_kota ?: '-' }}, {{ $menu->provinsi ?: '-' }}</p>
                        <span class="inline-block mt-1 text-xs font-medium bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Terverifikasi</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($lainnya->count())
        <section>
            <h2 class="text-lg font-bold mb-4">Menu lain dari {{ $menu->nama_usaha }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ($lainnya as $m)
                    @include('customer.katering._card', ['m' => $m])
                @endforeach
            </div>
        </section>
    @endif
</main>
@endsection