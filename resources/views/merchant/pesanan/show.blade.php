@extends('layouts.app')
@section('title', $pesanan->no_pesanan)
@section('content')
@php $input = 'w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400'; @endphp
<main class="max-w-full mx-auto space-y-5">
    <a href="{{ route('merchant.pesanan.index') }}" class="text-sm text-gray-500 hover:text-orange-600">&larr; Semua pesanan</a>
    @include('partials.alerts')

    <section class="bg-white rounded-2xl shadow-sm p-6">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold">{{ $pesanan->no_pesanan }}</h1>
                <p class="text-sm text-gray-500">{{ $pesanan->nama_kantor }} &middot; PIC {{ $pesanan->nama_pic }} ({{ $pesanan->customer_telp }})</p>
            </div>
            @include('customer.pesanan._status', ['status' => $pesanan->status])
        </div>

        @if (in_array($pesanan->status, ['cancelled', 'rejected']) && $pesanan->alasan_batal)
            <p class="mt-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700 p-3">Alasan: {{ $pesanan->alasan_batal }}</p>
        @endif

        {{-- Aksi status --}}
        <div class="mt-5 space-y-3">
            @php
                $aksi = [
                    'confirmed'  => ['preparing',  'Mulai Siapkan Pesanan'],
                    'preparing'  => ['delivering', 'Tandai Sedang Dikirim'],
                    'delivering' => ['completed',  'Tandai Selesai'],
                ];
            @endphp

            @if ($pesanan->status === 'pending')
                <form method="POST" action="{{ route('merchant.pesanan.status', $pesanan->id) }}">
                    @csrf <input type="hidden" name="status" value="confirmed">
                    <button class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl px-5 py-2.5">Terima Pesanan</button>
                </form>
                <details>
                    <summary class="cursor-pointer text-sm text-red-600 font-medium">Tolak pesanan</summary>
                    <form method="POST" action="{{ route('merchant.pesanan.status', $pesanan->id) }}" class="mt-3 space-y-3">
                        @csrf <input type="hidden" name="status" value="rejected">
                        <input type="text" name="alasan_batal" maxlength="255" required placeholder="Alasan penolakan" class="{{ $input }}">
                        <button class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl px-5 py-2.5">Tolak Pesanan</button>
                    </form>
                </details>
            @elseif (isset($aksi[$pesanan->status]))
                <form method="POST" action="{{ route('merchant.pesanan.status', $pesanan->id) }}">
                    @csrf <input type="hidden" name="status" value="{{ $aksi[$pesanan->status][0] }}">
                    <button class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-xl px-5 py-2.5">{{ $aksi[$pesanan->status][1] }}</button>
                </form>
            @endif
        </div>
    </section>

    <div class="grid md:grid-cols-2 gap-5">
        <section class="bg-white rounded-2xl shadow-sm p-6 text-sm space-y-1">
            <h2 class="font-semibold mb-2">Jadwal Pengiriman</h2>
            <p>{{ \Carbon\Carbon::parse($pesanan->tgl_pengiriman)->translatedFormat('l, d F Y') }}
               @if ($pesanan->jam_pengiriman) &middot; pukul {{ substr($pesanan->jam_pengiriman, 0, 5) }} @endif</p>
            @if ($pesanan->catatan)<p class="text-gray-500 pt-2">Catatan: {{ $pesanan->catatan }}</p>@endif
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
        <div class="bg-gray-50 px-6 py-4 text-sm flex justify-between font-bold text-base">
            <span>Total</span><span class="text-orange-600">Rp {{ number_format($pesanan->grand_total, 0, ',', '.') }}</span>
        </div>
    </section>

    {{-- Pembayaran --}}
    @if ($invoice)
        <section class="bg-white rounded-2xl shadow-sm p-6 space-y-4">
            <div class="flex items-start justify-between gap-3">
                <div class="text-sm">
                    <h2 class="font-semibold">Pembayaran</h2>
                    <p class="text-gray-500">{{ $invoice->no_invoice }} &middot; jatuh tempo {{ \Carbon\Carbon::parse($invoice->due_date)->translatedFormat('d F Y') }}</p>
                </div>
                @include('customer.invoice._status', ['status' => $invoice->status])
            </div>

            @forelse ($pembayaran as $b)
                <div class="rounded-xl border p-4 text-sm space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-medium">{{ $b->metode === 'transfer' ? 'Transfer bank' : 'Tunai' }} &middot; Rp {{ number_format($b->nominal, 0, ',', '.') }}</p>
                            <p class="text-gray-500">
                                {{ \Carbon\Carbon::parse($b->created_at)->translatedFormat('d F Y, H:i') }}
                                @if ($b->paid_at) &middot; dibayar {{ \Carbon\Carbon::parse($b->paid_at)->translatedFormat('d F Y, H:i') }} @endif
                                @if ($b->reference_no) &middot; Ref: {{ $b->reference_no }} @endif
                            </p>
                            @if ($b->bukti)
                                <a href="{{ asset($b->bukti) }}" target="_blank" class="text-orange-600 hover:underline">Lihat bukti transfer</a>
                            @endif
                            @if ($b->status === 'failed' && $b->alasan_tolak)
                                <p class="text-red-600 mt-1">Ditolak: {{ $b->alasan_tolak }}</p>
                            @endif
                        </div>
                        @include('customer.pembayaran._status', ['status' => $b->status])
                    </div>

                    @if ($b->status === 'pending')
                        <div class="flex flex-wrap items-start gap-3">
                            <form method="POST" action="{{ route('merchant.pembayaran.verify', $b->id) }}"
                                  onsubmit="return confirm('{{ $b->metode === 'cash' ? 'Pastikan uang tunai sudah Anda terima. Lanjutkan?' : 'Pastikan dana sudah masuk ke rekening Anda. Lanjutkan?' }}')">
                                @csrf
                                <button class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl px-4 py-2">Verifikasi</button>
                            </form>
                            <details class="flex-1 min-w-60">
                                <summary class="cursor-pointer text-sm text-red-600 font-medium py-2">Tolak</summary>
                                <form method="POST" action="{{ route('merchant.pembayaran.reject', $b->id) }}" class="mt-2 space-y-2">
                                    @csrf
                                    <input type="text" name="alasan_tolak" maxlength="255" required placeholder="Alasan penolakan" class="{{ $input }}">
                                    <button class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl px-4 py-2">Tolak Pembayaran</button>
                                </form>
                            </details>
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500">Belum ada pembayaran dari customer.</p>
            @endforelse
        </section>
    @endif
</main>
@endsection