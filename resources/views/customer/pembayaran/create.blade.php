@extends('layouts.app')
@section('title', 'Bayar Invoice')
@section('content')
@include('partials.customer-nav')
@php $input = 'w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400'; @endphp
@php $adaRekening = filled($merchant->bank_no_rekening); @endphp

<main class="max-w-xl mx-auto p-4 space-y-5">
    <a href="{{ route('customer.invoice.show', $inv->id) }}" class="text-sm text-gray-500 hover:text-orange-600">&larr; Kembali ke invoice</a>
    @include('partials.alerts')

    <section class="bg-white rounded-2xl shadow-sm p-6">
        <p class="text-sm text-gray-500">{{ $inv->no_invoice }} &middot; {{ $inv->nama_merchant }}</p>
        <p class="text-3xl font-bold text-orange-600 mt-1">Rp {{ number_format($inv->grand_total, 0, ',', '.') }}</p>
        <p class="text-xs text-gray-500 mt-1">Jatuh tempo {{ \Carbon\Carbon::parse($inv->due_date)->translatedFormat('d F Y') }}</p>
    </section>

    <form method="POST" action="{{ route('customer.pembayaran.store', $inv->id) }}" enctype="multipart/form-data"
          class="bg-white rounded-2xl shadow-sm p-6 space-y-5" id="form-bayar">
        @csrf

        <div class="space-y-2">
            <p class="text-sm font-medium">Metode pembayaran</p>
            <label class="flex gap-3 rounded-xl border p-4 cursor-pointer has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 {{ $adaRekening ? '' : 'opacity-50 cursor-not-allowed' }}">
                <input type="radio" name="metode" value="transfer" class="mt-1" @checked(old('metode', $adaRekening ? 'transfer' : 'cash') === 'transfer') @disabled(! $adaRekening)>
                <span class="text-sm"><span class="font-medium">Transfer bank</span>
                    <span class="block text-gray-500">Unggah bukti transfer untuk diverifikasi katering.</span>
                    @unless ($adaRekening)<span class="block text-xs text-red-600">Katering belum menyediakan rekening.</span>@endunless
                </span>
            </label>
            <label class="flex gap-3 rounded-xl border p-4 cursor-pointer has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50">
                <input type="radio" name="metode" value="cash" class="mt-1" @checked(old('metode', $adaRekening ? 'transfer' : 'cash') === 'cash')>
                <span class="text-sm"><span class="font-medium">Tunai</span>
                    <span class="block text-gray-500">Bayar langsung ke katering saat pesanan diterima.</span></span>
            </label>
        </div>

        {{-- Panel transfer --}}
        <div id="panel-transfer" class="space-y-4">
            @if ($adaRekening)
                <div class="rounded-xl bg-gray-50 border p-4 text-sm">
                    <p class="text-gray-500">Transfer ke</p>
                    <p class="font-semibold">{{ $merchant->bank_nama }} &middot; <span id="norek">{{ $merchant->bank_no_rekening }}</span></p>
                    <p class="text-gray-600">a.n. {{ $merchant->bank_atas_nama }}</p>
                    <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('norek').textContent.trim()); this.textContent='Tersalin'"
                            class="mt-2 text-xs text-orange-600 hover:underline">Salin no. rekening</button>
                    <p class="mt-3 text-xs text-gray-500">Transfer sesuai nominal tagihan: <b>Rp {{ number_format($inv->grand_total, 0, ',', '.') }}</b></p>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium mb-1">Waktu transfer</label>
                <input type="datetime-local" name="paid_at" value="{{ old('paid_at') }}" max="{{ now()->format('Y-m-d\TH:i') }}" class="{{ $input }}">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">No. referensi <span class="text-gray-400 font-normal">(opsional)</span></label>
                <input type="text" name="reference_no" value="{{ old('reference_no') }}" maxlength="100" class="{{ $input }}">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Bukti transfer (JPG, PNG, atau PDF, maks 2MB)</label>
                <input type="file" name="bukti" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-sm">
            </div>
        </div>

        {{-- Panel tunai --}}
        <div id="panel-cash" class="hidden rounded-xl bg-yellow-50 border border-yellow-200 text-sm text-yellow-800 p-4">
            Siapkan uang sebesar <b>Rp {{ number_format($inv->grand_total, 0, ',', '.') }}</b>. Pembayaran dianggap lunas setelah katering mengonfirmasi uang diterima.
        </div>

        <button class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl py-3">Selesai</button>
    </form>
</main>

<script>
(function () {
    var form = document.getElementById('form-bayar');
    function sync() {
        var m = form.querySelector('input[name=metode]:checked');
        var isTransfer = m && m.value === 'transfer';
        document.getElementById('panel-transfer').classList.toggle('hidden', !isTransfer);
        document.getElementById('panel-cash').classList.toggle('hidden', isTransfer);
    }
    form.querySelectorAll('input[name=metode]').forEach(function (r) { r.addEventListener('change', sync); });
    sync();
})();
</script>
@endsection