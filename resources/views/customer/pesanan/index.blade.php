@extends('layouts.app')
@section('title', 'Pesanan Saya')
@section('content')

@php
    $tabs = ['semua' => 'Semua', 'berjalan' => 'Berjalan', 'selesai' => 'Selesai', 'batal' => 'Dibatalkan'];
@endphp
<main class="max-w-full mx-auto space-y-5">
    @include('partials.alerts')
    <h1 class="text-2xl font-bold">Pesanan Saya</h1>

    <div class="flex gap-2 overflow-x-auto">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('customer.pesanan.index', ['tab' => $key]) }}"
               class="whitespace-nowrap text-sm px-4 py-2 rounded-full border transition
               {{ $tab === $key ? 'bg-orange-500 border-orange-500 text-white font-semibold' : 'bg-white border-gray-200 text-gray-700 hover:border-orange-400' }}">{{ $label }}</a>
        @endforeach
    </div>

    @forelse ($pesanan as $p)
        <a href="{{ route('customer.pesanan.show', $p->id) }}" class="block bg-white rounded-2xl shadow-sm hover:shadow-md transition p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="font-semibold">{{ $p->no_pesanan }}</p>
                    <p class="text-sm text-gray-500">{{ $p->nama_usaha }} &middot; {{ $p->jumlah_item }} menu</p>
                </div>
                @include('customer.pesanan._status', ['status' => $p->status])
            </div>
            <div class="mt-3 flex items-end justify-between text-sm">
                <p class="text-gray-500">Pengiriman: {{ \Carbon\Carbon::parse($p->tgl_pengiriman)->translatedFormat('d F Y') }}
                    @if ($p->jam_pengiriman) &middot; {{ substr($p->jam_pengiriman, 0, 5) }} @endif</p>
                <p class="font-bold text-orange-600">Rp {{ number_format($p->grand_total, 0, ',', '.') }}</p>
            </div>
        </a>
    @empty
        <div class="bg-white rounded-2xl shadow-sm p-12 text-center">
            <div class="text-5xl mb-3">📦</div>
            <p class="font-semibold">Belum ada pesanan</p>
            <a href="{{ route('customer.katering.index') }}" class="inline-block mt-4 text-orange-600 hover:underline text-sm">Mulai pesan katering &rarr;</a>
        </div>
    @endforelse

    {{ $pesanan->links() }}
</main>
@endsection