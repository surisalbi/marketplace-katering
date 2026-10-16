@extends('layouts.app')
@section('title', $invoice->no_invoice)
@section('content')

<main class="max-w-3xl mx-auto p-4 space-y-4">
    <div class="flex items-center justify-between print:hidden">
        <a href="{{ route('customer.pesanan.show', $invoice->pesanan_id) }}" class="text-sm text-gray-500 hover:text-orange-600">&larr; Kembali ke pesanan</a>
        <button onclick="window.print()" class="text-sm bg-gray-900 text-white px-4 py-2 rounded-lg">Cetak / Simpan PDF</button>
    </div>

    <section class="bg-white rounded-2xl shadow-sm print:shadow-none p-8">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-wide">INVOICE</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $invoice->no_invoice }}</p>
            </div>
            @include('customer.invoice._status', ['status' => $invoice->status])
        </div>

        <div class="grid sm:grid-cols-2 gap-6 mt-8 text-sm">
            <div>
                <p class="text-gray-400 text-xs uppercase mb-1">Dari</p>
                <p class="font-semibold">{{ $invoice->nama_merchant }}</p>
                <p class="text-gray-600">{{ $invoice->alamat_merchant }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs uppercase mb-1">Kepada</p>
                <p class="font-semibold">{{ $invoice->nama_customer }}</p>
                <p class="text-gray-600">{{ $invoice->alamat_customer }}</p>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4 mt-6 text-sm bg-gray-50 rounded-xl p-4">
            <div><p class="text-gray-400 text-xs uppercase">Tanggal terbit</p>
                <p class="font-medium">{{ \Carbon\Carbon::parse($invoice->issue_date)->translatedFormat('d F Y') }}</p></div>
            <div><p class="text-gray-400 text-xs uppercase">Jatuh tempo</p>
                <p class="font-medium">{{ \Carbon\Carbon::parse($invoice->due_date)->translatedFormat('d F Y') }}</p></div>
            <div><p class="text-gray-400 text-xs uppercase">No. pesanan</p>
                <p class="font-medium">{{ $pesanan->no_pesanan }}</p></div>
        </div>

        <table class="w-full text-sm mt-6">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="py-2">Menu</th>
                    <th class="py-2 text-right">Harga</th>
                    <th class="py-2 text-right">Qty</th>
                    <th class="py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($items as $i)
                    <tr>
                        <td class="py-3">{{ $i->nama_menu }}</td>
                        <td class="py-3 text-right">Rp {{ number_format($i->harga_menu, 0, ',', '.') }}</td>
                        <td class="py-3 text-right">{{ $i->qty }}</td>
                        <td class="py-3 text-right">Rp {{ number_format($i->total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4 ml-auto sm:w-72 text-sm space-y-2">
            <div class="flex justify-between"><span class="text-gray-600">Subtotal</span><span>Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span class="text-gray-600">Biaya pengiriman</span>
                <span>{{ $invoice->biaya_pengiriman > 0 ? 'Rp ' . number_format($invoice->biaya_pengiriman, 0, ',', '.') : 'Gratis' }}</span></div>
            @if ($invoice->nominal_pajak > 0)
                <div class="flex justify-between"><span class="text-gray-600">Pajak</span><span>Rp {{ number_format($invoice->nominal_pajak, 0, ',', '.') }}</span></div>
            @endif
            <div class="flex justify-between text-base font-bold pt-2 border-t">
                <span>Total tagihan</span><span class="text-orange-600">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</span>
            </div>
            @if ($invoice->paid_at)
                <p class="text-xs text-green-600 text-right">Dibayar pada {{ \Carbon\Carbon::parse($invoice->paid_at)->translatedFormat('d F Y, H:i') }}</p>
            @endif
        </div>
    </section>

    <section class="bg-white rounded-2xl shadow-sm p-6 space-y-4 print:hidden">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold">Pembayaran</h2>
            @if ($bolehBayar)
                <a href="{{ route('customer.pembayaran.create', $invoice->id) }}"
                class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-xl">Bayar Sekarang</a>
            @endif
        </div>

        {{-- @if ($invoice->status === 'unpaid' && ! $lewatTempo && $pesanan->status === 'pending')
            <p class="text-sm text-gray-500">Pembayaran dapat dilakukan setelah pesanan dikonfirmasi merchant.</p>
        @endif --}}

        @if ($lewatTempo && ! $pembayaran->contains('status', 'pending'))
            <p class="rounded-xl bg-red-50 border border-red-200 text-sm text-red-700 p-3">
                Invoice ini sudah melewati jatuh tempo ({{ \Carbon\Carbon::parse($invoice->due_date)->translatedFormat('d F Y') }}) dan tidak dapat dibayar.
                Silakan hubungi merchant.
            </p>
        @endif

        @forelse ($pembayaran as $b)
            <div class="rounded-xl border p-4 text-sm flex items-start justify-between gap-3">
                <div>
                    <p class="font-medium">{{ $b->metode === 'transfer' ? 'Transfer bank' : 'Tunai' }} &middot; Rp {{ number_format($b->nominal, 0, ',', '.') }}</p>
                    <p class="text-gray-500">{{ \Carbon\Carbon::parse($b->created_at)->translatedFormat('d F Y, H:i') }}
                        @if ($b->reference_no) &middot; Ref: {{ $b->reference_no }} @endif</p>
                    @if ($b->bukti)
                        <a href="{{ asset($b->bukti) }}" target="_blank" class="text-orange-600 hover:underline">Lihat bukti</a>
                    @endif
                    @if ($b->status === 'failed' && $b->alasan_tolak)
                        <p class="text-red-600 mt-1">Alasan ditolak: {{ $b->alasan_tolak }}</p>
                    @endif
                </div>
                @include('customer.pembayaran._status', ['status' => $b->status])
            </div>
        @empty
            <p class="text-sm text-gray-500">Belum ada pembayaran.</p>
        @endforelse
    </section>
</main>
@endsection