@extends('layouts.app')
@section('title', 'Keranjang Pesanan')
@section('content')
@php $input = 'w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400'; @endphp

<main class="max-w-6xl mx-auto p-4 space-y-5">
    @include('partials.alerts')
    <h1 class="text-2xl font-bold">Pesanan Anda</h1>

    @if ($cart['items']->isEmpty())
        <section class="bg-white rounded-2xl shadow-sm p-12 text-center">
            <div class="text-5xl mb-3">🛒</div>
            <h2 class="font-semibold text-lg">Keranjang masih kosong</h2>
            <p class="text-sm text-gray-500 mt-1">Pilih menu katering favorit Anda untuk mulai memesan.</p>
            <a href="{{ route('customer.katering.index') }}"
               class="inline-block mt-5 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-5 py-2.5 rounded-xl">Cari Katering</a>
        </section>
    @else
        <div class="grid lg:grid-cols-3 gap-6 items-start">

            {{-- Daftar item --}}
            <section class="lg:col-span-2 space-y-4">
                <div class="bg-white rounded-2xl shadow-sm p-5">
                    <p class="text-sm text-gray-500">Dipesan dari</p>
                    <p class="font-semibold">{{ $cart['merchant']->nama_usaha }}
                        <span class="text-gray-400 font-normal">&middot; 📍 {{ $cart['merchant']->kab_kota ?: '-' }}</span></p>
                </div>

                <form method="POST" action="{{ route('customer.keranjang.update') }}" id="form-items"
                      class="bg-white rounded-2xl shadow-sm divide-y">
                    @csrf
                    @foreach ($cart['items'] as $i)
                        <div class="p-5 flex gap-4">
                            @if ($i->foto)
                                <img src="{{ asset($i->foto) }}" class="w-20 h-20 rounded-xl object-cover" alt="">
                            @else
                                <div class="w-20 h-20 rounded-xl bg-orange-50 flex items-center justify-center text-3xl">🍱</div>
                            @endif

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold">{{ $i->nama }}</p>
                                        <p class="text-sm text-gray-500">Rp {{ number_format($i->harga, 0, ',', '.') }} / porsi</p>
                                    </div>
                                    <p class="font-bold text-orange-600 whitespace-nowrap">Rp {{ number_format($i->total, 0, ',', '.') }}</p>
                                </div>

                                <div class="mt-3 flex flex-wrap items-center gap-3">
                                    <div class="inline-flex items-center border rounded-xl overflow-hidden">
                                        <button type="button" onclick="stepItem(this, -1)" class="px-3 py-1.5 text-lg hover:bg-gray-100">&minus;</button>
                                        <input type="number" name="qty[{{ $i->menu_id }}]" value="{{ $i->qty }}" min="1" max="1000"
                                               onchange="this.form.submit()" class="w-16 text-center py-1.5 focus:outline-none">
                                        <button type="button" onclick="stepItem(this, 1)" class="px-3 py-1.5 text-lg hover:bg-gray-100">+</button>
                                    </div>
                                    <button type="submit" formaction="{{ route('customer.keranjang.remove', $i->menu_id) }}"
                                            class="text-sm text-red-600 hover:underline">Hapus</button>
                                </div>

                                <input type="text" name="catatan[{{ $i->menu_id }}]" value="{{ $i->catatan }}" maxlength="255"
                                       onchange="this.form.submit()" placeholder="Catatan untuk menu ini (opsional)"
                                       class="mt-3 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                            </div>
                        </div>
                    @endforeach
                </form>

                <div class="flex items-center justify-between text-sm">
                    <a href="{{ route('customer.katering.index', ['merchant' => $cart['merchant']->id]) }}" class="text-orange-600 hover:underline">+ Tambah menu lain</a>
                    <form method="POST" action="{{ route('customer.keranjang.clear') }}" onsubmit="return confirm('Kosongkan keranjang?')">
                        @csrf
                        <button class="text-gray-500 hover:text-red-600">Kosongkan keranjang</button>
                    </form>
                </div>
            </section>

            {{-- Pengiriman + ringkasan --}}
            <aside class="lg:sticky lg:top-20">
                <form method="POST" action="{{ route('customer.pesanan.store') }}" class="bg-white rounded-2xl shadow-sm p-5 space-y-4">
                    @csrf
                    <h2 class="font-semibold">Detail Pengiriman</h2>

                    {{-- Alamat --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-medium">Alamat pengiriman</label>
                        @forelse ($alamat as $a)
                            <label class="flex gap-3 rounded-xl border p-3 cursor-pointer has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50">
                                <input type="radio" name="alamat_id" value="{{ $a->id }}" class="mt-1"
                                       @checked(old('alamat_id', $alamat->firstWhere('is_default', 1)->id ?? null) == $a->id)>
                                <span class="text-sm">
                                    <span class="font-medium">{{ $a->label }}</span>
                                    @if ($a->is_default)<span class="text-[10px] bg-orange-100 text-orange-700 px-1.5 py-0.5 rounded-full ml-1">Utama</span>@endif
                                    <span class="block text-gray-600">{{ $a->nama_penerima }} &middot; {{ $a->telp }}</span>
                                    <span class="block text-gray-500">{{ $a->alamat }}, {{ $a->kota }}</span>
                                </span>
                            </label>
                        @empty
                            <div class="rounded-xl bg-yellow-50 border border-yellow-200 text-sm text-yellow-800 p-3">
                                Anda belum punya alamat.
                                <a href="{{ route('customer.alamat.create') }}" class="font-semibold underline">Tambah alamat</a> dulu.
                            </div>
                        @endforelse
                        @if ($alamat->count())
                            <a href="{{ route('customer.alamat.create') }}" class="text-xs text-orange-600 hover:underline">+ Alamat baru</a>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Tanggal</label>
                            <input type="date" name="tgl_pengiriman" min="{{ $minTanggal }}" value="{{ old('tgl_pengiriman') }}" required class="{{ $input }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Jam <span class="text-gray-400 font-normal">(opsional)</span></label>
                            <input type="time" name="jam_pengiriman" value="{{ old('jam_pengiriman') }}" class="{{ $input }}">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Catatan pesanan <span class="text-gray-400 font-normal">(opsional)</span></label>
                        <textarea name="catatan" rows="2" maxlength="500" class="{{ $input }}"
                                  placeholder="Contoh: antar ke resepsionis lantai 2">{{ old('catatan') }}</textarea>
                    </div>

                    {{-- Ringkasan --}}
                    <div class="border-t pt-4 space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-600">Subtotal</span><span>Rp {{ number_format($cart['subtotal'], 0, ',', '.') }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Biaya pengiriman</span>
                            <span>{{ $cart['ongkir'] > 0 ? 'Rp ' . number_format($cart['ongkir'], 0, ',', '.') : 'Gratis' }}</span></div>
                        @if ($cart['pajak'] > 0)
                            <div class="flex justify-between"><span class="text-gray-600">Pajak</span><span>Rp {{ number_format($cart['pajak'], 0, ',', '.') }}</span></div>
                        @endif
                        <div class="flex justify-between text-base font-bold pt-2 border-t">
                            <span>Total</span><span class="text-orange-600">Rp {{ number_format($cart['grand'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <button @disabled($alamat->isEmpty())
                            class="w-full bg-orange-500 hover:bg-orange-600 disabled:bg-gray-300 text-white font-semibold rounded-xl py-3">Buat Pesanan</button>
                    <p class="text-xs text-gray-400 text-center">Pesanan akan dikonfirmasi oleh merchant terlebih dahulu.</p>
                </form>
            </aside>
        </div>
    @endif
</main>

<script>
function stepItem(btn, delta) {
    var input = btn.parentElement.querySelector('input');
    input.value = Math.max(1, Math.min(1000, (parseInt(input.value) || 1) + delta));
    input.form.submit();
}
</script>
@endsection