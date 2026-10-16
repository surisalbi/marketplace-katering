@extends('layouts.app')
@section('title', $pesanan->no_pesanan)
@section('content')
@php
    $steps = [
        'pending'    => 'Menunggu',
        'confirmed'  => 'Dikonfirmasi',
        'preparing'  => 'Disiapkan',
        'delivering' => 'Dikirim',
        'completed'  => 'Selesai',
    ];
    $keys    = array_keys($steps);
    $current = array_search($pesanan->status, $keys);
    $ended   = in_array($pesanan->status, ['cancelled', 'rejected']);
@endphp
<main class="max-w-full mx-auto space-y-5">
    <a href="{{ route('customer.pesanan.index') }}" class="text-sm text-gray-500 hover:text-orange-600">&larr; Semua pesanan</a>
    @include('partials.alerts')

    <section class="bg-white rounded-2xl shadow-sm p-6">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold">{{ $pesanan->no_pesanan }}</h1>
                <p class="text-sm text-gray-500">{{ $pesanan->nama_usaha }} &middot; dibuat {{ \Carbon\Carbon::parse($pesanan->created_at)->translatedFormat('d F Y, H:i') }}</p>
            </div>
            @include('customer.pesanan._status', ['status' => $pesanan->status])
        </div>

        @if ($ended)
            <div class="mt-5 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700 p-4">
                Pesanan {{ $pesanan->status === 'rejected' ? 'ditolak oleh katering' : 'dibatalkan' }}.
                @if ($pesanan->alasan_batal) <br>Alasan: {{ $pesanan->alasan_batal }} @endif
            </div>
        @else
            <ol class="mt-6 grid grid-cols-5 gap-2 text-center text-xs">
                @foreach ($steps as $key => $label)
                    @php $done = $current !== false && array_search($key, $keys) <= $current; @endphp
                    <li>
                        <div class="h-1.5 rounded-full {{ $done ? 'bg-orange-500' : 'bg-gray-200' }}"></div>
                        <span class="block mt-2 {{ $done ? 'text-orange-600 font-medium' : 'text-gray-400' }}">{{ $label }}</span>
                    </li>
                @endforeach
            </ol>
        @endif
    </section>

    <div class="grid md:grid-cols-2 gap-5">
        <section class="bg-white rounded-2xl shadow-sm p-6 text-sm space-y-1">
            <h2 class="font-semibold mb-2">Jadwal Pengiriman</h2>
            <p>{{ \Carbon\Carbon::parse($pesanan->tgl_pengiriman)->translatedFormat('l, d F Y') }}
               @if ($pesanan->jam_pengiriman) &middot; pukul {{ substr($pesanan->jam_pengiriman, 0, 5) }} @endif</p>
            @if ($pesanan->catatan)
                <p class="text-gray-500 pt-2">Catatan: {{ $pesanan->catatan }}</p>
            @endif
        </section>

        <section class="bg-white rounded-2xl shadow-sm p-6 text-sm space-y-1">
            <h2 class="font-semibold mb-2">Alamat Pengiriman</h2>
            <p class="font-medium">{{ $pesanan->nama_penerima }} &middot; {{ $pesanan->telp_penerima }}</p>
            <p class="text-gray-600">{{ $pesanan->alamat_penerima }}, {{ $pesanan->kota_penerima }}</p>
        </section>
    </div>

    <section class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <h2 class="font-semibold p-6 pb-3">Rincian Pesanan</h2>
        <div class="divide-y">
            @foreach ($items as $i)
                <div class="px-6 py-3 flex justify-between gap-4 text-sm">
                    <div>
                        <p class="font-medium">{{ $i->nama_menu }}</p>
                        <p class="text-gray-500">{{ $i->qty }} &times; Rp {{ number_format($i->harga_menu, 0, ',', '.') }}</p>
                        @if ($i->catatan)<p class="text-xs text-gray-400">Catatan: {{ $i->catatan }}</p>@endif
                    </div>
                    <p class="font-medium whitespace-nowrap">Rp {{ number_format($i->total, 0, ',', '.') }}</p>
                </div>
            @endforeach
        </div>
        <div class="bg-gray-50 px-6 py-4 text-sm space-y-2">
            <div class="flex justify-between"><span class="text-gray-600">Subtotal</span><span>Rp {{ number_format($pesanan->subtotal, 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span class="text-gray-600">Biaya pengiriman</span>
                <span>{{ $pesanan->biaya_pengiriman > 0 ? 'Rp ' . number_format($pesanan->biaya_pengiriman, 0, ',', '.') : 'Gratis' }}</span></div>
            @if ($pesanan->nominal_pajak > 0)
                <div class="flex justify-between"><span class="text-gray-600">Pajak</span><span>Rp {{ number_format($pesanan->nominal_pajak, 0, ',', '.') }}</span></div>
            @endif
            <div class="flex justify-between text-base font-bold pt-2 border-t">
                <span>Total</span><span class="text-orange-600">Rp {{ number_format($pesanan->grand_total, 0, ',', '.') }}</span>
            </div>
        </div>
    </section>

    @if ($invoice)
        <section class="bg-white rounded-2xl shadow-sm p-6 flex flex-wrap items-center justify-between gap-4">
            <div class="text-sm">
                <p class="text-gray-500">Invoice</p>
                <p class="font-semibold">{{ $invoice->no_invoice }}</p>
                <p class="text-gray-500 mt-1">Jatuh tempo: {{ \Carbon\Carbon::parse($invoice->due_date)->translatedFormat('d F Y') }}</p>
            </div>
            <div class="flex items-center gap-3">
                @include('customer.invoice._status', ['status' => $invoice->status])
                <a href="{{ route('customer.invoice.show', $invoice->id) }}"
                class="text-sm bg-orange-500 hover:bg-orange-600 text-white font-semibold px-4 py-2 rounded-xl">Lihat Invoice</a>
            </div>
        </section>
    @endif

    @if ($pesanan->status === 'pending')
        <details class="bg-white rounded-2xl shadow-sm p-6">
            <summary class="cursor-pointer text-sm text-red-600 font-medium">Batalkan pesanan</summary>
            <form method="POST" action="{{ route('customer.pesanan.cancel', $pesanan->id) }}" class="mt-4 space-y-3"
                  onsubmit="return confirm('Yakin batalkan pesanan ini?')">
                @csrf
                <input type="text" name="alasan_batal" maxlength="255" required placeholder="Alasan pembatalan"
                       class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                <button class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl px-5 py-2.5">Batalkan Pesanan</button>
            </form>
        </details>
    @endif
</main>
@endsection