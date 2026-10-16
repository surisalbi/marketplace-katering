@php
    $map = [
        'pending'  => ['Menunggu Verifikasi', 'bg-yellow-100 text-yellow-700'],
        'verified' => ['Terverifikasi',       'bg-green-100 text-green-700'],
        'failed'   => ['Ditolak',             'bg-red-100 text-red-700'],
    ];
    [$label, $cls] = $map[$status] ?? [$status, 'bg-gray-100 text-gray-600'];
@endphp
<span class="text-xs font-medium px-2.5 py-1 rounded-full whitespace-nowrap {{ $cls }}">{{ $label }}</span>