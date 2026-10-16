@extends('layouts.app')
@section('title', 'Pesanan Masuk')
@section('content')
@php
    $tabs = [
        'baru'     => ['Baru',      $counts['pending'] ?? 0],
        'diproses' => ['Diproses',  ($counts['confirmed'] ?? 0) + ($counts['preparing'] ?? 0) + ($counts['delivering'] ?? 0)],
        'selesai'  => ['Selesai',   $counts['completed'] ?? 0],
        'batal'    => ['Dibatalkan', ($counts['cancelled'] ?? 0) + ($counts['rejected'] ?? 0)],
        'semua'    => ['Semua',     null],
    ];
@endphp
<main class="max-w-full mx-auto space-y-5">
    @include('partials.alerts')
    <h1 class="text-2xl font-bold">Pesanan Masuk</h1>

    <div class="flex gap-2 overflow-x-auto">
        @foreach ($tabs as $key => [$label, $n])
            <a href="{{ route('merchant.pesanan.index', ['tab' => $key]) }}"
               class="whitespace-nowrap text-sm px-4 py-2 rounded-full border transition
               {{ $tab === $key ? 'bg-orange-500 border-orange-500 text-white font-semibold' : 'bg-white border-gray-200 text-gray-700 hover:border-orange-400' }}">
                {{ $label }}@if ($n) ({{ $n }})@endif
            </a>
        @endforeach
    </div>

    @forelse ($pesanan as $p)
        <a href="{{ route('merchant.pesanan.show', $p->id) }}" class="block bg-white rounded-2xl shadow-sm hover:shadow-md transition p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="font-semibold">{{ $p->no_pesanan }}</p>
                    <p class="text-sm text-gray-500">{{ $p->nama_kantor }}</p>
                </div>
                <div class="flex flex-col items-end gap-1">
                    @include('customer.pesanan._status', ['status' => $p->status])
                    @if ($p->invoice_status)
                        @include('customer.invoice._status', ['status' => $p->invoice_status])
                    @endif
                </div>
            </div>
            <div class="mt-3 flex items-end justify-between text-sm">
                <p class="text-gray-500">Kirim: {{ \Carbon\Carbon::parse($p->tgl_pengiriman)->translatedFormat('d F Y') }}
                    @if ($p->jam_pengiriman) &middot; {{ substr($p->jam_pengiriman, 0, 5) }} @endif</p>
                <p class="font-bold text-orange-600">Rp {{ number_format($p->grand_total, 0, ',', '.') }}</p>
            </div>
            @if ($p->bayar_pending > 0)
                <p class="mt-3 text-xs font-medium text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-2">Ada pembayaran yang perlu diverifikasi</p>
            @endif
        </a>
    @empty
        <div class="bg-white rounded-2xl shadow-sm p-12 text-center">
            <div class="text-5xl mb-3">📭</div>
            <p class="font-semibold">Belum ada pesanan di sini</p>
        </div>
    @endforelse

    {{ $pesanan->links() }}
</main>
@endsection